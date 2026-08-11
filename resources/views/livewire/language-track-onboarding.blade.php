<div>
@if($isOpen)
<div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay (No wire:click close because it's forced) --}}
        <div class="fixed inset-0 transition-opacity bg-slate-900/80 backdrop-blur-sm" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-8 border border-slate-100">
            <div>
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-blue-50 rounded-2xl">
                    <span class="material-symbols-outlined text-primary text-3xl">language</span>
                </div>
                <div class="mt-5 text-center">
                    <h3 class="text-xl font-bold leading-6 text-slate-900 font-headline" id="modal-title">
                        Pilih Peminatan Bahasa
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500">
                            Silakan pilih setidaknya satu peminatan bahasa untuk menyesuaikan materi dan kursus yang akan Anda pelajari. Anda dapat memilih lebih dari satu.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center gap-4 p-4 border rounded-2xl cursor-pointer transition-all hover:bg-slate-50 {{ in_array('inggris', $selectedTracks) ? 'border-primary bg-blue-50/30' : 'border-slate-200' }}">
                    <input type="checkbox" wire:model.live="selectedTracks" value="inggris" class="w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary focus:ring-2">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-800">Bahasa Inggris</span>
                        <span class="text-xs text-slate-500">Materi kurikulum Bahasa Inggris</span>
                    </div>
                </label>

                <label class="flex items-center gap-4 p-4 border rounded-2xl cursor-pointer transition-all hover:bg-slate-50 {{ in_array('arab', $selectedTracks) ? 'border-primary bg-blue-50/30' : 'border-slate-200' }}">
                    <input type="checkbox" wire:model.live="selectedTracks" value="arab" class="w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary focus:ring-2">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-800">Bahasa Arab</span>
                        <span class="text-xs text-slate-500">Materi kurikulum Bahasa Arab</span>
                    </div>
                </label>
                
                @error('selectedTracks') <span class="text-xs text-error font-semibold mt-2 block text-center">{{ $message }}</span> @enderror
            </div>

            <div class="mt-8 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="save" class="inline-flex justify-center w-full px-4 py-3 text-sm font-bold text-white transition-all border border-transparent rounded-xl shadow-sm bg-primary hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Simpan Peminatan
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
