<?php

use Livewire\Component;

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login(): void
    {
        $this->validate();

        if (auth()->guard('support')->attempt(
        ['email' => $this->email, 'password' => $this->password],
        $this->remember
    )) {
        request()->session()->regenerate();

        $this->redirectIntended(route('ict-dashboard'));
        return;
    }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};
?>

<div class="min-h-screen bg-base-200/60 flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">

        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center p-3 bg-primary-100 rounded-sm mb-1">
                <img src="/disi-logo.avif" class="h-10 w-auto object-contain" alt="DISI Logo" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-base-content">Support Login</h1>
            <p class="text-xs text-base-content/70">Sign in with your email to access the dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="bg-base-100 border border-primary-200/50 rounded-box p-6 sm:p-8 shadow-sm space-y-5">
            <form wire:submit="login" class="space-y-4">

                <!-- Email Input -->
                <div class="form-control w-full">
                    <label class="label pb-1">
                        <span class="label-text font-semibold text-xs uppercase tracking-wider text-base-content/70">ICT Staff Email</span>
                    </label>
                    <div class="relative">
                        <input
                            wire:model="email"
                            type="email"
                            placeholder="officer@domain.com"
                            class="input input-bordered w-full pl-10 text-sm @error('email') input-error @enderror"
                            required
                            autofocus
                        />
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    @error('email')
                        <label class="label pt-1 pb-0">
                            <span class="label-text-alt text-error font-medium">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="form-control w-full">
                    <label class="label pb-1 flex justify-between">
                        <span class="label-text font-semibold text-xs uppercase tracking-wider text-base-content/70">Password</span>
                    </label>
                    <div class="relative">
                        <input
                            wire:model="password"
                            type="password"
                            placeholder="••••••••"
                            class="input input-bordered w-full pl-10 text-sm @error('password') input-error @enderror"
                            required
                        />
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    @error('password')
                        <label class="label pt-1 pb-0">
                            <span class="label-text-alt text-error font-medium">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input wire:model="remember" type="checkbox" class="checkbox checkbox-primary checkbox-sm rounded" />
                        <span class="text-xs font-medium text-base-content/80">Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full text-white gap-2 mt-2">
                    <span wire:loading.remove wire:target="login">Sign In</span>
                    <span wire:loading wire:target="login" class="loading loading-spinner loading-xs"></span>
                    <svg wire:loading.remove wire:target="login" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>

            </form>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-xs text-base-content/50 space-y-1">
            <p>Protected System — Authorized Personnel Only</p>
            <p>&copy; {{ date('Y') }} DISI ICT Department</p>
        </div>

    </div>
</div>
