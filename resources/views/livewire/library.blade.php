{{--
    Library / Courses Catalog — FLC UMJ Student Portal
    ────────────────────────────────────────────────────────────────────────────
    Displays structured courses instead of flat learning items, matching the CEFR
    framework difficulty levels and level requirements.
    ────────────────────────────────────────────────────────────────────────────
--}}

@section('sidebar-title')
    <h2 class="text-blue-800 font-bold font-headline">Library</h2>
    <p class="text-xs text-on-surface-variant mt-0.5">Knowledge Repository</p>
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
       class="flex items-center gap-3 bg-surface-container-lowest text-primary rounded-xl px-4 py-3 shadow-sm font-medium"
       aria-current="page">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">menu_book</span>
        <span>Library</span>
    </a>
@endsection

<div class="max-w-6xl mx-auto space-y-8">
    {{-- Page Header --}}
    <div>
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-primary text-3xl" aria-hidden="true">local_library</span>
            <span class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest">Curriculum</span>
        </div>
        <h1 class="text-4xl font-headline font-extrabold text-on-surface tracking-tight">
            Learning Library
        </h1>
        <p class="text-on-surface-variant mt-2 text-base max-w-xl">
            Access learning courses, modules, and practical tasks structured by your level of proficiency and CEFR guidelines.
        </p>
    </div>

    {{-- Courses Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($courses as $course)
            @php
                $isLocked = $course->isLockedForUser(auth()->user(), $completedCourseIds);
                $isCompleted = $completedCourseIds->contains($course->id);
                
                $difficultyColors = [
                    'beginner' => 'bg-green-100 text-green-800 border-green-200',
                    'intermediate' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'advanced' => 'bg-red-100 text-red-800 border-red-200',
                ];
                $diffClass = $difficultyColors[$course->difficulty_level] ?? 'bg-slate-100 text-slate-800';
            @endphp

            @if($isLocked)
                {{-- ── LOCKED STATE LOOK ── --}}
                <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/10 opacity-60 grayscale pointer-events-none relative flex flex-col justify-between h-72 shadow-sm"
                     aria-label="Locked: {{ $course->title }} (Requires level or prerequisite)">
                    
                    {{-- Lock Overlay Icon --}}
                    <div class="absolute inset-0 flex items-center justify-center bg-white/10 z-10">
                        <div class="bg-surface-container-lowest/90 p-4 rounded-full shadow-lg border border-outline-variant/20">
                            <span class="material-symbols-outlined text-4xl text-outline-variant" style="font-variation-settings:'FILL' 1;">lock</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold font-label uppercase tracking-widest {{ $diffClass }} px-2.5 py-1 rounded-full border">
                                {{ $course->difficulty_level }}
                            </span>
                            <span class="material-symbols-outlined text-outline-variant">lock</span>
                        </div>
                        <h3 class="font-headline font-bold text-on-surface text-lg leading-tight mb-2">
                            {{ $course->title }}
                        </h3>
                        <p class="text-xs text-on-surface-variant line-clamp-3">
                            {{ $course->description }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 mt-6 pt-4 border-t border-outline-variant/10">
                        @if($course->min_level_required !== null && auth()->user()->total_xp < $course->minLevel->min_xp)
                            <span class="text-[10px] font-bold font-label text-error uppercase tracking-wider flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">school</span>
                                Requires {{ $course->minLevel->name }}
                            </span>
                        @endif

                        @if($course->prerequisite_course_id !== null && !$completedCourseIds->contains($course->prerequisite_course_id))
                            <span class="text-[10px] font-bold font-label text-error uppercase tracking-wider flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">conversion_path</span>
                                Requires Course: {{ $course->prerequisite->title }}
                            </span>
                        @endif
                    </div>
                </div>
            @else
                {{-- ── UNLOCKED STATE LOOK ── --}}
                <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10 hover:shadow-md hover:border-primary/20 transition-all flex flex-col justify-between h-72 shadow-sm group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold font-label uppercase tracking-widest {{ $diffClass }} px-2.5 py-1 rounded-full border">
                                {{ $course->difficulty_level }}
                            </span>
                            
                            @if($isCompleted)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded-md border border-green-200">
                                    <span class="material-symbols-outlined text-xs" style="font-variation-settings:'FILL' 1;">check_circle</span>
                                    Completed
                                </span>
                            @else
                                <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">auto_stories</span>
                            @endif
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="block" wire:navigate>
                            <h3 class="font-headline font-bold text-on-surface text-lg leading-tight mb-2 hover:text-primary transition-colors">
                                {{ $course->title }}
                            </h3>
                        </a>
                        <p class="text-xs text-on-surface-variant line-clamp-3">
                            {{ $course->description }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-outline-variant/10">
                        <span class="text-[10px] font-bold font-label text-secondary uppercase tracking-widest flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings:'FILL' 1;">menu_book</span>
                            {{ $course->modules()->count() }} Modules
                        </span>
                        
                        <a href="{{ route('courses.show', $course) }}"
                           wire:navigate
                           class="bg-primary hover:bg-primary-container text-on-primary hover:text-primary px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                            {{ $isCompleted ? 'Review Course' : 'Enter Course' }}
                        </a>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-full text-center py-16 bg-surface-container-low rounded-2xl border border-outline-variant/10">
                <span class="material-symbols-outlined text-5xl text-outline-variant mb-3" aria-hidden="true">menu_book</span>
                <p class="font-headline font-bold text-on-surface">No courses published yet.</p>
                <p class="text-on-surface-variant text-sm mt-1">Check back later for new curriculum uploads.</p>
            </div>
        @endforelse
    </div>
</div>
