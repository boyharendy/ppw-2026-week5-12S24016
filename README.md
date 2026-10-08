# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)

Aplikasi web katalog perpustakaan Institut Teknologi Del berbasis *server-side rendering* dengan arsitektur *Model-View-Controller*.

## Panduan Instalasi

1. Pastikan Anda memiliki PHP 8.3+ dan Composer terinstal (atau bypass version checking seperti yang dilakukan di sini jika Anda menggunakan PHP 8.2).
2. Jalankan perintah berikut untuk menginstal dependensi (atau jika sudah, abaikan):
   ```bash
   composer install
   npm install
   ```
3. Salin file environment:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Migrasi dan Seeding Database:
   ```bash
   php artisan migrate:fresh --seed
   ```
6. Build assets:
   ```bash
   npm run build
   ```
7. Jalankan server:
   ```bash
   php artisan serve
   ```
8. Buka browser dan arahkan ke `http://localhost:8000`.

## Bukti Audit SQL (Eager Loading)

Sebelum optimasi (Lazy Loading), query yang berjalan untuk merender halaman `/buku` (dengan asumsi 10 data) adalah:

```
[SQL AUDIT] select count(*) as "aggregate" from "bukus"
[SQL AUDIT] select * from "bukus" limit 10 offset 0
[SQL AUDIT] select * from "kategoris" where "kategoris"."id" = 1 limit 1
[SQL AUDIT] select * from "kategoris" where "kategoris"."id" = 2 limit 1
... (10 kali select kategori)
[SQL AUDIT] select * from "kategoris" order by "nama_kategori" asc
```

Setelah ditambahkan `with('kategori')` di controller, ini adalah output audit log (N+1 query teratasi):

```
[2026-10-07 14:42:24] local.INFO: [SQL AUDIT] select count(*) as "aggregate" from "bukus" {"bindings":[],"time":1.19} 
[2026-10-07 14:42:24] local.INFO: [SQL AUDIT] select * from "bukus" limit 10 offset 0 {"bindings":[],"time":0.19} 
[2026-10-07 14:42:24] local.INFO: [SQL AUDIT] select * from "kategoris" where "kategoris"."id" in (1, 3, 4, 5) {"bindings":[],"time":0.19} 
[2026-10-07 14:42:24] local.INFO: [SQL AUDIT] select * from "kategoris" order by "nama_kategori" asc {"bindings":[],"time":0.17}
```
Total hanya 4 query, seberapapun jumlah data di halaman.
