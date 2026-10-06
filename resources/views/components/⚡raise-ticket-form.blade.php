<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\TicketCategory;
use App\Models\Equipment;
use App\Models\SupportTeam;
use App\Models\TicketAssignment;
use App\Models\Desk;
use App\Notifications\NotifyTicket;
new class extends Component
{
    use WithFileUploads;

    #[Validate('required|min:5|max:255')]
    public string $subject = '';

    #[Validate('required')]
    public string $category = '';

     #[Validate('required')]
    public string $department = '';

    #[Validate('required')]
    public string $priority = '';

    #[Validate('nullable')]
    public string $equipment = '';

    #[Validate('required|min:10')]
    public string $description = '';

    #[Validate('required')]
    public string $desk = '';

    #[Validate(['attachments.*' => 'nullable|file|max:10240|mimes:png,jpg,jpeg,pdf'])]
    public array $attachments = [];


    public $name = "";





    public function updatedCategory($value): void
    {

        $this->equipment = '';
    }


    public function save()
    {
        $this->validate();
        $storedPaths = [];

        if (!empty($this->attachments)) {
        foreach ($this->attachments as $file) {
            // 'storage/app/public/attachments'
            $storedPaths[] = $file->store('attachments', 'public');
        }
    }

        try {
            DB::transaction(function () use($storedPaths) {

        $ticket = Ticket::create([
        'subject'        => $this->subject,
        'categoryId'    => $this->category,
        'departmentId'  => $this->department,
        'priority'       => $this->priority,
        'equipmentId'   => $this->equipment ?: null,
        'description'    => $this->description,
        'attachment_url' => json_encode($storedPaths),
        'deskId' => $this->desk,
        'userId' => auth()->id()
    ]);


        $supportTeam = SupportTeam::whereColumn('ticket_count' , '<' , 'max_ticket_capacity')
        ->where('ticket_category_id' , '=' , $ticket->categoryId)
        ->where('available', true)
        ->lockForUpdate()
        ->first();

      if ($supportTeam) {
                TicketAssignment::create([
                    'teamId'   => $supportTeam->id,
                    'ticketId' => $ticket->id,
                ]);

                $supportTeam->increment('ticket_count');

                if ($supportTeam->ticket_count >= $supportTeam->max_ticket_capacity) {
                    $supportTeam->update(['available' => false]);
}
                $supportTeam->notify(new NotifyTicket($ticket, 'created'));
                }


        $this->reset(['attachments']);

        session()->flash('success', [
    'Ticket created successfully!',
    'Your ticket has been assigned to ' . $supportTeam->first_name . ' ' . $supportTeam->last_name . '.'
]);
        return redirect()->route('create-ticket');
    });
        } catch (\Throwable $th) {
           foreach ($storedPaths as $path) {
            Storage::disk('public')->delete($path);
        }

        throw $th;
        }

    }

    public function render(){

        $departments = Department::all();
        $desks = Desk::all();
        $categories = TicketCategory::all();
        $equipments = $this->category
            ? Equipment::where('categoryId', $this->category)->get()
            : collect();
        return view("components.⚡raise-ticket-form" , [
            'departments' => $departments,
            'categories' => $categories,
            'equipments' => $equipments,
            'desks' => $desks
        ]);
    }
};
?>

<div class="max-w-4xl mx-auto p-6 md:p-8 bg-base-100 rounded-box shadow-lg border border-base-300">
    <!-- Header -->
    <div class="mb-6 border-b border-base-200 pb-4">
        <h2 class="text-xl font-bold text-base-content">Create New Support Ticket</h2>
        <h3>{{ $name }}</h3>
        <p class="text-xs text-base-content/70 mt-0.5">Fill out the details below to submit your request to the IT team.</p>
    </div>

    <!-- Alert Flash Message -->
   @if (session()->has('success'))
    <div role="alert" class="alert alert-success mb-6 text-sm py-2 text-success-content">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            @if (is_array(session('success')))
                <ul class="list-disc list-inside space-y-1">
                    @foreach (session('success') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @else
                <span>{{ session('success') }}</span>
            @endif
        </div>
    </div>
@endif

    <form wire:submit="save" class="space-y-5">
        <!-- Subject -->
        <div class="form-control w-full">
            <label for="subject" class="label text-sm font-medium text-base-content mb-1">Subject</label>
            <input
                type="text"
                id="subject"
                wire:model="subject"
                placeholder="e.g., Paper Jam"
                class="input input-bordered w-full text-sm focus:input-primary @error('subject') input-error @enderror"
            />
            @error('subject')
                <p class="text-xs text-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Grid Options: Category, Priority, Equipment -->
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
            <!-- Category -->
            <div class="form-control w-full">
                <label for="category" class="label text-sm font-medium text-base-content mb-1">Category</label>
                <select
                    id="category"
                    wire:model.live="category"
                    class="select select-bordered w-full text-sm focus:select-primary @error('category') select-error @enderror"
                >
                    <option value="">Select Category</option>
                    @foreach ($categories as $cat )
                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div class="form-control w-full">
                <label for="department" class="label text-sm font-medium text-base-content mb-1">Department</label>
                <select
                    id="department"
                    wire:model="department"
                    class="select select-bordered w-full text-sm focus:select-primary @error('department') select-error @enderror"
                >
                    <option value="">Select Department</option>
                    @foreach ($departments as $dept )
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
                @error('department')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Priority -->
            <div class="form-control w-full">
                <label for="priority" class="label text-sm font-medium text-base-content mb-1">Priority</label>
                <select
                    id="priority"
                    wire:model="priority"
                    class="select select-bordered w-full text-sm focus:select-primary @error('priority') select-error @enderror"
                >
                    <option value="">Select Priority</option>
                    <option value="LOW">Low</option>
                    <option value="MODERATE">Moderate</option>
                    <option value="HIGH">High</option>
                </select>
                @error('priority')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Equipment -->
            <div class="form-control w-full">
                <label for="equipment" class="label text-sm font-medium text-base-content mb-1">Equipment</label>
                <select
                    id="equipment"
                    wire:model="equipment"
                    @disabled(empty($category))
                    class="select select-bordered w-full text-sm focus:select-primary @error('equipment') select-error @enderror"
                >
                    <option value="">Select Equipment</option>
                    @foreach ($equipments as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                    @endforeach
                </select>
                @error('equipment')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Desk -->
        <div class="form-control w-full">
            <label for="desk" class="label text-sm font-medium text-base-content mb-1">Desk</label>
            <select
                id="desk"
                wire:model="desk"
                class="select select-bordered w-full text-sm focus:select-primary @error('desk') select-error @enderror"
            >
                <option value="">Select Desk</option>
                @foreach ($desks as $desk)
                    <option value="{{ $desk->id }}">{{ $desk->desk_name }}</option>
                @endforeach
            </select>
            @error('desk')
                <p class="text-xs text-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="form-control w-full">
            <label for="description" class="label text-sm font-medium text-base-content mb-1">Description</label>
            <textarea
                id="description"
                wire:model="description"
                rows="4"
                placeholder="Describe the issue in detail..."
                class="textarea textarea-bordered w-full text-sm focus:textarea-primary @error('description') textarea-error @enderror"
            ></textarea>
            @error('description')
                <p class="text-xs text-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Attachments Upload -->
        <div class="form-control w-full">
            <label class="label text-sm font-medium text-base-content mb-1">Attachments</label>
            <div class="flex items-center justify-center w-full">
                <label for="attachments" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-base-300 rounded-btn cursor-pointer bg-base-200/50 hover:bg-base-200 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-xs text-base-content"><span class="font-semibold text-primary">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-base-content/60 mt-1">PNG, JPG, PDF up to 10MB</p>
                    </div>
                    <input id="attachments" type="file" wire:model="attachments" multiple class="hidden" />
                </label>
            </div>

            <!-- Upload Progress Indicator -->
            <div wire:loading wire:target="attachments" class="mt-2 text-xs text-primary font-medium flex items-center gap-2">
                <span class="loading loading-spinner loading-xs text-primary"></span>
                Uploading file(s)...
            </div>

            <!-- File List Preview -->
            @if ($attachments)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($attachments as $file)
                        <span class="badge badge-primary badge-outline text-xs gap-1 py-3 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ $file->getClientOriginalName() }}
                        </span>
                    @endforeach
                </div>
            @endif

            @error('attachments.*')
                <p class="text-xs text-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
            <button
                type="button"
                class="btn btn-ghost text-sm font-medium text-base-content"
            >
                Cancel
            </button>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="btn btn-primary text-primary-content text-sm px-6"
            >
                <span wire:loading.remove wire:target="save">Submit Ticket</span>
                <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
            </button>
        </div>
    </form>
</div>
