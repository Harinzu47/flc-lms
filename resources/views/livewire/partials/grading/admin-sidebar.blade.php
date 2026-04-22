{{--
    Partial: Admin Sidebar Navigation
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by GradingStation component):
      $pendingSubmissions — Collection<Submission> (for the badge count)
    ────────────────────────────────────────────────────────────────────────────
--}}

<aside class="h-screen w-64 fixed left-0 top-0 bg-slate-50 border-r border-slate-200 z-50 flex-shrink-0"
       aria-label="Admin navigation">
    <div class="flex flex-col h-full py-6">

        {{-- Brand --}}
        <div class="px-6 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-on-primary font-bold shadow-lg shadow-primary/20"
                     aria-hidden="true">
                    <span class="material-symbols-outlined">school</span>
                </div>
                <div>
                    <h2 class="font-headline font-extrabold text-blue-900 text-lg leading-none">Admin Portal</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Academic Management</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-4 space-y-1" aria-label="Admin sections">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-slate-100 hover:text-primary rounded-lg transition-all duration-200 text-sm font-medium">
                <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                Dashboard
            </a>
            <a href="#"
               class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-slate-100 hover:text-primary rounded-lg transition-all duration-200 text-sm font-medium">
                <span class="material-symbols-outlined" aria-hidden="true">group</span>
                Users
            </a>
            <a href="#"
               class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-slate-100 hover:text-primary rounded-lg transition-all duration-200 text-sm font-medium">
                <span class="material-symbols-outlined" aria-hidden="true">library_books</span>
                Materials
            </a>
            <a href="#"
               class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-slate-100 hover:text-primary rounded-lg transition-all duration-200 text-sm font-medium">
                <span class="material-symbols-outlined" aria-hidden="true">assignment</span>
                Tasks
            </a>
            {{-- Active: Grading --}}
            <a href="{{ route('admin.grading') }}"
               class="flex items-center gap-3 bg-blue-50 text-blue-700 rounded-lg px-4 py-3 border-l-4 border-primary transition-all duration-200 text-sm font-medium"
               aria-current="page">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">grade</span>
                Grading
                @if($pendingSubmissions->isNotEmpty())
                    <span class="ml-auto inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-error text-on-error rounded-full"
                          aria-label="{{ $pendingSubmissions->count() }} pending submissions">
                        {{ $pendingSubmissions->count() }}
                    </span>
                @endif
            </a>
        </nav>

        {{-- Bottom Actions --}}
        <div class="px-4 mt-auto pt-6">
            <button class="w-full bg-primary text-on-primary py-3 rounded-xl font-semibold flex items-center justify-center gap-2 shadow-md hover:opacity-90 transition-opacity text-sm">
                <span class="material-symbols-outlined" aria-hidden="true">add</span>
                New Course
            </button>
            <div class="mt-6 pt-4 space-y-1" style="border-top: 1px solid rgba(195,198,215,0.4);">
                <a href="#"
                   class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:text-primary transition-colors text-sm">
                    <span class="material-symbols-outlined" aria-hidden="true">help</span>
                    Support
                </a>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();"
                   class="flex items-center gap-3 text-error px-4 py-3 hover:bg-error-container/20 rounded-lg transition-colors text-sm">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    Logout
                </a>
                <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</aside>
