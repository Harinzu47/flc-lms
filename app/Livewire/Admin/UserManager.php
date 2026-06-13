<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Events\XpEarned;
use App\Models\Badge;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.base')]
#[Title('User Management — FLC Admin')]
class UserManager extends Component
{
    use WithPagination;

    // ── UI & Search States ───────────────────────────────────────────────────
    public bool $isModalOpen = false;
    public ?int $userId = null;

    #[Url(history: true)]
    public string $search = '';

    // ── Standard CRUD Form Fields ────────────────────────────────────────────
    public string $name = '';
    public string $email = '';
    public string $password = ''; // Diperlukan untuk Create, opsional untuk Edit
    public string $role = 'member';

    // ── Gamification Adjustment Fields ───────────────────────────────────────
    public int $xpDelta = 0;
    public string $xpReason = '';
    public array $selectedBadges = []; // Menyimpan array ID lencana terpilih

    // ── Lifecycle ─────────────────────────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        // Eager-loading relasi level dan badges untuk memotong N+1 kueri di UI list
        $users = User::query()
            ->with(['level', 'badges'])
            ->where('id', '!=', auth()->id()) // Proteksi diri: Jangan tampilkan admin yang sedang login
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $allBadges = Badge::orderBy('name')->get();

        return view('livewire.admin.user-manager', compact('users', 'allBadges'));
    }

    // ── CRUD Actions ─────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function edit(User $user): void
    {
        $this->resetForm();
        $this->userId         = $user->id;
        $this->name           = $user->name;
        $this->email          = $user->email;
        $this->role           = $user->role;
        $this->selectedBadges = $user->badges()->pluck('badge_id')->map(fn($id) => (string)$id)->toArray();
        $this->isModalOpen    = true;
    }

    public function save(): void
    {
        // Jalankan aturan validasi defensif dinamis berbasis kondisi edit/create
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'role'  => ['required', 'in:admin,member'],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
        ];

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId !== null) {
            User::findOrFail($this->userId)->update($data);
            $message = 'User account updated successfully.';
        } else {
            $data['total_xp'] = 0; // Default XP baru
            User::create($data);
            $message = 'User account created successfully.';
        }

        $this->closeModal();
        $this->dispatch('notify', message: $message);
    }

    // ── Advanced Gamification Management Actions ─────────────────────────────
    
    /**
     * Menyesuaikan XP User secara transaksional dan memicu Event Asinkron.
     */
    public function adjustXp(): void
    {
        $this->validate([
            'xpDelta'  => ['required', 'integer', 'not_in:0'],
            'xpReason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $user = User::findOrFail($this->userId);

        // Membungkus dalam transaksi database untuk menjamin kepatuhan ACID
        DB::transaction(function () use ($user) {
            // 1. Catat transaksi audit ke tabel xp_logs
            XpLog::create([
                'user_id'      => $user->id,
                'action'       => 'Manual Correction: ' . $this->xpReason,
                'xp_earned'    => $this->xpDelta,
                'reference_id' => auth()->id(), // Mencatat ID Admin yang melakukan manipulasi
            ]);

            // 2. Lakukan operasi hitung penambahan/pengurangan secara atomik
            $user->increment('total_xp', $this->xpDelta);
        });

        // 3. SELESAIKAN REFRESH MEMORI SEBELUM DISPATCH EVENT
        $user->refresh();

        // 4. Picu Event-Driven Engine untuk kalkulasi level & lencana di latar belakang
        XpEarned::dispatch($user, $this->xpDelta);

        $xpTemp = $this->xpDelta;

        $this->xpDelta = 0;
        $this->xpReason = '';
        $this->dispatch('notify', message: "XP adjusted by {$xpTemp} points. Background sync triggered.");
    }

    /**
     * Menyinkronkan lencana mahasiswa tanpa merusak timestamp asli.
     */
    public function syncBadges(): void
    {
        $user = User::findOrFail($this->userId);

        // Ambil data timestamp unlocked_at lama milik user agar tidak terhapus
        $existingBadges = $user->badges()->pluck('unlocked_at', 'badge_id')->toArray();
        
        $syncData = [];
        foreach ($this->selectedBadges as $badgeId) {
            $id = (int) $badgeId;
            $syncData[$id] = [
                'unlocked_at' => $existingBadges[$id] ?? now() // Gunakan waktu lama, atau set sekarang jika baru
            ];
        }

        // Jalankan sinkronisasi aman
        $user->badges()->sync($syncData);

        $this->dispatch('notify', message: 'User achievements synchronized successfully.');
    }

    public function delete(User $user): void
    {
        $user->delete();
        $this->resetPage();
        $this->dispatch('notify', message: "User '{$user->name}' has been removed.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->userId         = null;
        $this->name           = '';
        $this->email          = '';
        $this->password       = '';
        $this->role           = 'member';
        $this->xpDelta        = 0;
        $this->xpReason       = '';
        $this->selectedBadges = [];
        $this->resetValidation();
    }
}
