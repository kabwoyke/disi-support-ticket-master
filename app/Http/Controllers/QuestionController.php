<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;

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


}
