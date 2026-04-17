<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'descricao',
        'data_inicio',
        'data_encerramento',
        'status',
        'dia_vencimento',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_encerramento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemContrato::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    /**
     * Valor total calculado a partir dos itens — NUNCA salvo no banco.
     */
    public function getValorTotalAttribute(): float
    {
        return $this->itens->sum(fn ($item) => $item->quantidade * $item->valor_unitario);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
