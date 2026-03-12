<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('questionnaire'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'settings' => ['nullable', 'array'],
            'settings.time_limit' => ['nullable', 'integer', 'min:1'],
            'settings.items_per_page' => ['nullable', 'integer', 'min:1'],
            'settings.allow_back_navigation' => ['nullable', 'boolean'],
            'settings.shuffle_pages' => ['nullable', 'boolean'],
            'settings.shuffle_items' => ['nullable', 'boolean'],
            'pages' => ['nullable', 'array'],
            'pages.*.id' => ['nullable', 'integer'],
            'pages.*.title' => ['nullable', 'string', 'max:255'],
            'pages.*.description' => ['nullable', 'string'],
            'pages.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.settings' => ['nullable', 'array'],
            'pages.*.items' => ['nullable', 'array'],
            'pages.*.items.*.id' => ['nullable', 'integer'],
            'pages.*.items.*.type' => ['required', 'string', 'in:instruction,short_text,long_text,number,single_choice,multiple_choice,file_upload,image'],
            'pages.*.items.*.content' => ['nullable', 'string'],
            'pages.*.items.*.required' => ['nullable', 'boolean'],
            'pages.*.items.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.items.*.properties' => ['nullable', 'array'],
            'pages.*.items.*.choices' => ['nullable', 'array'],
            'pages.*.items.*.choices.*.id' => ['nullable', 'integer'],
            'pages.*.items.*.choices.*.content' => ['nullable', 'string'],
            'pages.*.items.*.choices.*.is_correct' => ['nullable', 'boolean'],
            'pages.*.items.*.choices.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.items.*.choices.*.properties' => ['nullable', 'array'],
        ];
    }
}
