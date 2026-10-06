<?php

namespace App\Http\Controllers;

use App\Models\SolveUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  /**
     * Display a listing of the users.
     */
    public function index()
    {
        return response()->json(SolveUser::latest()->get());
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:mysql_solves.solve_users,username|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'supervisor', 'user'])],
            'supervisor_type' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = SolveUser::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = SolveUser::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = SolveUser::findOrFail($id);

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mysql_solves.solve_users', 'username')->ignore($user->id)
            ],
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'supervisor', 'user'])],
            'supervisor_type' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $user = SolveUser::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
