<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    //

     public function store(Request $request, Question $question)
    {
        $validated = $request->validate([
            'answer_text' => ['required', 'string'],
            'attachment'  => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
        ]);

        $user = $request->user(); // swap for auth('solves')->user() if you're on a custom guard

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('answers', 'public');
        }

        Answer::create([
            'question_id' => $question->id,
            'answer_text' => $validated['answer_text'],
            'created_by'  => $user->id,
            // Admins get auto-approved; everyone else lands in the review queue.
            'status'      => $user->role === 'admin' ? 'approved' : 'pending',
            'attachment'  => $attachmentPath,
        ]);

        return back()->with('success', 'Answer submitted successfully.');
    }
}
