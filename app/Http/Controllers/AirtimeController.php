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
        $faceValue = (float) $request->amount;

        if ($user->wallet_balance < $faceValue)
            return response()->json(['error'=>'Insufficient balance'], 402);

        $sv = new PeyflexService();
        $reference = 'AT-'.Str::random(16);
        $buy = $sv->buyAirtime([
            'network'       => $request->network,
            'mobile_number' => $request->phone,
            'amount'        => $faceValue,
            'reference'     => $reference,
            'product'       => 'airtime'
        ]);

        if (!$buy || !is_array($buy)) {
            return response()->json(['error' => 'Peyflex service unavailable.'], 502);
        }

        $success = false;
        if (isset($buy['status'])) {
            if ($buy['status'] === true || $buy['status'] === 'success' || $buy['status'] === 200) {
                $success = true;
            }
        }
        if (!$success && isset($buy['success']) && $buy['success'] === true) {
            $success = true;
        }
        if (!$success && isset($buy['code']) && $buy['code'] == 200) {
            $success = true;
        }
        if (!$success && isset($buy['message']) && stripos($buy['message'], 'success') !== false) {
            $success = true;
        }

        if ($success) {
            (new WalletService())->debit($user, $faceValue, 'Airtime topup');
        }

        $short = $request->network;
        $discount = config("profit.airtime.{$short}", 0);
        $profit = $faceValue * $discount / 100;

        VtuTransaction::create([
            'user_id'      => $user->id,
            'reference'    => $reference,
            'service_type' => 'airtime',
            'network'      => $request->network,
            'phone'        => $request->phone,
            'amount'       => $faceValue,
            'profit'       => round($profit, 2),
            'api_response' => json_encode($buy),
            'status'       => $success ? 'success' : 'failed',
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Airtime sent' : ($buy['message'] ?? 'Failed')
        ]);
    }
}
