{{--
    Partial: Champion Podium (Top 3)
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by HallOfFame::render()):
      $topUsers — Collection<User> (ordered desc by total_xp)

    Uses optional() for safe access when fewer than 3 members exist.
    Medal colours follow the Academic Prestige design system:
      Gold   → tertiary / tertiary-fixed
      Silver → slate-400 / surface-container-high
      Bronze → amber-700 / amber-100
    ────────────────────────────────────────────────────────────────────────────
--}}

@php
    $gold   = $topUsers->get(0);
    $silver = $topUsers->get(1);
    $bronze = $topUsers->get(2);
@endphp

<section class="relative mb-12" aria-labelledby="podium-heading">

    {{-- Section header --}}
    <div class="flex items-center gap-3 mb-10">
        <div class="w-1 h-8 bg-primary rounded-full" aria-hidden="true"></div>
        <div>
            <h2 id="podium-heading" class="text-2xl font-headline font-extrabold text-on-surface tracking-tight">
                Champion Podium
            </h2>
            <p class="text-sm text-on-surface-variant mt-0.5">The top three scholars of FLC UMJ</p>
        </div>
    </div>

    {{-- Podium Layout: Silver | Gold | Bronze --}}
    <div class="flex items-end justify-center gap-6 lg:gap-10">

        {{-- ── 2nd Place: Silver ──────────────────────────────────────────── --}}
        @if($silver)
        <div class="flex flex-col items-center gap-4 flex-1 max-w-[220px]"
             role="listitem"
             aria-label="2nd place: {{ $silver->name }}, {{ number_format($silver->total_xp) }} XP">
            {{-- Avatar --}}
            <div class="relative">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center text-white font-headline font-extrabold text-2xl shadow-lg ring-4 ring-white">
                    {{ strtoupper(substr($silver->name, 0, 2)) }}
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-slate-400 flex items-center justify-center shadow-md border-2 border-white"
                     aria-hidden="true">
                    <span class="text-white font-black text-xs">2</span>
                </div>
            </div>
            {{-- Name & Level --}}
            <div class="text-center">
                <p class="font-headline font-bold text-on-surface text-sm leading-tight">{{ $silver->name }}</p>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-widest font-label mt-0.5">
                    {{ $silver->currentLevel()?->name ?? 'Novice' }}
                </p>
            </div>
            {{-- XP Chip --}}
            <div class="bg-surface-container-high text-on-surface-variant px-4 py-1.5 rounded-full text-xs font-bold font-label">
                {{ number_format($silver->total_xp) }} XP
            </div>
            {{-- Podium column --}}
            <div class="w-full h-24 bg-gradient-to-t from-slate-200 to-slate-100 rounded-t-2xl flex items-center justify-center shadow-inner"
                 aria-hidden="true">
                <span class="material-symbols-outlined text-slate-400 text-3xl"
                      style="font-variation-settings:'FILL' 1;">workspace_premium</span>
            </div>
        </div>
        @endif

        {{-- ── 1st Place: Gold (taller podium) ──────────────────────────── --}}
        @if($gold)
        <div class="flex flex-col items-center gap-4 flex-1 max-w-[260px]"
             role="listitem"
             aria-label="1st place: {{ $gold->name }}, {{ number_format($gold->total_xp) }} XP">
            {{-- Crown icon --}}
            <span class="material-symbols-outlined text-4xl text-tertiary"
                  style="font-variation-settings:'FILL' 1;"
                  aria-hidden="true">military_tech</span>
            {{-- Avatar (larger for #1) --}}
            <div class="relative">
                <div class="w-28 h-28 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-headline font-extrabold text-3xl shadow-xl shadow-primary/30 ring-4 ring-tertiary-fixed">
                    {{ strtoupper(substr($gold->name, 0, 2)) }}
                </div>
                <div class="absolute -bottom-2 -right-2 w-9 h-9 rounded-full bg-gradient-to-br from-tertiary to-tertiary-container flex items-center justify-center shadow-md border-2 border-white"
                     aria-hidden="true">
                    <span class="text-white font-black text-sm">1</span>
                </div>
            </div>
            {{-- Name & Level --}}
            <div class="text-center">
                <p class="font-headline font-bold text-on-surface text-base leading-tight">{{ $gold->name }}</p>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-widest font-label mt-0.5">
                    {{ $gold->currentLevel()?->name ?? 'Novice' }}
                </p>
            </div>
            {{-- XP Chip (gold accent) --}}
            <div class="bg-gradient-to-r from-tertiary-fixed to-tertiary-container text-on-tertiary-fixed px-5 py-1.5 rounded-full text-xs font-black font-label shadow-sm">
                {{ number_format($gold->total_xp) }} XP
            </div>
            {{-- Podium column (tallest) --}}
            <div class="w-full h-36 bg-gradient-to-t from-primary/20 to-primary/5 rounded-t-2xl flex items-center justify-center shadow-inner"
                 aria-hidden="true">
                <span class="material-symbols-outlined text-primary text-4xl"
                      style="font-variation-settings:'FILL' 1;">trophy</span>
            </div>
        </div>
        @endif

        {{-- ── 3rd Place: Bronze ──────────────────────────────────────────── --}}
        @if($bronze)
        <div class="flex flex-col items-center gap-4 flex-1 max-w-[220px]"
             role="listitem"
             aria-label="3rd place: {{ $bronze->name }}, {{ number_format($bronze->total_xp) }} XP">
            {{-- Avatar --}}
            <div class="relative">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-amber-600/70 to-amber-700/50 flex items-center justify-center text-white font-headline font-extrabold text-2xl shadow-lg ring-4 ring-white">
                    {{ strtoupper(substr($bronze->name, 0, 2)) }}
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-600/60 flex items-center justify-center shadow-md border-2 border-white"
                     aria-hidden="true">
                    <span class="text-white font-black text-xs">3</span>
                </div>
            </div>
            {{-- Name & Level --}}
            <div class="text-center">
                <p class="font-headline font-bold text-on-surface text-sm leading-tight">{{ $bronze->name }}</p>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-widest font-label mt-0.5">
                    {{ $bronze->currentLevel()?->name ?? 'Novice' }}
                </p>
            </div>
            {{-- XP Chip --}}
            <div class="bg-amber-50 text-amber-700 px-4 py-1.5 rounded-full text-xs font-bold font-label border border-amber-200/50">
                {{ number_format($bronze->total_xp) }} XP
            </div>
            {{-- Podium column --}}
            <div class="w-full h-16 bg-gradient-to-t from-amber-100 to-amber-50 rounded-t-2xl flex items-center justify-center shadow-inner"
                 aria-hidden="true">
                <span class="material-symbols-outlined text-amber-600/60 text-2xl"
                      style="font-variation-settings:'FILL' 1;">workspace_premium</span>
            </div>
        </div>
        @endif

    </div>
</section>
