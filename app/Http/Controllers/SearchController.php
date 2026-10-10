<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Support\IssueSearch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request, IssueSearch $search)
    {
        $q = trim((string) $request->input('q', ''));
        $category = $request->input('category', 'all');
        $solvedOnly = $request->boolean('solved');

        $payload = ['results' => [], 'total' => 0, 'suggestion' => null, 'terms' => []];
        $questions = collect();

        if ($q !== '') {
            [$docs, $questions] = $this->corpus($request, $category, $solvedOnly);
            $payload = $search->search($q, $docs, 30);
        }

        $results = collect($payload['results'])->map(function ($r) use ($questions) {
            $question = $questions[$r['id']];

            return $r + [
                'title' => $question->title,
                'category' => $question->category,
                'status' => $question->status,
                'views' => $question->views,
                'answer_count' => $question->answer->count(),
                'created_at' => $question->created_at?->toIso8601String(),
                'author' => $question->author?->only(['first_name', 'last_name', 'username']),
            ];
        })->values();

        return Inertia::render('Search', [
            'query' => $q,
            'filters' => ['category' => $category, 'solved' => $solvedOnly],
            'results' => $results,
            'total' => $payload['total'] ?? 0,
            'suggestion' => $payload['suggestion'],
            'terms' => $payload['terms'],
        ]);
    }

    /** JSON: top matches for a draft title, shown while raising a new issue. */
    public function similar(Request $request, IssueSearch $search)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 4) {
            return response()->json([]);
        }

        [$docs, $questions] = $this->corpus($request, 'all', false);

        return response()->json(
            collect($search->search($q, $docs, 5)['results'])
                ->filter(fn ($r) => $r['score'] >= 3)
                ->map(fn ($r) => [
                    'id' => $r['id'],
                    'title' => $questions[$r['id']]->title,
                    'solved' => $questions[$r['id']]->answer->isNotEmpty(),
                ])
                ->values()
        );
    }

    /**
     * Questions the current user may see, with their approved answers.
     *
     * @return array{0: array<int,array>, 1: \Illuminate\Support\Collection}
     */
    private function corpus(Request $request, string $category, bool $solvedOnly): array
    {
        $user = $request->user('solves');

        $questions = Question::query()
            ->with([
                'author:id,username,first_name,last_name',
                'answer' => fn ($q) => $q->where('status', 'approved')->select('id', 'question_id', 'answer_text'),
            ])
            ->when($user->role !== 'admin', fn ($q) => $q->where(fn ($q) => $q
                ->where('status', 'approved')
                ->orWhere('created_by', $user->id)))
            ->when($category !== 'all', fn ($q) => $q->where('category', $category))
            ->limit(3000)
            ->get()
            ->when($solvedOnly, fn ($c) => $c->filter(fn ($q) => $q->answer->isNotEmpty()))
            ->keyBy('id');

        $docs = $questions->map(fn ($q) => [
            'id' => $q->id,
            'title' => $q->title,
            'description' => $q->description,
            'category' => $q->category,
            'views' => $q->views,
            'answers' => $q->answer->pluck('answer_text')->implode("\n"),
        ])->values()->all();

        return [$docs, $questions];
    }
}
