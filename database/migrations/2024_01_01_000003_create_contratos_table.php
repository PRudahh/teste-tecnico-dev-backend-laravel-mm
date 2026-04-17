<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('descricao');
            $table->date('data_inicio');
            $table->date('data_encerramento')->nullable();
            $table->enum('status', ['ativo', 'encerrado', 'suspenso'])->default('ativo');
            $table->integer('dia_vencimento')->default(10)
                ->comment('Dia do mês em que as cobranças vencem');
            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
