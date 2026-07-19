{{-- Builder Mode: Module & Curriculum hierarchy --}}
<div class="flex items-center justify-between">
    <button wire:click="deselectCourse" class="inline-flex items-center gap-2 text-sm text-primary hover:text-blue-700 transition-colors font-semibold">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Back to Courses List
    </button>
    <div class="flex items-center gap-2">
        <button wire:click="editCourse({{ $course->id }})" class="bg-surface-container-high hover:bg-surface-container-highest text-on-surface px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">edit</span>
            Edit Course Info
        </button>
        <button wire:click="createModule" class="bg-primary hover:bg-primary-container text-on-primary hover:text-primary px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            Add Module
        </button>
    </div>
</div>

{{-- Course Cover summary card --}}
<div class="bg-surface-container-lowest border border-outline-variant/10 p-6 rounded-2xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-2">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1 text-[10px] font-bold font-label uppercase tracking-widest px-2.5 py-1 rounded-full border 
                {{ $course->difficulty_level === 'beginner' ? 'bg-green-100 text-green-800 border-green-200' : ($course->difficulty_level === 'intermediate' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-red-100 text-red-800 border-red-200') }}">
                {{ $course->difficulty_level }}
            </span>
            @if($course->is_published)
                <span class="bg-green-500 text-white text-[10px] uppercase font-bold tracking-widest px-2.5 py-1 rounded-full">Published</span>
            @else
                <span class="bg-slate-400 text-white text-[10px] uppercase font-bold tracking-widest px-2.5 py-1 rounded-full">Draft</span>
            @endif
        </div>
        <h1 class="text-3xl font-headline font-extrabold text-on-surface tracking-tight">{{ $course->title }}</h1>
        <p class="text-sm text-on-surface-variant max-w-2xl">{{ $course->description }}</p>
        <div class="flex flex-wrap items-center gap-4 text-xs text-on-surface-variant mt-2 font-medium">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-sm text-primary">school</span>
                Requires: {{ $course->minLevel?->name ?? 'None' }}
            </span>
            @if($course->prerequisite)
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-amber-500">grid_view</span>
                    Prerequisite: {{ $course->prerequisite->title }}
                </span>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button wire:click="deleteCourse({{ $course->id }})" wire:confirm="Are you sure you want to delete this course and ALL its modules, materials, and tasks? This cannot be undone." class="bg-error-container/20 hover:bg-error-container/50 text-error px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">delete</span>
            Delete Course
        </button>
    </div>
</div>

{{-- Tree Builder: Modules List --}}
<div class="space-y-4">
    <h2 class="text-xl font-headline font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">account_tree</span>
        Modules & Curriculum
    </h2>

    @forelse($course->modules as $module)
        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl shadow-sm overflow-hidden" wire:key="module-{{ $module->id }}">
            {{-- Module Title Header --}}
            <div class="bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-outline-variant/10 gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-primary bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                            Sort: {{ $module->sort_order }}
                        </span>
                        <h3 class="font-headline font-bold text-on-surface text-lg">{{ $module->title }}</h3>
                    </div>
                    <p class="text-xs text-on-surface-variant">{{ $module->description }}</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Reorder controls --}}
                    <button wire:click="moveModuleUp({{ $module->id }})" class="p-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 text-slate-600 transition-colors shadow-sm" title="Move Up">
                        <span class="material-symbols-outlined text-sm block">arrow_upward</span>
                    </button>
                    <button wire:click="moveModuleDown({{ $module->id }})" class="p-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 text-slate-600 transition-colors shadow-sm" title="Move Down">
                        <span class="material-symbols-outlined text-sm block">arrow_downward</span>
                    </button>
                    
                    {{-- Module operations --}}
                    <button wire:click="editModule({{ $module->id }})" class="text-xs font-bold text-primary bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition-all flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">edit</span>
                        Edit
                    </button>
                    <button wire:click="deleteModule({{ $module->id }})" wire:confirm="Are you sure you want to delete this module and all nested materials and tasks?" class="text-xs font-bold text-error bg-error-container/20 hover:bg-error-container/40 px-3 py-1.5 rounded-xl transition-all flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">delete</span>
                        Delete
                    </button>
                </div>
            </div>

            {{-- Module nested contents --}}
            <div class="p-6 space-y-4">
                
                {{-- Nested materials --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between border-b border-outline-variant/10 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-primary">menu_book</span>
                            Learning Materials ({{ $module->materials->count() }})
                        </h4>
                        <button wire:click="createMaterial({{ $module->id }})" class="text-[10px] font-bold text-primary border border-primary/20 hover:bg-primary hover:text-on-primary px-2 py-1 rounded-lg transition-all flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">add</span>
                            Add Material
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($module->materials as $mat)
                            <div class="bg-surface-container-low border border-outline-variant/10 p-3 rounded-xl flex items-center justify-between group" wire:key="mat-{{ $mat->id }}">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-sm">
                                            {{ $mat->type === 'video' ? 'play_circle' : ($mat->type === 'link' ? 'link' : 'description') }}
                                        </span>
                                        <span class="text-xs font-semibold text-on-surface font-headline">{{ $mat->title }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                                        <span class="bg-blue-100/50 text-blue-800 px-1.5 py-0.2 rounded-md font-bold">+{{ $mat->xp_reward }} XP</span>
                                        @if($mat->file_url)
                                            <span class="truncate max-w-[150px]">{{ $mat->file_url }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editMaterial({{ $mat->id }})" class="p-1 hover:text-primary transition-colors text-slate-500">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button wire:click="deleteMaterial({{ $mat->id }})" wire:confirm="Delete this material?" class="p-1 hover:text-error transition-colors text-slate-500">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant italic col-span-full">No learning materials added yet.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Nested tasks --}}
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between border-b border-outline-variant/10 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-amber-500">assignment</span>
                            Tasks & Exercises ({{ $module->tasks->count() }})
                        </h4>
                        <button wire:click="createTask({{ $module->id }})" class="text-[10px] font-bold text-amber-600 border border-amber-500/20 hover:bg-amber-500 hover:text-white px-2 py-1 rounded-lg transition-all flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">add</span>
                            Add Task
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($module->tasks as $tsk)
                            <div class="bg-surface-container-low border border-outline-variant/10 p-3 rounded-xl flex items-center justify-between group" wire:key="task-{{ $tsk->id }}">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-amber-500 text-sm">
                                            {{ $tsk->type === 'file_upload' ? 'upload_file' : ($tsk->type === 'quiz' ? 'quiz' : 'essay') }}
                                        </span>
                                        <span class="text-xs font-semibold text-on-surface font-headline">{{ $tsk->title }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                                        <span class="bg-amber-100/50 text-amber-800 px-1.5 py-0.2 rounded-md font-bold">{{ $tsk->base_xp }} XP</span>
                                        @if($tsk->days_limit)
                                            <span class="text-error-container text-error font-semibold">Deadline: {{ $tsk->days_limit }} hari setelah dibuka</span>
                                        @else
                                            <span>No Deadline</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editTask({{ $tsk->id }})" class="p-1 hover:text-primary transition-colors text-slate-500">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button wire:click="deleteTask({{ $tsk->id }})" wire:confirm="Delete this task?" class="p-1 hover:text-error transition-colors text-slate-500">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant italic col-span-full">No tasks added yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    @empty
        <div class="text-center py-12 bg-surface-container-low rounded-2xl border border-outline-variant/10">
            <span class="material-symbols-outlined text-4xl text-outline-variant mb-2">account_tree</span>
            <p class="font-headline font-bold text-on-surface">No Modules Created Yet</p>
            <p class="text-xs text-on-surface-variant mt-1">Create modules first to structure lessons and tasks for this course.</p>
            <button wire:click="createModule" class="mt-4 bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity">
                Add First Module
            </button>
        </div>
    @endforelse
</div>
