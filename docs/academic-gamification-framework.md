# Kerangka Teori Gamifikasi (Panduan Sidang Skripsi)

Dokumen ini disusun untuk membantu Anda menjawab pertanyaan dosen penguji mengenai **metode dan teori ilmiah gamifikasi** yang melandasi perancangan aplikasi **FLC UMJ Gamified LMS**. Teori-teori di bawah ini merupakan standar akademik yang sangat diakui dalam penelitian ilmiah sistem informasi dan edutech.

---

## 1. Kerangka Kerja PBL Triad (Points, Badges, Leaderboards)
* **Teoritikus Utama:** Kevin Werbach & Dan Hunter (2012)
* **Deskripsi:** Dalam bukunya *"For the Win: How Game Thinking Can Revolutionize Your Business"*, gamifikasi dibagi menjadi tiga hierarki elemen: **Dynamics (Dinamika)**, **Mechanics (Mekanika)**, dan **Components (Komponen)**. Aplikasi ini menerapkan komponen inti yang dikenal sebagai **PBL Triad**.

### Pemetaan dalam Kode Program:
* **Points (Poin):** Direpresentasikan sebagai **Experience Points (XP)**. 
  * *Implementasi:* Kolom `total_xp` pada tabel `users` dan pencatatan log transaksi di tabel `xp_logs`. Poin bertindak sebagai umpan balik instan (*immediate feedback*) atas tindakan akademik mahasiswa.
  * *File Bukti:* [AwardMaterialXpAction.php](file:///d:/LMS%20FLC/flc-lms/app/Actions/Gamification/AwardMaterialXpAction.php) (memberikan +10 XP statis) dan [GradeSubmissionAction.php](file:///d:/LMS%20FLC/flc-lms/app/Actions/LMS/GradeSubmissionAction.php) (kalkulasi XP proporsional berdasarkan nilai dosen).
* **Badges (Lencana):** Representasi visual dari pencapaian (*achievements*).
  * *Implementasi:* Tabel `badges` dan pivot `user_badges`. Digunakan untuk mengapresiasi tonggak sejarah belajar (misalnya: menyelesaikan 5 tugas pertama).
* **Leaderboards (Papan Peringkat):** Menunjukkan kedudukan sosial mahasiswa dalam komunitas belajar.
  * *Implementasi:* Halaman **Hall of Fame** yang diatur oleh komponen [HallOfFame.php](file:///d:/LMS%20FLC/flc-lms/app/Livewire/HallOfFame.php). Menggunakan query database teroptimasi untuk mengambil peringkat 50 besar mahasiswa teraktif.

---

## 2. Self-Determination Theory (SDT)
* **Teoritikus Utama:** Edward L. Deci & Richard M. Ryan (1985 / 2000)
* **Deskripsi:** Teori motivasi manusia yang menyatakan bahwa untuk menumbuhkan motivasi intrinsik (motivasi dari dalam diri mahasiswa untuk belajar, bukan sekadar karena terpaksa), sistem harus memenuhi **tiga kebutuhan psikologis dasar manusia**:

```
                  ┌─────────────────────────────────────────┐
                  │       Self-Determination Theory         │
                  └─────────────────────────────────────────┘
                                       │
         ┌─────────────────────────────┼─────────────────────────────┐
         ▼                             ▼                             ▼
   [ Autonomy ]                  [ Competence ]                [ Relatedness ]
  Kebebasan memilih             Perasaan menguasai             Rasa keterhubungan
   (Pilihan Materi)             (XP, Level Progress)           (Leaderboard/Rekan)
```

### Pemetaan dalam Kode Program:
1. **Autonomy (Otonomi):** Kebebasan bagi mahasiswa untuk mengatur jalannya pembelajaran mereka sendiri.
   * *Implementasi:* Keberagaman tipe materi di tabel `materials` (ada tipe `video`, `document`, dan `link`). Mahasiswa dapat memilih materi mana yang ingin mereka pelajari terlebih dahulu secara asinkron.
2. **Competence (Kompetensi):** Kebutuhan untuk merasa efektif dan berprestasi dalam melakukan tugas-tugas.
   * *Implementasi:* Diterapkan lewat **Leveling System** (`levels` dan progress bar di dashboard). Rumus XP proporsional pada tugas:
     $$\text{XP Earned} = \text{round}\left(\frac{\text{Skor}}{100} \times \text{Base XP}\right)$$
     Menunjukkan bahwa semakin tinggi kompetensi mahasiswa dalam mengerjakan tugas, semakin besar pula XP yang mereka dapatkan.
   * *File Bukti:* [User.php:L131-162](file:///d:/LMS%20FLC/flc-lms/app/Models/User.php#L131-162) (kalkulasi presentasi perkembangan menuju level berikutnya untuk menampilkan progress bar kompetensi).
3. **Relatedness (Keterhubungan):** Kebutuhan untuk merasa terhubung dan diakui secara sosial oleh kelompoknya.
   * *Implementasi:* Papan peringkat **Hall of Fame** membuat mahasiswa merasa menjadi bagian dari kelas/angkatan (komunitas FLC UMJ) dan dapat saling melihat pencapaian rekan-rekannya secara transparan.

---

## 3. Octalysis Framework (Analisis 8 Penggerak Core Drives)
* **Teoritikus Utama:** Yu-kai Chou (2015)
* **Deskripsi:** Metode analisis gamifikasi yang membagi motivasi manusia menjadi 8 Core Drives (Penggerak Utama). Sistem LMS ini sangat kuat pada **Left-Brain Gamification** (fokus pada pencapaian dan kepemilikan logika) serta **White-Hat Gamification** (fokus pada motivasi positif yang membuat pengguna merasa berdaya).

### Pemetaan Core Drives dalam Aplikasi:
* **Core Drive 2: Development & Accomplishment (Pengembangan & Pencapaian):** 
  * Ini penggerak utama aplikasi ini. Mahasiswa termotivasi belajar karena ingin melihat level mereka naik (misal dari "Beginner" ke "Intermediate") dan badge terkunci menjadi terbuka.
* **Core Drive 5: Social Influence & Relatedness (Pengaruh Sosial & Keterhubungan):**
  * Dipicu oleh kompetisi sehat di halaman Leaderboard. Mahasiswa melihat posisi temannya dan terdorong untuk mengejar ketertinggalan poin.
* **Core Drive 6: Scarcity & Impatience (Kelangkaan & Ketidaksabaran):**
  * Diterapkan melalui kolom `deadline` di tabel `tasks`. Batas waktu pengiriman tugas memicu dorongan bertindak cepat agar hak mendapatkan XP tugas tersebut tidak hilang (*Loss Avoidance / CD 8*).

---

## 4. Fogg Behavior Model (FBM)
* **Teoritikus Utama:** B.J. Fogg (Stanford Persuasive Technology Lab)
* **Formula:** 
  $$B = MAP$$
  *(Behavior / Perilaku terjadi jika terdapat Motivation, Ability, dan Prompt yang bertemu pada saat yang sama).*

### Penerapan dalam LMS:
1. **Motivation (Motivasi):** Ditingkatkan melalui visualisasi XP instan, toast notification saat materi selesai dibaca, dan grafik peningkatan level.
2. **Ability (Kemampuan):** Memudahkan mahasiswa mengakses materi kuliah secara fleksibel di mana pun melalui TALL Stack yang responsif di browser HP.
3. **Prompt (Pemicu/Trigger):** Dashboard mahasiswa menampilkan widget **Upcoming Tasks** (tugas mendatang terdekat beserta tenggat waktunya) sebagai pemicu agar mahasiswa segera melakukan tindakan pengumpulan tugas.
   * *File Bukti:* [GamifiedDashboard.php:L55-59](file:///d:/LMS%20FLC/flc-lms/app/Livewire/GamifiedDashboard.php#L55-59) yang menarik data tugas terdekat secara dinamis.

---

## Tips Menjawab Pertanyaan Dosen Penguji saat Sidang:

* **Pertanyaan:** *"Apa landasan ilmiah Anda memilih XP, Level, dan Leaderboard?"*
  * **Jawaban:** *"Saya menggunakan kerangka kerja **PBL Triad oleh Kevin Werbach**, di mana Points (XP) berfungsi sebagai umpan balik instan, Badges sebagai penanda pencapaian spesifik, dan Leaderboard sebagai pengukur relasi sosial. Kombinasi ketiganya terbukti secara empiris meningkatkan retensi keterlibatan pengguna dalam sistem e-learning."*
* **Pertanyaan:** *"Bagaimana aplikasi ini memotivasi mahasiswa secara psikologis?"*
  * **Jawaban:** *"Aplikasi ini mengadopsi **Self-Determination Theory oleh Deci & Ryan**. Kami merangsang motivasi intrinsik dengan memenuhi tiga kebutuhan psikologis mahasiswa: **Otonomi** (memilih format materi), **Kompetensi** (adanya umpan balik XP proporsional terhadap nilai tugas serta perkembangan level), dan **Keterhubungan** (melalui visualisasi kedudukan mahasiswa di leaderboard)."*
* **Pertanyaan:** *"Mengapa sistem perhitungan XP tugas dibuat proporsional terhadap nilai, bukan flat?"*
  * **Jawaban:** *"Tujuan utamanya adalah menjaga integritas akademik dan memicu **Core Drive 2 (Accomplishment)** dalam kerangka kerja **Octalysis**. Poin proporsional memotivasi mahasiswa untuk mengerjakan tugas sebaik mungkin demi mendapatkan XP maksimal, bukan sekadar asal mengumpulkan berkas kosong."*
