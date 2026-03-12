<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona soft delete a usuários e carteiras.
 *
 * Em sistemas financeiros, registros nunca devem ser apagados permanentemente.
 * O soft delete preserva todo o histórico para auditoria e compliance,
 * enquanto impede acesso de contas desativadas.
 *
 * Também ajusta as FKs de wallets.user_id para RESTRICT, impedindo
 * hard-deletes acidentais de usuários que possuam carteira.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->softDeletes();

            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
