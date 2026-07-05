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

## Teknologi & Tools yang Digunakan

Berikut adalah daftar lengkap teknologi yang dipakai dalam proyek ini:

| Teknologi | Fungsi | Versi |
|---|---|---|
| **PHP** | Bahasa pemrograman backend | ^8.3 |
| **Laravel** | Framework utama backend | ^13.0 |
| **Livewire** | Komponen interaktif tanpa menulis JavaScript | ^4.2 |
| **Tailwind CSS** | Utility-first CSS framework untuk styling | ^3.1 |
| **Alpine.js** | Micro-framework JavaScript untuk interaktivitas ringan | (via Livewire) |
| **Vite** | Build tool & dev server untuk aset frontend | ^8.0 |
| **MySQL** | Database relasional utama | 8.4 |
| **Redis** | Cache & antrian (queue) | Alpine |
| **Docker** | Kontainerisasi lingkungan pengembangan | - |
| **Laravel Sail** | Wrapper Docker khusus Laravel | ^1.56 |
| **Composer** | Manajer paket PHP | 2.x |
| **NPM / Node.js** | Manajer paket JavaScript | Node 20+ |
| **Git** | Version control / pengontrol versi kode | - |

---

## Panduan Lengkap: Setup Proyek dari Nol (Untuk Pemula)

Panduan ini ditulis **selangkah demi selangkah** supaya kamu yang belum pernah coding pun bisa mengikuti. Baca pelan-pelan, jangan di-skip ya! 🚀

---

### Tahap 1 — Instal Tools yang Dibutuhkan

Sebelum mulai, kamu perlu menginstal 2 aplikasi ini di komputermu:

#### 1.1 Instal Git

Git adalah alat untuk mengunduh (clone) dan mengelola kode sumber.

1. Buka browser, pergi ke: **https://git-scm.com/downloads**
2. Klik tombol **Download for Windows** (atau sesuai OS-mu).
3. Jalankan installer yang sudah diunduh (file `.exe`).
4. Saat proses instalasi, **klik Next terus** sampai selesai (pengaturan default sudah cukup).
5. Setelah selesai, buka **Command Prompt** atau **PowerShell**, lalu ketik:
   ```bash
   git --version
   ```
   Jika berhasil, akan muncul sesuatu seperti:
   ```
   git version 2.47.0.windows.1
   ```
   ✅ Selamat, Git sudah terpasang!

#### 1.2 Instal Docker Desktop

Docker digunakan untuk menjalankan seluruh lingkungan server (PHP, MySQL, Redis) di dalam kontainer, sehingga kamu **tidak perlu instal PHP, MySQL, dll satu per satu**.

1. Buka browser, pergi ke: **https://www.docker.com/products/docker-desktop/**
2. Klik **Download Docker Desktop** (pilih sesuai OS-mu: Windows/Mac/Linux).
3. Jalankan installer-nya.
4. **Untuk pengguna Windows:**
   - Saat instalasi, pastikan opsi **"Use WSL 2 instead of Hyper-V"** dicentang (direkomendasikan).
   - Jika diminta restart komputer, silakan restart.
   - Setelah restart, buka **Docker Desktop** dari Start Menu.
   - Tunggu hingga Docker Desktop menampilkan status **"Docker Desktop is running"** (ikon paus di taskbar berwarna hijau / tidak ada warning).
5. Untuk memastikan Docker berjalan, buka **Command Prompt** atau **PowerShell**, ketik:
   ```bash
   docker --version
   ```
   Jika berhasil, akan muncul sesuatu seperti:
   ```
   Docker version 27.4.0, build bde2b89
   ```
   Lalu cek juga Docker Compose:
   ```bash
   docker compose version
   ```
   Output contoh:
   ```
   Docker Compose version v2.31.0-desktop.2
   ```
   ✅ Docker sudah siap!

> **⚠️ PENTING (Khusus Windows):** Pastikan fitur **WSL 2 (Windows Subsystem for Linux)** sudah aktif.
> Jika belum, buka PowerShell **sebagai Administrator** lalu jalankan:
> ```powershell
> wsl --install
> ```
> Kemudian restart komputer. Setelah restart, buka Docker Desktop kembali.

---

### Tahap 2 — Clone (Unduh) Proyek dari GitHub

1. Buka **Command Prompt**, **PowerShell**, atau **Git Bash**.
2. Pindah ke folder tempat kamu ingin menyimpan proyek. Contoh:
   ```bash
   cd D:\Projects
   ```
   > 💡 **Tips:** Kamu bisa ganti `D:\Projects` dengan folder mana pun yang kamu mau. Kalau foldernya belum ada, buat dulu:
   > ```bash
   > mkdir D:\Projects
   > cd D:\Projects
   > ```
3. Clone repositori dengan perintah berikut:
   ```bash
   git clone https://github.com/Harinzu47/flc-lms.git
   ```
   Tunggu sampai proses unduh selesai. Outputnya kurang lebih seperti ini:
   ```
   Cloning into 'flc-lms'...
   remote: Enumerating objects: 1234, done.
   remote: Counting objects: 100% (1234/1234), done.
   Receiving objects: 100% (1234/1234), 1.23 MiB | 2.00 MiB/s, done.
   ```
4. Masuk ke folder proyek:
   ```bash
   cd flc-lms
   ```
   ✅ Kode proyek sudah berhasil diunduh!

5. **⚠️ PENTING — Pindah ke Branch `dev`:**

   Proyek ini memiliki **2 branch** (cabang kode):

   | Branch | Keterangan |
   |---|---|
   | `main` | Branch default, tapi **BUKAN yang paling update** |
   | `dev` | Branch pengembangan, **berisi kode terbaru dan paling lengkap** ✅ |

   Saat kamu clone, Git otomatis menempatkanmu di branch `main`. Kamu **harus pindah ke branch `dev`** untuk mendapatkan kode terbaru:

   ```bash
   git checkout dev
   ```

   Outputnya akan seperti ini:
   ```
   Switched to branch 'dev'
   Your branch is up to date with 'origin/dev'.
   ```

   > 💡 **Apa itu branch?**
   > Branch itu ibarat "versi paralel" dari kode. `main` adalah versi stabil/rilis, sedangkan `dev` adalah versi pengembangan yang berisi fitur-fitur terbaru. Bayangkan seperti buku draft (`dev`) dan buku cetakan final (`main`).

   Untuk memastikan kamu sudah berada di branch yang benar, ketik:
   ```bash
   git branch
   ```
   Output:
   ```
   * dev
     main
   ```
   Tanda bintang (`*`) menunjukkan kamu sedang berada di branch `dev`.

   ✅ Kamu sekarang berada di branch terbaru!

---

### Tahap 3 — Siapkan File Konfigurasi (.env)

File `.env` adalah file konfigurasi yang berisi pengaturan aplikasi (database, port, dll).

1. Salin file `.env.example` menjadi `.env`:

   **Windows (Command Prompt):**
   ```cmd
   copy .env.example .env
   ```

   **Windows (PowerShell):**
   ```powershell
   Copy-Item .env.example .env
   ```

   **Mac / Linux / Git Bash:**
   ```bash
   cp .env.example .env
   ```

2. Buka file `.env` yang baru dibuat menggunakan text editor (Notepad, VS Code, atau editor lain), lalu ubah bagian database menjadi seperti ini:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=sail
   DB_PASSWORD=password
   ```

   Dan ubah juga bagian Redis:
   ```env
   REDIS_HOST=redis
   ```

   > 💡 **Penjelasan:** `DB_HOST=mysql` dan `REDIS_HOST=redis` merujuk ke nama layanan Docker (bukan `localhost`), karena aplikasi berjalan di dalam kontainer Docker yang saling terhubung lewat jaringan internal.

3. Simpan file `.env`.

   ✅ Konfigurasi sudah siap!

---

### Tahap 4 — Instal Dependensi PHP (Composer)

Karena kita memakai Docker, kamu **tidak perlu instal PHP dan Composer di komputermu**. Kita akan menggunakan kontainer Docker sementara untuk menginstal dependensi.

Jalankan perintah berikut (satu perintah panjang, copy semuanya):

**Windows (PowerShell):**
```powershell
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php8.2-composer:latest composer install --ignore-platform-reqs
```

**Windows (Command Prompt / cmd):**
```cmd
docker run --rm -v "%cd%:/var/www/html" -w /var/www/html laravelsail/php8.2-composer:latest composer install --ignore-platform-reqs
```

**Mac / Linux / Git Bash:**
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php8.2-composer:latest \
    composer install --ignore-platform-reqs
```

> ⏳ Proses ini membutuhkan waktu beberapa menit karena Docker mengunduh image dan semua paket PHP. Tunggu sampai muncul pesan seperti:
> ```
> Generating optimized autoload files
> > @php artisan package:discover --ansi
> ```

✅ Dependensi PHP berhasil terpasang!

---

### Tahap 5 — Jalankan Docker (Laravel Sail)

Laravel Sail adalah wrapper Docker yang sudah dikonfigurasi khusus untuk proyek ini. Sail akan menjalankan 3 layanan sekaligus: **PHP/Laravel**, **MySQL**, dan **Redis**.

1. Jalankan Sail di mode latar belakang (detached):

   **Windows (PowerShell):**
   ```powershell
   ./vendor/bin/sail up -d
   ```

   **Windows (Command Prompt / cmd):**
   ```cmd
   vendor\bin\sail up -d
   ```

   **Mac / Linux:**
   ```bash
   ./vendor/bin/sail up -d
   ```

   > ⏳ Pertama kali menjalankan ini akan **memerlukan waktu cukup lama** (5–15 menit) karena Docker harus mengunduh dan membangun image PHP, MySQL, dan Redis. Sabar ya!
   >
   > Kamu akan melihat output seperti ini:
   > ```
   > [+] Building ...
   > [+] Running 3/3
   >  ✔ Container flc-lms-redis-1       Started
   >  ✔ Container flc-lms-mysql-1       Started
   >  ✔ Container flc-lms-laravel.test-1 Started
   > ```

2. Pastikan semua kontainer berjalan:
   ```bash
   docker ps
   ```
   Kamu harus melihat 3 kontainer: `laravel.test`, `mysql`, dan `redis`.

   ✅ Server lokal sudah berjalan!

---

### Tahap 6 — Setup Aplikasi Laravel

Sekarang kita akan menjalankan beberapa perintah Laravel di dalam kontainer Docker:

#### 6.1 Generate Application Key
```bash
./vendor/bin/sail artisan key:generate
```
> Ini akan menghasilkan kunci enkripsi unik untuk aplikasimu dan otomatis disimpan ke file `.env`.

#### 6.2 Jalankan Migrasi Database + Data Awal (Seeder)
```bash
./vendor/bin/sail artisan migrate --seed
```
> Perintah ini akan:
> - Membuat semua tabel di database MySQL (`migrate`).
> - Mengisi data contoh seperti kursus, modul, materi, dan akun pengguna (`--seed`).
>
> Tunggu sampai muncul:
> ```
> ✅ FLC UMJ LMS Hierarchical Seeding Complete!
> ```

#### 6.3 Instal Dependensi JavaScript (NPM)
```bash
./vendor/bin/sail npm install
```
> Ini menginstal paket JavaScript yang diperlukan (Tailwind CSS, Vite, dll).

#### 6.4 Jalankan Vite Dev Server (untuk kompilasi CSS & JS)
```bash
./vendor/bin/sail npm run dev
```
> Perintah ini akan menjalankan Vite dev server. **Biarkan terminal ini tetap terbuka** selama kamu mengembangkan/mengakses aplikasi.
>
> Outputnya akan terlihat seperti:
> ```
>   VITE v8.x.x  ready in xxx ms
>
>   ➜  Local:   http://localhost:5173/
>   ➜  press h + enter to show help
> ```

✅ Aplikasi sudah sepenuhnya siap!

---

### Tahap 7 — Buka Aplikasi di Browser

1. Buka browser favoritmu (Chrome, Firefox, Edge, dll).
2. Ketik alamat berikut di address bar:
   ```
   http://localhost
   ```
3. Kamu akan melihat halaman utama FLC UMJ Gamified LMS! 🎉

---

### Tahap 8 — Login dengan Akun Demo

Aplikasi sudah dilengkapi dengan akun demo yang bisa langsung digunakan:

| Role | Email | Password |
|---|---|---|
| **Admin / Dosen** | `admin@lms.local` | `password` |
| **Mahasiswa Berprestasi** | `student@lms.local` | `password` |
| **Mahasiswa 1** | `student1@lms.local` | `password` |
| **Mahasiswa 2** | `student2@lms.local` | `password` |
| **Mahasiswa 3** | `student3@lms.local` | `password` |
| **Mahasiswa 4** | `student4@lms.local` | `password` |
| **Mahasiswa 5** | `student5@lms.local` | `password` |

> 💡 Login sebagai **Admin** untuk mengelola kursus, menilai tugas, dan mengelola badge.
> Login sebagai **Mahasiswa** untuk melihat pengalaman belajar bergamifikasi.

---

## Perintah Sail yang Sering Digunakan

Berikut referensi cepat perintah-perintah yang akan sering kamu pakai:

| Perintah | Fungsi |
|---|---|
| `./vendor/bin/sail up -d` | Menjalankan semua kontainer di latar belakang |
| `./vendor/bin/sail down` | Menghentikan semua kontainer |
| `./vendor/bin/sail artisan migrate` | Menjalankan migrasi database |
| `./vendor/bin/sail artisan migrate:fresh --seed` | Reset database & isi ulang data awal |
| `./vendor/bin/sail artisan key:generate` | Generate application key |
| `./vendor/bin/sail npm install` | Instal paket NPM |
| `./vendor/bin/sail npm run dev` | Jalankan Vite dev server |
| `./vendor/bin/sail npm run build` | Build aset untuk produksi |
| `./vendor/bin/sail artisan tinker` | Buka Laravel REPL (untuk debugging) |
| `./vendor/bin/sail shell` | Masuk ke shell kontainer Laravel |
| `./vendor/bin/sail mysql` | Masuk ke MySQL CLI |

> 💡 **Tips:** Supaya tidak perlu mengetik `./vendor/bin/sail` terus-menerus, kamu bisa membuat alias. Tambahkan baris ini ke file `~/.bashrc` atau `~/.zshrc` (Mac/Linux):
> ```bash
> alias sail='./vendor/bin/sail'
> ```
> Setelah itu cukup ketik `sail up -d`, `sail artisan migrate`, dll.

---

## Troubleshooting (Penyelesaian Masalah Umum)

### ❌ "Port 80 is already in use"
Port 80 sudah dipakai oleh aplikasi lain (misal XAMPP, Apache, IIS, Skype). Solusi:
1. Matikan aplikasi yang memakai port 80, **ATAU**
2. Ubah port di file `.env`:
   ```env
   APP_PORT=8080
   ```
   Lalu akses aplikasi di `http://localhost:8080`.

### ❌ "Port 3306 is already in use"
MySQL lokal sudah berjalan. Solusi:
1. Matikan MySQL lokal (XAMPP, dsb), **ATAU**
2. Ubah port di file `.env`:
   ```env
   FORWARD_DB_PORT=33060
   ```

### ❌ "Docker Desktop is not running"
Pastikan Docker Desktop sudah dibuka dan statusnya **running** (ikon paus di system tray tidak ada tanda seru/warning).

### ❌ "WSL 2 installation is incomplete"
Buka PowerShell **sebagai Administrator**, jalankan:
```powershell
wsl --install
```
Restart komputer, lalu buka Docker Desktop kembali.

### ❌ "sail: command not found" atau "vendor/bin/sail not found"
Artinya langkah Tahap 4 (Instal Dependensi PHP) belum berhasil. Ulangi perintah `docker run ...` di Tahap 4.

### ❌ "SQLSTATE[HY000] [2002] Connection refused"
Database MySQL belum siap. Tunggu 10–30 detik setelah `sail up -d`, lalu coba lagi. Untuk memastikan MySQL sudah berjalan:
```bash
docker ps
```
Pastikan kontainer `mysql` statusnya **healthy**.

### ❌ Tampilan berantakan / CSS tidak muncul
Pastikan Vite dev server berjalan:
```bash
./vendor/bin/sail npm run dev
```
**Jangan tutup terminal ini** selama mengakses aplikasi.

---

## Struktur Folder Proyek

```
flc-lms/
├── app/                  # Kode utama aplikasi (Models, Controllers, Livewire, dll)
├── bootstrap/            # File bootstrap Laravel
├── config/               # File konfigurasi aplikasi
├── database/
│   ├── migrations/       # File migrasi (struktur tabel database)
│   └── seeders/          # File seeder (data awal/contoh)
├── docker/               # Konfigurasi Docker (nginx, php-fpm, dll)
├── docs/                 # Dokumentasi tambahan
├── public/               # File yang bisa diakses publik (index.php, gambar, dll)
├── resources/
│   ├── views/            # Template Blade (tampilan HTML)
│   ├── css/              # File CSS
│   └── js/               # File JavaScript
├── routes/               # Definisi rute URL
├── storage/              # File storage (log, cache, upload)
├── tests/                # Unit & feature tests
├── .env.example          # Contoh file konfigurasi environment
├── compose.yaml          # Konfigurasi Docker Compose (Sail)
├── composer.json         # Daftar dependensi PHP
├── package.json          # Daftar dependensi JavaScript
├── tailwind.config.js    # Konfigurasi Tailwind CSS
└── vite.config.js        # Konfigurasi Vite (bundler aset)
```

---

## Lisensi

Proyek ini dilisensikan di bawah **[MIT License](LICENSE)**.
