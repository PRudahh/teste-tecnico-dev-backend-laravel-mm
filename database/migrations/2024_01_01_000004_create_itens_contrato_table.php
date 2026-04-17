<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_contrato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->string('descricao')->comment('Ex: Desenvolvimento de sistema, Manutenção de site');
            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('valor_unitario', 12, 2);
            // valor_total NUNCA salvo — sempre calculado (quantidade * valor_unitario)
            $table->timestamps();

            $table->index('contrato_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_contrato');
    }
};
