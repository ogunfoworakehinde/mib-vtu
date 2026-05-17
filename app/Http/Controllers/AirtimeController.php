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
        if ($user->wallet_balance < $request->amount)
            return response()->json(['error'=>'Insufficient balance'], 402);

        $sv = new PeyflexService();
        $reference = 'AT-'.Str::random(16);
        $buy = $sv->buyAirtime([
            'network'       => $request->network,
            'mobile_number' => $request->phone,
            'amount'        => $request->amount,
            'reference'     => $reference
        ]);

        if (!$buy || !is_array($buy)) {
            return response()->json(['error' => 'Peyflex service unavailable. Try again later.'], 502);
        }

        // ----- Flexible success detection -----
        $success = false;
        if (isset($buy['status'])) {
            // Peyflex can return true (boolean) or 'success' (string)
            $success = $buy['status'] === true || $buy['status'] === 'success';
        }
        if (!$success && isset($buy['message']) && stripos($buy['message'], 'success') !== false) {
            $success = true;
        }
        // ------------------------------------

        if ($success) {
            (new WalletService())->debit($user, $request->amount, 'Airtime topup');
        }

        VtuTransaction::create([
            'user_id'      => $user->id,
            'reference'    => $reference,
            'service_type' => 'airtime',
            'network'      => $request->network,
            'phone'        => $request->phone,
            'amount'       => $request->amount,
            'api_response' => json_encode($buy),
            'status'       => $success ? 'success' : 'failed',
        ]);

        $message = $success
            ? 'Airtime sent successfully'
            : ($buy['message'] ?? 'Airtime purchase failed. Please try again.');

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
}
