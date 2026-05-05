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
        return response()->json($sv->getNetworks()['data'] ?? []);
    }
    public function plans(Request $request) {
        $sv = new PeyflexService();
        $plans = $sv->getPlans($request->network_id)['data'] ?? [];
        return response()->json(array_map(fn($p)=>['code'=>$p['code'],'name'=>$p['name'],'price'=>$p['price']], $plans));
    }
    public function buy(Request $request) {
        $request->validate([
            'network' => 'required',
            'plan_code' => 'required',
            'phone' => 'required|digits:11'
        ]);
        $user = auth()->user();
        $sv = new PeyflexService();
        $plans = $sv->getPlans($request->network)['data'] ?? [];
        $plan = collect($plans)->firstWhere('code', $request->plan_code);
        if (!$plan) return response()->json(['error'=>'Invalid plan'], 400);
        $amount = $plan['price'];
        if ($user->wallet_balance < $amount) return response()->json(['error'=>'Insufficient balance'], 402);

        $reference = 'DT-'.Str::random(16);
        $buy = $sv->buyData([
            'network' => $request->network,
            'plan_code' => $request->plan_code,
            'phone' => $request->phone,
            'reference' => $reference
        ]);
        $success = isset($buy['status']) && $buy['status'] === 'success';
        if ($success) {
            (new WalletService())->debit($user, $amount, 'Data: '.$plan['name']);
        }
        VtuTransaction::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'service_type' => 'data',
            'network' => $request->network,
            'phone' => $request->phone,
            'plan_name' => $plan['name'],
            'plan_code' => $plan['code'],
            'amount' => $amount,
            'api_response' => json_encode($buy),
            'status' => $success ? 'success' : 'failed',
        ]);
        return response()->json(['success'=>$success, 'message'=>$success?'Data purchased':'Failed']);
    }
}
