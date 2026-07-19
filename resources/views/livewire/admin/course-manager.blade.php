<div x-data="{ sidebarOpen: false }" class="bg-background text-on-surface font-body min-h-screen flex">

    {{-- ── ADMIN SIDEBAR ────────────────────────────────────────────────────── --}}
    @include('livewire.partials.admin.sidebar', ['activePage' => 'courses'])

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
    <main class="md:pl-64 min-h-screen flex flex-col w-full">

        {{-- Top App Bar --}}
        <header
            class="sticky top-0 z-40 flex items-center justify-between px-8 py-3 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 -ml-2 text-on-surface-variant hover:text-primary rounded-lg md:hidden focus:outline-none"
                    aria-label="Toggle admin sidebar">
                    <span class="material-symbols-outlined block text-2xl">menu</span>
                </button>
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
                    @include('livewire.admin.course.course-builder')
                @else
                    @include('livewire.admin.course.courses-list')
                @endif
            </div>
        </div>
    </main>

    {{-- ── MODALS CONFIGURATION ─────────────────────────────────────────────── --}}
    @include('livewire.admin.course.course-modal')
    @include('livewire.admin.course.module-modal')
    @include('livewire.admin.course.material-modal')
    @include('livewire.admin.course.task-modal')
</div>