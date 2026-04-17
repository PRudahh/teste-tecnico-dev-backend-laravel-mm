<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'        => ['required', 'integer', 'exists:clientes,id'],
            'descricao'         => ['required', 'string', 'max:255'],
            'data_inicio'       => ['required', 'date_format:Y-m-d'],
            'data_encerramento' => ['nullable', 'date_format:Y-m-d', 'after:data_inicio'],
            'status'            => ['sometimes', Rule::in(['ativo', 'encerrado', 'suspenso'])],
            'dia_vencimento'    => ['sometimes', 'integer', 'min:1', 'max:28'],

            'itens'                   => ['required', 'array', 'min:1'],
            'itens.*.descricao'       => ['required', 'string', 'max:255'],
            'itens.*.quantidade'      => ['required', 'integer', 'min:1'],
            'itens.*.valor_unitario'  => ['required', 'numeric', 'min:0.01'],
        ];
    }
}