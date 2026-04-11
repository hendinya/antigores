# Deploy ke VPS (tg.hpulsa.com)

## 1) DNS

- A Record `tg.hpulsa.com` -> IP VPS.

## 2) Paket server

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip unzip git composer nodejs npm certbot python3-certbot-nginx
```

## 3) Siapkan direktori aplikasi

```bash
sudo mkdir -p /var/www/antigores
sudo chown -R $USER:$USER /var/www/antigores
```

## 4) Deploy source

```bash
cd /var/www/antigores
curl -fsSL https://raw.githubusercontent.com/hendinya/antigores/main/deploy/vps/deploy.sh -o deploy.sh
chmod +x deploy.sh
APP_DIR=/var/www/antigores REPO_URL=https://github.com/hendinya/antigores.git BRANCH=main ./deploy.sh
```

## 5) Konfigurasi environment

```bash
cp /var/www/antigores/deploy/vps/env.production.example /var/www/antigores/.env
nano /var/www/antigores/.env
```

Pastikan `APP_URL=https://tg.hpulsa.com` dan konfigurasi DB benar.

Tambahkan konfigurasi Google OAuth:

```bash
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://tg.hpulsa.com/auth/google/callback
```

## 6) Nginx

```bash
sudo cp /var/www/antigores/deploy/vps/nginx-antigores.conf /etc/nginx/sites-available/antigores
sudo ln -s /etc/nginx/sites-available/antigores /etc/nginx/sites-enabled/antigores
sudo nginx -t
sudo systemctl reload nginx
```

## 7) HTTPS SSL

```bash
sudo certbot --nginx -d tg.hpulsa.com --redirect -m admin@hpulsa.com --agree-tos -n
```

## 8) Finalisasi Laravel

```bash
cd /var/www/antigores
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan optimize
```

## 9) Verifikasi

```bash
curl -I https://tg.hpulsa.com
curl -I https://tg.hpulsa.com/offline-products
```

Expected response `HTTP/2 200`.

## 10) Hardening (UFW + fail2ban)

```bash
chmod +x /var/www/antigores/deploy/vps/setup_security.sh
SSH_PORT=22 /var/www/antigores/deploy/vps/setup_security.sh
```

## 11) Backup DB harian

```bash
chmod +x /var/www/antigores/deploy/vps/db_backup.sh
/var/www/antigores/deploy/vps/db_backup.sh
echo "0 2 * * * root ENV_FILE=/var/www/antigores/.env BACKUP_DIR=/var/backups/antigores-db RETENTION_DAYS=14 /var/www/antigores/deploy/vps/db_backup.sh >> /var/log/antigores-db-backup.log 2>&1" > /etc/cron.d/antigores-db-backup
chmod 644 /etc/cron.d/antigores-db-backup
systemctl restart cron
```
