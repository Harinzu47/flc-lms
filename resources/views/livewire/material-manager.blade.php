{{--
    Material Manager — FLC UMJ Admin Portal
    ────────────────────────────────────────────────────────────────────────────
    Backend:  App\Livewire\Admin\MaterialManager
    Layout:   layouts.base (bare HTML shell — admin uses its own chrome)
    ────────────────────────────────────────────────────────────────────────────

    This view is a coordinator only. Partials live in:
      resources/views/livewire/partials/admin/

    All wire: directives resolve against MaterialManager via @include scope.
    ────────────────────────────────────────────────────────────────────────────
--}}

@push('styles')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e0e3e5; border-radius: 10px; }
    </style>
@endpush

{{-- ── Alpine Root: Toast listener + Modal bridge ─────────────────────────── --}}
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
    class="bg-background text-on-surface font-body min-h-screen flex"
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
    @include('livewire.partials.admin.sidebar', ['activePage' => 'materials'])

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
    <main class="pl-64 min-h-screen flex flex-col w-full">

        {{-- Top App Bar --}}
        <header class="sticky top-0 z-40 flex items-center justify-between px-8 py-3 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-6">
                <h1 class="text-xl font-bold text-blue-800 font-headline tracking-tight">FLC UMJ</h1>
                <div class="relative hidden lg:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                          style="font-size: 18px;"
                          aria-hidden="true">search</span>
                    <input
                        class="bg-surface-container-low border-none rounded-full pl-10 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-primary/20 transition-all"
                        placeholder="Search materials..."
                        type="search"
                        aria-label="Search materials"
                    >
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-surface-container-low transition-colors"
                        aria-label="Notifications">
                    <span class="material-symbols-outlined text-on-surface-variant" aria-hidden="true">notifications</span>
                </button>
                <div class="h-8 w-px bg-slate-200 mx-2" aria-hidden="true"></div>
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
                                Materials
                            </h2>
                        </div>
                        <p class="text-on-surface-variant ml-4 text-sm">
                            Manage learning materials, videos, and resource links.
                        </p>
                    </div>
                    {{-- Add New Material button --}}
                    <button
                        wire:click="create"
                        id="add-material-btn"
                        class="flex items-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary px-5 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                        aria-label="Add a new material"
                    >
                        <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">add</span>
                        Add New Material
                    </button>
                </div>

                {{-- ── DATA TABLE ───────────────────────────────────────────── --}}
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" aria-label="Materials catalogue">
                            <thead>
                                <tr class="bg-surface-container-low">
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Title</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Type</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">XP Reward</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Created</th>
                                    <th scope="col" class="text-right px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @forelse($materials as $material)
                                    <tr class="hover:bg-surface-container-low transition-colors group"
                                        wire:key="material-row-{{ $material->id }}">

                                        {{-- Title + description excerpt --}}
                                        <td class="px-6 py-4">
                                            <p class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">
                                                {{ $material->title }}
                                            </p>
                                            @if($material->description)
                                                <p class="text-on-surface-variant text-xs mt-0.5 line-clamp-1">
                                                    {{ $material->description }}
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Type chip --}}
                                        <td class="px-6 py-4">
                                            @php
                                                $typeConfig = [
                                                    'video'    => ['icon' => 'play_circle',   'bg' => 'bg-primary/10 text-primary'],
                                                    'document' => ['icon' => 'description',   'bg' => 'bg-secondary/10 text-secondary'],
                                                    'link'     => ['icon' => 'link',          'bg' => 'bg-tertiary/10 text-tertiary'],
                                                ];
                                                $cfg = $typeConfig[$material->type] ?? ['icon' => 'article', 'bg' => 'bg-surface-container-high text-on-surface-variant'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 {{ $cfg['bg'] }} px-2.5 py-1 rounded-full text-xs font-bold font-label uppercase tracking-wide">
                                                <span class="material-symbols-outlined"
                                                      style="font-size:13px; font-variation-settings:'FILL' 1;"
                                                      aria-hidden="true">{{ $cfg['icon'] }}</span>
                                                {{ $material->type }}
                                            </span>
                                        </td>

                                        {{-- XP --}}
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 font-headline font-black text-secondary">
                                                <span class="material-symbols-outlined text-sm"
                                                      style="font-variation-settings:'FILL' 1;"
                                                      aria-hidden="true">bolt</span>
                                                {{ $material->xp_reward }} XP
                                            </span>
                                        </td>

                                        {{-- Created date --}}
                                        <td class="px-6 py-4 text-on-surface-variant text-xs">
                                            {{ $material->created_at->format('d M Y') }}
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    wire:click="edit({{ $material->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-high text-on-surface-variant rounded-lg text-xs font-bold hover:bg-primary hover:text-on-primary transition-all"
                                                    aria-label="Edit {{ $material->title }}"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">edit</span>
                                                    Edit
                                                </button>
                                                <button
                                                    wire:click="delete({{ $material->id }})"
                                                    wire:confirm="Are you sure you want to delete '{{ addslashes($material->title) }}'? This cannot be undone."
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-error-container text-on-error-container rounded-lg text-xs font-bold hover:bg-error hover:text-on-error transition-all"
                                                    aria-label="Delete {{ $material->title }}"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">delete</span>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <span class="material-symbols-outlined text-5xl text-outline-variant block mb-3" aria-hidden="true">library_books</span>
                                            <p class="font-headline font-bold text-on-surface">No materials yet.</p>
                                            <p class="text-on-surface-variant text-sm mt-1">Click "Add New Material" to get started.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($materials->hasPages())
                        <div class="px-6 py-4 border-t border-outline-variant/10">
                            {{ $materials->links() }}
                        </div>
                    @endif

                </div>{{-- /data table card --}}

            </div>{{-- /max-w-6xl --}}
        </div>{{-- /page body --}}

    </main>{{-- /pl-64 --}}

    {{-- ────────────────────────────────────────────────────────────────────────
         CREATE / EDIT MODAL
         Alpine's `open` is @entangled with Livewire's $isModalOpen.
         The backdrop traps the user in-context; Escape / Cancel both close.
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
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        @keydown.escape.window="$wire.closeModal()"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-on-surface/30 backdrop-blur-sm"
             @click="$wire.closeModal()"
             aria-hidden="true"></div>

        {{-- Modal panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-surface-container-lowest rounded-2xl shadow-2xl shadow-on-surface/10 w-full max-w-lg mx-auto overflow-hidden"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-8 py-6 border-b border-outline-variant/10">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-7 bg-primary rounded-full" aria-hidden="true"></div>
                    <h3 id="modal-title" class="text-xl font-headline font-extrabold text-on-surface">
                        {{ $editId ? 'Edit Material' : 'New Material' }}
                    </h3>
                </div>
                <button wire:click="closeModal"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors"
                        aria-label="Close modal">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                </button>
            </div>

            {{-- Modal Body: Form --}}
            <div class="px-8 py-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">

                {{-- Title --}}
                <div>
                    <label for="material-title" class="block text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                        Title <span class="text-error" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="material-title"
                        wire:model="title"
                        type="text"
                        placeholder="e.g. Introduction to Fiqh"
                        class="w-full bg-surface-container-low rounded-xl px-4 py-3 text-sm font-body text-on-surface border-none focus:ring-2 focus:ring-primary/25 transition-all placeholder:text-outline-variant"
                        autocomplete="off"
                    >
                    @error('title')
                        <p class="text-error text-xs mt-1.5 flex items-center gap-1" role="alert">
                            <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="material-description" class="block text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                        Description
                    </label>
                    <textarea
                        id="material-description"
                        wire:model="description"
                        rows="3"
                        placeholder="A short summary of what this material covers..."
                        class="w-full bg-surface-container-low rounded-xl px-4 py-3 text-sm font-body text-on-surface border-none focus:ring-2 focus:ring-primary/25 transition-all placeholder:text-outline-variant resize-none"
                    ></textarea>
                    @error('description')
                        <p class="text-error text-xs mt-1.5 flex items-center gap-1" role="alert">
                            <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Type + XP Reward (side by side) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="material-type" class="block text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                            Type <span class="text-error" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="material-type"
                            wire:model="type"
                            class="w-full bg-surface-container-low rounded-xl px-4 py-3 text-sm font-body text-on-surface border-none focus:ring-2 focus:ring-primary/25 transition-all appearance-none"
                        >
                            <option value="document">📄 Document</option>
                            <option value="video">▶️ Video</option>
                            <option value="link">🔗 Link</option>
                        </select>
                        @error('type')
                            <p class="text-error text-xs mt-1.5 flex items-center gap-1" role="alert">
                                <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="material-xp" class="block text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                            XP Reward <span class="text-error" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="material-xp"
                                wire:model="xp_reward"
                                type="number"
                                min="0"
                                max="9999"
                                class="w-full bg-surface-container-low rounded-xl px-4 py-3 pr-14 text-sm font-body text-on-surface border-none focus:ring-2 focus:ring-primary/25 transition-all"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-label font-bold text-on-surface-variant uppercase"
                                  aria-hidden="true">XP</span>
                        </div>
                        @error('xp_reward')
                            <p class="text-error text-xs mt-1.5 flex items-center gap-1" role="alert">
                                <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- File / URL --}}
                <div>
                    <label for="material-url" class="block text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                        File URL / Link
                    </label>
                    <input
                        id="material-url"
                        wire:model="file_url"
                        type="url"
                        placeholder="https://..."
                        class="w-full bg-surface-container-low rounded-xl px-4 py-3 text-sm font-body text-on-surface border-none focus:ring-2 focus:ring-primary/25 transition-all placeholder:text-outline-variant"
                    >
                    @error('file_url')
                        <p class="text-error text-xs mt-1.5 flex items-center gap-1" role="alert">
                            <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>{{-- /modal body --}}

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 px-8 py-5 border-t border-outline-variant/10">
                <button
                    wire:click="closeModal"
                    class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant rounded-xl text-sm font-bold hover:bg-surface-container hover:text-on-surface transition-all focus:outline-none focus:ring-2 focus:ring-outline/30"
                >
                    Cancel
                </button>
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed !scale-100"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-br from-primary to-primary-container text-on-primary rounded-xl text-sm font-bold shadow-md shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all focus:outline-none focus:ring-2 focus:ring-primary/50"
                >
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:16px; font-variation-settings:'FILL' 1;" aria-hidden="true">save</span>
                        {{ $editId ? 'Update Material' : 'Create Material' }}
                    </span>
                    <svg wire:loading wire:target="save"
                         class="animate-spin h-4 w-4 text-on-primary" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading wire:target="save" class="font-bold">Saving...</span>
                </button>
            </div>

        </div>{{-- /modal panel --}}
    </div>{{-- /modal overlay --}}

</div>{{-- /x-data Alpine root --}}
