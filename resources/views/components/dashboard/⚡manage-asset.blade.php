<?php

use App\Models\Equipment;
use App\Models\TicketCategory;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    // Form inputs for Create/Update
    public ?int $editing_id = null;
    public string $name = '';
    public ?int $category_id = null;


    public ?Equipment $viewingAsset = null;

    public bool $showFormModal = false;
    public bool $showViewModal = false;


    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:ticket_categories,id',
        ];
    }

    /**
     * Open modal to create a new asset
     */
    public function openCreateModal(): void
    {
        $this->reset(['editing_id', 'name', 'category_id']);
        $this->resetValidation();
        $this->showFormModal = true;
    }

    /**
     * Open modal to edit an existing asset
     */
    public function openEditModal(Equipment $asset): void
    {
        $this->resetValidation();
        $this->editing_id = $asset->id;
        $this->name = $asset->name;
        $this->category_id = $asset->category_id;
        $this->showFormModal = true;
    }

    /**
     * Open modal to view asset details
     */
    public function openViewModal(Equipment $asset): void
    {
        $this->viewingAsset = $asset->load('category');
        $this->showViewModal = true;
    }

    /**
     * Save asset (handles both create and update)
     */
    public function saveAsset(): void
    {
        $validated = $this->validate();

        Equipment::updateOrCreate(
            ['id' => $this->editing_id],
            ["name" => $validated['name'] , "categoryId" => $validated["category_id"]]
        );

        $message = $this->editing_id ? 'Asset updated successfully!' : 'Asset added successfully!';
        session()->flash('message', $message);

        $this->closeFormModal();
    }

    /**
     * Close the creation/update modal
     */
    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->reset(['editing_id', 'name', 'category_id']);
    }

    /**
     * Close the viewing modal
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->reset('viewingAsset');
    }

    /**
     * Delete an asset
     */
    public function deleteAsset($id): void
    {
        Equipment::findOrFail($id)->delete();
        session()->flash('message', 'Asset deleted successfully!');
    }

    public function with(): array
    {
        $assets = Equipment::with('category')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($q) {
                          $q->where('category_name', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->get();

        return [
            'assets' => $assets,
            'categories' => TicketCategory::all(),
        ];
    }
};
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Hardware & Asset Management</h1>
            <p class="text-sm text-base-content/70">Manage office equipment registered across ticket categories.</p>
        </div>
        <div>
            <button wire:click="openCreateModal" class="btn btn-primary gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add New Asset
            </button>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div role="alert" class="alert alert-success shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="bg-base-100 border border-primary-200/50 rounded-box p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search asset or category..."
                class="input input-bordered w-full pl-10 input-sm sm:input-md"
            />
            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <div class="text-xs text-base-content/60 self-end sm:self-center">
            Total Items: <span class="font-bold text-primary">{{ count($assets) }}</span>
        </div>
    </div>

    <!-- Asset Table Card -->
    <div class="bg-base-100 border border-primary-200/50 rounded-box shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-left">
                <thead class="bg-base-200/50 text-xs text-base-content/70 uppercase">
                    <tr>
                        <th>Equipment Name</th>
                        <th>Ticket Category</th>
                        <th>Created Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200 text-sm">
                    @forelse($assets as $asset)
                        <tr wire:key="asset-{{ $asset->id }}" class="hover:bg-base-200/30 transition-colors">
                            <td>
                                <div class="font-semibold text-base-content">{{ $asset->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-primary badge-outline font-medium text-xs">
                                    {{ $asset->category->category_name ?? $asset->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="text-xs text-base-content/70 whitespace-nowrap">
                                {{ $asset->created_at ? $asset->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="text-right whitespace-nowrap space-x-1">
                                <!-- View Action -->
                                <button
                                    wire:click="openViewModal({{ $asset->id }})"
                                    class="btn btn-ghost btn-xs text-primary-500 gap-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 text-primary-500 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    View
                                </button>

                                <!-- Edit Action -->
                                <button
                                    wire:click="openEditModal({{ $asset->id }})"
                                    class="btn btn-ghost btn-xs text-warning gap-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    Edit
                                </button>

                                <!-- Delete Action -->
                                <button
                                    wire:click="deleteAsset({{ $asset->id }})"
                                    wire:confirm="Are you sure you want to delete this equipment asset?"
                                    class="btn btn-ghost btn-xs text-error gap-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-12 text-base-content/60">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="font-medium">No equipment found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Update Modal -->
    <dialog class="modal {{ $showFormModal ? 'modal-open' : '' }}">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">
                {{ $editing_id ? 'Update Asset' : 'Add New Asset' }}
            </h3>

            <form wire:submit="saveAsset" class="space-y-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-medium">Equipment Name</span>
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="e.g. Dell Latitude 5520"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                    />
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-medium">Ticket Category</span>
                    </label>
                    <select
                        wire:model="category_id"
                        class="select select-bordered w-full @error('category_id') select-error @enderror"
                    >
                        <option value="">Select a Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->category_name ?? $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="closeFormModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="saveAsset">
                            {{ $editing_id ? 'Save Changes' : 'Create Asset' }}
                        </span>
                        <span wire:loading wire:target="saveAsset" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeFormModal">close</button>
        </form>
    </dialog>

    <!-- View Asset Details Modal -->
    <dialog class="modal {{ $showViewModal ? 'modal-open' : '' }}">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Asset Details</h3>

            @if($viewingAsset)
                <div class="space-y-3">
                    <div class="bg-base-200/50 p-3 rounded-lg">
                        <span class="text-xs text-base-content/60 block">Asset ID</span>
                        <span class="font-semibold text-sm">#{{ $viewingAsset->id }}</span>
                    </div>
                    <div class="bg-base-200/50 p-3 rounded-lg">
                        <span class="text-xs text-base-content/60 block">Equipment Name</span>
                        <span class="font-semibold text-sm">{{ $viewingAsset->name }}</span>
                    </div>
                    <div class="bg-base-200/50 p-3 rounded-lg">
                        <span class="text-xs text-base-content/60 block">Category</span>
                        <span class="badge badge-primary badge-outline mt-1">
                            {{ $viewingAsset->category->category_name ?? $viewingAsset->category->name ?? 'Uncategorized' }}
                        </span>
                    </div>
                    <div class="bg-base-200/50 p-3 rounded-lg">
                        <span class="text-xs text-base-content/60 block">Registered Date</span>
                        <span class="font-semibold text-sm">
                            {{ $viewingAsset->created_at ? $viewingAsset->created_at->format('M d, Y h:i A') : 'N/A' }}
                        </span>
                    </div>
                </div>
            @endif

            <div class="modal-action">
                <button type="button" wire:click="closeViewModal" class="btn btn-ghost">Close</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" wire:click="closeViewModal">close</button>
        </form>
    </dialog>

</div>
