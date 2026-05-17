<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\PeyflexService;
use App\Services\WalletService;
use App\Models\VtuTransaction;
use Illuminate\Support\Str;

class DataController extends Controller {

    public function networks() {
        $sv = new PeyflexService();
        $result = $sv->getDataNetworks();
        $networks = $result['networks'] ?? [];
        return response()->json($networks);
    }

    public function plans(Request $request) {
        $networkId = $request->network_id;
        $sv = new PeyflexService();
        $result = $sv->getDataPlans($networkId);
        if (!$result || !isset($result['plans'])) {
            return response()->json([]);
        }
        $plans = $result['plans'];
        $mapped = array_map(function($p) {
            return [
                'code'  => $p['plan_code'],
                'name'  => $p['label'],
                'price' => $p['amount']
            ];
        }, $plans);
        return response()->json($mapped);
    }

    public function buy(Request $request) {
        $request->validate([
            'network'   => 'required',
            'plan_code' => 'required',
            'phone'     => 'required|digits:11'
        ]);
        $user = auth()->user();
        $sv = new PeyflexService();

        $result = $sv->getDataPlans($request->network);
        $plans = $result['plans'] ?? [];
        $plan = collect($plans)->firstWhere('plan_code', $request->plan_code);
        if (!$plan) return response()->json(['error'=>'Invalid plan'], 400);
        $amount = $plan['amount'];

        if ($user->wallet_balance < $amount)
            return response()->json(['error'=>'Insufficient balance'], 402);

        $reference = 'DT-'.Str::random(16);
        $buy = $sv->buyData([
            'network'       => $request->network,
            'plan_code'     => $request->plan_code,
            'mobile_number' => $request->phone,
            'reference'     => $reference
        ]);

        if (!$buy || !is_array($buy)) {
            return response()->json(['error' => 'Peyflex service unavailable.'], 502);
        }

        // ---------- STRONGER SUCCESS DETECTION ----------
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
        // -------------------------------------------------

        if ($success) {
            (new WalletService())->debit($user, $amount, 'Data: '.$plan['label']);
        }

        $short = explode('_', $request->network)[0];
        $discount = config("profit.data.{$short}", 0);
        $profit = $amount * $discount / 100;

        VtuTransaction::create([
            'user_id'      => $user->id,
            'reference'    => $reference,
            'service_type' => 'data',
            'network'      => $request->network,
            'phone'        => $request->phone,
            'plan_name'    => $plan['label'],
            'plan_code'    => $plan['plan_code'],
            'amount'       => $amount,
            'profit'       => round($profit, 2),
            'api_response' => json_encode($buy),
            'status'       => $success ? 'success' : 'failed',
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Data purchased' : ($buy['message'] ?? 'Failed')
        ]);
    }
}
