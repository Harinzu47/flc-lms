{{--
    Badge Manager — FLC UMJ Admin Portal
    ────────────────────────────────────────────────────────────────────────────
    Backend:  App\Livewire\Admin\BadgeManager
    Layout:   layouts.base
    ────────────────────────────────────────────────────────────────────────────
--}}

@push('styles')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e0e3e5; border-radius: 10px; }
    </style>
@endpush

<div
    x-data="{
        open: @entangle('isModalOpen'),
        toastVisible: false,
        toastMessage: '',
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => this.toastVisible = false, 4000);
        }
    }"
    @notify.window="showToast($event.detail.message)"
    class="bg-background text-on-surface font-body min-h-screen flex w-full"
>

    {{-- ── TOAST ────────────────────────────────────────────────────────────── --}}
    <div
        x-show="toastVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-6 right-6 z-[9999] flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-4 rounded-2xl shadow-[0_10px_25px_-5px_rgba(43,75,185,0.45)] pointer-events-none"
        role="alert"
        aria-live="polite"
    >
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">check_circle</span>
        <p class="font-headline font-bold text-base" x-text="toastMessage"></p>
    </div>

    {{-- ── ADMIN SIDEBAR ────────────────────────────────────────────────────── --}}
    @include('livewire.partials.admin.sidebar', ['activePage' => 'badges'])

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
    <main class="pl-64 min-h-screen flex flex-col w-full">

        {{-- Top App Bar --}}
        <header class="sticky top-0 z-40 flex items-center justify-between px-8 py-3 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-6">
                <h1 class="text-xl font-bold text-blue-800 font-headline tracking-tight">FLC UMJ</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-blue-700 font-headline">{{ auth()->user()->name }}</span>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-sm border-2 border-white shadow-sm"
                         aria-label="{{ auth()->user()->name }}">
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
                                Badges Management
                            </h2>
                        </div>
                        <p class="text-on-surface-variant ml-4 text-sm">
                            Define gamified badges, configure unlock criteria, and reward achievements.
                        </p>
                    </div>
                    {{-- Add New Badge button --}}
                    <button
                        wire:click="create"
                        class="flex items-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary px-5 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                    >
                        <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">add</span>
                        Tambah Lencana Baru
                    </button>
                </div>

                {{-- ── DATA TABLE ───────────────────────────────────────────── --}}
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" aria-label="Badges catalog">
                            <thead>
                                <tr class="bg-surface-container-low">
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest w-16">Ikon</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Nama Lencana</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Deskripsi</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Kriteria Aturan</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Angka Target</th>
                                    <th scope="col" class="text-right px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @forelse($badges as $badgeItem)
                                    <tr class="hover:bg-surface-container-low transition-colors group" wire:key="badge-row-{{ $badgeItem->id }}">
                                        
                                        {{-- Icon / Emoji --}}
                                        <td class="px-6 py-4">
                                            @if(str_contains($badgeItem->icon ?? '', '/') || str_contains($badgeItem->icon ?? '', '.'))
                                                <img src="{{ asset($badgeItem->icon) }}" alt="{{ $badgeItem->name }}" class="w-8 h-8 rounded-full object-cover">
                                            @else
                                                <span class="text-2xl">{{ $badgeItem->icon ?? '🏅' }}</span>
                                            @endif
                                        </td>

                                        {{-- Name --}}
                                        <td class="px-6 py-4 font-headline font-bold text-on-surface group-hover:text-primary transition-colors">
                                            {{ $badgeItem->name }}
                                        </td>

                                        {{-- Description --}}
                                        <td class="px-6 py-4 text-on-surface-variant max-w-xs truncate">
                                            {{ $badgeItem->description }}
                                        </td>

                                        {{-- Criteria Type --}}
                                        <td class="px-6 py-4 font-medium text-slate-700">
                                            @if($badgeItem->criteria_type === 'total_xp')
                                                Akumulasi Poin XP
                                            @elseif($badgeItem->criteria_type === 'materials_read')
                                                Total Materi yang Dibaca
                                            @elseif($badgeItem->criteria_type === 'tasks_completed')
                                                Total Tugas yang Selesai Dinilai
                                            @else
                                                {{ $badgeItem->criteria_type }}
                                            @endif
                                        </td>

                                        {{-- Target Value --}}
                                        <td class="px-6 py-4 font-mono font-bold text-secondary">
                                            {{ number_format($badgeItem->target_value) }}
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    wire:click="edit({{ $badgeItem->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-high text-on-surface-variant rounded-lg text-xs font-bold hover:bg-primary hover:text-on-primary transition-all"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">edit</span>
                                                    Edit
                                                </button>
                                                <button
                                                    wire:click="deleteBadge({{ $badgeItem->id }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus lencana '{{ addslashes($badgeItem->name) }}'? Hubungan pencapaian siswa untuk lencana ini juga akan dihapus."
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-error-container text-on-error-container rounded-lg text-xs font-bold hover:bg-error hover:text-on-error transition-all"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">delete</span>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-16 text-center">
                                            <span class="material-symbols-outlined text-5xl text-outline-variant block mb-3" aria-hidden="true">military_tech</span>
                                            <p class="font-headline font-bold text-on-surface">Tidak ada lencana ditemukan.</p>
                                            <p class="text-on-surface-variant text-sm mt-1">Klik "+ Tambah Lencana Baru" untuk membuat master lencana pertama Anda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($badges->hasPages())
                        <div class="px-6 py-4 border-t border-outline-variant/10">
                            {{ $badges->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </main>

    {{-- ────────────────────────────────────────────────────────────────────────
         CRUD MODAL
    ──────────────────────────────────────────────────────────────────────── --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
    >
        {{-- Modal Window --}}
        <div
            @click.away="$wire.closeModal()"
            @keydown.escape.window="$wire.closeModal()"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-3xl shadow-2xl border border-outline-variant/20 w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]"
        >
            {{-- Modal Header --}}
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 id="modal-title" class="text-xl font-headline font-extrabold text-blue-900">
                        {{ $selectedBadgeId ? 'Edit Master Lencana' : 'Tambah Lencana Baru' }}
                    </h3>
                </div>
                <button
                    wire:click="closeModal"
                    class="p-2 rounded-full hover:bg-slate-100 text-on-surface-variant transition-colors"
                    aria-label="Close modal"
                >
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form wire:submit.prevent="saveBadge" class="space-y-6">

                    {{-- Name --}}
                    <div class="space-y-2">
                        <label for="input-name" class="block text-sm font-bold font-headline text-on-surface">Nama Lencana</label>
                        <input
                            id="input-name"
                            wire:model="name"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-error @enderror"
                            placeholder="Contoh: Sang Juara Teks"
                            type="text"
                            required
                        >
                        @error('name') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-2">
                        <label for="input-description" class="block text-sm font-bold font-headline text-on-surface">Deskripsi</label>
                        <textarea
                            id="input-description"
                            wire:model="description"
                            rows="3"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('description') border-error @enderror"
                            placeholder="Tulis penjelasan bagaimana lencana didapatkan..."
                            required
                        ></textarea>
                        @error('description') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Icon / Emoji --}}
                    <div class="space-y-2">
                        <label for="input-icon" class="block text-sm font-bold font-headline text-on-surface">Ikon / Emoji</label>
                        <input
                            id="input-icon"
                            wire:model="icon"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('icon') border-error @enderror"
                            placeholder="🏅 atau URL gambar"
                            type="text"
                            required
                        >
                        <p class="text-xs text-on-surface-variant">Gunakan emoji tunggal (seperti 🧙, 🌟, 🏆) atau alamat berkas gambar.</p>
                        @error('icon') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Criteria Type --}}
                    <div class="space-y-2">
                        <label for="input-criteria-type" class="block text-sm font-bold font-headline text-on-surface">Pilih Kriteria Aturan</label>
                        <select
                            id="input-criteria-type"
                            wire:model.live="criteriaType"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        >
                            <option value="total_xp">Poin Kuantitas XP ('total_xp')</option>
                            <option value="materials_read">Total Materi yang Dibaca ('materials_read')</option>
                            <option value="tasks_completed">Total Tugas yang Selesai Dinilai ('tasks_completed')</option>
                        </select>
                        @error('criteriaType') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Target Value (Dynamic Label) --}}
                    <div class="space-y-2">
                        <label for="input-target-value" class="block text-sm font-bold font-headline text-on-surface">
                            @if($criteriaType === 'total_xp')
                                Target Poin XP
                            @elseif($criteriaType === 'materials_read')
                                Target Jumlah Materi
                            @elseif($criteriaType === 'tasks_completed')
                                Target Jumlah Tugas
                            @else
                                Target Angka Pencapaian
                            @endif
                        </label>
                        <input
                            id="input-target-value"
                            wire:model="targetValue"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('targetValue') border-error @enderror"
                            type="number"
                            min="1"
                            required
                        >
                        @error('targetValue') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Save Button for CRUD --}}
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-on-surface-variant hover:bg-slate-50 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-xl text-xs font-bold shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all"
                        >
                            Simpan Lencana
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
