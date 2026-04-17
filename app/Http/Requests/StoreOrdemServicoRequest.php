<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contrato_id'      => ['required', 'integer', 'exists:contratos,id'],
            'responsavel_id'   => ['required', 'integer', 'exists:users,id'],
            'titulo'           => ['required', 'string', 'max:255'],
            'descricao'        => ['nullable', 'string'],
            'horas_estimadas'  => ['nullable', 'numeric', 'min:0'],
            'horas_realizadas' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}