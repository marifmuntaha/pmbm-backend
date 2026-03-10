# Backend Deployment - PMBM Yayasan Darul Hikmah

Panduan ini menjelaskan alur deployment untuk backend aplikasi PMBM Yayasan Darul Hikmah.

## Prasyarat
- PHP >= 8.4
- MySQL / MariaDB
- Composer
- Git

## Alur Deployment (Manual)

1. **Clone Repository**
   ```bash
   git clone <repository-url> backend
   cd backend
   ```

2. **Setup Environment**
   Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya (Database, WhatsApp Service, dll).
   ```bash
   cp .env.example .env
   ```

3. **Install Dependensi**
   Gunakan instruksi berikut untuk menginstall library yang diperlukan secara optimal untuk production.
   ```bash
   composer install --no-interaction --optimize-autoloader --no-dev
   ```

4. **Generate App Key**
   ```bash
   php artisan key:generate
   ```

5. **Storage Link**
   Buat link simbolik untuk akses file publik.
   ```bash
   php artisan storage:link
   ```

6. **Migrasi Database**
   Jalankan migrasi untuk membuat tabel yang diperlukan.
   ```bash
   php artisan migrate --force
   ```

## Konfigurasi Queue Worker (Persistent Service)

Untuk production, menjalankan `queue:work` via cronjob kurang efisien karena prosesnya akan terus mati dan hidup. Disarankan menggunakan **process manager** agar worker selalu berjalan dan otomatis restart jika server melakukan reboot.

### Opsi 1: Menggunakan Systemd (Rekomendasi Built-in Linux)

Cara ini paling stabil karena tidak memerlukan instalasi software tambahan di kebanyakan distro modern (Ubuntu, Debian, dll).

1. Buat file service baru:
   ```bash
   sudo nano /etc/systemd/system/laravel-worker.service
   ```

2. Masukkan konfigurasi berikut (sesuaikan path):
   ```ini
   [Unit]
   Description=Laravel Queue Worker
   After=network.target mysql.service

   [Service]
   User=www-data
   Group=www-data
   Restart=always
   ExecStart=/usr/bin/php /var/www/html/backend/artisan queue:work --tries=3 --timeout=90
   WorkingDirectory=/var/www/html/backend

   [Install]
   WantedBy=multi-user.target
   ```

3. Jalankan dan aktifkan agar start otomatis saat boot:
   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable laravel-worker.service
   sudo systemctl start laravel-worker.service
   ```

### Opsi 2: Menggunakan Supervisor

Jika Anda lebih suka menggunakan Supervisor:

1. Install supervisor:
   ```bash
   sudo apt-get install supervisor
   ```

2. Konfigurasi worker:
   ```bash
   sudo nano /etc/supervisor/conf.d/laravel-worker.conf
   ```

3. Masukkan konfigurasi:
   ```ini
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/html/backend/artisan queue:work --tries=3 --timeout=90
   autostart=true
   autorestart=true
   user=www-data
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/var/www/html/backend/storage/logs/worker.log
   ```

4. Update dan jalankan:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   ```

> [!IMPORTANT]
> Ganti `/var/www/html/backend` dengan path absolut ke project Anda. Pastikan pula user `www-data` memiliki izin akses ke folder tersebut.
