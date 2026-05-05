<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\PeyflexService;
use App\Services\WalletService;
use App\Models\VtuTransaction;
use Illuminate\Support\Str;

class AirtimeController extends Controller {
    public function buy(Request $request) {
        $request->validate([
            'network' => 'required',
            'phone' => 'required|digits:11',
            'amount' => 'required|numeric|min:50'
        ]);
        $user = auth()->user();
        if ($user->wallet_balance < $request->amount) return response()->json(['error'=>'Insufficient balance'], 402);
        $sv = new PeyflexService();
        $reference = 'AT-'.Str::random(16);
        $buy = $sv->buyAirtime([
            'network' => $request->network,
            'phone' => $request->phone,
            'amount' => $request->amount,
            'reference' => $reference
        ]);
        $success = isset($buy['status']) && $buy['status'] === 'success';
        if ($success) {
            (new WalletService())->debit($user, $request->amount, 'Airtime topup');
        }
        VtuTransaction::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'service_type' => 'airtime',
            'network' => $request->network,
            'phone' => $request->phone,
            'amount' => $request->amount,
            'api_response' => json_encode($buy),
            'status' => $success ? 'success' : 'failed',
        ]);
        return response()->json(['success'=>$success, 'message'=>$success?'Airtime sent':'Failed']);
    }
}
