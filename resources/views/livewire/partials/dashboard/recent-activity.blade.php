{{--
    Partial: Recent XP Activity Feed
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by GamifiedDashboard::render()):
      $xpLogs — Illuminate\Support\Collection<XpLog>
                Each log has: action, xp_earned, created_at
    ────────────────────────────────────────────────────────────────────────────
--}}

<section aria-labelledby="activity-heading">
    <div class="flex items-center justify-between mb-6">
        <h2 id="activity-heading"
            class="text-2xl font-bold text-on-surface tracking-tight font-headline">Recent Activity</h2>
        <span class="text-xs font-label text-on-surface-variant uppercase tracking-widest">Last 5 events</span>
    </div>

    @if($xpLogs->isEmpty())
        <div class="bg-surface-container-low rounded-2xl p-8 text-center border border-outline-variant/10">
            <span class="material-symbols-outlined text-4xl text-outline-variant" aria-hidden="true">history</span>
            <p class="text-on-surface-variant mt-2 text-sm">No XP activity yet. Start reading materials!</p>
        </div>
    @else
        <div class="space-y-3" role="feed" aria-label="XP activity feed">
            @foreach($xpLogs as $log)
                @php
                    $isPositive = $log->xp_earned >= 0;
                @endphp
                <div class="flex items-center gap-4 bg-surface-container-lowest p-4 rounded-2xl shadow-sm border border-outline-variant/10 hover:shadow-md transition-all"
                     role="article">
                    {{-- XP Icon --}}
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center
                        {{ $isPositive ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container' }}"
                         aria-hidden="true">
                        <span class="material-symbols-outlined text-sm"
                              style="font-variation-settings:'FILL' 1;">
                            {{ $isPositive ? 'bolt' : 'remove_circle' }}
                        </span>
                    </div>

                    {{-- Action label --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-headline font-semibold text-on-surface text-sm capitalize truncate">
                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                        </p>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            {{ $log->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- XP Amount --}}
                    <div class="text-right flex-shrink-0">
                        <span class="font-headline font-black text-lg {{ $isPositive ? 'text-secondary' : 'text-error' }}"
                              aria-label="{{ $isPositive ? '+' : '' }}{{ $log->xp_earned }} XP">
                            {{ $isPositive ? '+' : '' }}{{ $log->xp_earned }} XP
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
