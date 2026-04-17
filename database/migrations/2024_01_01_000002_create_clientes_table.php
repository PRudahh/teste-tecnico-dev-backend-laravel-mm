<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('documento', 20)->unique()->comment('CPF ou CNPJ');
            $table->string('email')->unique();
            $table->string('telefone', 20)->nullable();
            $table->decimal('saldo_credito', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};