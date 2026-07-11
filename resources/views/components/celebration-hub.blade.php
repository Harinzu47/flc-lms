<div x-data="{ showBadgeModal: false, showLevelModal: false, badge: {}, level: {} }" x-init="
        let celebrations = @js($celebrations);
        celebrations.forEach(item => {
            if (item.type === 'badge-unlocked') {
                badge = { name: item.payload.name || '', description: item.payload.description || '', icon: item.payload.icon || '' };
                showBadgeModal = true;
                confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } });
            } else if (item.type === 'level-up') {
                level = { levelName: item.payload.levelName || '', targetXp: item.payload.targetXp || 0 };
                showLevelModal = true;
                confetti({ particleCount: 200, spread: 90 });
            }
        });
    "
    @badge-unlocked.window="badge = $event.detail; showBadgeModal = true; confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } });"
    @level-up.window="level = $event.detail; showLevelModal = true; confetti({ particleCount: 200, spread: 90 });">

    {{-- ── BADGE MODAL ──────────────────────────────────────────────────────── --}}
    <div x-show="showBadgeModal" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
        role="dialog" aria-modal="true" aria-labelledby="badge-modal-title">
        <div
            class="bg-white/90 border border-slate-200 rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl relative overflow-hidden">
            <div class="absolute -top-12 -left-12 w-24 h-24 bg-amber-400/20 rounded-full blur-2xl" aria-hidden="true">
            </div>
            <div class="absolute -bottom-12 -right-12 w-24 h-24 bg-primary/20 rounded-full blur-2xl" aria-hidden="true">
            </div>

            <div class="relative space-y-6">
                {{-- Animated badge bubble --}}
                <div
                    class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-amber-100 to-amber-200 text-amber-800 shadow-md animate-pulse">
                    <span class="text-5xl" x-text="badge.icon || '🏅'"></span>
                </div>

                <div class="space-y-2">
                    <span
                        class="text-[10px] font-bold text-amber-700 uppercase tracking-widest font-headline block">Lencana
                        Baru Terbuka!</span>
                    <h3 id="badge-modal-title" class="text-2xl font-extrabold text-blue-900 font-headline leading-tight"
                        x-text="badge.name"></h3>
                    <p class="text-sm text-slate-600 leading-relaxed" x-text="badge.description"></p>
                </div>

                <button @click="showBadgeModal = false"
                    class="w-full bg-gradient-to-br from-amber-500 to-amber-600 hover:opacity-90 text-white font-bold py-3.5 rounded-2xl shadow-lg transition-all text-sm font-headline">
                    Awesome!
                </button>
            </div>
        </div>
    </div>

    {{-- ── LEVEL UP MODAL ───────────────────────────────────────────────────── --}}
    <div x-show="showLevelModal" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
        role="dialog" aria-modal="true" aria-labelledby="level-modal-title">
        <div
            class="bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950 text-white border border-indigo-500/30 rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-48 bg-primary/20 rounded-full blur-3xl"
                aria-hidden="true"></div>

            <div class="relative space-y-6">
                {{-- Achievement Crown --}}
                <div
                    class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-amber-400 via-yellow-300 to-amber-500 text-slate-900 shadow-xl border-4 border-indigo-400/20">
                    <span class="text-5xl">👑</span>
                </div>

                <div class="space-y-2">
                    <span
                        class="text-xs font-black text-amber-400 uppercase tracking-widest font-headline block animate-pulse">LEVEL
                        UP!</span>
                    <h3 id="level-modal-title"
                        class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 via-amber-300 to-yellow-200 font-headline leading-none"
                        x-text="level.levelName"></h3>
                    <p class="text-sm text-indigo-200/80 leading-relaxed pt-2">
                        Luar biasa! Poin XP Anda telah melampaui batas minimum untuk tingkatan kelas baru ini. Terus
                        tingkatkan belajar Anda!
                    </p>
                </div>

                <button @click="showLevelModal = false"
                    class="w-full bg-gradient-to-r from-primary to-indigo-600 hover:opacity-90 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-primary/25 transition-all text-sm font-headline">
                    Lanjutkan Belajar
                </button>
            </div>
        </div>
    </div>

</div>