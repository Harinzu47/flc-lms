<div class="bg-background text-on-surface font-body min-h-screen flex">

    {{-- ── ADMIN SIDEBAR ────────────────────────────────────────────────────── --}}
    @include('livewire.partials.admin.sidebar', ['activePage' => 'dashboard', 'pendingSubmissions' => $pendingSubmissions])

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
    <main class="md:pl-64 min-h-screen flex flex-col w-full">

        {{-- Top App Bar --}}
        <header
            class="sticky top-0 z-40 flex items-center justify-between px-8 py-3 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-4">
                <button @click="$dispatch('toggle-sidebar')"
                    class="p-2 -ml-2 text-on-surface-variant hover:text-primary rounded-lg md:hidden focus:outline-none"
                    aria-label="Toggle admin sidebar">
                    <span class="material-symbols-outlined block text-2xl">menu</span>
                </button>
                <h1 class="text-xl font-bold text-blue-800 font-headline tracking-tight">FLC UMJ</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-blue-700 font-headline">{{ auth()->user()->name }}</span>
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-sm border-2 border-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- ── PAGE BODY ────────────────────────────────────────────────────── --}}
        <div class="flex-1 p-8">
            <div class="max-w-6xl mx-auto space-y-8">

                {{-- Page Header --}}
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-1 h-7 bg-primary rounded-full" aria-hidden="true"></div>
                            <h2 class="text-3xl font-headline font-extrabold text-on-surface tracking-tight">
                                BPH Analytics Command Center
                            </h2>
                        </div>
                        <p class="text-sm text-on-surface-variant font-medium">
                            Real-time platform metrics and student engagement telemetry.
                        </p>
                    </div>
                </div>

                {{-- Tab Navigation --}}
                <div class="border-b border-slate-200">
                    <nav class="flex gap-6 -mb-px" aria-label="Tabs">
                        <button wire:click="$set('activeTab', 'overview')"
                            class="py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200
                                {{ $activeTab === 'overview'
    ? 'border-primary text-primary'
    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                            Overview & Ringkasan
                        </button>
                        <button wire:click="$set('activeTab', 'leaderboard')"
                            class="py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200
                                {{ $activeTab === 'leaderboard'
    ? 'border-primary text-primary'
    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                            Matriks Aktivitas Mahasiswa (Leaderboard)
                        </button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                @if($activeTab === 'overview')
                    {{-- OVERVIEW TAB --}}
                    <div class="space-y-8">
                        {{-- Stat Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Card 1: Total Students --}}
                            <div
                                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total
                                        Mahasiswa Active</p>
                                    <h3 class="text-3xl font-black text-slate-800 font-headline">{{ $totalStudents }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined text-2xl">group</span>
                                </div>
                            </div>

                            {{-- Card 2: Pending Grading (Read-only for BPH) --}}
                            <div
                                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Antrean
                                        Penilaian Pending</p>
                                    <h3 class="text-3xl font-black text-slate-800 font-headline">
                                        {{ $pendingGradingCount }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-outlined text-2xl">pending</span>
                                </div>
                            </div>

                            {{-- Card 3: Flagged Tasks --}}
                            <div
                                class="bg-red-50/50 p-6 rounded-2xl border border-red-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-1">Tugas Terflagged
                                        Khusus</p>
                                    <h3 class="text-3xl font-black text-red-700 font-headline">{{ $flaggedCount }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                                    <span class="material-symbols-outlined text-2xl">report</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- LEADERBOARD TAB --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-800 font-headline">Matriks Aktivitas Mahasiswa
                                (Leaderboard)</h3>
                            <p class="text-xs text-slate-400">10 besar mahasiswa dengan akumulasi XP tertinggi pada
                                platform.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="bg-slate-50 text-[10px] uppercase font-black tracking-wider text-slate-400 border-b border-slate-100">
                                        <th class="py-3 px-6 w-16">Peringkat</th>
                                        <th class="py-3 px-6">Nama Mahasiswa</th>
                                        <th class="py-3 px-6">Tingkatan Level</th>
                                        <th class="py-3 px-6">Total Akumulasi XP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @forelse($topStudents as $index => $student)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-4 px-6 font-bold text-slate-500">
                                                #{{ $index + 1 }}
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center uppercase">
                                                        {{ substr($student->name, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-slate-800 block">{{ $student->name }}</span>
                                                        <span class="text-xs text-slate-400">{{ $student->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span
                                                    class="bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-bold border border-blue-100">
                                                    {{ $student->level->name ?? 'Level 1' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 font-headline font-black text-slate-700">
                                                {{ number_format($student->total_xp) }} XP
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-slate-400">
                                                Tidak ada data mahasiswa.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </main>

</div>