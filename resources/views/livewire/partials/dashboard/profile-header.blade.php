{{--
    Partial: Player Card / Profile Header
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by GamifiedDashboard::render()):
      $user  — App\Models\User (with currentLevel(), nextLevel(), progressPercentage())
    ────────────────────────────────────────────────────────────────────────────
--}}
@php
    $currentLevel = $user->currentLevel();
    $nextLevel    = $user->nextLevel();
    $progress     = $user->progressPercentage();
@endphp

<section class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-2xl p-8 text-on-primary shadow-xl"
         aria-labelledby="profile-name-heading">
    <div class="relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">

            {{-- Left: Identity --}}
            <div>
                <span class="bg-secondary-container/20 text-secondary-fixed px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4 inline-block font-label">
                    Current Rank: {{ $currentLevel?->name ?? 'Novice' }}
                </span>
                <h1 id="profile-name-heading"
                    class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2 font-headline">
                    {{ $user->name }}
                </h1>
                <p class="text-primary-fixed-dim text-lg">
                    {{ number_format($user->total_xp) }} XP earned in total
                </p>
            </div>

            {{-- Right: XP Counter --}}
            <div class="text-right flex-shrink-0">
                <span class="text-5xl font-black text-tertiary-fixed-dim leading-none">
                    {{ number_format($user->total_xp) }}
                </span>
                @if($nextLevel)
                    <span class="text-xl font-medium opacity-80 uppercase tracking-tighter">
                        / {{ number_format($nextLevel->min_xp) }} XP
                    </span>
                    <p class="text-xs font-bold uppercase tracking-widest mt-1 opacity-70 font-label">
                        Next: {{ $nextLevel->name }}
                    </p>
                @else
                    <span class="text-xl font-medium opacity-80 uppercase tracking-tighter">XP</span>
                    <p class="text-xs font-bold uppercase tracking-widest mt-1 opacity-70 font-label">Max Level Reached!</p>
                @endif
            </div>

        </div>

        {{-- XP Progress Bar --}}
        <div class="mt-8">
            <div class="flex justify-between text-xs font-bold uppercase tracking-widest mb-2 opacity-80 font-label">
                <span>Progression to {{ $nextLevel?->name ?? 'Max' }}</span>
                <span>{{ $progress }}% Complete</span>
            </div>
            <div class="h-4 w-full bg-on-primary/10 rounded-full overflow-hidden backdrop-blur-md"
                 role="progressbar"
                 aria-valuenow="{{ $progress }}"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-label="XP progress to next level">
                <div
                    class="h-full bg-gradient-to-r from-secondary-fixed to-secondary-container rounded-full shadow-[0_0_15px_rgba(111,251,190,0.4)] transition-all duration-700"
                    style="width: {{ $progress }}%;"
                ></div>
            </div>
            @if($currentLevel && $nextLevel)
                <div class="flex justify-between text-xs opacity-60 mt-1 font-label">
                    <span>{{ $currentLevel->name }} ({{ number_format($currentLevel->min_xp) }} XP)</span>
                    <span>{{ $nextLevel->name }} ({{ number_format($nextLevel->min_xp) }} XP)</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Decorative Background Elements --}}
    <div class="absolute -right-16 -top-16 w-64 h-64 bg-secondary-fixed/10 rounded-full blur-3xl" aria-hidden="true"></div>
    <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-tertiary-fixed/10 rounded-full blur-2xl" aria-hidden="true"></div>
    <span class="material-symbols-outlined absolute right-8 top-1/2 -translate-y-1/2 text-[180px] opacity-5 pointer-events-none select-none"
          aria-hidden="true">school</span>
</section>
