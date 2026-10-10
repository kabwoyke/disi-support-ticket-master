<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user('solves')->id;

        $questions = Question::where('created_by', $userId)
            ->withCount(['answer as answer_count' => fn ($q) => $q->where('status', 'approved')])
            ->get()
            ->map(fn ($q) => [
                'type' => 'question',
                'id' => $q->id,
                'question_id' => $q->id,
                'title' => $q->title,
                'category' => $q->category,
                'status' => $q->status,
                'views' => $q->views,
                'answer_count' => $q->answer_count,
                'created_at' => $q->created_at?->toIso8601String(),
            ]);

        $answers = Answer::with('question:id,title,category')
            ->where('created_by', $userId)
            ->get()
            ->map(fn ($a) => [
                'type' => 'answer',
                'id' => $a->id,
                'question_id' => $a->question_id,
                'title' => $a->question?->title ?? 'Deleted question',
                'category' => $a->question?->category,
                'excerpt' => str($a->answer_text)->limit(160)->toString(),
                'status' => $a->status,
                'created_at' => $a->created_at?->toIso8601String(),
            ]);

        $items = $questions->concat($answers)->sortByDesc('created_at')->values();

        return Inertia::render('Activity', [
            'items' => $items,
            'stats' => [
                'questions' => $questions->count(),
                'answers' => $answers->count(),
                'pending' => $items->where('status', 'pending')->count(),
                'approved' => $items->where('status', 'approved')->count(),
            ],
        ]);
    }
}
