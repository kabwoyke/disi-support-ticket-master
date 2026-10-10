<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    //


    public function index()
    {
        
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:ibml,softtrac,omniscan',
            'description' => 'required|string',
            // 'created_by' => 'numeric|required',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('questions', 'public');
        }

        Question::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'attachment' => $filePath,
            'created_by' => auth('solves')->id(),
        ]);

        return redirect()->back();
    }

    /**
     * Admins can delete any question; the author can delete their own while it's still pending.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user('solves');
        $question = Question::with('answer')->findOrFail($id);

        $isAdmin = $user->role === 'admin';
        $isOwnPending = $question->created_by === $user->id && $question->status === 'pending';
        abort_unless($isAdmin || $isOwnPending, 403, 'You cannot delete this question.');

        DB::connection('mysql_solves')->transaction(function () use ($question) {
            foreach ($question->answer as $answer) {
                if ($answer->attachment && ! str_starts_with($answer->attachment, 'http')) {
                    Storage::disk('public')->delete($answer->attachment);
                }
            }
            $question->answer()->delete();

            if ($question->attachment && ! str_starts_with($question->attachment, 'http')) {
                Storage::disk('public')->delete($question->attachment);
            }
            $question->delete();
        });

        // Deleting from the detail page would 404 on redirect-back, so go to the dashboard.
        return str_contains((string) url()->previous(), "/{$id}/details")
            ? redirect()->route('solves-dashboard')->with('success', 'Question deleted.')
            : back()->with('success', 'Question deleted.');
    }

}
