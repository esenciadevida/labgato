<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicSite\CalculatorController;
use App\Http\Controllers\PublicSite\InterpretationController;
use App\Http\Controllers\PublicSite\KnowledgeController;
use App\Http\Controllers\PublicSite\SopController;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;

class ResourcePreviewController extends Controller
{
    public function knowledge(KnowledgeResource $knowledge)
    {
        $knowledge->load(['categories', 'tags', 'contributor']);

        return app(KnowledgeController::class)->renderShow($knowledge);
    }

    public function sop(Sop $sop)
    {
        $sop->load(['categories', 'tags', 'contributor', 'referenceRanges']);

        return app(SopController::class)->renderShow($sop);
    }

    public function calculator(Calculator $calculator)
    {
        $calculator->load(['categories', 'tags']);

        return app(CalculatorController::class)->renderShow($calculator);
    }

    public function interpretation(InterpretationResource $interpretation)
    {
        $interpretation->load(['categories', 'tags', 'contributor']);

        return app(InterpretationController::class)->renderShow($interpretation);
    }
}
