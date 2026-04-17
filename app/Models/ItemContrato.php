<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemContrato extends Model
{
    use HasFactory;

    protected $table = 'itens_contrato';

    protected $fillable = [
        'contrato_id',
        'descricao',
        'quantidade',
        'valor_unitario',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Valor total do item — calculado dinamicamente, não persiste.
     */
    public function getValorTotalAttribute(): float
    {
        return $this->quantidade * $this->valor_unitario;
    }
}
