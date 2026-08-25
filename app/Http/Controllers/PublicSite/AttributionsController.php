<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Question;

class AttributionsController extends Controller
{
    public function index()
    {
        $questions = Question::query()
            ->whereNotNull('image_license')
            ->where('image_license', 'like', '%CC%')
            ->with('category')
            ->orderBy('image_source')
            ->orderBy('id')
            ->get();

        $grouped = $questions->groupBy(fn (Question $question) => $question->image_source ?: 'Other');

        return view('public.attributions', ['grouped' => $grouped]);
    }
}
