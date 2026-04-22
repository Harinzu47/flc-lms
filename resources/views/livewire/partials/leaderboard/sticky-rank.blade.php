{{--
    Partial: Sticky "Your Rank" Footer Panel
    ────────────────────────────────────────────────────────────────────────────
    Displayed at the bottom of the viewport as a glassmorphic sticky bar
    so the current user always knows their standing even when scrolled deep
    into the ranking list.

    Context variables (provided by HallOfFame::render()):
      $currentUserRank — int (computed via COUNT query in HallOfFame::render())

    Auth helper used directly for total_xp and name.
    ────────────────────────────────────────────────────────────────────────────
--}}

@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
@endphp

<div class="fixed bottom-0 left-64 right-0 z-40 px-8 pb-4 pointer-events-none"
     aria-label="Your current rank">
    <div class="pointer-events-auto max-w-4xl mx-auto">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-[0_-4px_30px_-8px_rgba(43,75,185,0.18)] border border-outline-variant/10 px-6 py-4 flex items-center gap-6">

            {{-- Rank badge --}}
            <div class="flex-shrink-0 flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-primary-container text-on-primary shadow-lg shadow-primary/25">
                <span class="text-[10px] font-label font-bold uppercase tracking-widest opacity-80 leading-none">Rank</span>
                <span class="font-headline font-black text-xl leading-tight">#{{ $currentUserRank }}</span>
            </div>

            {{-- Identity --}}
            <div class="flex items-center gap-3 flex-1 min-w-0">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold font-headline text-sm flex-shrink-0"
                     aria-hidden="true">
                    {{ strtoupper(substr($authUser->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-headline font-bold text-on-surface text-sm truncate">{{ $authUser->name }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-label mt-0.5">
                        {{ $authUser->currentLevel()?->name ?? 'Novice' }}
                    </p>
                </div>
            </div>

            {{-- XP total --}}
            <div class="flex-shrink-0 text-right">
                <p class="font-headline font-black text-2xl text-primary leading-none">
                    {{ number_format($authUser->total_xp) }}
                </p>
                <p class="text-[10px] font-label font-bold text-on-surface-variant uppercase tracking-widest mt-0.5">Total XP</p>
            </div>

            {{-- Progress hint --}}
            @php $nextLevel = $authUser->nextLevel(); @endphp
            @if($nextLevel)
                <div class="flex-shrink-0 hidden sm:flex flex-col items-end gap-1">
                    <p class="text-[10px] font-label text-on-surface-variant uppercase tracking-widest">
                        Next: <span class="text-primary font-bold">{{ $nextLevel->name }}</span>
                    </p>
                    <div class="w-28 h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full transition-all"
                            style="width: {{ $authUser->progressPercentage() }}%;"
                            role="progressbar"
                            aria-valuenow="{{ $authUser->progressPercentage() }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-label="Progress to {{ $nextLevel->name }}"
                        ></div>
                    </div>
                    <p class="text-[10px] font-label text-on-surface-variant">
                        {{ $authUser->progressPercentage() }}% complete
                    </p>
                </div>
            @else
                <div class="flex-shrink-0 hidden sm:flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary"
                          style="font-variation-settings:'FILL' 1;"
                          aria-hidden="true">military_tech</span>
                    <span class="text-xs font-label font-bold text-tertiary uppercase tracking-widest">Max Level!</span>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Bottom padding so the sticky bar doesn't cover the last list row --}}
<div class="h-24" aria-hidden="true"></div>
