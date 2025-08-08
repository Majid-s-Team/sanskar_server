<?php

// app/Http/Controllers/API/PaymentController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
class PaymentController extends Controller
{

    public function createStripeSession(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3', // e.g. usd, inr, eur
        ]);

        $user = User::findOrFail($request->user_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => strtolower($request->currency),
                        'product_data' => ['name' => 'Membership Fee'],
                        'unit_amount' => $request->amount * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => env('APP_URL') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('APP_URL') . '/payment/cancel',
            'metadata' => ['user_id' => $user->id],
        ]);

        Payment::create([
            'user_id' => $user->id,
            'payment_id' => $session->id,
            'amount' => $request->amount,
            'currency' => strtolower($request->currency),
            'payment_method' => 'stripe',
            'status' => 'pending',
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function handleStripeWebhook(Request $request)
    {
        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $userId = $session->metadata->user_id ?? null;
            $paymentIntentId = $session->payment_intent;

            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);

            $payment = Payment::where('payment_id', $session->id)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'currency' => $session->currency,
                    'payment_method' => $paymentMethod->type,
                    'card_brand' => $paymentMethod->card->brand ?? null,
                    'card_last4' => $paymentMethod->card->last4 ?? null,
                    'card_exp_month' => $paymentMethod->card->exp_month ?? null,
                    'card_exp_year' => $paymentMethod->card->exp_year ?? null,
                    'billing_name' => $paymentMethod->billing_details->name ?? null,
                    'billing_email' => $paymentMethod->billing_details->email ?? null,
                    'billing_country' => $paymentMethod->billing_details->address->country ?? null,
                    'billing_city' => $paymentMethod->billing_details->address->city ?? null,
                    'billing_line1' => $paymentMethod->billing_details->address->line1 ?? null,
                    'billing_postal_code' => $paymentMethod->billing_details->address->postal_code ?? null,
                ]);
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $intent = $event->data->object;
            $payment = Payment::where('payment_id', $intent->id)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $intent->last_payment_error->message ?? 'Unknown error',
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}