# FLC UMJ Gamified LMS (flc-lms)

FLC UMJ Gamified LMS (`flc-lms`) adalah Learning Management System (LMS) bergamifikasi untuk komunitas belajar kampus FLC Universitas Muhammadiyah Jakarta. Aplikasi ini dirancang menggunakan **TALL Stack** (Tailwind CSS, Alpine.js, Livewire, Laravel) untuk memberikan pengalaman belajar yang dinamis, interaktif, dan premium bagi mahasiswa.

---

## Fitur Utama

### 1. Struktur Kurikulum Terstruktur
- **Kursus (Course)**: Struktur utama kurikulum yang mencakup satu mata kuliah/kursus.
- **Modul (Module)**: Pengelompokan materi dan tugas secara sekuensial.
- **Materi & Tugas (Material & Task)**: Konten pembelajaran berupa bacaan (artikel), dokumen, video, tautan, dan tugas pengumpulan berkas/esai.

### 2. Mesin Gamifikasi
- **XP & Level**: Mahasiswa mendapatkan poin XP secara otomatis setelah menyelesaikan materi membaca (+10 XP) atau setelah tugas mereka dinilai oleh Admin (XP proporsional terhadap skor).
- **Lencana (Badges)**: Lencana pencapaian yang terbuka secara otomatis berbasis kriteria tertentu (event-driven).
- **Leaderboard / Hall of Fame**: Halaman peringkat mahasiswa teratas berdasarkan total XP untuk memicu keterlibatan kompetitif yang sehat.

### 3. Keamanan Tingkat Lanjut (Security Hardening)
- **Safe Output & XSS Protection**: Seluruh deskripsi materi dan tugas diparsing secara aman melalui Markdown parser dengan HTML escaping.
- **Secure Submissions Storage**: Berkas tugas mahasiswa disimpan secara privat pada disk lokal dan hanya dapat diunduh oleh pemilik berkas atau admin melalui pengontrol khusus.
- **Race Condition Prevention**: Mekanisme klaim XP membaca materi diamankan menggunakan *pessimistic database locking* pada record pengguna untuk menghindari eksploitasi perolehan XP ganda secara konkuren.
- **Mass Assignment Protection**: Atribut penting seperti `role`, `level_id`, dan `total_xp` dilindungi dari kerentanan manipulasi permintaan massal.

---

## Panduan Instalasi & Setup Lokal

Proyek ini terintegrasi penuh dengan **Laravel Sail** (lingkungan pengembangan berbasis Docker).

### Prasyarat
- Docker Desktop terinstal dan berjalan di komputer Anda.
- Git.

### Langkah-langkah Setup
1. Clone repositori ke mesin lokal Anda:
   ```bash
   git clone <repository-url> flc-lms
   cd flc-lms
   ```
2. Copy file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
3. Pasang dependensi PHP Composer menggunakan kontainer helper sementara:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php8.2-composer:latest \
       composer install --ignore-platform-reqs
   ```
4. Jalankan kontainer Sail di latar belakang:
   ```bash
   ./vendor/bin/sail up -d
   ```
5. Generate application key:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```
6. Jalankan migrasi database beserta seeder data awal:
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```
7. Pasang dependensi JavaScript (NPM) dan jalankan Vite dev server:
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```
8. Akses aplikasi melalui browser Anda di alamat `http://localhost`.

---

## Lisensi

Proyek ini dilisensikan di bawah **[MIT License](LICENSE)**.
