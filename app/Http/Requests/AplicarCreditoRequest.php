<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AplicarCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Apenas financeiro e admin podem aplicar crédito
        return $this->user()?->isFinanceiro() ?? false;
    }

    public function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'valor.min' => 'O valor do crédito deve ser maior que zero.',
        ];
    }

    protected function failedAuthorization(): never
    {
        abort(403, 'Apenas usuários com perfil financeiro podem aplicar crédito.');
    }
}