<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request de validação para transferências.
 */
class TransferRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a transferência.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ];
    }

    /**
     * Mensagens de validação personalizadas em português.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail do destinatário é obrigatório.',
            'email.email' => 'O e-mail informado não é válido.',
            'email.exists' => 'Destinatário não encontrado.',
            'amount.required' => 'O valor da transferência é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.min' => 'O valor mínimo é R$ 0,01.',
            'amount.max' => 'O valor máximo é R$ 999.999.999,99.',
        ];
    }
}
