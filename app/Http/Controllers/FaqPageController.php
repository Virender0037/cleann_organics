<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

class FaqPageController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'question', 'answer', 'topic', 'sort_order']);

        return view('faq', [
            'faqs' => $faqs,
            'topics' => Faq::TOPICS,
        ]);
    }
}
