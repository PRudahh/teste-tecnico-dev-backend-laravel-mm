<?php

namespace App\Http\Requests;

use App\Domain\Cobranca\StatusCobranca;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarCobrancasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => ['sometimes', 'array'],
            'status.*'    => ['string', Rule::in(StatusCobranca::todos())],
            'data_inicio' => ['sometimes', 'date_format:Y-m-d'],
            'data_fim'    => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
            'busca'       => ['sometimes', 'string', 'max:100'],
            'order_by'    => ['sometimes', 'string', Rule::in(StatusCobranca::ordenaveisWhitelist())],
            'order_dir'   => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'        => ['sometimes', 'integer', 'min:1'],
        ];
    }
}