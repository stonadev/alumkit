<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Models\Position;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Position $position */
        $position = $this->route('position');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:positions,name,'.$position->id],
        ];
    }
}
