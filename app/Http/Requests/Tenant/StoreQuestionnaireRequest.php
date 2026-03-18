<?php

namespace App\Http\Requests\Tenant;

use App\Models\Questionnaire;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Questionnaire::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'settings' => ['nullable', 'array'],
            'settings.time_limit' => ['nullable', 'integer', 'min:1'],
            'settings.items_per_page' => ['nullable', 'integer', 'min:1'],
            'settings.allow_back_navigation' => ['nullable', 'boolean'],
            'settings.shuffle_pages' => ['nullable', 'boolean'],
            'settings.shuffle_items' => ['nullable', 'boolean'],
            'pages' => ['nullable', 'array'],
            'pages.*.title' => ['nullable', 'string', 'max:255'],
            'pages.*.description' => ['nullable', 'string'],
            'pages.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.settings' => ['nullable', 'array'],
            'pages.*.items' => ['nullable', 'array'],
            'pages.*.items.*.type' => ['required', 'string', 'in:instruction,short_text,long_text,number,single_choice,multiple_choice,file_upload,image,rating,scale_rating,date_picker,spinner,fill_in_the_blank'],
            'pages.*.items.*.content' => ['nullable', 'string'],
            'pages.*.items.*.required' => ['nullable', 'boolean'],
            'pages.*.items.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.items.*.properties' => ['nullable', 'array'],
            'pages.*.items.*.choices' => ['nullable', 'array'],
            'pages.*.items.*.choices.*.content' => ['nullable', 'string'],
            'pages.*.items.*.choices.*.is_correct' => ['nullable', 'boolean'],
            'pages.*.items.*.choices.*.order' => ['required', 'integer', 'min:0'],
            'pages.*.items.*.choices.*.properties' => ['nullable', 'array'],
        ];
    }
}
