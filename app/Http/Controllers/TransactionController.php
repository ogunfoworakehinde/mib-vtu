<?php
namespace App\Http\Controllers;
use App\Models\VtuTransaction;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $vtu = VtuTransaction::where('user_id', $user->id)->latest()->get();
        $wallet = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->latest()
            ->get();

        // Merge and sort descending
        $merged = $vtu->concat($wallet)->sortByDesc('created_at');

        // Manual pagination
        $perPage = 20;
        $page = $request->input('page', 1);
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => url('/transactions')]
        );

        return view('transactions', compact('transactions'));
    }
}
