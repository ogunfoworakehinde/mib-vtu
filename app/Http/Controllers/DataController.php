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
        // get discount percentage for this network
        $short = explode('_', $networkId)[0]; // e.g., 'mtn'
        $discount = config("profit.data.{$short}", 0); // 0 if not set

        $mapped = array_map(function($p) use ($discount) {
            $cost = $p['amount']; // our cost from Peyflex
            if ($discount > 0) {
                // standard price = cost / (1 - discount/100)
                $standardPrice = round($cost / (1 - $discount / 100), 2);
            } else {
                $standardPrice = $cost; // no discount, sell at cost
            }
            return [
                'code'  => $p['plan_code'],
                'name'  => $p['label'],
                'price' => $standardPrice,   // displayed to user
                'cost'  => $cost             // for profit calculation
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

        // Fetch plan to get our cost and the standard price
        $result = $sv->getDataPlans($request->network);
        $plans = $result['plans'] ?? [];
        $plan = collect($plans)->firstWhere('plan_code', $request->plan_code);
        if (!$plan) return response()->json(['error'=>'Invalid plan'], 400);
        $cost = $plan['amount'];

        $short = explode('_', $request->network)[0];
        $discount = config("profit.data.{$short}", 0);
        if ($discount > 0) {
            $standardPrice = round($cost / (1 - $discount / 100), 2);
        } else {
            $standardPrice = $cost;
        }
        $profit = $standardPrice - $cost;

        if ($user->wallet_balance < $standardPrice)
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

        $success = isset($buy['status']) && ($buy['status'] === true || $buy['status'] === 'success');
        if ($success) {
            // Debit the user the STANDARD price
            (new WalletService())->debit($user, $standardPrice, 'Data: '.$plan['label']);
        }

        VtuTransaction::create([
            'user_id'      => $user->id,
            'reference'    => $reference,
            'service_type' => 'data',
            'network'      => $request->network,
            'phone'        => $request->phone,
            'plan_name'    => $plan['label'],
            'plan_code'    => $plan['plan_code'],
            'amount'       => $standardPrice,   // what user paid
            'profit'       => $profit,
            'api_response' => json_encode($buy),
            'status'       => $success ? 'success' : 'failed',
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Data purchased' : ($buy['message'] ?? 'Failed')
        ]);
    }
}
