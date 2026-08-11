{{-- Assign Instruktur Modal --}}
@if($isAssignInstrukturModalOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Background overlay --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" aria-hidden="true" wire:click="closeAssignInstrukturModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal panel --}}
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-slate-100">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <span class="material-symbols-outlined text-blue-600">person_add</span>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-bold leading-6 text-slate-900 font-headline" id="modal-title">
                            Assign Instruktur
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">
                                Select instruktur accounts to assign to this course. They will be able to manage this course and grade its submissions.
                            </p>
                        </div>
                        
                        <div class="mt-4 max-h-64 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100">
                            @forelse($instrukturList as $inst)
                                <label class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition-colors">
                                    <input type="checkbox" wire:model="selectedInstrukturIds" value="{{ $inst->id }}" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary focus:ring-2">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-700">{{ $inst->name }}</span>
                                        <span class="text-xs text-slate-500">{{ $inst->email }}</span>
                                    </div>
                                </label>
                            @empty
                                <div class="p-4 text-center text-sm text-slate-500">
                                    No instruktur accounts found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" wire:click="saveInstrukturAssignments" class="inline-flex justify-center w-full px-4 py-2 text-base font-semibold text-white transition-all border border-transparent rounded-xl shadow-sm bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:w-auto sm:text-sm">
                        Save Assignments
                    </button>
                    <button type="button" wire:click="closeAssignInstrukturModal" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-semibold transition-all bg-white border rounded-xl shadow-sm border-slate-300 text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
