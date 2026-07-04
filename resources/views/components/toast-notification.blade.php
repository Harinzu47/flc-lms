{{--
    Component: Toast / Snackbar Notification
    ────────────────────────────────────────────────────────────────────────────
    Self-contained Alpine.js toast that listens for Livewire's "notify" browser event.
    Drop into any layout to provide global toast capability.

    Usage in layouts:  <x-toast-notification />

    Dispatch from Livewire:
        $this->dispatch('notify', message: 'XP Awarded!');
        $this->dispatch('notify', message: 'Saved!', subtitle: 'Changes applied.', icon: 'check_circle');
    ────────────────────────────────────────────────────────────────────────────
--}}
<div
    x-data="{
        toastVisible: false,
        toastMessage: '',
        toastSubtitle: '',
        toastIcon: 'task_alt',
        showToast(detail) {
            this.toastMessage  = detail.message  || '';
            this.toastSubtitle = detail.subtitle || '';
            this.toastIcon     = detail.icon     || 'task_alt';
            this.toastVisible  = true;
            setTimeout(() => this.toastVisible = false, 4000);
        }
    }"
    @notify.window="showToast($event.detail)"
>
    <div
        x-show="toastVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-24 right-6 z-[9999] flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-4 rounded-2xl shadow-[0_10px_25px_-5px_rgba(43,75,185,0.45)]"
        role="alert"
        aria-live="polite"
    >
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" x-text="toastIcon"></span>
        <div>
            <p class="font-headline font-bold text-base leading-none" x-text="toastMessage"></p>
            <p x-show="toastSubtitle" class="text-on-primary/75 text-sm mt-0.5" x-text="toastSubtitle"></p>
        </div>
    </div>
</div>
