<?php

namespace App\Http\Requests\Tenant;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('candidate'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $candidate = $this->route('candidate');
        $candidateId = $candidate instanceof User ? $candidate->id : $candidate;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($candidateId)],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
        ];
    }
}
