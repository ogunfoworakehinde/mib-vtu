<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('paystack_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount',15,2);
            $table->string('currency',10)->default('NGN');
            $table->enum('status',['pending','success','failed'])->default('pending');
            $table->text('gateway_response')->nullable();
            $table->string('channel',50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('paystack_transactions'); }
};
