<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            'nome'      => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:20', "unique:clientes,documento,{$clienteId}"],
            'email'     => ['required', 'email', "unique:clientes,email,{$clienteId}"],
            'telefone'  => ['nullable', 'string', 'max:20'],
        ];
    }
}