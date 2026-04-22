{{--
    Partial: Badges / Achievements Grid
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by GamifiedDashboard::render()):
      $badges — Illuminate\Database\Eloquent\Collection<Badge>
                Each badge has: name, icon_url, pivot->unlocked_at
    ────────────────────────────────────────────────────────────────────────────
--}}

<section aria-labelledby="badges-heading">
    <div class="flex items-center justify-between mb-6">
        <h2 id="badges-heading"
            class="text-2xl font-bold text-on-surface tracking-tight font-headline">My Badges</h2>
        <span class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">
            {{ $badges->count() }} Earned
        </span>
    </div>

    @if($badges->isEmpty())
        {{-- Empty state --}}
        <div class="bg-surface-container-low rounded-2xl p-10 flex flex-col items-center text-center border border-outline-variant/10">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4" aria-hidden="true">military_tech</span>
            <h3 class="font-headline font-bold text-lg text-on-surface">No Badges Yet</h3>
            <p class="text-on-surface-variant mt-2 max-w-xs text-sm">
                Keep learning and submitting tasks to earn your first badge!
            </p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" role="list" aria-label="Earned badges">
            @foreach($badges as $badge)
                <div class="group bg-surface-container-lowest rounded-2xl p-5 shadow-sm hover:shadow-md transition-all border border-outline-variant/10 flex flex-col items-center text-center"
                     role="listitem">
                    {{-- Badge icon: use icon_url if available, otherwise Material Symbol fallback --}}
                    @if($badge->icon_url)
                        <img src="{{ $badge->icon_url }}"
                             alt="{{ $badge->name }}"
                             class="w-14 h-14 rounded-full object-cover mb-3 border-2 border-secondary-container group-hover:scale-110 transition-transform">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-secondary-container to-secondary-fixed flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm"
                             aria-hidden="true">
                            <span class="material-symbols-outlined text-on-secondary-container text-2xl"
                                  style="font-variation-settings:'FILL' 1;">military_tech</span>
                        </div>
                    @endif
                    <p class="font-headline font-bold text-sm text-on-surface group-hover:text-primary transition-colors">
                        {{ $badge->name }}
                    </p>
                    @if($badge->pivot->unlocked_at)
                        <p class="text-[10px] text-on-surface-variant mt-1 font-label uppercase tracking-widest">
                            {{ \Carbon\Carbon::parse($badge->pivot->unlocked_at)->format('d M Y') }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
