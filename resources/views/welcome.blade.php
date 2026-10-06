<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disi Group | Support tickets and Disi Solves</title>
    <meta name="description" content="Raise a support ticket with Disi Group, or search Disi Solves for answers to scanning and IT support problems.">
    @vite(['resources/css/solves.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground">

    {{-- Navigation --}}
    <header class="sticky top-0 z-40 border-b border-white/10 bg-dark-green/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5">
           <a href="{{ url('/') }}" class="flex h-full shrink-0 items-center">
    <img src="{{ asset('disi-logo.avif') }}" alt="Disi Group" class="h-9 w-auto max-w-40 object-contain sm:h-10" width="160" height="40">
</a>
            <nav class="hidden items-center gap-8 text-sm text-white/70 md:flex">
                <a href="#services" class="hover:text-white focus-visible:text-white">Services</a>
                <a href="#how-it-works" class="hover:text-white focus-visible:text-white">How it works</a>
                <a href="#topics" class="hover:text-white focus-visible:text-white">Topics</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ url('/disi-solves/auth/login') }}" class="hidden rounded-md px-3 py-2 text-sm font-medium text-white/80 hover:text-white sm:inline-block">Disi Solves</a>
                <a href="{{ url('/auth/login') }}" class="rounded-md bg-lime-green px-4 py-2 text-sm font-semibold text-dark-green hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-green">Raise a ticket</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="bg-dark-green text-white">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-16 md:py-24 lg:grid-cols-2">
            <div>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
                    Scanning or IT problem? Get it fixed, or find the answer already written.
                </h1>
                <p class="mt-5 max-w-lg text-lg text-white/70">
                    Disi Group gives you two ways to get help: a support desk that tracks every request to resolution, and Disi Solves, a searchable library of problems and answers from our team and community.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/auth/login') }}" class="inline-flex items-center justify-center rounded-md bg-lime-green px-6 py-3 font-semibold text-dark-green hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-green">
                        Raise a support ticket
                    </a>
                    <a href="{{ url('/disi-solves/auth/login') }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-6 py-3 font-semibold text-white hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        Browse Disi Solves
                    </a>
                </div>
                <p class="mt-4 text-sm text-white/50">Sign in with your Disi account to continue.</p>
            </div>

            {{-- Product preview: a ticket and an accepted answer --}}
            <div class="relative mx-auto w-full max-w-md" aria-hidden="true">
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <div class="flex items-center justify-between text-xs text-white/60">
                        <span class="font-medium text-white">Ticket #2048</span>
                        <span class="rounded-full bg-lime-green/20 px-2.5 py-1 font-medium text-lime-green">In progress</span>
                    </div>
                    <p class="mt-3 font-medium">Scanner skips every second page on the batch feeder</p>
                    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full w-2/3 rounded-full bg-lime-green"></div>
                    </div>
                    <p class="mt-2 text-xs text-white/50">Assigned to a support engineer. Last update 12 minutes ago.</p>
                </div>
                <div class="-mt-2 ml-8 rounded-xl bg-background p-5 text-foreground shadow-xl">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span class="rounded bg-lime-green px-2 py-0.5 font-semibold text-dark-green">Solved</span>
                        <span>Disi Solves</span>
                    </div>
                    <p class="mt-3 font-medium">Softrac scanner shows "Rear camera error" on startup</p>
                    <p class="mt-1 text-sm text-muted-foreground">Unplug the rear camera cable, reconnect it firmly, then restart the Softrac service and recalibrate the camera before scanning.</p>
                    <p class="mt-3 text-xs text-muted-foreground">24 people found this helpful</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section id="services" class="mx-auto max-w-6xl px-5 py-20">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-semibold tracking-tight">Pick the route that fits your problem</h2>
            <p class="mt-3 text-muted-foreground">Start with Disi Solves if you think someone has hit this before. Raise a ticket when you need an engineer to look at your case.</p>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <article class="flex flex-col rounded-xl border bg-card p-8">
                <div class="grid size-11 place-items-center rounded-lg bg-dark-green text-lime-green">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/></svg>
                </div>
                <h3 class="mt-5 text-xl font-semibold">Support tickets</h3>
                <p class="mt-2 text-muted-foreground">Describe the issue, attach screenshots or scan samples, and follow the request from submission to resolution.</p>
                <ul class="mt-5 space-y-2 text-sm">
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Every ticket has a status and an assigned engineer</li>
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Full conversation history in one place</li>
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Updates as soon as your ticket changes</li>
                </ul>
                <a href="{{ url('/auth/login') }}" class="mt-8 inline-flex w-fit items-center rounded-md bg-lime-green px-5 py-2.5 font-semibold text-dark-green hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-green">
                    Raise a support ticket
                </a>
            </article>

            <article class="flex flex-col rounded-xl border bg-card p-8">
                <div class="grid size-11 place-items-center rounded-lg bg-dark-green text-lime-green">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
                <h3 class="mt-5 text-xl font-semibold">Disi Solves</h3>
                <p class="mt-2 text-muted-foreground">Search real problems about scanning and IT support, read the accepted answers, and post your own question if yours is new.</p>
                <ul class="mt-5 space-y-2 text-sm">
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Answers marked as solved by the asker or our team</li>
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Vote on answers that worked for you</li>
                    <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-lime-green"></span>Browse by scanner, software, or error message</li>
                </ul>
                <a href="{{ url('/disi-solves/auth/login') }}" class="mt-8 inline-flex w-fit items-center rounded-md bg-secondary px-5 py-2.5 font-semibold text-secondary-foreground hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground">
                    Browse Disi Solves
                </a>
            </article>
        </div>
    </section>

    {{-- How it works: a real sequence, so numbered --}}
    <section id="how-it-works" class="border-y bg-muted/50">
        <div class="mx-auto max-w-6xl px-5 py-20">
            <h2 class="max-w-2xl text-3xl font-semibold tracking-tight">From problem to fix</h2>
            <ol class="mt-10 grid gap-8 md:grid-cols-4">
                @foreach ([
                    ['Search first', 'Look up your error or symptom in Disi Solves. Many issues already have a tested answer.'],
                    ['Raise a ticket', 'If nothing fits, sign in and describe what happened, with files or screenshots.'],
                    ['Track progress', 'See who is handling your request and reply to questions from the engineer.'],
                    ['Share the fix', 'Once resolved, the solution can be added to Disi Solves to help the next person.'],
                ] as $i => [$title, $text])
                    <li>
                        <span class="grid size-8 place-items-center rounded-full bg-dark-green text-sm font-semibold text-lime-green">{{ $i + 1 }}</span>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Topics --}}
    <section id="topics" class="mx-auto max-w-6xl px-5 py-20">
        <h2 class="max-w-2xl text-3xl font-semibold tracking-tight">What people solve here</h2>
        <p class="mt-3 max-w-2xl text-muted-foreground">Typical topics in the library and the support desk.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            @foreach (['Scanner drivers', 'Image quality and DPI', 'Batch scanning', 'Skewed or cropped pages', 'PDF and OCR output', 'Network scanners', 'Printer and device setup', 'Account and password access', 'Software installation', 'Slow computers', 'Email and Wi-Fi'] as $topic)
                <span class="rounded-full border bg-card px-4 py-2 text-sm">{{ $topic }}</span>
            @endforeach
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-dark-green text-white">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-5 py-14 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Need help today?</h2>
                <p class="mt-2 text-white/70">Sign in to raise a ticket, or look for an answer first.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ url('/auth/login') }}" class="inline-flex items-center justify-center rounded-md bg-lime-green px-6 py-3 font-semibold text-dark-green hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-green">Raise a support ticket</a>
                <a href="{{ url('/disi-solves/auth/login') }}" class="inline-flex items-center justify-center rounded-md border border-white/25 px-6 py-3 font-semibold hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Browse Disi Solves</a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-5 py-8 text-sm text-muted-foreground sm:flex-row">
            <p>&copy; {{ date('Y') }} Disi Group. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="{{ url('/auth/login') }}" class="hover:text-foreground">Support tickets</a>
                <a href="{{ url('/disi-solves/auth/login') }}" class="hover:text-foreground">Disi Solves</a>
            </div>
        </div>
    </footer>

</body>
</html>
