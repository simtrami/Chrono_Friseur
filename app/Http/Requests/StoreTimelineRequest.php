<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'slug' => [
                'required', 'string', 'alpha_dash', 'min:2', 'max:255',
                Rule::unique('timelines')
                    ->where(fn (Builder $query) => $query->where('user_id', $user->id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'picture' => 'illustration',
        ];
    }
}
