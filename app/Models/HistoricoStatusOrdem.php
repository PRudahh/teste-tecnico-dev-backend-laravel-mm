<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoStatusOrdem extends Model
{
    public $timestamps = false;

    protected $table = 'historico_status_ordens';

    protected $fillable = [
        'ordem_servico_id',
        'usuario_id',
        'status_anterior',
        'status_novo',
        'observacao',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
