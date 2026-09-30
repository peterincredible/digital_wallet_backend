<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('currency_id');
            /*
                since we have a fixed currency it is best each of them have there own amount column
            */
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal("usd_amount",9,2)->default(0);
            $table->decimal("usdt_amount",9,2)->default(0);
            $table->decimal("ngn_amount",9,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
