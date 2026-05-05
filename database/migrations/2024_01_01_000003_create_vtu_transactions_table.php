<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('vtu_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('service_type',['data','airtime']);
            $table->string('network',50);
            $table->string('phone',20);
            $table->string('plan_name',100)->nullable();
            $table->string('plan_code',50)->nullable();
            $table->decimal('amount',15,2);
            $table->decimal('profit',15,2)->default(0.00);
            $table->text('api_response')->nullable();
            $table->enum('status',['pending','success','failed'])->default('pending');
            $table->string('provider',50)->default('peyflex');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vtu_transactions'); }
};
