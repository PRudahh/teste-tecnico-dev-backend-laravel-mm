<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_status_ordens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')
                ->comment('Quem fez a mudança de status');
            $table->string('status_anterior');
            $table->string('status_novo');
            $table->text('observacao')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('ordem_servico_id');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_status_ordens');
    }
};
