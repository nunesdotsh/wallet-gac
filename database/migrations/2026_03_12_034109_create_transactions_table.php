<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('status', 20)->default('completed');
            $table->uuid('related_transaction_id')->nullable();
            $table->uuid('counterpart_wallet_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')
                ->references('id')
                ->on('wallets');

            $table->foreign('counterpart_wallet_id')
                ->references('id')
                ->on('wallets');

            $table->index('wallet_id');
            $table->index('status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('related_transaction_id')
                ->references('id')
                ->on('transactions');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['related_transaction_id']);
        });

        Schema::dropIfExists('transactions');
    }
};
