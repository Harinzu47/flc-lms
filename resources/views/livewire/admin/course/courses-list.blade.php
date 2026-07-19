{{-- List Mode: Courses Overview Table --}}
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
                    <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label">Difficulty</th>
                    <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label">Min Level</th>
                    <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label">Prerequisite</th>
                    <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label text-right">Actions</th>
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
