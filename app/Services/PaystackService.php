<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class PaystackService {
    public function initialize($email, $amount) {
        $res = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amount * 100,
                'callback_url' => config('services.paystack.callback'),
                'currency' => 'NGN'
            ]);
        return $res->json();
    }
    public function verify($reference) {
        $res = Http::withToken(config('services.paystack.secret'))
            ->get("https://api.paystack.co/transaction/verify/$reference");
        return $res->json();
    }
}
