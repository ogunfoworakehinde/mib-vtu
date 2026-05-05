<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('type',['credit','debit']);
            $table->decimal('amount',15,2);
            $table->decimal('balance_before',15,2);
            $table->decimal('balance_after',15,2);
            $table->string('description')->nullable();
            $table->enum('status',['pending','success','failed'])->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('wallet_transactions'); }
};
