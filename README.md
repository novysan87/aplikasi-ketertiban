# Aplikasi Ketertiban Siswa

Sistem pencatatan pelanggaran siswa dengan manajemen poin, surat peringatan otomatis, notifikasi realtime, dan dashboard statistik.

**Tech Stack:** Laravel 13 · MySQL · Vite + Tailwind CSS v4 · Alpine.js · Laravel Reverb

---

## Fitur

- **Input Pelanggaran** — search siswa via API, pilih jenis pelanggaran, upload foto bukti (max 5)
- **Sistem Poin** — setiap jenis punya poin, akumulasi otomatis, ambang surat peringatan SP1/SP2/SP3
- **Surat Peringatan** — auto-generate saat poin capai ambang, format kop surat sekolah, siap cetak
- **Dashboard** — statistik hari ini, feed realtime, top 5 poin, notifikasi ambang SP
- **Realtime Notifikasi** — Laravel Reverb + Echo, notifikasi muncul saat pelanggaran baru
- **Manajemen User** — role admin, BK, wali kelas, staff
- **Sinkronisasi Siswa** — tarik data dari Database Kesiswaan via API token
- **Foto Bukti** — upload via drag-drop atau kamera HP

---

## Panduan Deploy

### 1. Clone Repository

```bash
cd /var/www
git clone https://github.com/novysan87/aplikasi-ketertiban.git
cd aplikasi-ketertiban
```

### 2. Setup Environment

```bash
cp .env.example .env
```

Edit `.env` — sesuaikan dengan server produksi:

```
APP_NAME="Aplikasi Ketertiban"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.100.6

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aplikasi_ketertiban
DB_USERNAME=ketertiban
DB_PASSWORD=***password***

REVERB_APP_ID=app-id
REVERB_APP_KEY=***key***
REVERB_APP_SECRET=***secret***
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="***key***"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
```

Generate APP_KEY:

```bash
php artisan key:generate
```

### 3. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 4. Setup Database

```sql
CREATE DATABASE aplikasi_ketertiban CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ketertiban'@'localhost' IDENTIFIED BY 'password_database';
GRANT ALL ON aplikasi_ketertiban.* TO 'ketertiban'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Migration & Link

```bash
php artisan migrate --force
php artisan storage:link
```

### 6. Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Izin Direktori

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 8. Web Server

**Apache VirtualHost:**

```apache
<VirtualHost *:80>
    ServerName aplikasi-ketertiban.local
    DocumentRoot /var/www/aplikasi-ketertiban/public

    <Directory /var/www/aplikasi-ketertiban/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/aplikasi-ketertiban-error.log
    CustomLog ${APACHE_LOG_DIR}/aplikasi-ketertiban-access.log combined
</VirtualHost>
```

Aktifkan: `sudo a2ensite aplikasi-ketertiban.conf && sudo systemctl reload apache2`

### 9. Reverb WebSocket (Opsional)

Jalankan manual:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080 &
```

Atau via Supervisor di `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aplikasi-ketertiban/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/aplikasi-ketertiban/storage/logs/reverb.log
```

### 10. Queue Worker

File `/etc/supervisor/conf.d/queue-worker.conf`:

```ini
[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aplikasi-ketertiban/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/aplikasi-ketertiban/storage/logs/queue.log
```

### 11. User Default

Buat akun awal via tinker:

```bash
php artisan tinker
```

```php
User::create([
    'name' => 'Administrator',
    'username' => 'admin',
    'email' => 'admin@sekolah.sch.id',
    'password' => bcrypt('Admin123!'),
    'role' => 'admin',
    'is_active' => true,
]);
User::create([
    'name' => 'BK Operator',
    'username' => 'bk',
    'email' => 'bk@sekolah.sch.id',
    'password' => bcrypt('Bk123!'),
    'role' => 'bk',
    'is_active' => true,
]);
```

### 12. Sinkronisasi Siswa

Login → **Pengaturan → Sinkronisasi** → isi URL API + Token → klik Sinkronkan Sekarang.

### 13. Verifikasi

Akses di browser: `http://IP-SERVER/`

---

## Catatan Penting

| Item | Detail |
|------|--------|
| **Akses** | HTTP via IP lokal (`192.168.100.6`). Belum HTTPS. |
| **Upload foto** | Tersimpan di `storage/app/public/violations/` |
| **Logo sekolah** | Upload di Pengaturan Sekolah setelah login |
| **Background login** | Upload di Pengaturan Sekolah, ideal 1200×800 px |
| **Queue & Reverb** | Wajib jalan via Supervisor untuk notifikasi realtime |

---

## Akun Default

| User | Username | Password | Role |
|------|----------|----------|------|
| Admin | `admin` | `Admin123!` | Full access |
| BK | `bk` | `Bk123!` | Operasional |

---

## Struktur Database

```
users → role (admin, bk, wali_kelas, staff)
violation_categories → violation_types
students → violations → violation_evidences
students → sp_letters → sp_thresholds
users → notifications
settings (key-value)
classes (sync dari kesiswaan)
```

---

Dikembangkan oleh [NOCTKJ.net](https://noctkj.net)
