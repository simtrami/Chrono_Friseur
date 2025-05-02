<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $timeline = $this->route('timeline');

        return $timeline && $this->user()->can('update', $timeline);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $timeline = $this->route('timeline');

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'slug' => [
                'required', 'string', 'alpha_dash', 'min:2', 'max:255',
                Rule::unique('timelines')
                    ->ignore($timeline->id)
                    ->where(fn (Builder $query) => $query->where('user_id', $timeline->user_id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'picture' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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
