{{--
    Course Builder UI — FLC UMJ Admin Portal
    ────────────────────────────────────────────────────────────────────────────
    Consolidated Course Builder wrapped in the standard admin shell chrome
    (sidebar, header, content area) for absolute layout consistency.
    ────────────────────────────────────────────────────────────────────────────
--}}

<div
    x-data="{
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
    @include('livewire.partials.admin.sidebar', ['activePage' => 'courses'])

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
            <div class="max-w-6xl mx-auto space-y-6">

                @if($selectedCourseId !== null)
                    {{-- ───────────────────────────────────────────────────────────── --}}
                    {{-- BUILDER MODE                                                  --}}
                    {{-- ───────────────────────────────────────────────────────────── --}}
                    
                    {{-- Header Breadcrumbs & Action --}}
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
                                                <div class="bg-surface-container-low border border-outline-variant/10 p-3 rounded-xl flex items-center justify-between group">
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
                                                <div class="bg-surface-container-low border border-outline-variant/10 p-3 rounded-xl flex items-center justify-between group">
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

                @else
                    {{-- ───────────────────────────────────────────────────────────── --}}
                    {{-- LIST MODE                                                     --}}
                    {{-- ───────────────────────────────────────────────────────────── --}}
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-4xl font-headline font-extrabold text-on-surface tracking-tight">Courses Management</h1>
                            <p class="text-on-surface-variant mt-1 text-sm">Manage curriculum, learning modules, lessons, and assignments for the LMS.</p>
                        </div>
                        <button wire:click="createCourse" class="bg-primary text-on-primary px-5 py-3 rounded-xl font-semibold flex items-center justify-center gap-2 shadow-md hover:opacity-90 transition-opacity text-sm">
                            <span class="material-symbols-outlined">add</span>
                            Create Course
                        </button>
                    </div>

                    <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-outline-variant/10">
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label">Course Title</th>
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label font-label">Difficulty</th>
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label font-label">Min Level</th>
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label font-label">Prerequisite</th>
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label font-label">Status</th>
                                        <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label font-label text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/5">
                                    @forelse($courses as $crs)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="space-y-1">
                                                    <p class="font-headline font-bold text-on-surface text-base">{{ $crs->title }}</p>
                                                    <p class="text-xs text-on-surface-variant line-clamp-1 max-w-sm">{{ $crs->description }}</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center text-[10px] font-bold font-label uppercase tracking-widest px-2.5 py-1 rounded-full border 
                                                    {{ $crs->difficulty_level === 'beginner' ? 'bg-green-100 text-green-800 border-green-200' : ($crs->difficulty_level === 'intermediate' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-red-100 text-red-800 border-red-200') }}">
                                                    {{ $crs->difficulty_level }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-on-surface">
                                                {{ $crs->minLevel?->name ?? 'Level 1 (Any)' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-on-surface-variant max-w-[200px] truncate">
                                                {{ $crs->prerequisite?->title ?? 'None' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($crs->is_published)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                        Published
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                                        Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button wire:click="selectCourse({{ $crs->id }})" class="bg-primary hover:bg-primary-container text-on-primary hover:text-primary px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-0.5">
                                                        <span class="material-symbols-outlined text-sm">settings_input_composite</span>
                                                        Builder
                                                    </button>
                                                    <button wire:click="editCourse({{ $crs->id }})" class="p-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl transition-all shadow-sm" title="Edit Course details">
                                                        <span class="material-symbols-outlined text-base block">edit</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                                                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2">auto_stories</span>
                                                <p class="font-headline font-bold text-on-surface">No courses available.</p>
                                                <p class="text-xs text-on-surface-variant mt-1">Get started by creating your first course.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($courses instanceof \Illuminate\Contracts\Pagination\Paginator && $courses->hasPages())
                            <div class="px-6 py-4 border-t border-outline-variant/10">
                                {{ $courses->links() }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </main>

    {{-- ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── --}}
    {{-- MODALS CONFIGURATION (z-[100] overlays the z-50 sidebar cleanly)      --}}
    {{-- ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── --}}

    {{-- COURSE MODAL --}}
    <div x-data="{ open: @entangle('isCourseModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="open = false; $wire.closeCourseModal()" @keydown.escape.window="open = false; $wire.closeCourseModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
            <h3 class="text-xl font-headline font-bold text-on-surface">{{ $courseId ? 'Edit Course Info' : 'Create New Course' }}</h3>
            
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

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
                <button @click="open = false; $wire.closeCourseModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
                <button wire:click="saveCourse" type="button" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity">Save Course</button>
            </div>
        </div>
    </div>

    {{-- MODULE MODAL --}}
    <div x-data="{ open: @entangle('isModuleModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="open = false; $wire.closeModuleModal()" @keydown.escape.window="open = false; $wire.closeModuleModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
            <h3 class="text-xl font-headline font-bold text-on-surface">{{ $moduleId ? 'Edit Module' : 'Add New Module' }}</h3>
            
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

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
                <button @click="open = false; $wire.closeModuleModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
                <button wire:click="saveModule" type="button" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity">Save Module</button>
            </div>
        </div>
    </div>

    {{-- MATERIAL MODAL --}}
    <div x-data="{ open: @entangle('isMaterialModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="open = false; $wire.closeMaterialModal()" @keydown.escape.window="open = false; $wire.closeMaterialModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
            <h3 class="text-xl font-headline font-bold text-on-surface">{{ $materialId ? 'Edit Material' : 'Add Learning Material' }}</h3>
            
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

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
                <button @click="open = false; $wire.closeMaterialModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
                <button wire:click="saveMaterial" type="button" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity">Save Material</button>
            </div>
        </div>
    </div>

    {{-- TASK MODAL --}}
    <div x-data="{ open: @entangle('isTaskModalOpen') }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="open = false; $wire.closeTaskModal()" @keydown.escape.window="open = false; $wire.closeTaskModal()" class="bg-surface-container-lowest border border-outline-variant/10 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
            <h3 class="text-xl font-headline font-bold text-on-surface">{{ $taskId ? 'Edit Task' : 'Add Task / Assignment' }}</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Task Title</label>
                    <input type="text" wire:model="taskTitle" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('taskTitle') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Instructions / Description</label>
                    <textarea wire:model="taskDescription" rows="4" placeholder="Detail petunjuk pengerjaan tugas bagi mahasiswa..." class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none"></textarea>
                    @error('taskDescription') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">Submission Type</label>
                        <select wire:model="taskType" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="essay">Online Writing (Essay)</option>
                            <option value="file_upload">File Upload (PDF/ZIP)</option>
                            <option value="quiz">Interactive Quiz</option>
                        </select>
                        @error('taskType') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold font-headline text-on-surface mb-1">Max Base XP</label>
                        <input type="number" wire:model="taskBaseXp" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        @error('taskBaseXp') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold font-headline text-on-surface mb-1">Deadline Duration (Days)</label>
                    <input type="number" min="1" max="365" wire:model="taskDaysLimit" placeholder="No deadline (unlimited)" class="w-full bg-surface-container-low border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @error('taskDaysLimit') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/10">
                <button @click="open = false; $wire.closeTaskModal()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-bold transition-all">Cancel</button>
                <button wire:click="saveTask" type="button" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-opacity">Save Task</button>
            </div>
        </div>
    </div>

</div>
