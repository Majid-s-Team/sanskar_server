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
        ]);

        $user = User::findOrFail($request->user_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
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

            $user = User::find($userId);
            if ($user) {
                $user->update(['is_payment_done' => true]);

                $user->payments()->create([
                    'amount' => $session->amount_total / 100,
                    'currency' => $session->currency,
                    'payment_id' => $session->id,
                    'payment_method' => 'stripe',
                    'status' => 'completed',
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}