<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Answer;
use App\Models\Question;
use App\Models\SolveUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //

    public function store_question_answer(Request $request){

            $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:ibml,softtrac,omniscan',
            'answer_text' => 'required|string',
            'question_attachment' => 'nullable|image|max:5120',
            'answer_attachment' => 'nullable|image|max:5120',
        ]);

        DB::connection('mysql_solves')->transaction(function () use ($request, $validated) {
            $questionPath = null;
            $answerPath = null;

            if ($request->hasFile('question_attachment')) {
                $questionPath = $request->file('question_attachment')->store('attachments/questions', 'public');
            }

            if ($request->hasFile('answer_attachment')) {
                $answerPath = $request->file('answer_attachment')->store('attachments/answers', 'public');
            }

            // Create Question Record
            $questionId = DB::connection('mysql_solves')->table('questions')->insertGetId([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => 'medium',
                'created_by' => Auth::id(),
                'status' => 'approved',
                'is_final' => 1,
                'attachment' => $questionPath,

            ]);

            // Create Answer Record
            DB::connection('mysql_solves')->table('answers')->insert([
                'question_id' => $questionId,
                'answer_text' => $validated['answer_text'],
                'created_by' => Auth::id(),
                'status' => 'approved',
                'attachment' => $answerPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Question and answer published successfully.');

    }


  public function approve_question(Request $request, $id)
{
    // 1. Authorize: Ensure only admins can approve questions
    if (auth('solves')->user()?->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    // 2. Find and update the question record
    $question = Question::findOrFail($id);
    $question->update([
        'status' => 'approved'
    ]);

    // 3. Return back to refresh Inertia props cleanly
    return redirect()->back()->with('success', 'Question approved successfully.');
}


public function reject_question(Request $request, $id)
{
    // Authorize: Ensure only admins can reject questions
    if (auth('solves')->user()?->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    $question = Question::findOrFail($id);
    $question->update([
        'status' => 'rejected'
    ]);

    return redirect()->back()->with('success', 'Question rejected.');
}

public function detail_page(Request $request, $id)
{
   $user = $request->user('solves');
    $isAdmin = $user->role === 'admin';

    $questionDetail = Question::with([
        'author',
        // Admins see every answer; everyone else sees approved ones plus their own.
        'answer' => fn ($q) => $q
            ->when(! $isAdmin, fn ($q) => $q->where(fn ($q) => $q
                ->where('status', 'approved')
                ->orWhere('created_by', $user->id)))
            ->oldest(),
        'answer.author',
    ])->findOrFail($id);

    // Unapproved questions are only visible to their author and admins.
    abort_unless(
        $isAdmin || $questionDetail->status === 'approved' || $questionDetail->created_by === $user->id,
        403
    );

    $questionDetail->increment('views');

    return Inertia::render('QuestionDetail', [
        'question' => $questionDetail,
    ]);
}

public function render_users_page()
{
    $users = SolveUser::select([
        'id',
        'username',
        'first_name',
        'last_name',
        'role',
        'supervisor_type',
        'created_at',
    ])->get();

    return Inertia::render('UserManagement', [
        'users' => $users,
    ]);
}


/**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'   => ['required', 'string', 'min:3', 'max:50', 'unique:mysql_solves.solve_users,username'],
            'password'   => ['required', 'string', 'min:6'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'role'       => ['required', Rule::in(['user', 'supervisor', 'admin'])],
            'supervisor_type' => ['nullable', 'string', 'max:100'],
        ]);

        SolveUser::create([
            'username'        => $validated['username'],
            'password'        => Hash::make($validated['password']),
            'first_name'      => $validated['first_name'],
            'last_name'       => $validated['last_name'],
            'role'            => $validated['role'],
            'supervisor_type' => $validated['supervisor_type'] ?? null,
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }


    public function update(Request $request, $id)
    {
        $user = SolveUser::findOrFail($id);

        $validated = $request->validate([
            'username'   => ['required', 'string', 'min:3', 'max:50', Rule::unique('mysql_solves.solve_users', 'username')->ignore($user->id)],
            'password'   => ['nullable', 'string', 'min:6'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'role'       => ['required', Rule::in(['user', 'supervisor', 'admin'])],
            'supervisor_type' => ['nullable', 'string', 'max:100'],
        ]);

        $updateData = [
            'username'        => $validated['username'],
            'first_name'      => $validated['first_name'],
            'last_name'       => $validated['last_name'],
            'role'            => $validated['role'],
            'supervisor_type' => $validated['supervisor_type'] ?? null,
        ];

        // Only update the password if a new one is provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = SolveUser::findOrFail($id);

        if ($user->id === auth('solves')->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Answers hold a foreign key to the author, so remove accounts that have posted answers last.
        if (Answer::where('created_by', $user->id)->exists()) {
            return redirect()->back()->with('error', 'This user has posted answers. Delete those answers first, then remove the account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}


