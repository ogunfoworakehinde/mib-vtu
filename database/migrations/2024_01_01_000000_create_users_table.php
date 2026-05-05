<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone',20)->unique();
            $table->string('password');
            $table->decimal('wallet_balance',15,2)->default(0.00);
            $table->decimal('commission_balance',15,2)->default(0.00);
            $table->enum('theme',['light','dark','blue'])->default('light');
            $table->enum('status',['active','suspended'])->default('active');
            $table->enum('role',['user','admin'])->default('user');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
