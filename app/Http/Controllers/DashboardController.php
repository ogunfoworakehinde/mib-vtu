<?php
namespace App\Http\Controllers;
use App\Models\VtuTransaction;
use App\Models\WalletTransaction;

class DashboardController extends Controller {
    public function index() {
        $user = auth()->user();

        $vtu = VtuTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $wallet = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')   // only funding credits
            ->latest()
            ->take(5)
            ->get();

        // Merge both collections, sort by latest, take 5
        $all = $vtu->concat($wallet)->sortByDesc('created_at')->take(5);

        return view('dashboard', ['user' => $user, 'transactions' => $all]);
    }
}
