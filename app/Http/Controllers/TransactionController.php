<?php
namespace App\Http\Controllers;
use App\Models\VtuTransaction;
class TransactionController extends Controller
{
    public function index()
    {
        $transactions = VtuTransaction::where('user_id', auth()->id())
            ->latest()->paginate(20);
        return view('transactions', compact('transactions'));
    }
}
