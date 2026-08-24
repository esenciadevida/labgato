<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends StoreQuestionRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->after(function ($validator) {
            /** @var \App\Models\Question|null $question */
            $question = $this->route('question');

            if ($question === null || ! $question->usesImage()) {
                return;
            }

            $wantsRemoval = $this->boolean('remove_image');
            $keepsExisting = $question->image_path !== null && ! $wantsRemoval;

            if (! $this->hasFile('image') && ! $keepsExisting) {
                $validator->errors()->add('image', 'An image is required for image-based questions.');
            }
        });
    }
}
