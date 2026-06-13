{{--
    User Manager — FLC UMJ Admin Portal
    ────────────────────────────────────────────────────────────────────────────
    Backend:  App\Livewire\Admin\UserManager
    Layout:   layouts.base (bare HTML shell — admin uses its own chrome)
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
        activeTab: 'profile', // profile, xp, badges
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
    @include('livewire.partials.admin.sidebar', ['activePage' => 'users'])

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
                        wire:model.live.debounce.300ms="search"
                        class="bg-surface-container-low border-none rounded-full pl-10 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-primary/20 transition-all"
                        placeholder="Search users by name/email..."
                        type="search"
                        aria-label="Search users"
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
                                Users Management
                            </h2>
                        </div>
                        <p class="text-on-surface-variant ml-4 text-sm">
                            Manage user profiles, adjust XP points, and sync achievements.
                        </p>
                    </div>
                    {{-- Add New User button --}}
                    <button
                        wire:click="create"
                        id="add-user-btn"
                        class="flex items-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary px-5 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                        aria-label="Add a new user"
                    >
                        <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">add</span>
                        Add New User
                    </button>
                </div>

                {{-- ── DATA TABLE ───────────────────────────────────────────── --}}
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" aria-label="Users catalog">
                            <thead>
                                <tr class="bg-surface-container-low">
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Name / Email</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Role</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Level</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">XP Points</th>
                                    <th scope="col" class="text-left px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Badges</th>
                                    <th scope="col" class="text-right px-6 py-4 text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @forelse($users as $userItem)
                                    <tr class="hover:bg-surface-container-low transition-colors group"
                                        wire:key="user-row-{{ $userItem->id }}">

                                        {{-- Name + Email --}}
                                        <td class="px-6 py-4">
                                            <p class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">
                                                {{ $userItem->name }}
                                            </p>
                                            <p class="text-on-surface-variant text-xs mt-0.5">
                                                {{ $userItem->email }}
                                            </p>
                                        </td>

                                        {{-- Role --}}
                                        <td class="px-6 py-4">
                                            @if($userItem->role === 'admin')
                                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 border border-red-200 px-2.5 py-0.5 rounded-full text-xs font-bold font-label uppercase">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 border border-green-200 px-2.5 py-0.5 rounded-full text-xs font-bold font-label uppercase">
                                                    Student
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Level --}}
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-on-surface-variant">
                                                {{ $userItem->level?->name ?? 'Novice' }}
                                            </span>
                                        </td>

                                        {{-- XP --}}
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 font-headline font-black text-secondary">
                                                <span class="material-symbols-outlined text-sm"
                                                      style="font-variation-settings:'FILL' 1;"
                                                      aria-hidden="true">bolt</span>
                                                {{ number_format($userItem->total_xp) }} XP
                                            </span>
                                        </td>

                                        {{-- Badges unlocked --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-1">
                                                @if($userItem->badges->isNotEmpty())
                                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-lg text-xs font-black">
                                                        <span class="material-symbols-outlined text-xs" style="font-variation-settings:'FILL' 1;">military_tech</span>
                                                        {{ $userItem->badges->count() }} Badges
                                                    </span>
                                                @else
                                                    <span class="text-xs text-on-surface-variant italic">No badges</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    wire:click="edit({{ $userItem->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-high text-on-surface-variant rounded-lg text-xs font-bold hover:bg-primary hover:text-on-primary transition-all"
                                                    aria-label="Edit {{ $userItem->name }}"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">edit</span>
                                                    Manage
                                                </button>
                                                <button
                                                    wire:click="delete({{ $userItem->id }})"
                                                    wire:confirm="Are you sure you want to delete user '{{ addslashes($userItem->name) }}'? This will remove all their submissions and XP logs."
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-error-container text-on-error-container rounded-lg text-xs font-bold hover:bg-error hover:text-on-error transition-all"
                                                    aria-label="Delete {{ $userItem->name }}"
                                                >
                                                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">delete</span>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-16 text-center">
                                            <span class="material-symbols-outlined text-5xl text-outline-variant block mb-3" aria-hidden="true">group</span>
                                            <p class="font-headline font-bold text-on-surface">No users found.</p>
                                            <p class="text-on-surface-variant text-sm mt-1">Try a different search query or create a new user.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($users->hasPages())
                        <div class="px-6 py-4 border-t border-outline-variant/10">
                            {{ $users->links() }}
                        </div>
                    @endif

                </div>{{-- /data table card --}}

            </div>{{-- /max-w-6xl --}}
        </div>{{-- /page body --}}

    </main>{{-- /pl-64 --}}

    {{-- ────────────────────────────────────────────────────────────────────────
         MANAGE MODAL
         Alpine's `open` is @entangled with Livewire's $isModalOpen.
         The backdrop traps the user in-context; Escape / Cancel both close.
         Has 3 tabs for Clean Separation of concerns:
           1. profile (CRUD info)
           2. xp (XP adjustments)
           3. badges (sync achievements)
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
            @click.away="open = false; $wire.closeModal()"
            @keydown.escape.window="open = false; $wire.closeModal()"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-3xl shadow-2xl border border-outline-variant/20 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]"
        >
            {{-- Modal Header --}}
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 id="modal-title" class="text-xl font-headline font-extrabold text-blue-900">
                        {{ $userId ? 'Manage User Account' : 'Add New User' }}
                    </h3>
                    @if($userId)
                        <p class="text-xs text-on-surface-variant mt-0.5">ID: {{ $userId }} · Editing profile and gamification properties</p>
                    @endif
                </div>
                <button
                    @click="open = false; $wire.closeModal()"
                    class="p-2 rounded-full hover:bg-slate-100 text-on-surface-variant transition-colors"
                    aria-label="Close modal"
                >
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>

            @if($userId)
                {{-- Tabs Header --}}
                <div class="bg-slate-50/50 px-8 border-b border-slate-100 flex gap-4 text-sm font-medium">
                    <button
                        @click="activeTab = 'profile'"
                        :class="activeTab === 'profile' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary'"
                        class="py-3 border-b-2 font-headline transition-all"
                    >
                        Account Info
                    </button>
                    <button
                        @click="activeTab = 'xp'"
                        :class="activeTab === 'xp' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary'"
                        class="py-3 border-b-2 font-headline transition-all"
                    >
                        XP Adjustment
                    </button>
                    <button
                        @click="activeTab = 'badges'"
                        :class="activeTab === 'badges' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-primary'"
                        class="py-3 border-b-2 font-headline transition-all"
                    >
                        Achievements
                    </button>
                </div>
            @endif

            {{-- Modal Body (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">

                {{-- ── TAB 1: Profile CRUD ── --}}
                <div x-show="!$wire.userId || activeTab === 'profile'" class="space-y-6">
                    <form wire:submit.prevent="save" class="space-y-6">
                        
                        {{-- Name --}}
                        <div class="space-y-2">
                            <label for="input-name" class="block text-sm font-bold font-headline text-on-surface">Full Name</label>
                            <input
                                id="input-name"
                                wire:model="name"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-error @enderror"
                                placeholder="e.g. John Doe"
                                type="text"
                                required
                            >
                            @error('name') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="space-y-2">
                            <label for="input-email" class="block text-sm font-bold font-headline text-on-surface">Email Address</label>
                            <input
                                id="input-email"
                                wire:model="email"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('email') border-error @enderror"
                                placeholder="e.g. student@lms.local"
                                type="email"
                                required
                            >
                            @error('email') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div class="space-y-2">
                            <label for="input-role" class="block text-sm font-bold font-headline text-on-surface">System Role</label>
                            <select
                                id="input-role"
                                wire:model="role"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                            >
                                <option value="member">Student (Member)</option>
                                <option value="admin">Instructor (Admin)</option>
                            </select>
                            @error('role') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div class="space-y-2">
                            <label for="input-password" class="block text-sm font-bold font-headline text-on-surface">
                                Password {{ $userId ? '(Leave blank to keep current)' : '' }}
                            </label>
                            <input
                                id="input-password"
                                wire:model="password"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-error @enderror"
                                placeholder="{{ $userId ? '••••••••' : 'Minimum 8 characters' }}"
                                type="password"
                                {{ $userId ? '' : 'required' }}
                            >
                            @error('password') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Save Button for CRUD --}}
                        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="open = false; $wire.closeModal()"
                                class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-on-surface-variant hover:bg-slate-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-xl text-xs font-bold shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all"
                            >
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>

                {{-- ── TAB 2: XP Manual Adjustments ── --}}
                @if($userId)
                    <div x-show="activeTab === 'xp'" class="space-y-6">
                        <form wire:submit.prevent="adjustXp" class="space-y-6">
                            
                            <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">info</span>
                                <div class="text-xs text-blue-900">
                                    <strong>XP Log Audit Trail:</strong> Adjusting XP will immediately append a record in the <code>xp_logs</code> audit trail and automatically run background queue tasks to recalculate the student's Level and check Badge triggers.
                                </div>
                            </div>

                            {{-- XP Delta (add/subtract) --}}
                            <div class="space-y-2">
                                <label for="input-xp-delta" class="block text-sm font-bold font-headline text-on-surface">XP Points Adjustment</label>
                                <input
                                    id="input-xp-delta"
                                    wire:model="xpDelta"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('xpDelta') border-error @enderror"
                                    placeholder="e.g. +500 or -200"
                                    type="number"
                                    required
                                >
                                <p class="text-xs text-on-surface-variant">Use positive integers (e.g. <code>100</code>) to award points, or negative (e.g. <code>-50</code>) to deduct points.</p>
                                @error('xpDelta') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>

                            {{-- XP Correction Reason --}}
                            <div class="space-y-2">
                                <label for="input-xp-reason" class="block text-sm font-bold font-headline text-on-surface">Audit Reason</label>
                                <input
                                    id="input-xp-reason"
                                    wire:model="xpReason"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('xpReason') border-error @enderror"
                                    placeholder="e.g. Bonus Kehadiran or Koreksi Salah Input"
                                    type="text"
                                    required
                                >
                                @error('xpReason') <span class="text-error text-xs block mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>

                            {{-- Adjust Button --}}
                            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="open = false; $wire.closeModal()"
                                    class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-on-surface-variant hover:bg-slate-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="bg-secondary text-on-secondary px-6 py-2.5 rounded-xl text-xs font-bold shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all"
                                >
                                    Adjust XP Points
                                </button>
                            </div>

                        </form>
                    </div>
                @endif

                {{-- ── TAB 3: Achievements (Badges Sync) ── --}}
                @if($userId)
                    <div x-show="activeTab === 'badges'" class="space-y-6">
                        <form wire:submit.prevent="syncBadges" class="space-y-6">

                            <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-2xl flex items-center gap-3">
                                <span class="material-symbols-outlined text-amber-700" style="font-variation-settings:'FILL' 1;">military_tech</span>
                                <div class="text-xs text-amber-900">
                                    <strong>Badge Unlocked Timestamps:</strong> Checked badges are currently unlocked by this user. Unchecking a badge will revoke it. Newly checked badges will be awarded with the current timestamp. Existing badge timestamps will be preserved.
                                </div>
                            </div>

                            {{-- Badges Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($allBadges as $badge)
                                    <label class="flex items-start gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-100/50 transition-colors">
                                        <input
                                            type="checkbox"
                                            wire:model="selectedBadges"
                                            value="{{ $badge->id }}"
                                            class="rounded text-primary focus:ring-primary/20 border-slate-300 mt-1"
                                        >
                                        <div class="flex gap-2">
                                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold flex-shrink-0">
                                                <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">military_tech</span>
                                            </div>
                                            <div>
                                                <p class="font-headline font-bold text-xs text-on-surface">{{ $badge->name }}</p>
                                                <p class="text-[10px] text-on-surface-variant leading-tight mt-0.5">{{ $badge->description }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="col-span-2 text-center py-8 text-on-surface-variant italic text-sm">
                                        No achievements/badges found in the database.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Sync Button --}}
                            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="open = false; $wire.closeModal()"
                                    class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-on-surface-variant hover:bg-slate-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="bg-primary hover:bg-primary-container text-on-primary px-6 py-2.5 rounded-xl text-xs font-bold shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all"
                                >
                                    Sync Achievements
                                </button>
                            </div>

                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>
