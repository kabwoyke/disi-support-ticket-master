<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        return Inertia::render('Profile');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user = $request->user('solves');
        $attributes = [
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
        ];

        if ($request->hasFile('photo')) {
            $this->deleteStoredPicture($user->profile_picture);
            $attributes['profile_picture'] = $request->file('photo')->store('avatars/solves', 'public');
        }

        $user->update($attributes);

        return back()->with('success', 'Profile updated.');
    }

    public function destroyPicture(Request $request)
    {
        $user = $request->user('solves');
        $this->deleteStoredPicture($user->profile_picture);
        $user->update(['profile_picture' => null]);

        return back()->with('success', 'Profile picture removed.');
    }

    private function deleteStoredPicture(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
