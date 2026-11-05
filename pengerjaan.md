# PANDUAN LENGKAP & NASKAH VIDEO PRESENTASI
## PRAKTIKUM MINGGU 9: WEB SERVICES, SOAP LEGACY, & RESTFUL WEB API (SIPUS-DEL)
### Mata Kuliah: Pemrograman dan Pengujian Aplikasi Web (12S3101) — Institut Teknologi Del

---

## 📌 I. INFORMASI IDENTITAS & PROYEK
- **Nama Mahasiswa** : Boy Simamora
- **NIM** : 12S24016
- **Kelas** : 12S3101 — Pemrograman dan Pengujian Aplikasi Web (PPW)
- **Program Studi** : Sarjana Sistem Informasi
- **Fakultas** : Fakultas Informatika dan Teknik Elektro (FITE)
- **Dosen Pengampu** : Chandro Pardede, S.Kom., M.Sc.
- **Topik Penugasan** : Arsitektur Web Services Terdistribusi, Implementasi Ringkas SOAP (WSDL & XML Envelope), Perancangan & Implementasi Mendalam RESTful Web API (HATEOAS, RFC 7807, Sanctum Bearer Token, Transaksi Sirkulasi ACID `lockForUpdate`, dan Service Consumer OpenLibrary) pada Laravel 11.x / 13.x
- **Git Branch** : `week9-rest-api`

---

## 📋 II. CHECKLIST KESIAPAN SEBELUM MEREKAM VIDEO (*PRE-RECORDING CHECKLIST*)

Pastikan seluruh poin berikut telah disiapkan di laptop Anda sebelum menekan tombol **Record**:

1. [x] **Basis Data Aktif**: MySQL aktif pada port `3306` (Laragon / service MySQL).
2. [x] **Server Laravel Menyala**: Buka terminal dan jalankan:
   ```bash
   php artisan serve
   ```
   *(Server aktif di `http://localhost:8000`)*.
3. [x] **Aplikasi Postman Siap**:
   - Buka Postman Desktop.
   - Pastikan koleksi `SIPUS-Del Web Services & RESTful API v1 (Minggu 9)` dari file `docs/SIPUS-Del-API-v1.postman_collection.json` sudah diimpor.
   - Pastikan variabel `base_url` bernilai `http://localhost:8000/api/v1` dan `soap_url` bernilai `http://localhost:8000/soap/sipus`.
4. [x] **Editor Kode (VS Code) Siap**:
   - Buka folder proyek `SIPUS-Del` pada branch `week9-rest-api`.
   - Siapkan tab file kunci:
     - `app/Http/Controllers/Soap/LibrarySoapController.php`
     - `app/Http/Controllers/Api/V1/BukuApiController.php`
     - `app/Http/Controllers/Api/V1/PeminjamanApiController.php`
     - `app/Http/Resources/Api/V1/BukuResource.php`
     - `bootstrap/app.php`
     - `docs/openapi-sipus-v1.yaml`
     - `README.md`
5. [x] **Terminal Pengujian (TDD Verification)**:
   - Siapkan terminal untuk mendemonstrasikan eksekusi test suite:
     ```bash
     php vendor/phpunit/phpunit/phpunit
     ```
     *(Memastikan 28 tests, 172 assertions berstatus 100% OK)*.

---

## 🎬 III. RUNDOWN & STRUKTUR DURASI VIDEO (ESTIMASI: 8 - 10 MENIT)

| Segmen | Topik Pembahasan | Estimasi Durasi | Antarmuka Layar yang Ditampilkan |
| :---: | :--- | :---: | :--- |
| **1** | Pembukaan, Salam, & Identitas Mahasiswa | 0:00 - 0:45 | Tampilkan Webcam / Halaman `README.md` (Bagian Identitas) |
| **2** | Latar Belakang Masalah & Feasibility (SOAP vs REST) | 0:45 - 2:00 | Diagram Arsitektur di `README.md` & Matriks Evaluasi |
| **3** | Demo Layanan SOAP Legacy (WSDL & XML Envelope) | 2:00 - 3:15 | Browser (WSDL XML) & Postman (Request SOAP Sukses & Fault) |
| **4** | Demo Otentikasi Stateless Bearer Token Sanctum | 3:15 - 4:15 | Postman (`/auth/login`, `/auth/me`, dan `/auth/logout`) |
| **5** | Demo REST Provider Katalog Buku & Level 3 HATEOAS | 4:15 - 5:30 | Postman (GET Katalog, Filter, Dinamika `_links.pinjam_buku`) |
| **6** | Demo Semantik HTTP: Bukti Nyata PUT vs PATCH | 5:30 - 6:30 | Postman (Eksekusi PATCH stok vs PUT replace seluruh field) |
| **7** | Demo Transaksi Sirkulasi ACID & Concurrency Locking | 6:30 - 8:00 | Postman (Peminjaman sukses, stok berkurang, tolak 422, tolak 409, pengembalian) |
| **8** | Demo REST Service Consumer (OpenLibrary API) | 8:00 - 8:45 | Postman (`GET /bukus/{id}/referensi-eksternal`) & VS Code |
| **9** | Standarisasi Galat RFC 7807 & Kontrak OpenAPI 3.0 | 8:45 - 9:30 | File `bootstrap/app.php` & `docs/openapi-sipus-v1.yaml` |
| **10** | Verifikasi Otomatis TDD (28 Tests) & Penutup | 9:30 - 10:00 | Terminal `phpunit` (28/28 OK) & Salam Penutup |

---

## 🗣️ IV. NASKAH VIDEO KATA DEMI KATA (WORD-FOR-WORD SCRIPT) & PETUNJUK AKSI

---

### SEGMEN 1: PEMBUKAAN & IDENTITAS MAHASISWA (0:00 - 0:45)
**Aksi di Layar:**
> Tampilkan wajah Anda via webcam (jika wajib) atau sorot bagian atas file `README.md` repositori `SIPUS-Del`.

**Naskah Dialog:**
> *"Syalom, selamat pagi/siang/sore kepada Bapak Dosen Pengampu mata kuliah Pemrograman dan Pengujian Aplikasi Web, Bapak Chandro Pardede, S.Kom., M.Sc., serta rekan-rekan asisten dosen dan penonton sekalian.*
>
> *Perkenalkan, nama saya **Boy Simamora** dengan NIM **12S24016** dari Program Studi Sarjana Sistem Informasi, Fakultas Informatika dan Teknik Elektro, Institut Teknologi Del.*
>
> *Pada video kali ini, saya akan mendemonstrasikan secara komprehensif pengerjaan penugasan praktikum **Minggu ke-9 (Web Services & RESTful Web API)** pada proyek lanjutan Sistem Informasi Perpustakaan Kampus Del atau **SIPUS-Del** yang telah diselesaikan pada branch `week9-rest-api`."*

---

### SEGMEN 2: LATAR BELAKANG MASALAH & ANALISIS KELAYAKAN (0:45 - 2:00)
**Aksi di Layar:**
> Arahkan kursor ke Bagian **II. ANALISIS KEBUTUHAN & KELAYAKAN WEB SERVICES** pada `README.md`. Tunjukkan diagram alur sistem dan tabel komparasi SOAP vs REST.

**Naskah Dialog:**
> *"Pertama, mari kita tinjau transformasi arsitektur yang mendasari praktikum ini. Pada Minggu 1 hingga Minggu 6, kita membangun aplikasi monolitik berorientasi manusia, di mana server merender antarmuka visual Blade HTML seberat puluhan kilobyte.*
>
> *Namun di industri modern berlaku prinsip: No Super-App, No Super-Enterprise. Perpustakaan IT Del kini dituntut membuka pertukaran data antar-mesin (Machine-to-Machine) yang ringan, cepat, dan nir-status.*
>
> *Pada proyek ini, SIPUS-Del membagi perannya ke dalam dua arsitektur utama:*
> 1. *Sebagai **REST Service Provider**, menyediakan endpoint nir-status `/api/v1/*` berformat JSON murni untuk aplikasi mobile mahasiswa dan kiosk peminjaman mandiri.*
> 2. *Sebagai **SOAP Service Provider**, menyediakan endpoint simulasi berbasis XML Envelope dan kontrak WSDL untuk melayani integrasi sistem core yudisium kampus yang masih berarsitektur legacy.*
> 3. *Serta sebagai **REST Service Consumer**, di mana server backend kita memanggil OpenLibrary API secara terorkestrasi untuk melengkapi metadata buku internasional.*
>
> *Mengapa REST dipilih untuk mobile sedangkan SOAP untuk yudisium? Format JSON pada REST 5 hingga 8 kali lebih ringkas, hemat kuota data seluler, dan parsing-nya native di JavaScript maupun Flutter. Sementara SOAP dipertahankan semata-mata untuk kompatibilitas ke sistem enterprise lama yang mewajibkan kontrak WSDL."*

---

### SEGMEN 3: DEMO LAYANAN SOAP LEGACY (WSDL & XML ENVELOPE) (2:00 - 3:15)
**Aksi di Layar:**
> 1. Buka browser, akses URL: `http://localhost:8000/soap/sipus.wsdl`. Tunjukkan struktur pohon XML kontrak WSDL.
> 2. Pindah ke Postman, buka folder `01. SOAP Legacy Web Services` -> request `01.2 Cek Bebas Pustaka - Sukses`. Klik **Send**.
> 3. Buka request `01.3 Cek Bebas Pustaka - Fault NIM Kosong`. Klik **Send**.

**Naskah Dialog:**
> *"Sekarang mari kita uji implementasi layanan SOAP pada Lab 1 dan Tugas Mandiri.*
>
> *Di peramban web, saya mengakses `http://localhost:8000/soap/sipus.wsdl`. Dapat kita amati, server menyajikan dokumen kontrak formal Web Services Description Language yang mendeskripsikan elemen message, portType, binding SOAP over HTTP, dan service location secara baku.*
>
> *Selanjutnya, di Postman, saya mengirimkan HTTP POST ke `/soap/sipus` dengan Content-Type `text/xml`. Payload dibungkus dalam tag `<soap:Envelope>` yang memuat permintaan `<m:CheckClearanceStatusRequest>` untuk NIM mahasiswa `12S24016`.*
>
> *(Klik Send)*
>
> *Server merespons status 200 OK dengan amplop balasan resmi. Di dalam `<soap:Body>`, tag `<m:active_loans>` bernilai 0 dan `<m:is_cleared>` bernilai `true`, menandakan mahasiswa ini bebas pustaka.*
>
> *Bagaimana jika klien mengirimkan XML cacat atau tag NIM kosong? Mari kita uji skenario `01.3`.*
>
> *(Klik Send)*
>
> *Server merespons status 500 dengan blok baku `<soap:Fault>`, memuat `<faultcode>soap:Client</faultcode>` dan pesan kesalahan yang jelas. Rute ini juga telah dikecualikan secara aman dari verifikasi CSRF di `bootstrap/app.php`."*

---

### SEGMEN 4: DEMO OTENTIKASI STATELESS BEARER TOKEN (SANCTUM) (3:15 - 4:15)
**Aksi di Layar:**
> 1. Pindah ke folder Postman `02. Autentikasi Stateless Bearer Token`.
> 2. Buka `02.1 Login Pustakawan (POST)`. Tunjukkan body JSON: `pustakawan@del.ac.id` / `password123`. Klik **Send**.
> 3. Tunjukkan tab Response: Status 200 OK, perlihatkan field `data.access_token`.
> 4. Buka `02.2 Profil Pustakawan Me (GET)`. Tunjukkan Authorization: Bearer `{{token}}`. Klik **Send**.
> 5. Buka `02.3 Logout & Token Revocation (POST)`. Klik **Send**. Lalu coba kirim ulang `02.2` untuk membuktikan penolakan 401.

**Naskah Dialog:**
> *"Sekarang kita beralih ke arsitektur RESTful API modern pada rute `/api/v1/*`.*
>
> *Sesuai batasan arsitektur Roy Fielding nomor 2, REST wajib bersifat **Stateless (Nir-Status)**. Server tidak boleh mengingat sesi login di memori atau file session server.*
>
> *Di Postman, saya mengeksekusi request `POST /api/v1/auth/login` menggunakan kredensial pustakawan uji: `pustakawan@del.ac.id` dan `password123`.*
>
> *(Klik Send)*
>
> *Status 200 OK. Server menerbitkan Stateless Bearer Token Sanctum. Skrip uji Postman otomatis menyimpan token ini ke variabel koleksi `{{token}}`.*
>
> *Ketika saya memanggil endpoint terproteksi `GET /api/v1/auth/me` dengan header `Authorization: Bearer <token>`, server mengenali identitas pustakawan dan mengembalikan profil akun.*
>
> *Ketika sesi selesai, saya memanggil `POST /api/v1/auth/logout`. Token dicabut dan dihapus dari database. Jika saya mencoba memanggil `/auth/me` kembali...*
>
> *(Klik Send di /auth/me)*
>
> *Permintaan langsung ditolak dengan status **401 Unauthorized** dalam struktur standar IETF RFC 7807!"*

---

### SEGMEN 5: DEMO REST PROVIDER KATALOG BUKU & LEVEL 3 HATEOAS (4:15 - 5:30)
**Aksi di Layar:**
> 1. Buka folder `03. REST Provider Katalog Buku` -> request `03.1 GET Koleksi Buku Terpaginasi & Filter`. Klik **Send**.
> 2. Sorot bagian `meta` (paginasi).
> 3. Sorot blok `_links` pada buku yang stoknya > 0 (tunjukkan ada `pinjam_buku`).
> 4. Sorot buku yang stoknya 0 (tunjukkan `pinjam_buku` tidak ada).
> 5. Tunjukkan query params `?tersedia=1&search=...&sort=judul`. Klik **Send**.

**Naskah Dialog:**
> *"Berikutnya, pada Lab 3 dan Tugas Mandiri, kita membangun REST Provider untuk Katalog Buku.*
>
> *Pada request `GET /api/v1/bukus`, sistem mengembalikan data katalog terpaginasi. Di bagian bawah terdapat metadata lengkap: `current_page`, `total_items`, serta tautan navigasi `first`, `next`, dan `last`.*
>
> *Yang paling istimewa adalah penerapan **Richardson Maturity Model Level 3, yaitu HATEOAS (Hypermedia As The Engine Of Application State)**.*
>
> *Perhatikan objek buku dengan ID 1 yang stoknya tersedia: di dalam blok `_links`, terdapat aksi `pinjam_buku` lengkap dengan metode HTTP dan URL tujuannya.*
>
> *Namun, perhatikan buku dengan stok 0: tautan `pinjam_buku` **otomatis disembunyikan** oleh `BukuResource`! Ini membuktikan konsep HATEOAS di mana state dari server yang mengarahkan tombol apa saja yang boleh tampil di sisi aplikasi Mobile pengguna.*
>
> *Kueri ini juga sepenuhnya bebas dari masalah **N+1 Query Problem** karena di controller kita menggunakan `Buku::with('kategori')->withCount('peminjamans')`."*

---

### SEGMEN 6: DEMO SEMANTIK HTTP: BUKTI NYATA PUT VS PATCH (5:30 - 6:30)
**Aksi di Layar:**
> 1. Buka request `03.4 PATCH Update Sebagian Atribut (Stok Saja)`.
>    Tunjukkan body: `{"stok": 15}`. Klik **Send**.
>    Tunjukkan balasan: Atribut `stok` berubah jadi 15, sementara `judul`, `penulis`, `sinopsis` tetap aman tidak terhapus.
> 2. Buka request `03.5 PUT Ganti Seluruh Representasi Buku`.
>    Tunjukkan body: Seluruh atribut dikirim lengkap KECUALI `sinopsis`. Klik **Send**.
>    Tunjukkan balasan: Seluruh representasi diganti, dan kolom `sinopsis` direset menjadi `null`.

**Naskah Dialog:**
> *"Di industri perangkat lunak, perbedaan semantik antara **PUT** dan **PATCH** sering diuji saat wawancara kerja. Mari kita buktikan implementasinya pada SIPUS-Del.*
>
> *Pertama, saya menggunakan metode **PATCH** pada `/api/v1/bukus/{id}`. Sesuai semantik RFC, PATCH adalah modifikasi parsial. Saya hanya mengirimkan `{"stok": 15}`.*
>
> *(Klik Send)*
>
> *Dapat kita lihat, stok berhasil diperbarui menjadi 15, sedangkan judul, penulis, dan sinopsis buku tetap aman dan utuh di database!*
>
> *Kedua, saya menggunakan metode **PUT**. Sesuai standar, PUT adalah **Full Replacement** atau penggantian seluruh representasi sumber daya. Klien wajib mengirimkan seluruh field secara lengkap.*
>
> *(Tunjukkan body tanpa field sinopsis, lalu klik Send)*
>
> *Perhatikan hasil responnya: seluruh representasi buku digantikan, dan karena atribut `sinopsis` tidak disertakan di dalam payload body PUT, nilainya direset menjadi `null` di basis data! Ini membuktikan perbedaan konkret antara PUT dan PATCH secara semantik."*

---

### SEGMEN 7: DEMO TRANSAKSI SIRKULASI ACID & CONCURRENCY LOCKING (6:30 - 8:00)
**Aksi di Layar:**
> 1. Buka folder `04. REST Sirkulasi Peminjaman ACID`.
> 2. Buka `04.2 POST Peminjaman Buku ACID`. Masukkan `buku_id: 1`, `nim: 12S24016`. Klik **Send**.
>    - Tunjukkan status `201 Created` + Header `Location: /api/v1/peminjamans/{id}`.
> 3. Klik **Send** sekali lagi dengan data yang sama persis untuk menguji penolakan duplikasi.
>    - Tunjukkan status `409 Conflict` bertajuk `Duplicate Loan Conflict`.
> 4. Coba pinjam buku yang stoknya 0.
>    - Tunjukkan penolakan `422 Unprocessable Content` bertajuk `Book Out of Stock`.
> 5. Buka `04.3 PATCH Pengembalian Buku & Pulihkan Stok`. Klik **Send**.
>    - Tunjukkan status `200 OK`, `tanggal_kembali` terisi, stok buku bertambah kembali (+1).
> 6. Klik **Send** sekali lagi pada pengembalian yang sama:
>    - Tunjukkan penolakan `409 Conflict` bertajuk `Conflict — Book Already Returned`.

**Naskah Dialog:**
> *"Kini kita masuk ke inti transaksi bisnis pada Lab 4 dan Tugas Mandiri: Sirkulasi Peminjaman Buku berprinsip **ACID dengan Pessimistic Concurrency Locking**.*
>
> *Di Postman request `POST /api/v1/peminjamans`, mahasiswa dengan NIM `12S24016` meminjam buku ID 1.*
>
> *(Klik Send)*
>
> *Status menunjukkan **201 Created**, dan di tab Headers Response disertakan header standar **Location** menuju ID peminjaman baru.*
>
> *Di balik layar, proses ini dibungkus dalam `DB::transaction()` dengan mengunci baris buku secara eksklusif menggunakan `Buku::where('id', $bukuId)->lockForUpdate()->first()`. Stok buku dipotong 1 secara atomik sehingga mustahil terjadi Race Condition.*
>
> *Sekarang, apa yang terjadi jika mahasiswa yang sama mencoba meminjam buku yang sama sebelum mengembalikannya? Saya klik Send kembali.*
>
> *(Klik Send)*
>
> *Server langsung menolak dengan status semantik **409 Conflict** bertajuk `Duplicate Loan Conflict`!*
>
> *Begitu juga jika stok buku sudah habis (0), server menolak dengan status **422 Unprocessable Content** tanpa membuat stok menjadi negatif.*
>
> *Terakhir, untuk pengembalian buku, saya memanggil `PATCH /api/v1/peminjamans/{id}/kembalikan`.*
>
> *(Klik Send)*
>
> *Status 200 OK. Di dalam transaksi ACID, status peminjaman diubah menjadi 'dikembalikan', tanggal kembali dicatat, dan stok buku dipulihkan kembali bertambah 1.*
>
> *Jika tombol Send diklik ulang, sistem menolak pengembalian ganda dengan status **409 Conflict**."*

---

### SEGMEN 8: DEMO REST SERVICE CONSUMER (OPENLIBRARY API) (8:00 - 8:45)
**Aksi di Layar:**
> 1. Buka folder `05. REST Service Consumer Eksternal` -> request `05.1 GET Pengayaan Metadata OpenLibrary`. Klik **Send**.
> 2. Tampilkan payload respons JSON yang memuat `number_of_pages`, `publishers`, `openlibrary_url`.
> 3. Tunjukkan potongan kode di `BukuApiController.php` method `referensiEksternal`.

**Naskah Dialog:**
> *"Selain bertindak sebagai Provider, sistem SIPUS-Del juga bertindak sebagai **REST Service Consumer** pada endpoint `GET /api/v1/bukus/1/referensi-eksternal`.*
>
> *(Klik Send)*
>
> *Dapat kita lihat, server Laravel kita secara transparan melakukan HTTP Request ke server publik OpenLibrary API menggunakan fasad `Illuminate\Support\Facades\Http` dengan batas waktu `timeout(8)` detik.*
>
> *Server mengekstrak jumlah halaman resmi, penerbit, dan tautan cover buku dari internet lalu mengembalikannya ke klien dengan orkestrasi yang rapi, lengkap dengan penanganan fallback 404 jika buku tidak ditemukan di internet atau 502 Bad Gateway jika server OpenLibrary mengalami gangguan."*

---

### SEGMEN 9: STANDARISASI GALAT IETF RFC 7807 & KONTRAK OPENAPI 3.0 (8:45 - 9:30)
**Aksi di Layar:**
> 1. Buka file `bootstrap/app.php` di VS Code, sorot blok `withExceptions`.
> 2. Buka file `docs/openapi-sipus-v1.yaml` di VS Code.

**Naskah Dialog:**
> *"Seluruh respons galat pada kanal `/api/*` dijamin 100% konsisten berstandar **IETF RFC 7807 Problem Details**.*
>
> *Pada `bootstrap/app.php`, kita telah mengonfigurasi global exception handler sehingga error validasi 422, error token 401, error 404, dan error integritas 409 selalu mereturn media type `application/problem+json` dengan struktur seragam: `type`, `title`, `status`, `detail`, dan `instance`.*
>
> *Dan untuk kontrak layanan resmi REST API v1, berkas spesifikasi **OpenAPI 3.0.3** telah tersedia pada file `docs/openapi-sipus-v1.yaml` yang dapat langsung diimpor ke Swagger Editor untuk integrasi antartim pengembang."*

---

### SEGMEN 10: VERIFIKASI TDD (28 TESTS PASS) & PENUTUP (9:30 - 10:00)
**Aksi di Layar:**
> 1. Buka terminal VS Code.
> 2. Ketikkan perintah:
>    ```bash
>    php vendor/phpunit/phpunit/phpunit
>    ```
> 3. Tunjukkan luaran hijau: `OK (28 tests, 172 assertions)`.
> 4. Sorot log commit git:
>    ```bash
>    git log --oneline -n 7
>    ```

**Naskah Dialog:**
> *"Untuk memastikan seluruh kode memenuhi standar rekayasa perangkat lunak yang tangguh, saya menjalankan seluruh suite pengujian otomatis:*
>
> *(Jalankan phpunit)*
>
> *Hasilnya: **28 test cases dengan 172 assertions dinyatakan 100% PASS**.*
>
> *Riwayat commit git pada branch `week9-rest-api` juga tersusun bertahap mengikuti konvensi commit modul tanpa membocorkan file `.env` atau token rahasia.*
>
> *Demikian presentasi dan demonstrasi praktikum Minggu ke-9 Pemrograman dan Pengujian Aplikasi Web dari saya, **Boy Simamora (NIM 12S24016)**. Mohon maaf apabila terdapat kekurangan dalam penyampaian. Terima kasih banyak, dan Syalom."*

---

## 💡 V. TIPS TAMBAHAN AGAR REKAMAN MAKSIMAL
1. **Pengaturan Resolusi Layar**: Gunakan resolusi 1080p (1920x1080) dengan skala teks 100% atau 125% agar teks Postman dan kode di VS Code terbaca jelas oleh dosen.
2. **Postman Environment**: Karena skrip pada request login otomatis mengisi variabel `{{token}}`, Anda tidak perlu lagi melakukan copy-paste token secara manual selama video berlangsung.
3. **Penyensoran Token**: Saat login, token Bearer yang muncul tidak masalah diperlihatkan karena merupakan token akun seeder uji lokal (`pustakawan@del.ac.id`), bukan token akun cloud privat.
