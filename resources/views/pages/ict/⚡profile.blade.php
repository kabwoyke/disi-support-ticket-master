<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $first_name = '';
    public string $last_name = '';
    public $photo = null;

    public function mount(): void
    {
        $user = auth('support')->user();
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
    }

    public function save(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user = auth('support')->user();
        $data = [
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
        ];

        if ($this->photo) {
            $this->deleteOldPhoto($user->profile_picture);
            $data['profile_picture'] = $this->photo->store('avatars/support', 'public');
        }

        $user->update($data);
        $this->reset('photo');

        session()->flash('saved', 'Profile updated.');
    }

    public function removePhoto(): void
    {
        $user = auth('support')->user();
        $this->deleteOldPhoto($user->profile_picture);
        // support_teams.profile_picture is NOT NULL, so clear it with an empty string.
        $user->update(['profile_picture' => '']);

        session()->flash('saved', 'Profile picture removed.');
    }

    protected function deleteOldPhoto(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    public function render()
    {
        return view('pages::ict.⚡profile')->layout('layouts::support');
    }
};
?>

<div class="max-w-xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-base-content">My Profile</h2>
        <p class="text-xs text-base-content/70 mt-1">Change your name and profile picture. Customers see these in the chat.</p>
    </div>

    @if (session('saved'))
        <div role="alert" class="alert alert-success text-sm py-2">{{ session('saved') }}</div>
    @endif

    <form wire:submit="save" class="bg-base-100 rounded-box border border-base-300 shadow-sm p-6 space-y-5">
        <div class="flex items-center gap-4">
            <div class="avatar">
                <div class="w-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <img src="{{ $photo ? $photo->temporaryUrl() : auth('support')->user()->avatar_url }}" alt="Profile picture" />
                </div>
            </div>
            <div class="space-y-2">
                <input type="file" wire:model="photo" accept="image/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                <div class="flex items-center gap-3">
                    <span wire:loading wire:target="photo" class="loading loading-spinner loading-xs"></span>
                    @if (auth('support')->user()->profile_picture)
                        <button type="button" wire:click="removePhoto" wire:confirm="Remove your profile picture?" class="text-xs text-error hover:underline">Remove picture</button>
                    @endif
                </div>
                @error('photo') <p class="text-error text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="form-control w-full">
                <span class="label-text text-xs font-semibold mb-1 block">First name</span>
                <input type="text" wire:model="first_name" class="input input-bordered w-full" />
                @error('first_name') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </label>
            <label class="form-control w-full">
                <span class="label-text text-xs font-semibold mb-1 block">Last name</span>
                <input type="text" wire:model="last_name" class="input input-bordered w-full" />
                @error('last_name') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </label>
        </div>

        <label class="form-control w-full">
            <span class="label-text text-xs font-semibold mb-1 block">Email</span>
            <input type="email" value="{{ auth('support')->user()->email }}" class="input input-bordered w-full" disabled />
        </label>

        <button type="submit" class="btn btn-primary text-white" wire:loading.attr="disabled" wire:target="save,photo">
            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
            Save changes
        </button>
    </form>
</div>
