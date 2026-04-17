<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'documento',
        'email',
        'telefone',
        'saldo_credito',
    ];

    protected $casts = [
        'saldo_credito' => 'decimal:2',
    ];

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    public function cobrancasPendentes()
    {
        return $this->hasMany(Cobranca::class)
            ->where('status', 'aguardando_pagamento')
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('id', 'asc');
    }

    public function temSaldoCredito(): bool
    {
        return $this->saldo_credito > 0;
    }
}
