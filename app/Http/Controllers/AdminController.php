<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
   $questionDetail = Question::with([
        'author',
        // Include 'author_id' (or 'user_id') so Eloquent can resolve the relation
        'answer.author'
    ])->findOrFail($id);

    // dd($questionDetail->answer[0]->author->first_name);

    // dd($questionDetail);
    // Increment view count
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

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}


