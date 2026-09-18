# Ubuntu Setup for EasyTimeOnlineSaas

This document explains how to set up this Laravel project on Ubuntu, run it in production, and configure queue workers using Supervisor.

---

# Fresh Installation on Ubuntu

This section is for first-time setup on a new Ubuntu server.

## 1. Install system dependencies

Run the following commands in the Ubuntu terminal:

```bash
sudo apt update
sudo apt upgrade -y

sudo apt install -y git curl zip unzip software-properties-common
sudo apt install -y php php-cli php-common php-mysql php-xml php-mbstring php-curl php-zip php-gd php-bcmath php-intl sqlite3
```

If you need a newer PHP version, use:

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-sqlite3
```

Check PHP:

```bash
php -v
```

---

## 2. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

---

## 3. Clone or copy project

If using Git:

```bash
cd /var/www
sudo git clone <repo-url> easytimeonline
cd /var/www/easytimeonline
```

If project files are already uploaded manually:

```bash
sudo mkdir -p /var/www/easytimeonline
sudo chown -R $USER:$USER /var/www/easytimeonline
```

---

## 4. Set up environment file

```bash
cp .env.example .env
nano .env
```

Example `.env` values:

```env
APP_NAME=EasyTimeOnline
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=easytimeonline
DB_USERNAME=root
DB_PASSWORD=your_password

QUEUE_CONNECTION=database_tenant
```

Important:

- Make sure the database exists before running migrations.
- For this project, tenant queue is configured through `database_tenant` connection.

---

## 5. Install PHP dependencies

```bash
cd /var/www/easytimeonline
composer install --no-interaction --prefer-dist --optimize-autoloader
```

---

## 6. Generate Laravel application key

```bash
php artisan key:generate
```

---

## 7. Set permissions

```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data /var/www/easytimeonline
```

If running as your own user, you can instead do:

```bash
sudo chown -R $USER:$USER /var/www/easytimeonline
```

---

## 8. Run database migrations

```bash
php artisan migrate --force
```

If your app uses tenant databases, make sure tenant setup is configured properly and the required tenant migrations/seeding are executed according to your project logic.

---

## 9. Frontend build (if needed)

If the project uses Vite/frontend assets:

```bash
npm install
npm run build
```

---

## 10. Queue worker setup with Supervisor

This is the recommended production setup for multiple tenant jobs.

### Install Supervisor

```bash
sudo apt install -y supervisor
```

Create config file:

```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

Add:

```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/easytimeonline/artisan queue:work database_tenant --queue=notifications,import,export,activity-log --sleep=3 --tries=3
directory=/var/www/easytimeonline
autostart=true
autorestart=true
startsecs=10
stopwaitsecs=10
numprocs=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/easytimeonline/storage/logs/queue-worker.log
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
sudo supervisorctl start laravel-queue:*
```

Check queue logs:

```bash
tail -f /var/www/easytimeonline/storage/logs/queue-worker.log
```

This ensures multiple workers run in parallel, so jobs from different tenants can be processed at the same time.

---

## 11. Nginx setup for production

Install Nginx:

```bash
sudo apt install -y nginx
```

Create site config:

```bash
sudo nano /etc/nginx/sites-available/easytimeonline
```

Paste:

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    root /var/www/easytimeonline/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/easytimeonline /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 12. Install and enable PHP-FPM

```bash
sudo apt install -y php8.3-fpm
sudo systemctl enable php8.3-fpm
sudo systemctl start php8.3-fpm
```

---

---

# Update / Reload on Existing Server

This section is for deploying new code changes to an already running Ubuntu production server.

## 1. Deploy code changes from development to Ubuntu production

Suppose you made a feature in your local dev branch and now want to deploy to the Ubuntu server.

### Option A: Git pull workflow

```bash
cd /var/www/easytimeonline
sudo git pull origin main
```

If you're on a different branch:

```bash
sudo git checkout your-branch
sudo git pull origin your-branch
```

Then run:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

If frontend assets changed:

```bash
npm install
npm run build
```

### Option B: Upload files manually

If your server is not using Git, upload the updated files via FTP/SFTP or Rsync and then run:

```bash
cd /var/www/easytimeonline
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

---

## 2. When queue code changes, restart the queue workers

If you changed the job class logic, queue job code, or any queue-related behavior, the running worker processes may still use the old code in memory.

So you need to restart the queue workers.

### Restart Supervisor-managed workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-queue:*
```

Or stop and start explicitly:

```bash
sudo supervisorctl stop laravel-queue:*
sudo supervisorctl start laravel-queue:*
```

### Check status

```bash
sudo supervisorctl status
```

### View logs

```bash
tail -f /var/www/easytimeonline/storage/logs/queue-worker.log
```

> Important: If you changed queue job code, always restart workers. Otherwise old code may keep processing queued jobs.

---

## 3. Safe production deployment checklist

Before going live, always do this:

```bash
cd /var/www/easytimeonline
sudo git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
sudo supervisorctl restart laravel-queue:*
```

This is the standard flow for deploying Laravel changes to Ubuntu production and restarting the queue after code updates.

---

## 4. Quick deployment summary

```bash
cd /var/www/easytimeonline
sudo git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
sudo supervisorctl restart laravel-queue:*
```

This is enough for most feature deployments on Ubuntu production.

---

---

## 13. Optional: SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

---

## 14. Clear Laravel caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

---

## 15. Useful commands

### Start queue manually

```bash
php artisan queue:work database_tenant --queue=notifications,import,export,activity-log --tries=3
```

### Start Supervisor workers

```bash
sudo supervisorctl start laravel-queue:*
```

### Check worker status

```bash
sudo supervisorctl status
```

### Restart workers

```bash
sudo supervisorctl restart laravel-queue:*
```

### Stop workers

```bash
sudo supervisorctl stop laravel-queue:*
```

---

## Final note

For this project, the queue is designed to support multi-tenant processing using the tenant-aware queue bootstrapper and tenant-specific queue connection. To run jobs from multiple tenants at the same time, one worker is not enough; you need multiple queue workers managed by Supervisor.

---

## Quick start summary

```bash
cd /var/www/easytimeonline
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue:*
```

This is the basic production setup for your Laravel project on Ubuntu.
