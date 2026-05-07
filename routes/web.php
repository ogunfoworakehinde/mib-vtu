<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// -------------------- Authentication Routes --------------------
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// -------------------- Authenticated Routes --------------------
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Wallet
    Route::post('/wallet/fund', [WalletController::class, 'fund'])->name('wallet.fund');
    Route::post('/wallet/verify', [WalletController::class, 'verify'])->name('wallet.verify');

    // Data
    Route::get('/data/networks', [DataController::class, 'networks'])->name('data.networks');
    Route::get('/data/plans', [DataController::class, 'plans'])->name('data.plans');
    Route::post('/data/buy', [DataController::class, 'buy'])->name('data.buy');

    // Airtime
    Route::get('/airtime/networks', [AirtimeController::class, 'networks'])->name('airtime.networks');
    Route::post('/airtime/buy', [AirtimeController::class, 'buy'])->name('airtime.buy');

    // Theme
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/{id}', function ($id) {
        $tx = \App\Models\VtuTransaction::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$tx) return response()->json(['error' => 'Not found'], 404);
        return response()->json($tx->only('id','reference','service_type','network','phone','plan_name','amount','status','created_at'));
    })->name('transaction.show');

    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support');

    // Admin
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    });
});
