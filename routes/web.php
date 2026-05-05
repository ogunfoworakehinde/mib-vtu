<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/wallet/fund', [WalletController::class, 'fund'])->name('wallet.fund');
    Route::post('/wallet/verify', [WalletController::class, 'verify'])->name('wallet.verify');
    Route::get('/data/networks', [DataController::class, 'networks'])->name('data.networks');
    Route::get('/data/plans', [DataController::class, 'plans'])->name('data.plans');
    Route::post('/data/buy', [DataController::class, 'buy'])->name('data.buy');
    Route::post('/airtime/buy', [AirtimeController::class, 'buy'])->name('airtime.buy');
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');


// Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password');

// Transaction History
Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
// Transaction Receipt (already exists, but ensure it's there)
Route::get('/transactions/{id}', function ($id) {
    $tx = \App\Models\VtuTransaction::where('id', $id)->where('user_id', auth()->id())->first();
    if (!$tx) return response()->json(['error' => 'Not found'], 404);
    return response()->json($tx->only('id','reference','service_type','network','phone','plan_name','amount','status','created_at'));
})->name('transaction.show');

// Support Page
Route::get('/support', [App\Http\Controllers\SupportController::class, 'index'])->name('support');


Route::prefix('admin')->middleware(['auth','admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/transactions', [App\Http\Controllers\AdminController::class, 'transactions'])->name('admin.transactions');
});

});
