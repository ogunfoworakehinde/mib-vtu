<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VtuTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalTransactions = VtuTransaction::count();
        $totalRevenue = VtuTransaction::sum('amount');
        return view('admin.dashboard', compact('totalUsers', 'totalTransactions', 'totalRevenue'));
    }

    public function users()
    {
        $users = User::latest()->get();
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,suspended',
        ]);

        $user->update($request->only('full_name','email','phone','role','status'));

        if ($request->filled('new_password')) {
            $request->validate(['new_password' => 'min:6']);
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return back()->with('success', 'User updated.');
    }

    public function transactions(Request $request)
    {
        $query = VtuTransaction::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('service')) {
            $query->where('service_type', $request->service);
        }

        $transactions = $query->paginate(30);
        return view('admin.transactions', compact('transactions'));
    }
}
