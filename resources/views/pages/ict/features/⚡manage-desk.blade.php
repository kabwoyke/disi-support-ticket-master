<?php

use App\Models\Desk;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    // Create form property
    public string $desk_name = '';

    // Modal & update properties
    public ?Desk $selectedDesk = null;
    public string $edit_desk_name = '';
    public bool $showEditModal = false;

    /**
     * Create a new desk
     */
    public function createDesk(): void
    {
        $this->validate([
            'desk_name' => 'required|string|max:255|unique:desks,desk_name',
        ]);

        Desk::create([
            'desk_name' => $this->desk_name,
        ]);

        $this->reset('desk_name');
        session()->flash('message', 'Desk created successfully!');
    }

    /**
     * Open update modal
     */
    public function openEditModal(Desk $desk): void
    {
        $this->selectedDesk = $desk;
        $this->edit_desk_name = $desk->desk_name;
        $this->showEditModal = true;
    }

    /**
     * Update the active desk
     */
    public function updateDesk(): void
    {
        $this->validate([
            'edit_desk_name' => 'required|string|max:255|unique:desks,desk_name,' . $this->selectedDesk->id,
        ]);

        $this->selectedDesk->update([
            'desk_name' => $this->edit_desk_name,
        ]);

        $this->closeEditModal();
        session()->flash('message', 'Desk updated successfully!');
    }

    /**
     * Close modal and clear temporary state
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['selectedDesk', 'edit_desk_name']);
    }

    /**
     * Delete desk record
     */
    public function deleteDesk(Desk $desk): void
    {
        $desk->delete();
        session()->flash('message', 'Desk deleted successfully!');
    }

    /**
     * Pass paginated query data to the view
     */
    public function with(): array
    {
        return [
            'desks' => Desk::latest()->paginate(10),
        ];
    }

    public function render(){
        return view("pages::ict.features.⚡manage-desk")->layout('layouts::support');
    }
};
?>

<div class="p-6 space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between border-b pb-4 border-base-300">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Manage Desks</h1>
            <p class="text-sm text-base-content/70">Create, edit, and organize system support desks</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div role="alert" class="alert alert-success shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Desk Form -->
        <div class="card bg-base-100 border border-base-200 shadow-sm h-fit">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">Add New Desk</h2>
                <form wire:submit="createDesk" class="space-y-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-medium">Desk Name</span>
                        </label>
                        <input
                            type="text"
                            wire:model="desk_name"
                            placeholder="e.g., IT Support Desk"
                            class="input input-bordered w-full @error('desk_name') input-error @enderror"
                        />
                        @error('desk_name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        <span wire:loading.remove wire:target="createDesk">Add Desk</span>
                        <span wire:loading wire:target="createDesk" class="loading loading-spinner loading-xs"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Desks List Table -->
        <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-0">
                <div class="p-4 border-b border-base-200">
                    <h2 class="card-title text-lg">Existing Desks</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Desk Name</th>
                                <th>Created Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($desks as $desk)
                                <tr wire:key="{{ $desk->id }}">
                                    <th>{{ $desk->id }}</th>
                                    <td class="font-medium">{{ $desk->desk_name }}</td>
                                    <td class="text-xs text-base-content/70">{{ $desk->created_at->format('M d, Y') }}</td>
                                    <td class="text-right space-x-1">
                                        <button
                                            wire:click="openEditModal({{ $desk->id }})"
                                            class="btn btn-square btn-ghost btn-xs text-info"
                                            title="Edit Desk"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 text-primary-500 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="deleteDesk({{ $desk->id }})"
                                            wire:confirm="Are you sure you want to delete this desk?"
                                            class="btn btn-square btn-ghost btn-xs text-error"
                                            title="Delete Desk"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-base-content/60">
                                        No desks found. Add one above!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($desks->hasPages())
                    <div class="p-4 border-t border-base-200">
                        {{ $desks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- DaisyUI Edit Modal -->
    <dialog class="modal {{ $showEditModal ? 'modal-open' : '' }}">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Update Desk</h3>
            <form wire:submit="updateDesk" class="py-4 space-y-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-medium">Desk Name</span>
                    </label>
                    <input
                        type="text"
                        wire:model="edit_desk_name"
                        class="input input-bordered w-full @error('edit_desk_name') input-error @enderror"
                    />
                    @error('edit_desk_name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="closeEditModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="updateDesk">Save Changes</span>
                        <span wire:loading wire:target="updateDesk" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeEditModal">close</button>
        </form>
    </dialog>
</div>
