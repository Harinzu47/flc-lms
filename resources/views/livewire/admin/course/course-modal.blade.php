{{-- Course Dialog Modal --}}
<div x-data="{ open: @entangle('isCourseModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
    <div @click.away="open = false; $wire.closeCourseModal()" @keydown.escape.window="open = false; $wire.closeCourseModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
        <h3 class="text-xl font-headline font-bold text-on-surface">{{ $courseId ? 'Edit Course Info' : 'Create New Course' }}</h3>
        
        <div wire:loading.remove wire:target="createCourse, editCourse">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Course Title</label>
                    <input type="text" wire:model="courseTitle" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('courseTitle') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Description</label>
                    <textarea wire:model="courseDescription" rows="3" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none"></textarea>
                    @error('courseDescription') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">Difficulty</label>
                        <select wire:model="courseDifficultyLevel" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        @error('courseDifficultyLevel') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">Min Level Required</label>
                        <select wire:model="courseMinLevelRequired" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="">None (Any Level)</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl->id }}">{{ $lvl->name }} ({{ $lvl->min_xp }} XP)</option>
                            @endforeach
                        </select>
                        @error('courseMinLevelRequired') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Prerequisite Course</label>
                    <select wire:model="coursePrerequisiteId" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        <option value="">None</option>
                        @foreach($coursesList as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}</option>
                        @endforeach
                    </select>
                    @error('coursePrerequisiteId') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="courseIsPublished" wire:model="courseIsPublished" class="rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="courseIsPublished" class="text-sm font-semibold text-on-surface">Publish this Course (Visible to Students)</label>
                </div>
            </div>
        </div>

        {{-- Skeleton Loader --}}
        <div wire:loading wire:target="createCourse, editCourse" class="animate-pulse space-y-4">
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
            <button @click="open = false; $wire.closeCourseModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
            <button wire:click="saveCourse" type="button" wire:loading.attr="disabled" wire:target="saveCourse" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity flex items-center justify-center">
                <svg wire:loading wire:target="saveCourse" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="saveCourse">Save Course</span>
                <span wire:loading wire:target="saveCourse">Processing...</span>
            </button>
        </div>
    </div>
</div>
