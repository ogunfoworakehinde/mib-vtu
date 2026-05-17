<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\PeyflexService;
use App\Services\WalletService;
use App\Models\VtuTransaction;
use Illuminate\Support\Str;

class AirtimeController extends Controller {

    public function networks() {
        $sv = new PeyflexService();
        $result = $sv->getAirtimeNetworks();
        $networks = $result['networks'] ?? [];
        return response()->json($networks);
    }

    public function buy(Request $request) {
        $request->validate([
            'network' => 'required',
            'phone'   => 'required|digits:11',
            'amount'  => 'required|numeric|min:50'
        ]);
        $user = auth()->user();

        $faceValue = (float) $request->amount;   // what the user asked for

        $short = $request->network;
        $discount = config("profit.airtime.{$short}", 0); // e.g., 1% for MTN
        $profit = $faceValue * $discount / 100;
        // The cost to us from Peyflex is $faceValue - $profit, but we don't need the exact cost
        // The user pays the full face value

        if ($user->wallet_balance < $faceValue)
            return response()->json(['error'=>'Insufficient balance'], 402);

        $sv = new PeyflexService();
        $reference = 'AT-'.Str::random(16);
        $buy = $sv->buyAirtime([
            'network'       => $request->network,
            'mobile_number' => $request->phone,
            'amount'        => $faceValue,   // send the exact amount the user wants
            'reference'     => $reference
        ]);

        if (!$buy || !is_array($buy)) {
            return response()->json(['error' => 'Peyflex service unavailable.'], 502);
        }

        $success = isset($buy['status']) && ($buy['status'] === true || $buy['status'] === 'success');
        if ($success) {
            // Deduct the exact face value from the user's wallet
            (new WalletService())->debit($user, $faceValue, 'Airtime topup');
        }

        VtuTransaction::create([
            'user_id'      => $user->id,
            'reference'    => $reference,
            'service_type' => 'airtime',
            'network'      => $request->network,
            'phone'        => $request->phone,
            'amount'       => $faceValue,   // what user paid
            'profit'       => $profit,
            'api_response' => json_encode($buy),
            'status'       => $success ? 'success' : 'failed',
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Airtime sent' : ($buy['message'] ?? 'Failed')
        ]);
    }
}
