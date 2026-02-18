<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        // Seulement les utilisateurs authentifiés peuvent passer commande
        return auth()->check();
    }

    public function rules()
    {
        return [
            'shipping_full_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_country' => 'required|string|max:100',
        ];
    }
}