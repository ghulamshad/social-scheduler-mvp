# 🛠️ Development Setup Guide - Social Scheduler

> **Complete local development environment setup for the Social Scheduler platform**

## 📋 Prerequisites

### System Requirements
- **Operating System**: Windows 10+, macOS 10.15+, or Ubuntu 18.04+
- **PHP**: 8.2+ with required extensions
- **Node.js**: 18+ and npm 9+
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Redis**: 6.0+ (for caching and queues)
- **Git**: Latest version
- **Composer**: Latest version
- **Docker**: Optional, for containerized development

### Required PHP Extensions
```bash
# Ubuntu/Debian
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-redis php8.2-bcmath php8.2-ctype php8.2-json php8.2-openssl php8.2-pdo php8.2-tokenizer

# macOS (using Homebrew)
brew install php@8.2
brew install redis mysql

# Windows (using XAMPP or WAMP)
# Install XAMPP with PHP 8.2+ or use WAMP
```

## 🚀 Quick Start

### 1. Clone Repository
```bash
# Clone the repository
git clone https://github.com/yourusername/social-scheduler.git
cd social-scheduler

# Checkout development branch
git checkout develop
```

### 2. Backend Setup (Laravel API)

```bash
# Navigate to backend directory
cd social-scheduler-api

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
nano .env
```

**Environment Configuration:**
```env
APP_NAME="Social Scheduler"
APP_ENV=local
APP_KEY=base64:your_generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_scheduler_dev
DB_USERNAME=root
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AI Services (Optional for development)
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key

# Social Media APIs (Optional for development)
TWITTER_API_KEY=your_twitter_api_key
TWITTER_API_SECRET=your_twitter_api_secret
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
```

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE social_scheduler_dev;"

# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed --class=DemoSeeder

# Create storage link
php artisan storage:link

# Start development server
php artisan serve
```

### 3. Frontend Setup (Vue.js)

```bash
# Navigate to frontend directory
cd ../social-scheduler-frontend

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Configure frontend environment
nano .env
```

**Frontend Environment Configuration:**
```env
VITE_APP_TITLE="Social Scheduler"
VITE_APP_VERSION="1.0.0"
VITE_API_BASE_URL="http://localhost:8000/api"
VITE_APP_ENV="development"
VITE_APP_DEBUG="true"
```

```bash
# Start development server
npm run dev
```

## 🐳 Docker Development Setup

### 1. Docker Compose Configuration

```bash
# Create docker-compose.yml in project root
nano docker-compose.yml
```

```yaml
version: '3.8'

services:
  # MySQL Database
  mysql:
    image: mysql:8.0
    container_name: social_scheduler_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: social_scheduler_dev
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_USER: scheduler_user
      MYSQL_PASSWORD: user_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - social_scheduler_network

  # Redis Cache
  redis:
    image: redis:7-alpine
    container_name: social_scheduler_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - social_scheduler_network

  # Laravel API
  api:
    build:
      context: ./social-scheduler-api
      dockerfile: Dockerfile.dev
    container_name: social_scheduler_api
    restart: unless-stopped
    ports:
      - "8000:8000"
    volumes:
      - ./social-scheduler-api:/var/www/html
      - /var/www/html/vendor
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=social_scheduler_dev
      - DB_USERNAME=scheduler_user
      - DB_PASSWORD=user_password
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - social_scheduler_network

  # Vue.js Frontend
  frontend:
    build:
      context: ./social-scheduler-frontend
      dockerfile: Dockerfile.dev
    container_name: social_scheduler_frontend
    restart: unless-stopped
    ports:
      - "3000:3000"
    volumes:
      - ./social-scheduler-frontend:/app
      - /app/node_modules
    environment:
      - VITE_API_BASE_URL=http://localhost:8000/api
    depends_on:
      - api
    networks:
      - social_scheduler_network

volumes:
  mysql_data:
  redis_data:

networks:
  social_scheduler_network:
    driver: bridge
```

### 2. Backend Dockerfile

```bash
# Create Dockerfile.dev in social-scheduler-api
nano social-scheduler-api/Dockerfile.dev
```

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### 3. Frontend Dockerfile

```bash
# Create Dockerfile.dev in social-scheduler-frontend
nano social-scheduler-frontend/Dockerfile.dev
```

```dockerfile
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy application files
COPY . .

# Expose port
EXPOSE 3000

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

### 4. Start Docker Environment

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec api php artisan migrate

# Seed database
docker-compose exec api php artisan db:seed --class=DemoSeeder

# Create storage link
docker-compose exec api php artisan storage:link
```

## 🔧 Development Tools

### 1. IDE Configuration

**VS Code Extensions:**
```json
{
  "recommendations": [
    "bradlc.vscode-tailwindcss",
    "esbenp.prettier-vscode",
    "ms-vscode.vscode-typescript-next",
    "vue.volar",
    "ms-vscode.vscode-json",
    "formulahendry.auto-rename-tag",
    "christian-kohler.path-intellisense",
    "ms-vscode.vscode-php-debug",
    "bmewburn.vscode-intelephense-client"
  ]
}
```

**VS Code Settings:**
```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "php.validate.enable": true,
  "php.suggest.basic": false,
  "emmet.includeLanguages": {
    "vue-html": "html",
    "vue": "html"
  }
}
```

### 2. Git Hooks

```bash
# Install Husky for Git hooks
cd social-scheduler-frontend
npm install --save-dev husky lint-staged

# Configure pre-commit hooks
npx husky install
npx husky add .husky/pre-commit "npm run lint-staged"
```

**package.json configuration:**
```json
{
  "lint-staged": {
    "*.{js,ts,vue}": [
      "eslint --fix",
      "prettier --write"
    ],
    "*.{css,scss}": [
      "prettier --write"
    ]
  }
}
```

### 3. Development Scripts

**Backend Scripts (package.json):**
```json
{
  "scripts": {
    "dev": "php artisan serve",
    "migrate": "php artisan migrate",
    "seed": "php artisan db:seed",
    "test": "php artisan test",
    "queue": "php artisan queue:work",
    "schedule": "php artisan schedule:run",
    "clear": "php artisan cache:clear && php artisan config:clear && php artisan route:clear"
  }
}
```

**Frontend Scripts:**
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vue-tsc && vite build",
    "preview": "vite preview",
    "lint": "eslint . --ext .vue,.js,.jsx,.cjs,.mjs,.ts,.tsx,.cts,.mts --fix --ignore-path .gitignore",
    "format": "prettier --write src/"
  }
}
```

## 🧪 Testing Setup

### 1. Backend Testing

```bash
# Install testing dependencies
composer require --dev phpunit/phpunit

# Run tests
php artisan test

# Run specific test
php artisan test --filter PostControllerTest

# Generate test coverage
php artisan test --coverage
```

### 2. Frontend Testing

```bash
# Install testing dependencies
npm install --save-dev vitest @vue/test-utils jsdom

# Run tests
npm run test

# Run tests with coverage
npm run test:coverage

# Run tests in watch mode
npm run test:watch
```

### 3. API Testing

```bash
# Install Postman or use curl for API testing

# Test authentication
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Test login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## 🔍 Debugging

### 1. Laravel Debugging

```bash
# Enable debug mode
APP_DEBUG=true

# View logs
tail -f storage/logs/laravel.log

# Use Laravel Telescope for debugging
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 2. Vue.js Debugging

```bash
# Install Vue DevTools
# Chrome: Vue.js devtools extension
# Firefox: Vue.js devtools add-on

# Enable source maps in vite.config.ts
export default defineConfig({
  build: {
    sourcemap: true
  }
})
```

### 3. Database Debugging

```bash
# Enable query logging
DB_QUERY_LOG=true

# Use Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev
```

## 📊 Performance Monitoring

### 1. Backend Performance

```bash
# Install performance monitoring
composer require spatie/laravel-ray --dev

# Use Laravel Horizon for queue monitoring
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

### 2. Frontend Performance

```bash
# Install performance monitoring
npm install --save-dev webpack-bundle-analyzer

# Analyze bundle size
npm run build:analyze
```

## 🔄 Development Workflow

### 1. Feature Development

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature

# Create pull request
# Merge after review
```

### 2. Database Migrations

```bash
# Create migration
php artisan make:migration create_new_table

# Edit migration file
# Run migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback
```

### 3. API Development

```bash
# Create controller
php artisan make:controller Api/NewController

# Create resource
php artisan make:resource NewResource

# Test API endpoints
php artisan route:list --path=api
```

## 🚀 Deployment Preparation

### 1. Environment Configuration

```bash
# Create production environment
cp .env .env.production

# Configure production settings
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. Build Optimization

```bash
# Backend optimization
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend optimization
npm run build
```

### 3. Testing Before Deployment

```bash
# Run all tests
php artisan test
npm run test

# Check for security issues
composer audit
npm audit

# Performance testing
php artisan route:list --compact
```

## 📚 Additional Resources

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Vite Documentation](https://vitejs.dev/guide/)

### Development Tools
- [Laravel Telescope](https://laravel.com/docs/telescope)
- [Laravel Horizon](https://laravel.com/docs/horizon)
- [Vue DevTools](https://devtools.vuejs.org/)
- [Postman](https://www.postman.com/)

### Community Resources
- [Laravel Community](https://laravel.com/community)
- [Vue.js Community](https://vuejs.org/community/)
- [Stack Overflow](https://stackoverflow.com/)

---

**Last Updated**: January 15, 2024  
**Development Version**: v1.0  
**Compatible with**: PHP 8.2+, Node.js 18+, MySQL 8.0+ 