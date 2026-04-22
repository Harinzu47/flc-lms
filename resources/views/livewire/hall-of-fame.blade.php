{{--
    Hall of Fame — FLC UMJ Gamified LMS
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: 3eaf16a6930a4150a3d7990abc1a5a55
    Backend:  App\Livewire\HallOfFame
    Layout:   layouts.gamified (owns Top App Bar, Sidebar, and Toast chrome)
    ────────────────────────────────────────────────────────────────────────────

    Coordinator view only. All UI logic lives in:
      resources/views/livewire/partials/leaderboard/
        podium.blade.php       → Top 3 champion podium
        ranking-list.blade.php → Rank 4–50 table rows
        sticky-rank.blade.php  → Fixed footer with current user's rank

    Variables available in all @includes (shared scope):
      $topUsers        — Collection<User>  (top 50, ordered by total_xp desc)
      $currentUserRank — int               (auth user's rank by COUNT query)
    ────────────────────────────────────────────────────────────────────────────
--}}

{{-- ── Sidebar sections injected into layouts.gamified ─────────────────────── --}}
@section('sidebar-title')
    <h2 class="text-blue-800 font-bold font-headline">Hall of Fame</h2>
    <p class="text-xs text-on-surface-variant mt-0.5">Global Scholar Rankings</p>
@endsection

@section('sidebar-nav')
    <a href="{{ route('dashboard') }}"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('leaderboard') }}"
       class="flex items-center gap-3 bg-surface-container-lowest text-primary rounded-xl px-4 py-3 shadow-sm font-medium"
       aria-current="page">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">leaderboard</span>
        <span>Hall of Fame</span>
    </a>
    <a href="#"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
        <span>Courses</span>
    </a>
    <a href="#"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>
        <span>Calendar</span>
    </a>
@endsection

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────────────── --}}
<div class="max-w-4xl mx-auto">

    {{-- Page Hero Header --}}
    <div class="mb-12 relative">
        {{-- Decorative background gradient --}}
        <div class="absolute inset-0 -mx-8 -mt-8 h-48 bg-gradient-to-br from-primary/8 to-transparent rounded-3xl"
             aria-hidden="true"></div>

        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-tertiary text-3xl"
                      style="font-variation-settings:'FILL' 1;"
                      aria-hidden="true">military_tech</span>
                <span class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">
                    Season · {{ now()->format('Y') }}
                </span>
            </div>
            <h1 class="text-5xl font-headline font-extrabold tracking-tight text-on-surface leading-tight">
                Hall of Fame
            </h1>
            <p class="text-on-surface-variant mt-3 text-lg max-w-xl">
                Celebrating academic excellence and scholarly achievement within the FLC UMJ community.
            </p>

            {{-- Stats row --}}
            <div class="flex flex-wrap gap-6 mt-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-sm"
                          style="font-variation-settings:'FILL' 1;"
                          aria-hidden="true">group</span>
                    <span class="text-sm font-label font-bold text-on-surface-variant uppercase tracking-widest">
                        {{ $topUsers->count() }} Scholars Ranked
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-sm"
                          style="font-variation-settings:'FILL' 1;"
                          aria-hidden="true">bolt</span>
                    <span class="text-sm font-label font-bold text-on-surface-variant uppercase tracking-widest">
                        Your Rank: #{{ $currentUserRank }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Champion Podium (ranks 1–3) --}}
    @include('livewire.partials.leaderboard.podium')

    {{-- Divider treatment (pillar, not hr) --}}
    <div class="flex items-center gap-4 my-10">
        <div class="h-px flex-1 bg-gradient-to-r from-transparent to-outline-variant/30" aria-hidden="true"></div>
        <span class="material-symbols-outlined text-outline-variant" aria-hidden="true">expand_more</span>
        <div class="h-px flex-1 bg-gradient-to-l from-transparent to-outline-variant/30" aria-hidden="true"></div>
    </div>

    {{-- Full Ranking List (ranks 4–50) --}}
    @include('livewire.partials.leaderboard.ranking-list')

</div>{{-- /max-w-4xl --}}

{{-- Sticky "Your Rank" footer bar (fixed, outside the max-w container) --}}
@include('livewire.partials.leaderboard.sticky-rank')
