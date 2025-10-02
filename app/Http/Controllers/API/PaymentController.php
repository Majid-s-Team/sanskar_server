<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class PaymentController extends Controller
{

public function createStripeSession(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'student_id' => 'required|exists:students,id',
        'amount' => 'required|numeric|min:0.5',
        'currency' => 'required|string|size:3',
    ]);

    $user = User::findOrFail($request->user_id);
    $student = Student::findOrFail($request->student_id);
    if ($student->user_id !== $user->id) {
        return response()->json([
            'status' => false,
            'error' => 'Student does not belong to this user.'
        ], 400);
    }


    $unitAmount = (int) round($request->amount * 100);

    Stripe::setApiKey(config('services.stripe.secret'));

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => strtolower($request->currency),
                'product_data' => ['name' => 'Registration Fee'],
                'unit_amount' => $unitAmount, // in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',

        'success_url' => url('/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
        // 'success_url' => 'https://sanskar-ruddy.vercel.app/login',
        // 'cancel_url'  => url('/payment/cancel'),
        // 'cancel_url'  => url('/payment/cancel'),
        'cancel_url'  => 'https://sanskaracademy-dev.org/',
        'metadata' => ['user_id' => $user->id,'student_id' => $student->id],
    ]);


    Payment::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'payment_id' => $session->id,
        'amount' => $unitAmount,
        'currency' => strtolower($request->currency),
        'payment_method' => 'stripe',
        'status' => 'pending',
    ]);

    return response()->json(['url' => $session->url]);
}
public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            abort(404, 'Session ID missing.');
        }

Stripe::setApiKey(config('services.stripe.secret'));
$session = Session::retrieve($sessionId);

        // Optional: verify payment status
        if ($session->payment_status === 'paid') {
            return view('success', ['session' => $session]);
        }

        return abort(404, 'Payment not found or not paid.');
    }

    public function cancel()
    {
        return view('payment.success');
    }

   public function handleStripeWebhook(Request $request)
{
    \Log::info('Stripe webhook hit', ['headers' => $request->headers->all(), 'payload' => $request->all()]);

    Stripe::setApiKey(config('services.stripe.secret'));

    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $secret = config('services.stripe.webhook_secret');

    try {
        $event = Webhook::constructEvent($payload, $sig_header, $secret);
    } catch (\UnexpectedValueException $e) {
        \Log::error('Invalid payload', ['err' => $e->getMessage()]);
        return response()->json(['error' => 'Invalid payload'], 400);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        \Log::error('Invalid signature', ['err' => $e->getMessage()]);
        return response()->json(['error' => 'Invalid signature'], 400);
    } catch (\Exception $e) {
        \Log::error('Webhook error', ['err' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 400);
    }


   if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $paymentIntentId = $session->payment_intent ?? null;

    try {
        if ($paymentIntentId) {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentMethod = PaymentMethod::retrieve($paymentIntent->payment_method);
        } else {
            $paymentIntent = null;
            $paymentMethod = null;
        }
    } catch (\Exception $e) {
        \Log::warning('Failed to retrieve paymentIntent/paymentMethod', ['err' => $e->getMessage()]);
        $paymentIntent = null;
        $paymentMethod = null;
    }

    $payment = Payment::where('payment_id', $session->id)->first();
    if ($payment) {
        $payment->update([
            'status' => 'completed',
            'currency' => $session->currency ?? $payment->currency,
            'payment_method' => $paymentMethod->type ?? $payment->payment_method,
            'card_brand' => $paymentMethod->card->brand ?? $payment->card_brand ?? null,
            'card_last4' => $paymentMethod->card->last4 ?? $payment->card_last4 ?? null,
            'card_exp_month' => $paymentMethod->card->exp_month ?? $payment->card_exp_month ?? null,
            'card_exp_year' => $paymentMethod->card->exp_year ?? $payment->card_exp_year ?? null,
            'billing_name' => $paymentMethod->billing_details->name ?? $payment->billing_name ?? null,
            'billing_email' => $paymentMethod->billing_details->email ?? $payment->billing_email ?? null,
            'billing_country' => $paymentMethod->billing_details->address->country ?? $payment->billing_country ?? null,
            'billing_city' => $paymentMethod->billing_details->address->city ?? $payment->billing_city ?? null,
            'billing_line1' => $paymentMethod->billing_details->address->line1 ?? $payment->billing_line1 ?? null,
            'billing_postal_code' => $paymentMethod->billing_details->address->postal_code ?? $payment->billing_postal_code ?? null,
        ]);

        $user = User::find($payment->user_id);
        if ($user) {
            $user->update(['is_payment_done' => 1]);
        }
         $student = Student::find($payment->student_id);
        if ($student) {
            $student->update(['is_payment_done' => 1]);
        }
    } else {
        \Log::warning('Payment DB record not found for session', ['session_id' => $session->id]);
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

    return response()->json(['status' => 'success'], 200);
}
    public function checkAllUsersPayments()
    {
        $users = User::whereHas('payments', function ($q) {
            $q->where('status', 'completed');
        })
        ->with([
            'students',
            'payments' => function ($q) {
                $q->where('status', 'completed');
            }
        ])->get();

        $report = $users->map(function ($user) {
            $expectedTotal = $user->students->sum('fee');


            $paidTotal = $user->payments->sum('amount') / 100;

            return [
                'user_id'        => $user->id,
                'name'           => $user->father_name . ' & ' . $user->mother_name,
                'student_count'  => $user->students->count(),
                'students'       => $user->students->map(function ($student) {
                    return [
                        'student_id' => $student->id,
                        'full_name'  => $student->first_name . ' ' . $student->last_name,
                        'fee'        => $student->fee,
                    ];
                }),
                'expected_total' => $expectedTotal,
                'paid_total'     => $paidTotal,
                'is_fully_paid'  => $paidTotal >= $expectedTotal,
                'remaining'      => max($expectedTotal - $paidTotal, 0),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $report
        ]);
    }


}
