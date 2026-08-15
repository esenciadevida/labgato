<?php

namespace App\Services\Ai\UseCases;

use App\Support\AiResponse;

interface AiUseCaseInterface
{
    public function actionName(): string;

    public function displayName(): string;

    /**
     * @param  array{title?: string, outline?: string, content?: string}  $input
     */
    public function generate(array $input): AiResponse;
}
