<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCommitteeMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('user_id'))) {
            $this->merge(['user_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'position_id' => ['required', 'exists:positions,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('user_id')) && blank($this->input('name'))) {
                $validator->errors()->add('name', 'Either a registered member or a name must be provided.');
            }
        });
    }
}
