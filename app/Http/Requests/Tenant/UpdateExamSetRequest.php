<?php

namespace App\Http\Requests\Tenant;

use App\Models\ExamSet;
use App\Models\Tenant;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $examSet = $this->route('exam_set');

        if (! $user || ! $examSet instanceof ExamSet || ! $user->can('update', $examSet)) {
            return false;
        }

        if ($this->input('status') === 'published') {
            return $user->can('publish', $examSet);
        }

        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenant = $this->route('tenant');
        $tenantId = $tenant instanceof Tenant ? $tenant->id : 0;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published'],
            'questionnaire_ids' => [
                'nullable',
                'array',
            ],
            'questionnaire_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('questionnaires', 'id')->where(
                    fn (Builder $query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'published'),
                ),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (
                $this->input('status') === 'published'
                && count($this->input('questionnaire_ids', [])) === 0
            ) {
                $validator->errors()->add(
                    'questionnaire_ids',
                    'Published exam sets must include at least one questionnaire.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'An exam set title is required.',
            'status.required' => 'Select whether the exam set stays in draft or is published.',
            'questionnaire_ids.*.distinct' => 'Each questionnaire may only be attached once.',
            'questionnaire_ids.*.exists' => 'Only published questionnaires from this tenant may be attached.',
        ];
    }
}
