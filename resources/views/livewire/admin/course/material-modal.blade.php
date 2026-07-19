{{-- Material Dialog Modal --}}
<div x-data="{ open: @entangle('isMaterialModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
    <div @click.away="open = false; $wire.closeMaterialModal()" @keydown.escape.window="open = false; $wire.closeMaterialModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
        <h3 class="text-xl font-headline font-bold text-on-surface">{{ $materialId ? 'Edit Material' : 'Add Learning Material' }}</h3>
        
        <div wire:loading.remove wire:target="createMaterial, editMaterial">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Material Title</label>
                    <input type="text" wire:model="materialTitle" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('materialTitle') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div x-data="{ tab: 'write' }" class="space-y-2">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-bold font-headline text-on-surface">
                            {{ $materialType === 'article' ? 'Konten Artikel (Markdown)' : 'Description' }}
                        </label>
                        @if ($materialType === 'article')
                            <div class="flex border border-slate-200 rounded-lg overflow-hidden bg-slate-100 p-0.5 text-xs">
                                <button type="button" @click="tab = 'write'" :class="tab === 'write' ? 'bg-white text-slate-800 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1 rounded-md transition-all">Write</button>
                                <button type="button" @click="tab = 'preview'" :class="tab === 'preview' ? 'bg-white text-slate-800 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1 rounded-md transition-all">Preview</button>
                            </div>
                        @endif
                    </div>
                    
                    <div x-show="tab === 'write' || '{{ $materialType }}' !== 'article'">
                        <textarea wire:model.live.debounce.300ms="materialDescription" rows="{{ $materialType === 'article' ? 8 : 3 }}" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="{{ $materialType === 'article' ? 'Tulis artikel menggunakan sintaksis Markdown...' : '' }}"></textarea>
                    </div>

                    @if ($materialType === 'article')
                        <div x-show="tab === 'preview'" class="p-4 bg-slate-50 rounded-xl border border-slate-200 max-h-60 overflow-y-auto prose prose-slate max-w-none text-sm leading-relaxed text-slate-700">
                            @if(empty($materialDescription))
                                <span class="text-slate-400 italic">Belum ada konten untuk dipratinjau.</span>
                            @else
                                {!! \Illuminate\Support\Str::markdown($materialDescription ?? '', [
                                    'html_input' => 'escape',
                                    'allow_unsafe_links' => false,
                                ]) !!}
                            @endif
                        </div>
                    @endif
                    @error('materialDescription') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">Material Type</label>
                        <select wire:model.live="materialType" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="document">PDF / Document</option>
                            <option value="video">Video URL</option>
                            <option value="link">External Web Link</option>
                            <option value="article">Markdown Article</option>
                        </select>
                        @error('materialType') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">XP Reward</label>
                        <input type="number" wire:model="materialXpReward" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        @error('materialXpReward') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">
                        {{ $materialType === 'article' ? 'Lampiran Dokumen Tambahan (Opsional)' : 'External File URL' }}
                    </label>
                    <input type="url" wire:model="materialFileUrl" placeholder="https://example.com/file.pdf" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('materialFileUrl') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Skeleton Loader --}}
        <div wire:loading wire:target="createMaterial, editMaterial" class="animate-pulse space-y-4">
            <div class="space-y-2">
                <div class="bg-slate-200 h-4 w-24 rounded"></div>
                <div class="bg-slate-200 h-10 w-full rounded-xl"></div>
            </div>
            <div class="space-y-2">
                <div class="bg-slate-200 h-4 w-32 rounded"></div>
                <div class="bg-slate-200 h-16 w-full rounded-xl"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="bg-slate-200 h-4 w-20 rounded"></div>
                    <div class="bg-slate-200 h-10 w-full rounded-xl"></div>
                </div>
                <div class="space-y-2">
                    <div class="bg-slate-200 h-4 w-28 rounded"></div>
                    <div class="bg-slate-200 h-10 w-full rounded-xl"></div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
            <button @click="open = false; $wire.closeMaterialModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
            <button wire:click="saveMaterial" type="button" wire:loading.attr="disabled" wire:target="saveMaterial" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity flex items-center justify-center">
                <svg wire:loading wire:target="saveMaterial" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="saveMaterial">Save Material</span>
                <span wire:loading wire:target="saveMaterial">Processing...</span>
            </button>
        </div>
    </div>
</div>
