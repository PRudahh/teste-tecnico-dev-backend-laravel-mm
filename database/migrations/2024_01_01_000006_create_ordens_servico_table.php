<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos');
            $table->foreignId('responsavel_id')->constrained('users')
                ->comment('Usuário interno responsável');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('status', [
                'aberta',
                'em_andamento',
                'aguardando_aprovacao',
                'concluida',
                'cancelada',
            ])->default('aberta');
            $table->decimal('horas_estimadas', 8, 2)->nullable();
            $table->decimal('horas_realizadas', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('contrato_id');
            $table->index('responsavel_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
