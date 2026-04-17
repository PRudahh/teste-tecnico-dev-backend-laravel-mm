<?php

namespace App\Http\Requests;

use App\Domain\Cobranca\StatusCobranca;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarStatusCobrancaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'               => ['required', 'string', Rule::in(StatusCobranca::todos())],
            'motivo_cancelamento'  => [
                Rule::requiredIf(fn () => $this->input('status') === StatusCobranca::CANCELADO),
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_cancelamento.required' => 'O motivo do cancelamento é obrigatório ao cancelar uma cobrança.',
        ];
    }
}