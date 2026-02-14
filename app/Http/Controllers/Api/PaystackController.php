<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaystackController extends Controller
{
    //  Initialize Payment
    public function initialize(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->amount * 100; // convert to kobo

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->post(env('PAYSTACK_PAYMENT_URL') . '/transaction/initialize', [
                'email' => $request->email,
                'amount' => $amount,
            ]);

        return response()->json($response->json());
    }

    //  Verify Payment
    public function verify($reference)
    {
        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->get(env('PAYSTACK_PAYMENT_URL') . "/transaction/verify/{$reference}");

        return response()->json($response->json());
    }
}
