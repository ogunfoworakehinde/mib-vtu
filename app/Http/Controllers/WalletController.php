<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\PaystackService;
use App\Services\WalletService;
use App\Models\PaystackTransaction;

class WalletController extends Controller {
    public function fund(Request $request) {
        $request->validate(['amount' => 'required|numeric|min:100']);
        $ps = new PaystackService();
        $res = $ps->initialize(auth()->user()->email, $request->amount);
        if (isset($res['status']) && $res['status']) {
            return response()->json(['authorization_url' => $res['data']['authorization_url']]);
        }
        return response()->json(['error' => 'Payment gateway error'], 500);
    }

    public function verify(Request $request) {
        $ref = $request->reference;
        $ps = new PaystackService();
        $res = $ps->verify($ref);
        if (isset($res['status']) && $res['status'] && $res['data']['status'] === 'success') {
            $user = auth()->user();
            $amount = $res['data']['amount'] / 100;
            PaystackTransaction::create([
                'user_id' => $user->id,
                'reference' => $ref,
                'amount' => $amount,
                'status' => 'success',
                'gateway_response' => $res['data']['gateway_response'] ?? '',
                'channel' => $res['data']['channel'] ?? '',
                'paid_at' => now(),
            ]);
            (new WalletService())->credit($user, $amount, 'Paystack funding');
            return response()->json(['success' => true, 'balance' => $user->fresh()->wallet_balance]);
        }
        return response()->json(['error' => 'Payment verification failed'], 400);
    }
}
