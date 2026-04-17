<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $table = 'cobrancas';

    protected $fillable = [
        'contrato_id',
        'cliente_id',
        'valor',
        'valor_pago',
        'credito_aplicado',
        'status',
        'data_referencia',
        'data_vencimento',
        'motivo_cancelamento',
    ];

    protected $casts = [
        'valor'             => 'decimal:2',
        'valor_pago'        => 'decimal:2',
        'credito_aplicado'  => 'decimal:2',
        'data_referencia'   => 'date',
        'data_vencimento'   => 'date',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estaVencida(): bool
    {
        return $this->data_vencimento->isPast();
    }

    public function valorRestante(): float
    {
        return max(0, $this->valor - $this->valor_pago - $this->credito_aplicado);
    }

    public function scopeStatus($query, array|string $status)
    {
        return $query->whereIn('status', (array) $status);
    }

    public function scopeEmAberto($query)
    {
        return $query->whereIn('status', ['aguardando_pagamento', 'pago_parcial']);
    }
}
