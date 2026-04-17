<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->decimal('valor', 12, 2);
            $table->decimal('valor_pago', 12, 2)->default(0);
            $table->decimal('credito_aplicado', 12, 2)->default(0);
            $table->enum('status', [
                'pendente',
                'aguardando_pagamento',
                'pago_parcial',
                'pago',
                'inadimplente',
                'cancelado',
            ])->default('pendente');
            $table->date('data_referencia')->comment('Mês/ano ao qual a cobrança se refere');
            $table->date('data_vencimento')->comment('Dia do vencimento — padrão dia_vencimento do contrato');
            $table->string('motivo_cancelamento')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('cliente_id');
            $table->index('contrato_id');
            $table->index('data_referencia');
            $table->index('data_vencimento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};
