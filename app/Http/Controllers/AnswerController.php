<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function approve($id)
    {
        Answer::findOrFail($id)->update(['status' => 'approved']);

        return back()->with('success', 'Answer approved.');
    }

    public function reject($id)
    {
        Answer::findOrFail($id)->update(['status' => 'rejected']);

        return back()->with('success', 'Answer rejected.');
    }

    /**
     * Admins can delete any answer; the author can delete their own while it's still pending.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user('solves');
        $answer = Answer::findOrFail($id);

        $isAdmin = $user->role === 'admin';
        $isOwnPending = $answer->created_by === $user->id && $answer->status === 'pending';
        abort_unless($isAdmin || $isOwnPending, 403, 'You cannot delete this answer.');

        if ($answer->attachment && ! str_starts_with($answer->attachment, 'http')) {
            Storage::disk('public')->delete($answer->attachment);
        }
        $answer->delete();

        return back()->with('success', 'Answer deleted.');
    }

}
