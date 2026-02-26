# 🎓 PRIMA -- Sistem Informasi Akademik

**PRIMA (Platform Informasi Manajemen Akademik)** adalah aplikasi Sistem
Informasi Akademik berbasis web yang dibangun menggunakan Laravel
Framework.\
Sistem ini dirancang untuk mendukung pengelolaan akademik secara
terintegrasi dan efisien.

## ✨ Fitur Utama

-   📅 Manajemen Jadwal Perkuliahan\
-   📊 Pengelolaan Beban Kerja Dosen (BKD)\
-   ✅ Sistem Approval Dokumen Akademik\
-   📧 Notifikasi Email Otomatis (Background Queue)

------------------------------------------------------------------------

## 🛠️ Teknologi yang Digunakan

-   PHP 8.1+
-   Laravel Framework
-   MySQL / MariaDB
-   Vite (Frontend Build Tool)
-   Laravel Queue (Background Job Processing)
-   Brevo SMTP (Email Service)

------------------------------------------------------------------------

# 💻 Persyaratan Sistem

Pastikan perangkat Anda telah menginstal:

-   PHP ≥ 8.1
-   Composer
-   Node.js & NPM
-   MySQL / MariaDB
-   Git

------------------------------------------------------------------------

# 🚀 Panduan Instalasi Lokal

Ikuti langkah berikut untuk menjalankan proyek di perangkat baru.

## 1️⃣ Clone Repository

``` bash
git clone <url-repository-anda>
cd prima
```

------------------------------------------------------------------------

## 2️⃣ Instal Dependensi

``` bash
composer install
npm install
```

------------------------------------------------------------------------

## 3️⃣ Konfigurasi Environment

Salin file konfigurasi:

``` bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

``` env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_lokal_anda
DB_USERNAME=root
DB_PASSWORD=
```

------------------------------------------------------------------------

## 4️⃣ Generate Application Key

``` bash
php artisan key:generate
```

------------------------------------------------------------------------

## 5️⃣ Migrasi & Seeder Database

Pastikan Anda sudah membuat database kosong terlebih dahulu.

``` bash
php artisan migrate --seed
```

Perintah ini akan: - Membuat struktur tabel - Mengisi data awal (Role,
Admin, dll.)

------------------------------------------------------------------------

## 6️⃣ Storage Link (Opsional namun Disarankan)

Jika aplikasi menggunakan fitur upload dokumen:

``` bash
php artisan storage:link
```

------------------------------------------------------------------------

# 📧 Konfigurasi Email (Brevo SMTP)

Sistem menggunakan layanan SMTP dari Brevo untuk pengiriman notifikasi
email.

### Langkah Konfigurasi:

1.  Login ke dashboard Brevo\
2.  Masuk ke: **Transactional \> Email \> Settings \> SMTP & API**\
3.  Ambil informasi berikut:
    -   SMTP Server
    -   Port
    -   Login (Email)
    -   Master Password

Edit file `.env` dan tambahkan konfigurasi berikut:

``` env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=email_brevo_anda@example.com
MAIL_PASSWORD=master_password_smtp_brevo_anda
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin@prima-kampus.com
MAIL_FROM_NAME="${APP_NAME}"
```

------------------------------------------------------------------------

# ▶️ Menjalankan Aplikasi

Untuk menjalankan sistem secara penuh, buka **3 terminal terpisah** di
folder proyek.

## 🖥 Terminal 1 -- Server Laravel

``` bash
php artisan serve
```

Akses aplikasi di: http://localhost:8000

------------------------------------------------------------------------

## 🎨 Terminal 2 -- Frontend (Vite)

``` bash
npm run dev
```

Digunakan untuk memuat: - CSS - JavaScript - Komponen interaktif (Chart,
dll.)

------------------------------------------------------------------------

## 📬 Terminal 3 -- Queue Worker

``` bash
php artisan queue:work
```

Digunakan agar: - Notifikasi email terkirim - Proses approval berjalan
di background - Halaman tidak mengalami loading lama

------------------------------------------------------------------------

# 🛡️ License

Proyek ini dibangun menggunakan Laravel Framework yang berlisensi
open-source di bawah MIT License.

------------------------------------------------------------------------

## ⚡ Catatan Tambahan

Jika aplikasi tidak menggunakan Queue (email dikirim secara sinkron),
maka perintah:

``` bash
php artisan queue:work
```

dapat diabaikan.

Namun, sangat disarankan tetap menggunakan Queue agar proses seperti
klik **"Ajukan"** tetap responsif dan cepat.
