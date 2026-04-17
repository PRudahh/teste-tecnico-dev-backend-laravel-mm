<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'contrato_id',
        'responsavel_id',
        'titulo',
        'descricao',
        'status',
        'horas_estimadas',
        'horas_realizadas',
    ];

    protected $casts = [
        'horas_estimadas'  => 'decimal:2',
        'horas_realizadas' => 'decimal:2',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function historico()
    {
        return $this->hasMany(HistoricoStatusOrdem::class, 'ordem_servico_id')
            ->orderBy('created_at', 'desc');
    }
}
