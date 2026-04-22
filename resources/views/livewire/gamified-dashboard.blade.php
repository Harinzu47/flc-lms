{{--
    Gamified Dashboard — FLC UMJ Student Portal
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Gamified Dashboard
    Backend:  App\Livewire\GamifiedDashboard
    Layout:   layouts.gamified (owns the Top App Bar, Sidebar chrome, and Toast)
    ────────────────────────────────────────────────────────────────────────────

    This view is a coordinator only. Partials live in:
      resources/views/livewire/partials/dashboard/

    All partials share this component's variable scope via @include.
    ────────────────────────────────────────────────────────────────────────────
--}}

{{-- ── Sidebar sections for the gamified layout ────────────────────────────── --}}
@section('sidebar-title')
    <h2 class="text-blue-800 font-bold font-headline">My Dashboard</h2>
    <p class="text-xs text-on-surface-variant mt-0.5">
        {{ $user->currentLevel()?->name ?? 'Novice' }}
    </p>
@endsection

@section('sidebar-nav')
    <a href="{{ route('dashboard') }}"
       class="flex items-center gap-3 bg-surface-container-lowest text-primary rounded-xl px-4 py-3 shadow-sm font-medium"
       aria-current="page">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">school</span>
        <span>Academy</span>
    </a>
    <a href="#"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
        <span>Library</span>
    </a>
    <a href="{{ route('admin.grading') }}"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">admin_panel_settings</span>
        <span>Admin</span>
    </a>
@endsection

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────────────── --}}
<div class="max-w-screen-2xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- ── LEFT COLUMN (col-span-8): Primary Content ───────────────────── --}}
        <div class="lg:col-span-8 space-y-8">

            {{-- Player Card: name, level, XP counter, progress bar --}}
            @include('livewire.partials.dashboard.profile-header')

            {{-- Badges / Achievements grid --}}
            @include('livewire.partials.dashboard.badges-grid')

            {{-- Recent XP Activity Feed --}}
            @include('livewire.partials.dashboard.recent-activity')

        </div>{{-- /left col --}}

        {{-- ── RIGHT COLUMN (col-span-4): Mini-Sidebar Widgets ────────────── --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- Mini Leaderboard — Global Arena --}}
            <section class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/10"
                     aria-labelledby="leaderboard-heading">
                <h2 id="leaderboard-heading"
                    class="text-xl font-bold mb-6 flex items-center gap-2 font-headline">
                    <span class="material-symbols-outlined text-tertiary"
                          style="font-variation-settings:'FILL' 1;"
                          aria-hidden="true">leaderboard</span>
                    Global Arena
                </h2>
                <div class="space-y-3">
                    @php
                        $medalColors = ['text-tertiary border-tertiary', 'text-slate-400 border-slate-300', 'text-amber-700/50 border-amber-600/30'];
                    @endphp

                    @foreach($leaderboard as $rank => $student)
                        @php $isMe = $student->id === $user->id; @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl shadow-sm
                            {{ $isMe ? 'bg-primary/5 border border-primary/10' : 'bg-surface-container-lowest' }}
                            {{ isset($medalColors[$rank]) ? 'border-l-4 ' . explode(' ', $medalColors[$rank])[1] : ($isMe ? '' : 'border-l-4 border-transparent') }}"
                             aria-label="{{ $isMe ? 'You' : $student->name }}, rank {{ $rank + 1 }}, {{ number_format($student->total_xp) }} XP">
                            <div class="flex items-center gap-3">
                                <span class="font-black w-5 text-sm {{ isset($medalColors[$rank]) ? explode(' ', $medalColors[$rank])[0] : ($isMe ? 'text-primary' : 'text-on-surface-variant') }}"
                                      aria-hidden="true">
                                    {{ $rank + 1 }}
                                </span>
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $isMe ? 'from-primary to-primary-container' : 'from-surface-container-high to-surface-container' }} flex items-center justify-center text-{{ $isMe ? 'on-primary' : 'on-surface-variant' }} font-bold text-xs flex-shrink-0"
                                     aria-hidden="true">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold {{ $isMe ? 'text-primary' : 'text-on-surface' }}">
                                        {{ $isMe ? 'You' : $student->name }}
                                    </p>
                                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant">
                                        {{ $student->currentLevel()?->name ?? 'Novice' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-sm font-black {{ $isMe ? 'text-primary' : 'text-on-surface' }}">
                                {{ number_format($student->total_xp) }} XP
                            </span>
                        </div>
                    @endforeach

                    @if($leaderboard->doesntContain('id', $user->id))
                        {{-- Current user is outside top 5 — show them separately --}}
                        <div class="pt-3 mt-1" style="border-top: 1px solid rgba(195,198,215,0.3);">
                            <div class="flex items-center justify-between p-3 bg-primary/5 rounded-xl border border-primary/10">
                                <div class="flex items-center gap-3">
                                    <span class="font-black text-primary w-5 text-sm" aria-hidden="true">…</span>
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-xs"
                                         aria-hidden="true">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-primary">You</p>
                                        <p class="text-[10px] uppercase tracking-widest text-primary/60">
                                            {{ $user->currentLevel()?->name ?? 'Novice' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-sm font-black text-primary">
                                    {{ number_format($user->total_xp) }} XP
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Critical Tasks (upcoming deadlines) --}}
            <section class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/10"
                     aria-labelledby="critical-tasks-heading">
                <h2 id="critical-tasks-heading"
                    class="text-xl font-bold mb-6 flex items-center gap-2 font-headline">
                    <span class="material-symbols-outlined text-error" aria-hidden="true">event_upcoming</span>
                    Critical Tasks
                </h2>

                @if($upcomingTasks->isEmpty())
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-4xl text-outline-variant" aria-hidden="true">task_alt</span>
                        <p class="text-on-surface-variant mt-2 text-sm">No upcoming deadlines. Nice work!</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($upcomingTasks as $task)
                            @php
                                $isToday    = $task->deadline->isToday();
                                $isTomorrow = $task->deadline->isTomorrow();
                                $urgencyBg  = $isToday ? 'border-error' : 'border-tertiary';
                                $badgeBg    = $isToday ? 'bg-error/10 text-error' : 'bg-tertiary/10 text-tertiary';
                                $badgeText  = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $task->deadline->format('d M'));
                            @endphp
                            <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm border-r-4 {{ $urgencyBg }}"
                                 aria-label="{{ $task->title }}, due {{ $badgeText }}">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-sm text-on-surface">{{ $task->title }}</h4>
                                    <span class="{{ $badgeBg }} px-2 py-0.5 rounded text-[10px] font-black uppercase flex-shrink-0 ml-2">
                                        {{ $badgeText }}
                                    </span>
                                </div>
                                <p class="text-xs text-on-surface-variant mb-3 line-clamp-2">
                                    +{{ $task->base_xp }} XP reward · {{ ucfirst(str_replace('_', ' ', $task->type)) }}
                                </p>
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('tasks.show', $task) }}"
                                       class="{{ $isToday ? 'bg-error text-on-error hover:opacity-90' : 'bg-surface-container-high text-on-surface hover:bg-surface-dim' }} px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">
                                        {{ $isToday ? 'Submit Now' : 'View Task' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

        </div>{{-- /right col --}}

    </div>{{-- /grid --}}
</div>{{-- /max-w-screen-2xl --}}

{{-- ── Mobile Bottom Nav ───────────────────────────────────────────────────── --}}
<div class="md:hidden fixed bottom-0 left-0 w-full bg-surface-container-lowest border-t border-outline-variant/10 px-4 py-2 flex justify-around items-center z-50"
     role="navigation"
     aria-label="Mobile navigation">
    <a href="{{ route('dashboard') }}"
       class="flex flex-col items-center p-2 text-primary"
       aria-current="page">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">school</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Academy</span>
    </a>
    <button class="flex flex-col items-center p-2 text-on-surface-variant">
        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Library</span>
    </button>
    <button class="flex flex-col items-center p-2 text-on-surface-variant">
        <span class="material-symbols-outlined" aria-hidden="true">sports_esports</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Arena</span>
    </button>
    <button class="flex flex-col items-center p-2 text-on-surface-variant">
        <span class="material-symbols-outlined" aria-hidden="true">leaderboard</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Leader</span>
    </button>
    <button class="flex flex-col items-center p-2 text-on-surface-variant">
        <span class="material-symbols-outlined" aria-hidden="true">military_tech</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Awards</span>
    </button>
</div>
