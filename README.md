# Cantabu – PHP CRUD (Profile & Photo)

Proyek sederhana PHP + PostgreSQL untuk manajemen profile dan foto profile. UI menggunakan styling ringan di `layout/header.php`.

Catatan singkat:
- Database: PostgreSQL (koneksi di `config/db.php`).
- Schema default: `cantabu` (lihat `config/db.php` yang meng-set `search_path`).
- Uploads disimpan di folder `uploads/` (dapat dibuat otomatis oleh skrip).

## Prasyarat
- PHP 8+ dengan `pdo_pgsql` dan `pgsql` aktif.
- PostgreSQL (server) + kredensial yang sesuai.
- Web server (Apache/IIS) yang menunjuk folder proyek di `htdocs`.

## Cara jalankan cepat
1. Edit `config/db.php` dan isi host/port/db/user/password.
2. Sesuaikan `config/app.php` jika perlu (`$UPLOAD_DIR` dan `$PUBLIC_BASE`).
3. Pastikan folder `uploads/` dapat ditulis oleh proses web.
4. Buka `http://localhost/Cantabu/` (atau path sesuai) di browser.

## Struktur proyek
Ringkasan file dan folder penting (relatif ke root proyek):

```
Cantabu/
├─ config/
│  ├─ db.php            # koneksi PDO ke PostgreSQL (set search_path)
│  └─ app.php           # setting upload (UPLOAD_DIR, PUBLIC_BASE, allowed mimes)
├─ layout/
│  ├─ header.php        # tag <head>, style dasar, header + container
│  └─ footer.php        # penutup container dan </body></html>
├─ profile/
│  ├─ index.php         # daftar profile (grid kartu + pencarian)
│  ├─ create.php        # form tambah profile (plus upload foto opsional)
│  ├─ edit.php          # edit profile (ganti/hapus foto)
│  └─ delete.php        # endpoint hapus profile (POST)
├─ photo/
│  ├─ index.php         # daftar foto (tabel) dan link ke upload
│  ├─ upload.php        # form + handler upload foto (simpan metadata ke DB)
│  └─ delete.php        # endpoint hapus foto
├─ uploads/             # (buat otomatis) tempat menyimpan file gambar
│  └─ avatars/          # subfolder untuk avatar yang dibuat oleh fitur profile
├─ index.php            # halaman Menu Utama
└─ README.md            # dokumen utama (ringkasan)
```

## Catatan tambahan
- Jika muncul error terkait kolom `photo` pada tabel `profile`, tambahkan kolom dengan SQL:
  ```sql
  ALTER TABLE cantabu.profile ADD COLUMN photo VARCHAR(255);
  ```
- Pastikan `config/app.php` -> `$PUBLIC_BASE` cocok dengan URL publik ke folder `uploads` (mis. `/Cantabu/uploads`).
- Untuk debugging upload, periksa pesan error di halaman `photo/upload.php` dan log PHP/Apache.