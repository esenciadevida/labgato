<?php

namespace App\Http\Requests\Admin;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('questions.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'question_category_id' => ['required', Rule::exists('question_categories', 'id')],
            'question_type' => ['required', Rule::in(Question::TYPES)],
            'difficulty' => ['required', Rule::in(Question::DIFFICULTIES)],
            'status' => ['required', Rule::in(Question::STATUSES)],
            'stem' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'image' => [
                'nullable',
                'required_if:question_type,image,text_image',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_image' => ['nullable', 'boolean'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:1000'],
            'correct_index' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $options = $this->input('options', []);
            $correctIndex = $this->input('correct_index');

            // Blank options are silently dropped on save, which can leave a
            // question with zero correct answers. Reject them outright.
            foreach (array_values($options) as $i => $option) {
                if (trim((string) $option) === '') {
                    $validator->errors()->add("options.{$i}", 'Option '.chr(65 + $i).' cannot be empty.');
                }
            }

            if (is_numeric($correctIndex)) {
                $ci = (int) $correctIndex;
                if ($ci < 0 || $ci >= count($options)) {
                    $validator->errors()->add('correct_index', 'The correct option must be one of the provided options.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'image.required_if' => 'An image is required for image-based questions.',
            'options.min' => 'A question needs at least 2 options.',
            'options.max' => 'A question can have at most 6 options.',
        ];
    }
}
