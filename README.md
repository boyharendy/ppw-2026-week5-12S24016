# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)

[![Laravel Version](https://img.shields.io/badge/Laravel-13.x%20%2F%2011.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![REST API](https://img.shields.io/badge/RESTful-Level%203%20HATEOAS-0052CC?style=for-the-badge&logo=fastapi&logoColor=white)](https://restfulapi.net)
[![SOAP Support](https://img.shields.io/badge/SOAP-WSDL%20%26%20Envelope-orange?style=for-the-badge)](https://www.w3.org/TR/soap/)
[![OpenAPI 3.0](https://img.shields.io/badge/OpenAPI-3.0.3%20Spec-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)](https://swagger.io)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum%20Bearer%20Token-red?style=for-the-badge)](https://laravel.com/docs/sanctum)

Aplikasi web terintegrasi Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del) yang melayani antarmuka web monolitik (*server-side rendering* Blade), layanan terdistribusi antar-mesin (**Machine-to-Machine / M2M**), simulasi ringkas protokol **SOAP Legacy (WSDL & XML Envelope)** untuk administrasi yudisium, serta implementasi mendalam **RESTful Web API v1 (Richardson Maturity Model Level 3 HATEOAS, RFC 7807 Problem Details, Transaksi Sirkulasi ACID, dan Stateless Bearer Token)** pada **Laravel 11.x / 13.x**.

Repositori ini merupakan implementasi penugasan lanjutan praktikum **Minggu ke-9** untuk mata kuliah **Pemrograman dan Pengujian Aplikasi Web (12S3101)** pada branch `week9-rest-api`.

---

## I. IDENTITAS MAHASISWA

- **Nama Lengkap** : Boy Simamora
- **NIM** : 12S24016
- **Kelas** : 12S3101 — Pemrograman dan Pengujian Aplikasi Web (PPW)
- **Program Studi** : Sarjana Sistem Informasi
- **Fakultas** : Fakultas Informatika dan Teknik Elektro (FITE)
- **Institusi** : Institut Teknologi Del, Laguboti
- **Dosen Pengampu** : Chandro Pardede, S.Kom., M.Sc.

---

## II. ANALISIS KEBUTUHAN & KELAYAKAN (FEASIBILITY) WEB SERVICES

### 2.1. Transformasi Paradigma: Web Monolitik (W1–W6) ke Komunikasi Antar-Mesin (W9)

Pada paruh awal perkuliahan (Minggu 1 s.d. Minggu 6), SIPUS-Del dibangun sebagai sistem web monolitik berbasis *Server-Side Rendering* (SSR). Ketika pengguna membuka browser, server Laravel mengeksekusi kueri Eloquent dan mengembalikan seluruh dokumen visual HTML, CSS, dan JavaScript seberat puluhan hingga ratusan kilobyte. 

Namun, seiring ekspansi layanan kampus IT Del ("No Super-Enterprise, No Super-App"), sistem perpustakaan dituntut untuk terintegrasi secara otomatis dengan sistem lain tanpa keterlibatan peramban manusia:
1. **Aplikasi Mobile Mahasiswa (Android/iOS)** membutuhkan pertukaran data murni berukuran kecil (<2 KB) tanpa tag tampilan HTML.
2. **Kiosk Peminjaman Mandiri** di lobi perpustakaan membutuhkan otentikasi nir-status (*stateless*) yang tahan terhadap konkurensi tinggi.
3. **Sistem Yudisium Kelulusan Kampus (Core System Legacy)** membutuhkan verifikasi bebas pustaka melalui protokol SOAP berbasis kontrak XML WSDL.
4. **Pengayaan Metadata Buku Global** memanfaatkan katalog buku OpenLibrary API melalui pemanggilan *Service Consumer*.

### 2.2. Matriks Dua Peran Ekosistem Layanan pada SIPUS-Del

```
+----------------------------------------------------------------------------------------------------+
|                                ARSITEKTUR INTEGRASI LAYANAN SIPUS-DEL                              |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
|    A. SEBAGAI SERVICE PROVIDER (Menyediakan Endpoint untuk Pihak Luar):                            |
|       1. SOAP Provider: Sistem Administrasi Yudisium ──(POST /soap/sipus)──► [SIPUS-Del SOAP Engine]
|       2. REST Provider: Mobile App Mahasiswa / Kiosk ─(Bearer Token + JSON)─► [/api/v1/bukus & loans]
|                                                                                                    |
|    B. SEBAGAI SERVICE CONSUMER (Mengonsumsi Layanan Pihak Lain):                                   |
|       [SIPUS-Del Backend] ──(GET https://openlibrary.org/api/books?bibkeys=...)──► [OpenLibrary API] |
|                                                                                                    |
+----------------------------------------------------------------------------------------------------+
```

| Peran Arsitektur | Aktor Konsumen / Penyedia | Protokol & Endpoint | Alasan Pemilihan & Kelayakan Teknis |
| :--- | :--- | :--- | :--- |
| **Service Provider (REST API v1)** | Aplikasi Mobile Mahasiswa, Web SPA Frontend, Kiosk Mandiri | REST over HTTPS (`/api/v1/*`), JSON, Bearer Token | Format data JSON sangat ringan (hemat bandwidth seluler), didukung secara *native* oleh Flutter/Kotlin/React, dan bebas dari overhead parsing tag XML. |
| **Service Provider (SOAP Legacy)** | Sistem Core Administrasi Yudisium IT Del | SOAP 1.1 over HTTP (`/soap/sipus`), XML Envelope, WSDL | Menyesuaikan sistem enterprise yudisium warisan (*legacy*) yang hanya mendukung pemanggilan RPC berbasis WSDL tanpa merombak sistem yudisium tersebut. |
| **Service Consumer (REST Client)** | Penyedia Katalog Buku Global (OpenLibrary / Internet Archive) | REST over HTTPS (`https://openlibrary.org/api/books`), JSON | Data metadata buku global (jumlah halaman, sinopsis resmi, cover) berada di luar domain lokal IT Del; jauh lebih efisien mengonsumsinya via HTTP Client dibanding mengetik manual ribuan data buku. |

### 2.3. Komparasi Ringkas & Analisis Kelayakan: SOAP vs REST

| Aspek Evaluasi | Layanan SOAP (`/soap/sipus`) | Layanan REST API (`/api/v1/*`) | Alasan Kelayakan pada Kasus SIPUS-Del |
| :--- | :--- | :--- | :--- |
| **Bentuk Formal** | Protokol komunikasi formal kaku (W3C Standard) | Gaya/pola arsitektural fleksibel (Roy Fielding) | REST memberikan fleksibilitas evolusi endpoint versi 1 (`/v1`) tanpa merusak klien lama. |
| **Format Payload** | **Wajib XML** (`<soap:Envelope>`) | **Format Terbuka (Dominan JSON)** | JSON menghasilkan ukuran dokumen 5–8x lebih kecil dibanding XML SOAP, krusial bagi aplikasi mobile. |
| **Kontrak Layanan** | File kontrak XML WSDL (`.wsdl`) wajib ada | Spesifikasi OpenAPI 3.0 (`openapi.yaml`) | OpenAPI 3.0 mempermudah kolaborasi tim Frontend & Mobile melalui dokumentasi interaktif Swagger. |
| **Metode HTTP** | Hanya memakai metode **HTTP POST** (*Tunneling*) | Memanfaatkan penuh **GET, POST, PUT, PATCH, DELETE** | REST mematuhi semantik protokol HTTP apa adanya (Safe & Idempotent). |
| **Manajemen Status** | Umumnya Stateless (bisa WS-Session) | **Wajib Stateless (Stateless Bearer Token)** | Server backend *Zero Session*; siap di-*scale horizontally* di balik Load Balancer tanpa *session stickiness*. |
| **Overhead CPU & Jaringan** | Tinggi (parsing DOM/XPath pohon XML besar) | Sangat Ringan (parsing *native native string* JSON) | Mobile phone memiliki keterbatasan daya baterai dan CPU; format JSON jauh lebih ramah sumber daya. |

---

## III. TABEL KATALOG ENDPOINT WEB SERVICES (SOAP & REST API V1)

Seluruh endpoint REST API berjalan di bawah awalan rute `/api/v1` dan merespons dalam format `application/json` (atau `application/problem+json` berstandar IETF RFC 7807 saat terjadi kegagalan):

| No | Metode | URI Endpoint | Proteksi Autentikasi | Deskripsi Bisnis | Status Respons Sukses | Status Respons Galat |
| :---: | :---: | :--- | :--- | :--- | :---: | :---: |
| **A** | **SOAP** | **Layanan Bebas Pustaka Yudisium (Legacy SOAP)** | | | | |
| 1 | `GET` | `/soap/sipus.wsdl` | Publik | Menyajikan dokumen kontrak XML WSDL untuk operasi `CheckClearanceStatus` | `200 OK` (XML) | `500 Internal Error` |
| 2 | `POST` | `/soap/sipus` | Publik (Exempt CSRF) | Menerima `<soap:Envelope>` berisi `<nim_peminjam>`, cek pinjaman aktif, return status `<is_cleared>` | `200 OK` (XML Envelope) | `500` (`<soap:Fault>`) |
| **B** | **AUTH** | **Otentikasi Stateless Token (Sanctum)** | | | | |
| 3 | `POST` | `/api/v1/auth/login` | Publik | Verifikasi email & password pustakawan, terbitkan Stateless Sanctum Bearer Token | `200 OK` | `401`, `422` |
| 4 | `GET` | `/api/v1/auth/me` | `auth:sanctum` | Mengambil data profil pustakawan pemilik token aktif | `200 OK` | `401 Unauthorized` |
| 5 | `POST` | `/api/v1/auth/logout` | `auth:sanctum` | Mencabut (*revoke*) token aktif dari database (mengakhiri sesi API) | `200 OK` | `401 Unauthorized` |
| **C** | **BUKU** | **Katalog Buku & Hypermedia HATEOAS** | | | | |
| 6 | `GET` | `/api/v1/bukus` | Publik | Katalog buku terpaginasi (filter `search`, `kategori_id`, `tersedia`, sort `sort`/`dir`) | `200 OK` | `400 Bad Request` |
| 7 | `GET` | `/api/v1/bukus/{id}` | Publik | Detail satu buku berdasarkan ID atau nomor ISBN beserta relasi kategori dan HATEOAS | `200 OK` | `404 Not Found` |
| 8 | `POST` | `/api/v1/bukus` | `auth:sanctum` | Menambah buku baru ke katalog (mengembalikan Header `Location: /api/v1/bukus/{id}`) | `201 Created` | `401`, `422` |
| 9 | `PUT` | `/api/v1/bukus/{id}` | `auth:sanctum` | Mengganti seluruh representasi buku (*Full Replacement* — seluruh field wajib dikirim) | `200 OK` | `401`, `404`, `422` |
| 10 | `PATCH` | `/api/v1/bukus/{id}` | `auth:sanctum` | Mengubah sebagian atribut buku (*Partial Update* — misal hanya memperbarui stok) | `200 OK` | `400`, `401`, `404`, `422` |
| 11 | `DELETE`| `/api/v1/bukus/{id}` | `auth:sanctum` | Menghapus buku (ditolak `409 Conflict` jika buku memiliki pinjaman aktif 'dipinjam') | `204 No Content` | `401`, `404`, `409 Conflict` |
| 12 | `GET` | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | **Service Consumer:** Mengambil pengayaan metadata buku via OpenLibrary API | `200 OK` | `404`, `502 Bad Gateway` |
| **D** | **PINJAM**| **Sirkulasi Peminjaman ACID & Concurrency** | | | | |
| 13 | `GET` | `/api/v1/peminjamans` | `auth:sanctum` | Menampilkan riwayat peminjaman buku terpaginasi (filter `status`, `nim`, `buku_id`) | `200 OK` | `401 Unauthorized` |
| 14 | `GET` | `/api/v1/peminjamans/{id}`| `auth:sanctum` | Detail satu transaksi sirkulasi peminjaman buku | `200 OK` | `401`, `404 Not Found` |
| 15 | `POST` | `/api/v1/peminjamans` | `auth:sanctum` | **Transaksi ACID:** Catat pinjaman baru, kurangi stok (-1) dengan `lockForUpdate()` | `201 Created` | `401`, `409`, `422` |
| 16 | `PATCH` | `/api/v1/peminjamans/{id}/kembalikan` | `auth:sanctum` | **Transaksi ACID:** Kembalikan buku, pulihkan stok (+1) dengan `lockForUpdate()` | `200 OK` | `401`, `404`, `409 Conflict` |

---

## IV. PANDUAN INSTALASI & PENGUJIAN CEPAT

### 4.1. Kredensial Akun Uji Pustakawan Baku

| Peran Pengguna | Alamat Email | Kata Sandi | Kegunaan Pengujian |
| :--- | :--- | :--- | :--- |
| **Pustakawan Del** | `pustakawan@del.ac.id` | `password123` | Login API (`POST /api/v1/auth/login`) untuk mendapatkan Bearer Token |

### 4.2. Langkah Menjalankan Aplikasi di Komputer Lokal

1. **Pastikan Basis Data MySQL Aktif pada Port 3306:**
   Pastikan Laragon atau service MySQL lokal menyala pada port `3306`.
2. **Checkout Branch Minggu 9:**
   ```bash
   git checkout week9-rest-api
   ```
3. **Instalasi Dependensi:**
   ```bash
   composer install
   npm install
   ```
4. **Migrasi dan Eksekusi Seeder Baku:**
   ```bash
   php artisan migrate:fresh --seed
   ```
5. **Menjalankan Server Web Lokal:**
   ```bash
   php artisan serve
   ```
   Server REST API dan antarmuka web aktif pada `http://localhost:8000` (atau `http://127.0.0.1:8000`).

### 4.3. Menjalankan Suite Pengujian Otomatis (TDD Verification)

Seluruh logika bisnis dilindungi oleh 28 automated test case:
```bash
php vendor/phpunit/phpunit/phpunit
```
*Hasil: 28 tests, 172 assertions, 100% PASS.*

### 4.4. Panduan Impor Koleksi Postman

1. Buka aplikasi **Postman Desktop**.
2. Klik tombol **Import** di sudut kiri atas.
3. Arahkan dan pilih file koleksi yang tersedia di repositori ini:  
   📂 `docs/SIPUS-Del-API-v1.postman_collection.json`
4. Koleksi telah dilengkapi variabel bawaan:
   - `{{base_url}}` = `http://localhost:8000/api/v1`
   - `{{soap_url}}` = `http://localhost:8000/soap/sipus`
   - `{{token}}` = Otomatis terisi dan tersimpan ke *collection variable* saat mengeksekusi request `02.1 Login Pustakawan`!

---

## V. SPESIFIKASI KONTRAK OPENAPI 3.0

Dokumen Service Contract resmi pengganti WSDL pada arsitektur RESTful API tersedia dalam format baku OpenAPI 3.0.3 pada berkas:
📄 `docs/openapi-sipus-v1.yaml`

Anda dapat menginspeksi atau menyalin seluruh isi berkas tersebut ke editor visual resmi [Swagger Editor](https://editor.swagger.io) untuk melihat dokumentasi interaktif dan mencoba pemanggilan endpoint secara langsung.

---

## VI. GALERI BUKTI PENGUJIAN SISTEMATIS (7 SKENARIO WAJIB POIN 5.3.5)

Berikut adalah ringkasan hasil pengujian komprehensif pada masing-masing dari 7 skenario wajib praktikum Minggu 9:

### 1. Skenario (a): Uji SOAP XML Bebas Pustaka Yudisium & Penanganan `<soap:Fault>`
- **Uji Sukses (200 OK):** Mengirim `<soap:Envelope>` ke `POST /soap/sipus` dengan NIM mahasiswa `12S24016`. Server merespons dokumen XML `<m:CheckClearanceStatusResponse>` yang memuat tag `<m:active_loans>0</m:active_loans>` dan `<m:is_cleared>true</m:is_cleared>`.
- **Uji Galat Fault (500 Error):** Mengirim tag `<nim_peminjam>` kosong atau XML cacat. Server merespons amplop `<soap:Fault>` berisi `<faultcode>soap:Client</faultcode>` dan pesan kesalahan deskriptif.

### 2. Skenario (b): Otentikasi Stateless Bearer Token (Sanctum)
- **POST `/api/v1/auth/login` (200 OK):** Mengirim kredensial pustakawan (`pustakawan@del.ac.id` / `password123`). Server mengembalikan JSON amplop dengan atribut `data.access_token` berawalan format Sanctum (misal: `1|...`).
- **GET `/api/v1/auth/me` (200 OK vs 401 Unauthorized):** Permintaan dengan header `Authorization: Bearer <token>` berhasil menampilkan data identitas pustakawan. Tanpa token atau token salah, server menolak dengan status `401 Unauthorized` dalam struktur IETF RFC 7807 (`type`, `title`, `status`, `detail`).
- **POST `/api/v1/auth/logout` (200 OK):** Token aktif dicabut dari database; pemanggilan berikutnya dengan token yang sama langsung ditolak `401`.

### 3. Skenario (c): GET Katalog Terpaginasi & Kontrol Hypermedia HATEOAS
- **Paginasi & Filter Bebas N+1:** Endpoint `GET /api/v1/bukus?per_page=5&tersedia=1` menyajikan 5 data buku beserta metadata paginasi (`current_page`, `total_items`, `total_pages`, tautan `first`/`next`/`prev`/`last`). Kueri dieksekusi efisien melalui `with('kategori')->withCount('peminjamans')`.
- **HATEOAS Level 3 Dinamis:**
  - Pada buku dengan `stok > 0`: Blok `_links` menyertakan aksi `pinjam_buku` (`POST /api/v1/peminjamans`).
  - Pada buku dengan `stok === 0`: Tautan aksi `pinjam_buku` **otomatis disembunyikan** dari respons JSON sehingga klien mobile tidak perlu menduplikasi validasi backend.

### 4. Skenario (d): POST Buku Baru (201 Created + Header Location)
- **POST `/api/v1/bukus`:** Mengirim representasi buku baru lengkap dengan header `Authorization: Bearer <token>`.
- **Header Location:** Server merespons kode status `201 Created` dan menyematkan header standar `Location: http://localhost:8000/api/v1/bukus/{id_baru}`.

### 5. Skenario (e): Bukti Nyata Perbedaan Semantik PUT vs PATCH
- **PATCH `/api/v1/bukus/{id}` (Partial Update):** Klien hanya mengirimkan payload ringkas `{"stok": 15}`. Atribut lainnya (`judul`, `penulis`, `sinopsis`) tidak tersentuh dan tetap utuh di basis data.
- **PUT `/api/v1/bukus/{id}` (Full Replacement):** Klien wajib mengirimkan seluruh atribut representasi buku. Jika field nullable (seperti `sinopsis`) tidak disertakan di dalam body request PUT, kolom tersebut secara semantik direset menjadi `null` di basis data.

### 6. Skenario (f): Transaksi Peminjaman ACID & Penanganan Konflik
- **Transaksi Peminjaman Sukses (`POST /api/v1/peminjamans` - 201 Created):** Dibungkus dalam `DB::transaction()` dengan pessimistic locking `lockForUpdate()`. Stok buku berkurang 1 secara atomik, record pinjaman dibuat dengan kode pinjam unik, dan mengembalikan header `Location`.
- **Penolakan Stok Habis (422 Unprocessable Content):** Saat stok buku bernilai 0, permintaan ditolak dengan kode status `422` berformat RFC 7807 tanpa membocorkan atau mendefisitkan stok.
- **Penolakan Pinjam Ganda (409 Conflict):** Jika mahasiswa dengan NIM yang sama meminjam buku yang sama sebelum pinjaman sebelumnya berstatus 'dikembalikan', transaksi ditolak secara atomik dengan kode `409 Conflict` bertajuk `Duplicate Loan Conflict`.
- **Pengembalian Buku (`PATCH /api/v1/peminjamans/{id}/kembalikan` - 200 OK):** Mengubah status menjadi `dikembalikan`, mencatat `tanggal_kembali`, dan memulihkan stok buku (+1) di dalam transaksi `lockForUpdate()`. Percobaan pengembalian ulang pada transaksi yang sama ditolak `409 Conflict`.

### 7. Skenario (g): REST Service Consumer (Pengayaan Metadata OpenLibrary)
- **GET `/api/v1/bukus/{id}/referensi-eksternal`:** Backend Laravel bertindak sebagai *Service Consumer* yang memanggil OpenLibrary REST API via fasad `Illuminate\Support\Facades\Http` dengan `timeout(8)` detik.
- Mengembalikan data pengayaan metadata: `title`, `publish_date`, `number_of_pages`, `publishers`, `cover_url`, dan `openlibrary_url` dengan penanganan *fallback* aman jika ISBN tidak ditemukan (404) atau layanan eksternal *timeout* (502).

---

## VII. RIWAYAT COMMIT TERSTANDARISASI (CONVENTIONAL COMMITS MINGGU 9)

Proyek dikembangkan secara modular dan terdokumentasi rapi pada branch `week9-rest-api` mengikuti konvensi commit resmi modul:

```
* docs(contract): add OpenAPI 3.0 specification, Postman collection, and test report in README
* feat(api-consumer): integrate external book metadata lookup via Laravel Http client
* feat(api-loans): implement ACID book borrowing and return REST endpoints with lockForUpdate
* feat(api-books): build BukuApiController and BukuResource with dynamic HATEOAS links
* feat(api-auth): implement stateless Sanctum bearer token login and revocation
* feat(api-core): install laravel api routing, ApiResponse trait, and RFC 7807 exception handler
* feat(soap): add concise LibrarySoapController and WSDL contract for clearance check
```

---
*Modul Praktikum disusun oleh Chandro Pardede, S.Kom., M.Sc. untuk Mata Kuliah Pemrograman dan Pengujian Aplikasi Web (12S3101) — Program Studi Sarjana Sistem Informasi / Informatika, Fakultas Informatika dan Teknik Elektro, Institut Teknologi Del.*
