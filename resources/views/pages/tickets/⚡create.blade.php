<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('pages.tickets.⚡create')->layout('layouts::user');
    }
};
?>

<div>
    <livewire:raise-ticket-form />
</div>
