{{--
    Course Overview / Modules Timeline — FLC UMJ Student Portal
    ────────────────────────────────────────────────────────────────────────────
    Displays the course curriculum hierarchy and enforces sequential module locking
    and checks completed materials/tasks in-memory.
    ────────────────────────────────────────────────────────────────────────────
--}}

@section('sidebar-title')
    <h2 class="text-blue-800 font-bold font-headline">Course Path</h2>
    <p class="text-xs text-on-surface-variant mt-0.5">Learning Progress</p>
@endsection

@section('sidebar-nav')
    <a href="{{ route('dashboard') }}"
       wire:navigate
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">school</span>
        <span>Academy</span>
    </a>
    <a href="{{ route('library') }}"
       wire:navigate
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
        <span>Library</span>
    </a>
    <a href="{{ route('admin.grading') }}"
       wire:navigate
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">admin_panel_settings</span>
        <span>Admin</span>
    </a>
@endsection

<div class="max-w-4xl mx-auto space-y-8">
    
    {{-- Back Link & Stats Header --}}
    <div class="space-y-4">
        <a href="{{ route('library') }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-primary hover:text-blue-700 transition-colors font-semibold">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Back to Library
        </a>

        <div class="bg-surface-container-lowest border border-outline-variant/10 p-6 rounded-2xl shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold font-label uppercase tracking-widest px-2.5 py-1 rounded-full border 
                        {{ $course->difficulty_level === 'beginner' ? 'bg-green-100 text-green-800 border-green-200' : ($course->difficulty_level === 'intermediate' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-red-100 text-red-800 border-red-200') }}">
                        {{ $course->difficulty_level }}
                    </span>
                    <h1 class="text-3xl font-headline font-extrabold text-on-surface tracking-tight">{{ $course->title }}</h1>
                    <p class="text-sm text-on-surface-variant">{{ $course->description }}</p>
                </div>
                <div class="text-right">
                    <span class="text-3xl font-extrabold text-primary font-headline">{{ $progressPercent }}%</span>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Completed</p>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
    </div>

    {{-- Modules Learning Timeline --}}
    <div class="space-y-6">
        <h2 class="text-xl font-headline font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">route</span>
            Learning Pathway
        </h2>

        @forelse($course->modules as $index => $module)
            @php
                $isModuleLocked = $module->isLockedForUser(auth()->user(), $completedModuleIds);
            @endphp

            @if($isModuleLocked)
                {{-- ── LOCKED MODULE LOOK ── --}}
                <div class="bg-surface-container-low border border-outline-variant/5 rounded-2xl p-6 opacity-60 grayscale relative select-none">
                    <div class="absolute inset-0 bg-white/5 z-10 flex items-center justify-end pr-8">
                        <div class="bg-surface-container-lowest border border-outline-variant/10 p-3 rounded-full shadow-md">
                            <span class="material-symbols-outlined text-2xl text-outline-variant">lock</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-md">
                                Module {{ $index + 1 }}
                            </span>
                            <h3 class="text-lg font-headline font-bold text-on-surface">{{ $module->title }}</h3>
                        </div>
                        <p class="text-xs text-on-surface-variant max-w-xl">{{ $module->description }}</p>
                        <p class="text-[10px] font-bold text-error uppercase tracking-wider flex items-center gap-1 mt-2">
                            <span class="material-symbols-outlined text-xs">lock_open</span>
                            Complete previous modules to unlock
                        </p>
                    </div>
                </div>
            @else
                {{-- ── UNLOCKED MODULE LOOK ── --}}
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl shadow-sm overflow-hidden">
                    {{-- Module Header --}}
                    <div class="bg-slate-50/50 px-6 py-4 border-b border-outline-variant/10">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-primary bg-blue-50 border border-blue-100 px-2.5 py-0.5 rounded-md">
                                Module {{ $index + 1 }}
                            </span>
                            <h3 class="text-lg font-headline font-bold text-on-surface">{{ $module->title }}</h3>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">{{ $module->description }}</p>
                    </div>

                    {{-- Module Items List --}}
                    <div class="divide-y divide-outline-variant/5">
                        
                        {{-- Materials --}}
                        @foreach($module->materials as $mat)
                            @php
                                $hasRead = $readMaterialIds->contains($mat->id);
                            @endphp
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/20 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-primary shadow-sm border border-blue-100">
                                        <span class="material-symbols-outlined text-base">
                                            {{ $mat->type === 'video' ? 'play_circle' : ($mat->type === 'link' ? 'link' : 'description') }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface font-headline">{{ $mat->title }}</p>
                                        <p class="text-xs text-on-surface-variant line-clamp-1 max-w-lg">{{ $mat->description }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-[10px] font-bold text-secondary uppercase tracking-widest flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">bolt</span>
                                        +{{ $mat->xp_reward }} XP
                                    </span>
                                    
                                    @if($hasRead)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50 text-green-600 border border-green-100" title="Completed">
                                            <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">check_circle</span>
                                        </span>
                                    @else
                                        <a href="{{ route('materials.show', $mat) }}"
                                           wire:navigate
                                           class="bg-primary hover:bg-primary-container text-on-primary hover:text-primary px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Read Lesson
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Tasks --}}
                        @foreach($module->tasks as $task)
                            @php
                                $sub = $submissionsMap->get($task->id);
                                $isGraded = $sub && $sub->status === 'graded';
                                $isPending = $sub && $sub->status === 'pending';
                            @endphp
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/20 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 shadow-sm border border-amber-100">
                                        <span class="material-symbols-outlined text-base">
                                            {{ $task->type === 'file_upload' ? 'upload_file' : ($task->type === 'quiz' ? 'quiz' : 'assignment') }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface font-headline">{{ $task->title }}</p>
                                        <p class="text-xs text-on-surface-variant line-clamp-1 max-w-lg">{{ $task->description }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">grade</span>
                                        {{ $task->base_xp }} XP max
                                    </span>
                                    
                                    @if($isGraded)
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded-md border border-green-200">
                                                Score: {{ $sub->score }}
                                            </span>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50 text-green-600 border border-green-100">
                                                <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">check_circle</span>
                                            </span>
                                        </div>
                                    @elseif($isPending)
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                                Pending Grading
                                            </span>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-500 border border-amber-100">
                                                <span class="material-symbols-outlined text-lg">pending</span>
                                            </span>
                                        </div>
                                    @else
                                        <a href="{{ route('tasks.show', $task) }}"
                                           wire:navigate
                                           class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Open Task
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-12 bg-surface-container-low rounded-2xl border border-outline-variant/10">
                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2">auto_stories</span>
                <p class="font-headline font-bold text-on-surface">No Modules Available</p>
                <p class="text-xs text-on-surface-variant mt-1">Check back later for module content uploads.</p>
            </div>
        @endforelse
    </div>
</div>
