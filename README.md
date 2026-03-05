# Sistem Monitoring Warga Desa

Sistem Monitoring Warga Desa adalah aplikasi berbasis web yang digunakan untuk mendata dan memantau kondisi kesejahteraan warga desa. Aplikasi ini memungkinkan pengelolaan data warga, klasifikasi status kemiskinan berdasarkan penghasilan, serta visualisasi data dalam bentuk grafik. Dilengkapi dengan sistem autentikasi dan otorisasi berdasarkan peran (admin, perangkat desa, warga).

## ✨ Fitur Utama

- **Autentikasi & Otorisasi**  
  - Registrasi dan login dengan password terenkripsi.
  - Tiga level pengguna: **Admin**, **Perangkat Desa**, dan **Warga**.
  - Halaman berbeda untuk setiap peran (warga melihat data dirinya sendiri).

- **Manajemen Data Warga**  
  - Tambah, lihat, edit, dan hapus data warga.
  - Input data meliputi NIK, nama, jenis kelamin, pekerjaan, penghasilan, jumlah tanggungan, RT/RW.
  - Status kemiskinan dihitung otomatis berdasarkan penghasilan (< 1jt = Miskin, 1-3jt = Rentan, >3jt = Sejahtera).
  - Upload dokumen pendukung (KTP, KK, dokumen lain) ke **Cloudinary** (otomatis dikompres).
  - Lihat dan hapus dokumen yang telah diunggah.

- **Dashboard Interaktif**  
  - Grafik pie komposisi status kemiskinan.
  - Statistik total warga, miskin, rentan, sejahtera.
  - Data terbaru ditampilkan dalam tabel.
  - Dashboard khusus warga menampilkan data diri dan dokumen pribadi.

- **Pencarian & Filter**  
  - Pencarian data warga berdasarkan NIK atau nama.

- **Import / Export Data**  
  - Import data warga secara massal menggunakan file Excel (format .xlsx atau .xls) dengan library **Box/Spout**.
  - Export data warga ke file Excel.
  - Registrasi akun secara batch melalui template Excel (khusus admin).

- **Manajemen Pengguna (Admin)**  
  - Lihat daftar user, cari berdasarkan nama.
  - Edit dan hapus user (dengan proteksi agar admin tidak bisa menghapus dirinya sendiri).
  - Hapus user secara massal dengan fitur checkbox.

- **Keamanan**  
  - Semua query menggunakan **prepared statements** untuk mencegah SQL Injection.
  - Password di-hash dengan `password_hash()`.
  - Validasi input sisi server.

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8 (native)
- **Database**: MySQL 8 / MariaDB
- **Frontend**: Tailwind CSS, Font Awesome, Chart.js
- **Library**:
  - [Box/Spout](https://github.com/box/spout) - Membaca & menulis file Excel
  - [Cloudinary PHP SDK](https://cloudinary.com/documentation/php_integration) - Upload & manajemen gambar
- **Lainnya**: Composer untuk manajemen dependensi

## 📋 Persyaratan Sistem

- PHP 8.0 atau lebih baru
- MySQL 5.7 / MariaDB 10.2+
- Composer
- Ekstensi PHP: `mysqli`, `json`, `fileinfo`, `gd` (untuk manipulasi gambar)
- Akun Cloudinary (opsional, untuk fitur upload dokumen)

## 🚀 Instalasi

1. **Clone repositori**
   ```bash
   git clone https://github.com/username/monitoring-desa.git
   cd monitoring-desa
   ```

2. **Install dependensi dengan Composer**
   ```bash
   composer install
   ```

3. **Buat database**
   - Buat database baru, misal `monitoring_desa`.
   - Import file `database/monitoring_desa.sql` (sesuaikan dengan struktur terbaru).

4. **Konfigurasi koneksi database**
   - Salin file `config/database.example.php` menjadi `config/database.php`.
   - Sesuaikan `host`, `username`, `password`, dan `database`.

5. **Konfigurasi Cloudinary (opsional)**
   - Salin `config/cloudinary.example.php` menjadi `config/cloudinary.php`.
   - Isi dengan `cloud_name`, `api_key`, dan `api_secret` dari akun Cloudinary Anda.

6. **Atur base URL (jika perlu)**
   - Sesuaikan path pada file-file yang menggunakan `header("Location: ...")` jika tidak berada di root server.

7. **Jalankan aplikasi**
   - Akses melalui browser: `http://localhost/monitoring-desa`

## 🔧 Konfigurasi Tambahan

- **Struktur folder** pastikan dapat diakses oleh web server.
- **Hak akses** folder `vendor/` dan file konfigurasi sebaiknya tidak dapat diakses publik.

## 📸 Tampilan Aplikasi

<img width="4046" height="2147" alt="LOGIN" src="https://github.com/user-attachments/assets/35c075bf-2ed8-441e-999c-ad1ab827f9c6" />
<img width="3318" height="2230" alt="DASH" src="https://github.com/user-attachments/assets/c0bc2916-6efe-4905-abe1-59a68d79bc6a" />


## 📁 Struktur Direktori

```
monitoring-desa/
├── assets/            # File statis (CSS, JS, gambar)
├── auth/              # Halaman autentikasi (login, register, logout, users)
├── config/            # File konfigurasi database & cloudinary
├── dashboard/         # Halaman dashboard (admin & warga)
├── warga/             # Modul data warga (CRUD, upload, detail, import/export)
├── vendor/            # Dependensi Composer
├── index.php          # Redirect ke login/dashboard
└── README.md
```


- Pastikan folder `vendor/` sudah terinstall.
- Untuk fitur upload, pastikan konfigurasi Cloudinary benar.
- Jika menggunakan PHP built-in server, jalankan dari folder root dengan:
  ```bash
  php -S localhost:8000
  ```
  Akses `http://localhost:8000`

  <a href="https://asifaahmad.web.app" target="_blank" class="text-white font-semibold hover:underline">By : Asifa Ahmad </a>

