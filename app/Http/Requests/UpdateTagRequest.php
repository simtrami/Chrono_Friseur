<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: changer lors de l'implémentation de User
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'exclude',
            'name' => 'required|array:fr',
            'name.fr' => 'required|string|max:255',
            'slug' => 'exclude',
            'type' => 'exclude',
            'order_column' => 'exclude',
            'color' => 'required|string|hex_color|max:7',
            'created_at' => 'exclude',
            'updated_at' => 'exclude',
        ];
    }

    public function messages(): array
    {
        return [
            'name.fr.required' => 'Le champ nom est obligatoire',
            'name.fr.string' => 'Le champ nom doit être une chaîne de caractères.',
            'name.fr.max' => 'Le champ nom ne doit pas contenir plus de :max caractères.',
        ];
    }
}
