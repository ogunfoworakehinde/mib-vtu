<?php
namespace App\Http\Controllers;
use App\Models\VtuTransaction;
class DashboardController extends Controller {
    public function index() {
        $user = auth()->user();
        $transactions = VtuTransaction::where('user_id', $user->id)->latest()->take(5)->get();
        return view('dashboard', compact('user', 'transactions'));
    }
}
