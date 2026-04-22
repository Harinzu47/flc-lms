{{--
    Partial: Ranking List (Rank 4 onwards)
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by HallOfFame::render()):
      $topUsers — Collection<User> (ordered desc by total_xp)

    Skips the first 3 (podium) entries and renders rank 4+.
    Highlights the authenticated user's row with a primary accent.
    ────────────────────────────────────────────────────────────────────────────
--}}

<section aria-labelledby="ranking-list-heading">

    {{-- Section header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-1 h-7 bg-primary rounded-full" aria-hidden="true"></div>
            <h2 id="ranking-list-heading"
                class="text-xl font-headline font-bold text-on-surface tracking-tight">
                Full Rankings
            </h2>
        </div>
        <span class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest bg-surface-container-high px-3 py-1.5 rounded-full">
            Top {{ $topUsers->count() }}
        </span>
    </div>

    @php $restUsers = $topUsers->skip(3); @endphp

    @if($restUsers->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center bg-surface-container-low rounded-2xl border border-outline-variant/10">
            <span class="material-symbols-outlined text-5xl text-outline-variant mb-3" aria-hidden="true">leaderboard</span>
            <p class="font-headline font-bold text-on-surface">Only Three Scholars So Far</p>
            <p class="text-on-surface-variant text-sm mt-1">Keep learning to join the leaderboard!</p>
        </div>
    @else
        <div class="space-y-2" role="list" aria-label="Rankings from position 4">
            @foreach($restUsers as $index => $rankedUser)
                @php
                    $rank   = $index + 4;
                    $isMe   = $rankedUser->id === auth()->id();
                    $isEven = $index % 2 === 0;
                @endphp
                <div
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all
                        {{ $isMe
                            ? 'bg-primary/5 ring-1 ring-primary/20 shadow-sm'
                            : ($isEven ? 'bg-surface-container-lowest' : 'bg-surface-container-low') }}"
                    role="listitem"
                    aria-label="Rank {{ $rank }}: {{ $isMe ? 'You' : $rankedUser->name }}, {{ number_format($rankedUser->total_xp) }} XP"
                >

                    {{-- Rank number --}}
                    <div class="w-8 text-center flex-shrink-0">
                        <span class="font-headline font-black text-sm {{ $isMe ? 'text-primary' : 'text-on-surface-variant' }}">
                            {{ $rank }}
                        </span>
                    </div>

                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-sm font-headline
                        {{ $isMe
                            ? 'bg-gradient-to-br from-primary to-primary-container text-on-primary shadow-md shadow-primary/20'
                            : 'bg-gradient-to-br from-surface-container-high to-surface-container text-on-surface-variant' }}"
                         aria-hidden="true">
                        {{ strtoupper(substr($rankedUser->name, 0, 2)) }}
                    </div>

                    {{-- Name & Level --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-headline font-bold text-sm truncate {{ $isMe ? 'text-primary' : 'text-on-surface' }}">
                            {{ $isMe ? 'You (' . $rankedUser->name . ')' : $rankedUser->name }}
                        </p>
                        <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-label mt-0.5 truncate">
                            {{ $rankedUser->currentLevel()?->name ?? 'Novice' }}
                        </p>
                    </div>

                    {{-- XP amount --}}
                    <div class="text-right flex-shrink-0">
                        <span class="font-headline font-black text-base {{ $isMe ? 'text-primary' : 'text-on-surface' }}">
                            {{ number_format($rankedUser->total_xp) }}
                        </span>
                        <span class="text-xs text-on-surface-variant font-label ml-0.5">XP</span>
                    </div>

                    {{-- "That's you" indicator --}}
                    @if($isMe)
                        <div class="flex-shrink-0 ml-1">
                            <span class="inline-flex items-center gap-1 bg-primary text-on-primary px-2.5 py-1 rounded-full text-[10px] font-black font-label uppercase tracking-wide">
                                <span class="material-symbols-outlined" style="font-size:12px; font-variation-settings:'FILL' 1;" aria-hidden="true">person</span>
                                You
                            </span>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</section>
