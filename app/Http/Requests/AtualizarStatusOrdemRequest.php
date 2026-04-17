<?php

namespace App\Http\Requests;

use App\Domain\OrdemServico\StatusOrdemServico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarStatusOrdemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'     => ['required', 'string', Rule::in(StatusOrdemServico::todos())],
            'observacao' => ['nullable', 'string', 'max:500'],
        ];
    }
}