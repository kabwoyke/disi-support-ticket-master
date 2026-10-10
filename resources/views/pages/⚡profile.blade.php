<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public $photo = null;

    public function mount(): void
    {
        $this->name = (string) auth()->user()->name;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();
        $data = ['name' => trim($this->name)];

        if ($this->photo) {
            $this->deleteOldPhoto($user->profile_picture);
            $data['profile_picture'] = $this->photo->store('avatars/users', 'public');
        }

        $user->update($data);
        $this->reset('photo');

        session()->flash('saved', 'Profile updated.');
    }

    public function removePhoto(): void
    {
        $user = auth()->user();
        $this->deleteOldPhoto($user->profile_picture);
        $user->update(['profile_picture' => null]);

        session()->flash('saved', 'Profile picture removed.');
    }

    protected function deleteOldPhoto(?string $path): void
    {
        // Only delete files we stored ourselves, never external URLs.
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    public function render()
    {
        return view('pages.⚡profile')->layout('layouts::user');
    }
};
?>

<div class="max-w-xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-base-content">My Profile</h2>
        <p class="text-xs text-base-content/70 mt-1">Change your display name and profile picture.</p>
    </div>

    @if (session('saved'))
        <div role="alert" class="alert alert-success text-sm py-2">{{ session('saved') }}</div>
    @endif

    <form wire:submit="save" class="bg-base-100 rounded-box border border-base-300 shadow-sm p-6 space-y-5">
        <div class="flex items-center gap-4">
            <div class="avatar">
                <div class="w-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <img src="{{ $photo ? $photo->temporaryUrl() : auth()->user()->avatar_url }}" alt="Profile picture" />
                </div>
            </div>
            <div class="space-y-2">
                <input type="file" wire:model="photo" accept="image/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                <div class="flex items-center gap-3">
                    <span wire:loading wire:target="photo" class="loading loading-spinner loading-xs"></span>
                    @if (auth()->user()->profile_picture)
                        <button type="button" wire:click="removePhoto" wire:confirm="Remove your profile picture?" class="text-xs text-error hover:underline">Remove picture</button>
                    @endif
                </div>
                @error('photo') <p class="text-error text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="form-control w-full">
            <span class="label-text text-xs font-semibold mb-1 block">Name</span>
            <input type="text" wire:model="name" class="input input-bordered w-full" />
            @error('name') <span class="text-error text-xs">{{ $message }}</span> @enderror
        </label>

        <label class="form-control w-full">
            <span class="label-text text-xs font-semibold mb-1 block">Email</span>
            <input type="email" value="{{ auth()->user()->email }}" class="input input-bordered w-full" disabled />
        </label>

        <button type="submit" class="btn btn-primary text-white" wire:loading.attr="disabled" wire:target="save,photo">
            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
            Save changes
        </button>
    </form>
</div>
