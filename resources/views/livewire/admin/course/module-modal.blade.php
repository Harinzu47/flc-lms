{{-- Module Dialog Modal --}}
<div x-data="{ open: @entangle('isModuleModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
    <div @click.away="open = false; $wire.closeModuleModal()" @keydown.escape.window="open = false; $wire.closeModuleModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
        <h3 class="text-xl font-headline font-bold text-on-surface">{{ $moduleId ? 'Edit Module' : 'Add New Module' }}</h3>
        
        <div wire:loading.remove wire:target="createModule, editModule">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Module Title</label>
                    <input type="text" wire:model="moduleTitle" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('moduleTitle') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Description</label>
                    <textarea wire:model="moduleDescription" rows="3" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none"></textarea>
                    @error('moduleDescription') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Sort Order</label>
                    <input type="number" wire:model="moduleSortOrder" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('moduleSortOrder') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Skeleton Loader --}}
        <div wire:loading wire:target="createModule, editModule" class="animate-pulse space-y-4">
            <div class="space-y-2">
                <div class="bg-slate-200 h-4 w-24 rounded"></div>
                <div class="bg-slate-200 h-10 w-full rounded-xl"></div>
            </div>
            <div class="space-y-2">
                <div class="bg-slate-200 h-4 w-32 rounded"></div>
                <div class="bg-slate-200 h-16 w-full rounded-xl"></div>
            </div>
            <div class="space-y-2">
                <div class="bg-slate-200 h-4 w-20 rounded"></div>
                <div class="bg-slate-200 h-10 w-full rounded-xl"></div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
            <button @click="open = false; $wire.closeModuleModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
            <button wire:click="saveModule" type="button" wire:loading.attr="disabled" wire:target="saveModule" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity flex items-center justify-center">
                <svg wire:loading wire:target="saveModule" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="saveModule">Save Module</span>
                <span wire:loading wire:target="saveModule">Processing...</span>
            </button>
        </div>
    </div>
</div>
