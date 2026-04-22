{{--
    Partial: Admin Portal Sidebar Navigation
    ────────────────────────────────────────────────────────────────────────────
    Shared across all Admin-layout pages (layouts.base users).

    Optional context variables:
      $activePage — string  One of: 'dashboard', 'users', 'materials',
                                     'tasks', 'grading'
                            Defaults gracefully to none active if not passed.

      $pendingSubmissions — Collection<Submission>|null
                            Passed by GradingStation only; used for the badge
                            count on the Grading link. Other pages pass nothing.
    ────────────────────────────────────────────────────────────────────────────
--}}

@php
    $activePage ??= '';
    $pendingCount = isset($pendingSubmissions) ? $pendingSubmissions->count() : 0;

    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard',  'icon' => 'dashboard',      'route' => route('dashboard')],
        ['key' => 'users',     'label' => 'Users',       'icon' => 'group',          'route' => '#'],
        ['key' => 'materials', 'label' => 'Materials',   'icon' => 'library_books',  'route' => route('admin.materials')],
        ['key' => 'tasks',     'label' => 'Tasks',       'icon' => 'assignment',     'route' => '#'],
        ['key' => 'grading',   'label' => 'Grading',     'icon' => 'grade',          'route' => route('admin.grading')],
    ];
@endphp

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
            @foreach($navItems as $item)
                @php $isActive = $activePage === $item['key']; @endphp
                <a href="{{ $item['route'] }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 text-sm font-medium
                       {{ $isActive
                           ? 'bg-blue-50 text-blue-700 border-l-4 border-primary'
                           : 'text-on-surface-variant hover:bg-slate-100 hover:text-primary' }}"
                   {{ $isActive ? 'aria-current=page' : '' }}>
                    <span class="material-symbols-outlined"
                          style="{{ $isActive ? 'font-variation-settings:\'FILL\' 1;' : '' }}"
                          aria-hidden="true">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                    {{-- Pending badge: only shown on the Grading link when count > 0 --}}
                    @if($item['key'] === 'grading' && $pendingCount > 0)
                        <span class="ml-auto inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-error text-on-error rounded-full"
                              aria-label="{{ $pendingCount }} pending submissions">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            @endforeach
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
                   onclick="event.preventDefault(); document.getElementById('admin-sidebar-logout-form').submit();"
                   class="flex items-center gap-3 text-error px-4 py-3 hover:bg-error-container/20 rounded-lg transition-colors text-sm">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    Logout
                </a>
                <form id="admin-sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</aside>
