<x-layouts::app>
  <div class="bg-base-100 flex h-screen overflow-hidden text-base-content"
       x-data="chatNotifier({ channel: 'App.Models.SupportTeam.{{ auth('support')->id() }}', unread: {{ (int) auth('support')->user()->unreadNotifications()->where('data->kind', 'chat')->count() }} })"
       @chat-unread.window="unread = $event.detail.count">
        <x-chat.notifier-toast />

        <!-- ICT Sidebar (Uses base-200 for dark mode compatibility) -->
        <div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"></div>
        <button type="button" @click="nav = true" aria-label="Open menu"
                class="md:hidden fixed top-3 left-3 z-20 btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <aside class="bg-base-200 flex h-screen w-64 flex-col justify-between p-4 shrink-0 border-r border-base-300 overflow-y-auto fixed md:static inset-y-0 left-0 z-40 transition-transform -translate-x-full md:translate-x-0"
               :class="{ 'translate-x-0': nav }" id="sidebar">
            <div class="space-y-6">

                <!-- Logo -->
                <div class="flex items-center justify-center py-2" id="logo">
                    <img src="/disi-logo.avif" class="h-auto w-28 object-contain" alt="DISI Logo">
                </div>

                <!-- Navigation -->
                <nav id="sidebar-items-container">
                    <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-base-content/60">ICT Operations</div>
                    <ul class="space-y-1">
                        <!-- Assigned / Active Queue -->
                        <li>
                            <a wire:navigate href="{{ route('ict-dashboard') }}"
                               class="flex items-center justify-between rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('ict-dashboard') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>

                                    <span>Home</span>
                                </div>

                            </a>
                        </li>

                        <!-- Floor Plan & Desk Map -->
                        <li>
                            <a wire:navigate href="{{ route('manage-desk') }}"
                               class="flex items-center gap-3 rounded-btn active:bg-primary-600 px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('manage-desk') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Manage Desk</span>
                            </a>
                        </li>

                        <!-- Asset / Hardware Inventory -->
                        <li>
                            <a wire:navigate href="{{ route('manage-asset') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('manage-asset') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                <span>Hardware & Assets</span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('view-notification') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('view-notification') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                            </svg>

                                <span>Notifications</span>
                            </a>
                        </li>


                         <li>
                            <a wire:navigate href="{{ route('my-tickets') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('my-tickets') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
</svg>


                                <span>My Tickets</span>
                            </a>
                        </li>


                        <li>
                            <a wire:navigate href="{{ route('support-chat') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('support-chat') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>


                                <span>Chat</span>
                                <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="badge badge-primary badge-sm text-white ml-auto"></span>
                            </a>
                        </li>
                    </ul>

                    <div class="px-3 mt-5 mb-2 text-xs font-semibold uppercase tracking-wider text-base-content/60">Knowledge Base</div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ url('/disi-solves/search') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all hover:bg-base-300 text-base-content">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Search Solutions</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/disi-solves/dashboard') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all hover:bg-base-300 text-base-content">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span>DISI Solves</span>
                            </a>
                        </li>
                    </ul>

                    <div class="px-3 mt-5 mb-2 text-xs font-semibold uppercase tracking-wider text-base-content/60">Switch Portal</div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('create-ticket') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all hover:bg-base-300 text-base-content">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Clerk Portal</span>
                            </a>
                        </li>
                    </ul>

                </nav>
            </div>

            <!-- ICT Officer Info & Logout -->
            <div class="border-base-300 border-t pt-4 space-y-3">
                <a wire:navigate href="{{ route('support-profile') }}" title="Edit profile"
                   class="flex items-center gap-3 px-2 py-1.5 bg-base-300/50 rounded-btn hover:bg-base-300 transition-colors">
                    <div class="avatar">
                        <div class="w-8 rounded-full">
                            <img src="{{ auth()->guard('support')->user()->avatar_url }}" alt="Profile" />
                        </div>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold truncate text-base-content">{{ auth()->guard('support')->user()->display_name }}</p>
                        <p class="text-[10px] text-base-content/70 truncate">My profile</p>
                    </div>
                </a>
                <ul>
                 <li>
                            <a wire:navigate href="{{ route('system-health') }}"
                               class="flex items-center gap-3 rounded-btn px-4 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('system-health') ? 'bg-primary text-primary-content shadow-sm' : 'hover:bg-base-300 text-base-content' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                <span>System Health</span>
                            </a>
                        </li>
                </ul>
                <form action="{{ route('support-logout') }}" method="POST">
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

        <!-- Livewire View Content Area -->
        <main class="flex-1 overflow-y-auto bg-base-300/30 p-6 pt-16 md:pt-8 md:p-8" id="main-content">
            <div class="max-w-6xl mx-auto space-y-6">
                {{ $slot }}
            </div>
        </main>

    </div>
</x-layouts::app>
