<x-layouts::app>
    <div class="bg-base-100 flex h-screen overflow-hidden text-base-content">

        <!-- Sidebar using DaisyUI base colors -->
        <aside class="bg-base-200 flex h-screen w-64 flex-col justify-between p-4 shrink-0 border-r border-base-300" id="sidebar">
            <div class="space-y-6">

                <div class="flex items-center justify-center py-2" id="logo">
                    <img src="/disi-logo.avif" class="h-auto w-28 object-contain" alt="DISI Logo">
                </div>

          <nav id="sidebar-items-container">
    <ul class="space-y-1">
        <li>
            <a wire:navigate href="{{ route('create-ticket') }}"
               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('create-ticket') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content dark:text-white' }}">
                <svg class="h-5 dark:text-white dark:font-semibold w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class=" dark:text-white ">Raise a Ticket</span>
            </a>
        </li>

        <li>
            <a wire:navigate href="{{ route('get-tickets') }}"
               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('get-tickets') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content dark:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 dark:text-white dark:font-semibold w-5">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
</svg>

                <span class=" dark:text-white ">View Tickets</span>
            </a>
        </li>

        {{-- <li>
            <a wire:navigate href="{{ route('user-chat') }}"
               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('user-chat') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content dark:text-white' }}">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 dark:text-white dark:font-semibold w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
             </svg>


                    <span class=" dark:text-white ">Chat</span>
                </a>
        </li> --}}
    </ul>
</nav>
            </div>


            <div class="border-base-300 border-t pt-4 space-y-1">
                <ul class="space-y-1 text-sm font-medium">
                    <li>
                        <a wire:navigate href="{{ route("help-page") }}"
                           class="flex items-center gap-3 rounded-btn px-4 py-2 transition-colors {{ request()->routeIs('help-page') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Help</span>
                        </a>
                    </li>
                    <li>
                        <a href=""
                           class="flex items-center gap-3 rounded-btn px-4 py-2 transition-colors {{ request()->routeIs('support') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Support</span>
                        </a>
                    </li>
                </ul>


                <form action="{{ route("logout") }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="w-full hover:bg-error/10 text-error flex items-center gap-3 rounded-btn px-4 py-2 text-sm font-medium transition-colors text-left"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>


        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto bg-base-300/30 p-6 md:p-8" id="main-content">
            <div class="max-w-5xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts::app>
