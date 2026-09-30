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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal("amount",9,2)->nullable();
            $table->integer("currency_id")->nullable();
            // $table->integer("wallet_id")->nullable();
            $table->integer("sender_id")->nullable();
            $table->integer("reciever_id")->nullable();
            $table->integer("status")->default(1);//1 => pending, 2 =>completed
            $table->text("note")->nullable();//->default("");
            $table->datetime("init_time");
            $table->datetime("completed_time")->nullable();
            $table->integer("transaction_type")->nullable();//p2p,topup
            $table->string('refrence_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
