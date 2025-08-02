# 🚀 Deployment Guide - Social Scheduler

> **Complete production deployment guide for the Social Scheduler platform**

## 📋 Prerequisites

### System Requirements
- **Server**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **PHP**: 8.2+ with extensions (BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, GD, Redis)
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Node.js**: 18+ (for frontend build)
- **Redis**: 6.0+ (for caching and queues)
- **SSL Certificate**: Let's Encrypt or commercial certificate

### Domain Setup
```
Frontend: app.yourdomain.com
API: api.yourdomain.com
```

## 🏗️ Backend Deployment (Laravel API)

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-redis redis-server composer git unzip

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE social_scheduler;
CREATE USER 'scheduler_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON social_scheduler.* TO 'scheduler_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/social-scheduler-api
sudo chown -R $USER:$USER /var/www/social-scheduler-api

# Clone repository
cd /var/www/social-scheduler-api
git clone https://github.com/ghulamshad/social-scheduler-mvp.git .
cd social-scheduler-api

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chown -R www-data:www-data /var/www/social-scheduler-api
sudo chmod -R 755 /var/www/social-scheduler-api
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment variables
nano .env
```

```env
APP_NAME="Social Scheduler"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://api.yourdomain.com
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_scheduler
DB_USERNAME=scheduler_user
DB_PASSWORD=strong_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Social Media API Keys
TWITTER_API_KEY=your_twitter_api_key
TWITTER_API_SECRET=your_twitter_api_secret
TWITTER_ACCESS_TOKEN=your_twitter_access_token
TWITTER_ACCESS_TOKEN_SECRET=your_twitter_access_token_secret

FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_ACCESS_TOKEN=your_facebook_access_token

LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_ACCESS_TOKEN=your_linkedin_access_token

# AI Service Keys
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key

# Third-party Services
BUFFER_API_KEY=your_buffer_api_key
HOOTSUITE_API_KEY=your_hootsuite_api_key

# Webhook Configuration
WEBHOOK_SECRET=your_webhook_secret_key

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60
AI_RATE_LIMIT_PER_MINUTE=10
```

### 5. Database Migration and Seeding

```bash
# Run migrations
php artisan migrate --force

# Seed database with demo data (optional)
php artisan db:seed --class=DemoSeeder --force

# Create storage link
php artisan storage:link

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Queue Worker Setup

```bash
# Create systemd service for queue worker
sudo nano /etc/systemd/system/social-scheduler-queue.service
```

```ini
[Unit]
Description=Social Scheduler Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/social-scheduler-api/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
StandardOutput=append:/var/log/social-scheduler-queue.log
StandardError=append:/var/log/social-scheduler-queue.log

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start queue worker
sudo systemctl enable social-scheduler-queue
sudo systemctl start social-scheduler-queue
```

### 7. Scheduler Setup

```bash
# Add cron job for Laravel scheduler
crontab -e
```

Add this line:
```bash
* * * * * cd /var/www/social-scheduler-api && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Nginx Configuration

```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/social-scheduler-api
```

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    limit_req zone=api burst=20 nodelay;

    root /var/www/social-scheduler-api/public;
    index index.php index.html index.htm;

    # Logging
    access_log /var/log/nginx/social-scheduler-api-access.log;
    error_log /var/log/nginx/social-scheduler-api-error.log;

    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Handle static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(vendor|storage|bootstrap|config|database|resources|routes|tests) {
        deny all;
    }
}
```

```bash
# Enable site and restart Nginx
sudo ln -s /etc/nginx/sites-available/social-scheduler-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d api.yourdomain.com

# Set up auto-renewal
sudo crontab -e
```

Add this line:
```bash
0 12 * * * /usr/bin/certbot renew --quiet
```

## 🎨 Frontend Deployment (Vue.js)

### 1. Build Application

```bash
# Navigate to frontend directory
cd /var/www/social-scheduler-frontend

# Install dependencies
npm ci --production

# Build for production
npm run build

# The built files will be in the dist/ directory
```

### 2. Nginx Configuration for Frontend

```bash
# Create Nginx configuration for frontend
sudo nano /etc/nginx/sites-available/social-scheduler-frontend
```

```nginx
server {
    listen 80;
    server_name app.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/app.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'; connect-src 'self' https://api.yourdomain.com;" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    root /var/www/social-scheduler-frontend/dist;
    index index.html;

    # Logging
    access_log /var/log/nginx/social-scheduler-frontend-access.log;
    error_log /var/log/nginx/social-scheduler-frontend-error.log;

    # Handle Vue Router
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

```bash
# Enable site and restart Nginx
sudo ln -s /etc/nginx/sites-available/social-scheduler-frontend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Obtain SSL certificate for frontend
sudo certbot --nginx -d app.yourdomain.com
```

## 🔧 Environment-Specific Configurations

### Production Environment Variables

```bash
# Create production-specific environment file
nano /var/www/social-scheduler-api/.env.production
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_scheduler
DB_USERNAME=scheduler_user
DB_PASSWORD=your_strong_password

# Cache and Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Social Scheduler"

# Social Media APIs
TWITTER_API_KEY=your_twitter_api_key
TWITTER_API_SECRET=your_twitter_api_secret
TWITTER_ACCESS_TOKEN=your_twitter_access_token
TWITTER_ACCESS_TOKEN_SECRET=your_twitter_access_token_secret

FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_ACCESS_TOKEN=your_facebook_access_token

LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_ACCESS_TOKEN=your_linkedin_access_token

# AI Services
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key

# Third-party Services
BUFFER_API_KEY=your_buffer_api_key
HOOTSUITE_API_KEY=your_hootsuite_api_key

# Webhooks
WEBHOOK_SECRET=your_webhook_secret

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60
AI_RATE_LIMIT_PER_MINUTE=10

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Frontend Environment Variables

```bash
# Create frontend environment file
nano /var/www/social-scheduler-frontend/.env.production
```

```env
VITE_APP_TITLE="Social Scheduler"
VITE_APP_VERSION="1.0.0"
VITE_API_BASE_URL="https://api.yourdomain.com/api"
VITE_APP_ENV="production"
VITE_APP_DEBUG="false"
```

## 📊 Monitoring and Logging

### 1. Application Monitoring

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Set up log rotation
sudo nano /etc/logrotate.d/social-scheduler
```

```bash
/var/www/social-scheduler-api/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
```

### 2. Health Checks

```bash
# Create health check script
nano /var/www/social-scheduler-api/health-check.sh
```

```bash
#!/bin/bash

# Check if application is responding
if curl -f https://api.yourdomain.com/health > /dev/null 2>&1; then
    echo "API is healthy"
    exit 0
else
    echo "API is down"
    exit 1
fi
```

```bash
# Make executable
chmod +x /var/www/social-scheduler-api/health-check.sh

# Add to crontab for monitoring
crontab -e
```

Add this line:
```bash
*/5 * * * * /var/www/social-scheduler-api/health-check.sh
```

## 🔒 Security Hardening

### 1. Firewall Configuration

```bash
# Configure UFW firewall
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Fail2ban Setup

```bash
# Install Fail2ban
sudo apt install fail2ban

# Configure for Nginx
sudo nano /etc/fail2ban/jail.local
```

```ini
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/access.log
findtime = 600
maxretry = 10
```

```bash
# Restart Fail2ban
sudo systemctl restart fail2ban
```

### 3. Database Security

```sql
-- Secure MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add these lines:
```ini
[mysqld]
bind-address = 127.0.0.1
max_connections = 200
innodb_buffer_pool_size = 256M
query_cache_size = 32M
```

## 🚀 Deployment Scripts

### 1. Automated Deployment Script

```bash
# Create deployment script
nano /var/www/deploy.sh
```

```bash
#!/bin/bash

set -e

echo "🚀 Starting deployment..."

# Pull latest changes
cd /var/www/social-scheduler-api
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
sudo systemctl restart social-scheduler-queue

# Deploy frontend
cd /var/www/social-scheduler-frontend
git pull origin main
npm ci --production
npm run build

echo "✅ Deployment completed successfully!"
```

```bash
# Make executable
chmod +x /var/www/deploy.sh
```

### 2. Backup Script

```bash
# Create backup script
nano /var/www/backup.sh
```

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/social-scheduler"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u scheduler_user -p social_scheduler > $BACKUP_DIR/database_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/social-scheduler-api

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
chmod +x /var/www/backup.sh

# Add to crontab for daily backups
crontab -e
```

Add this line:
```bash
0 2 * * * /var/www/backup.sh
```

## 📈 Performance Optimization

### 1. PHP-FPM Optimization

```bash
# Configure PHP-FPM
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 2. Redis Optimization

```bash
# Configure Redis
sudo nano /etc/redis/redis.conf
```

```ini
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 3. Nginx Optimization

```bash
# Configure Nginx
sudo nano /etc/nginx/nginx.conf
```

```nginx
worker_processes auto;
worker_rlimit_nofile 65535;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 100M;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

## 🔍 Troubleshooting

### Common Issues and Solutions

#### 1. Queue Worker Not Processing
```bash
# Check queue worker status
sudo systemctl status social-scheduler-queue

# Restart queue worker
sudo systemctl restart social-scheduler-queue

# Check queue logs
tail -f /var/log/social-scheduler-queue.log
```

#### 2. Database Connection Issues
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

#### 3. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificates
sudo certbot renew --dry-run
```

#### 4. Performance Issues
```bash
# Check server resources
htop
df -h
free -h

# Check Nginx error logs
tail -f /var/log/nginx/error.log
```

## 📞 Support and Maintenance

### Monitoring Commands
```bash
# Check application status
sudo systemctl status nginx mysql redis php8.2-fpm social-scheduler-queue

# Monitor logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
tail -f /var/www/social-scheduler-api/storage/logs/laravel.log

# Check disk usage
df -h
du -sh /var/www/social-scheduler-api/storage/logs/*
```

### Update Procedures
```bash
# Update application
cd /var/www/social-scheduler-api
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
sudo systemctl restart social-scheduler-queue

# Update frontend
cd /var/www/social-scheduler-frontend
git pull origin main
npm ci --production
npm run build
```

---

**Last Updated**: January 15, 2024  
**Deployment Version**: v1.0  
**Compatible with**: Ubuntu 20.04+, CentOS 8+, Debian 11+ 