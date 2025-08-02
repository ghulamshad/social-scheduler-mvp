# Social Scheduler API

A robust Laravel 12 API backend for the Social Scheduler application, featuring Laravel Sanctum authentication, automated post scheduling, and comprehensive social media account management.

## 🚀 Features

### Authentication & Security
- **Laravel Sanctum** - Token-based API authentication
- **User Registration & Login** - Secure user management
- **Protected Routes** - Middleware-based route protection
- **CORS Configuration** - Cross-origin resource sharing enabled

### Post Management
- **CRUD Operations** - Create, read, update, delete posts
- **Scheduling System** - Schedule posts for future publication
- **Status Tracking** - Draft, scheduled, published, failed states
- **Image Support** - Optional image URLs for posts
- **Account Association** - Link posts to specific social media accounts

### Social Media Accounts
- **Multi-Platform Support** - Twitter, Facebook, Instagram, LinkedIn
- **Account Management** - Add, edit, delete social accounts
- **User Isolation** - Each user sees only their own accounts
- **Platform Validation** - Platform-specific content validation

### Automated Scheduling Engine
- **Laravel Scheduler** - Runs every minute to process due posts
- **Platform Validation** - Character limits, image requirements
- **Failure Handling** - Comprehensive error logging and status updates
- **Simulated Publishing** - Realistic social media API simulation

## 🛠 Tech Stack

- **Laravel 12** - Latest PHP framework
- **Laravel Sanctum** - API authentication
- **MySQL** - Database
- **Laravel Scheduler** - Automated task processing
- **Eloquent ORM** - Database relationships and queries

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Laravel 12

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd social-scheduler-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
Copy the environment file and configure your database:
```bash
cp .env.example .env
```

Update the `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_scheduler
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Demo Data
```bash
php artisan db:seed
```

This creates:
- Demo user: `demo@example.com` / `password`
- 4 social media accounts (Twitter, Facebook, Instagram, LinkedIn)
- 8 sample posts with various statuses

### 7. Start the Development Server
```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "demo@example.com",
  "password": "password"
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

### Posts Endpoints

#### List Posts
```http
GET /api/posts?page=1
Authorization: Bearer {token}
```

#### Create Post
```http
POST /api/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Hello World!",
  "schedule_time": "2024-01-15T10:00:00",
  "account_id": 1,
  "image_path": "https://example.com/image.jpg"
}
```

#### Update Post
```http
PUT /api/posts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Updated content",
  "schedule_time": "2024-01-15T11:00:00"
}
```

#### Delete Post
```http
DELETE /api/posts/{id}
Authorization: Bearer {token}
```

### Accounts Endpoints

#### List Accounts
```http
GET /api/accounts
Authorization: Bearer {token}
```

#### Create Account
```http
POST /api/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "My Twitter Account",
  "platform": "twitter",
  "username": "myusername",
  "avatar_url": "https://example.com/avatar.jpg"
}
```

#### Update Account
```http
PUT /api/accounts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Account Name",
  "username": "newusername"
}
```

#### Delete Account
```http
DELETE /api/accounts/{id}
Authorization: Bearer {token}
```

## ⚙️ Scheduling Engine

### Configuration
The scheduling engine is configured in `routes/console.php`:
```php
Schedule::command('posts:process')->everyMinute();
```

### Manual Execution
```bash
php artisan posts:process
```

### Platform Validations
- **Twitter**: 280 character limit
- **Instagram**: Requires image
- **LinkedIn**: 3000 character limit
- **Facebook**: No specific restrictions

### Status Tracking
- **draft**: Initial state
- **scheduled**: Ready for processing
- **published**: Successfully published
- **failed**: Publishing failed

## 🗄️ Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `created_at`, `updated_at` - Timestamps

### Posts Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `account_id` - Foreign key to accounts (nullable)
- `content` - Post content (max 1000 chars)
- `image_path` - Optional image URL
- `schedule_time` - Scheduled publication time
- `status` - Enum: draft, scheduled, published, failed
- `published_at` - Actual publication time
- `publish_log` - Publishing result log
- `created_at`, `updated_at` - Timestamps

### Accounts Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `name` - Account display name
- `platform` - Enum: twitter, facebook, instagram, linkedin
- `username` - Platform username
- `avatar_url` - Profile image URL
- `is_active` - Account status
- `platform_data` - JSON for platform-specific data
- `created_at`, `updated_at` - Timestamps

## 🔒 Security Features

- **Laravel Sanctum** - Secure token-based authentication
- **CSRF Protection** - Cross-site request forgery protection
- **Input Validation** - Comprehensive request validation
- **User Isolation** - Users can only access their own data
- **Password Hashing** - Secure password storage
- **CORS Configuration** - Controlled cross-origin access

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Manual API Testing
```bash
# Test authentication
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password"}'

# Test protected endpoint
curl -X GET http://127.0.0.1:8000/api/accounts \
  -H "Authorization: Bearer {your_token}"
```

## 🚀 Deployment

### Production Setup
1. **Environment Configuration**
   ```bash
   cp .env.example .env
   # Update with production values
   ```

2. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

3. **Scheduler Setup**
   Add to crontab:
   ```bash
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Web Server Configuration**
   - Configure Apache/Nginx
   - Set up SSL certificates
   - Configure environment variables

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_DOMAIN=your-domain.com
```

## 🔗 Social Media API Integration

### Platform-Specific Requirements

#### Twitter API Integration
```bash
# Install Twitter API package
composer require abraham/twitteroauth

# Environment variables
TWITTER_CONSUMER_KEY=your_consumer_key
TWITTER_CONSUMER_SECRET=your_consumer_secret
TWITTER_ACCESS_TOKEN=your_access_token
TWITTER_ACCESS_TOKEN_SECRET=your_access_token_secret
```

**Setup Process:**
1. Apply for Twitter Developer Account at https://developer.twitter.com
2. Create a new app in the Twitter Developer Portal
3. Generate API keys and access tokens
4. Request elevated access for v2 API endpoints
5. Configure webhook URLs for real-time updates

#### Facebook Graph API Integration
```bash
# Install Facebook SDK
composer require facebook/graph-sdk

# Environment variables
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret
FACEBOOK_ACCESS_TOKEN=your_access_token
```

**Setup Process:**
1. Create Facebook App at https://developers.facebook.com
2. Submit app for review (required for publishing)
3. Configure app permissions (pages_manage_posts, pages_read_engagement)
4. Generate long-lived access tokens
5. Set up webhooks for page events

#### Instagram Basic Display API
```bash
# Environment variables
INSTAGRAM_APP_ID=your_app_id
INSTAGRAM_APP_SECRET=your_app_secret
INSTAGRAM_ACCESS_TOKEN=your_access_token
```

**Setup Process:**
1. Create Instagram Basic Display app
2. Connect Instagram Business account
3. Configure app permissions (user_profile, user_media)
4. Generate access tokens with proper scopes
5. Set up webhooks for media updates

#### LinkedIn API Integration
```bash
# Install LinkedIn SDK
composer require linkedin/linkedin

# Environment variables
LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
LINKEDIN_ACCESS_TOKEN=your_access_token
```

**Setup Process:**
1. Create LinkedIn App at https://www.linkedin.com/developers
2. Configure OAuth 2.0 settings
3. Request API permissions (w_member_social, r_liteprofile)
4. Generate access tokens
5. Set up webhook endpoints

### Third-Party Service Integration

#### Buffer API Integration
```bash
# Install Buffer SDK
composer require bufferapp/buffer-php

# Environment variables
BUFFER_ACCESS_TOKEN=your_access_token
BUFFER_CLIENT_ID=your_client_id
BUFFER_CLIENT_SECRET=your_client_secret
```

**Implementation:**
```php
// Example Buffer integration
private function publishViaBuffer(Post $post)
{
    $client = new \Buffer\BufferApp([
        'access_token' => env('BUFFER_ACCESS_TOKEN'),
    ]);
    
    $response = $client->post('updates/create', [
        'profile_ids' => [$post->account->buffer_profile_id],
        'text' => $post->content,
        'scheduled_at' => $post->schedule_time,
        'media' => $post->image_path ? ['photo' => $post->image_path] : null,
    ]);
    
    return $response;
}
```

#### Hootsuite API Integration
```bash
# Environment variables
HOOTSUITE_CLIENT_ID=your_client_id
HOOTSUITE_CLIENT_SECRET=your_client_secret
HOOTSUITE_ACCESS_TOKEN=your_access_token
```

### Webhook Implementation

#### Webhook Endpoints
```php
// routes/api.php
Route::post('/webhooks/twitter', [WebhookController::class, 'twitter']);
Route::post('/webhooks/facebook', [WebhookController::class, 'facebook']);
Route::post('/webhooks/instagram', [WebhookController::class, 'instagram']);
Route::post('/webhooks/linkedin', [WebhookController::class, 'linkedin']);
```

#### Webhook Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function twitter(Request $request)
    {
        // Verify webhook signature
        $this->verifyTwitterSignature($request);
        
        $data = $request->all();
        
        // Update post status based on webhook data
        if (isset($data['tweet_create_events'])) {
            foreach ($data['tweet_create_events'] as $tweet) {
                $post = Post::where('twitter_tweet_id', $tweet['id_str'])->first();
                if ($post) {
                    $post->markAsPublished("Tweet published successfully");
                }
            }
        }
        
        return response()->json(['status' => 'success']);
    }
    
    public function facebook(Request $request)
    {
        // Handle Facebook webhook verification
        if ($request->has('hub_mode') && $request->hub_mode === 'subscribe') {
            return response($request->hub_challenge);
        }
        
        $data = $request->all();
        
        // Process Facebook webhook events
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        // Update post status based on Facebook events
                        $this->processFacebookEvent($change);
                    }
                }
            }
        }
        
        return response()->json(['status' => 'success']);
    }
    
    private function verifyTwitterSignature(Request $request)
    {
        $signature = $request->header('X-Twitter-Webhooks-Signature');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, env('TWITTER_WEBHOOK_SECRET'));
        
        if (!hash_equals($expectedSignature, $signature)) {
            abort(401, 'Invalid signature');
        }
    }
}
```

### Rate Limiting & Error Handling

#### Rate Limiting Implementation
```php
// app/Http/Middleware/SocialMediaRateLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SocialMediaRateLimit
{
    protected $limiter;
    
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }
    
    public function handle(Request $request, Closure $next, $platform = null)
    {
        $key = "social_media_{$platform}_" . $request->user()->id;
        
        if ($this->limiter->tooManyAttempts($key, $this->getMaxAttempts($platform))) {
            return response()->json([
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }
        
        $this->limiter->hit($key, $this->getDecayMinutes($platform));
        
        return $next($request);
    }
    
    private function getMaxAttempts($platform)
    {
        return [
            'twitter' => 300,    // 300 requests per 15 minutes
            'facebook' => 200,   // 200 requests per hour
            'instagram' => 100,  // 100 requests per hour
            'linkedin' => 100,   // 100 requests per day
        ][$platform] ?? 100;
    }
    
    private function getDecayMinutes($platform)
    {
        return [
            'twitter' => 15,
            'facebook' => 60,
            'instagram' => 60,
            'linkedin' => 1440, // 24 hours
        ][$platform] ?? 60;
    }
}
```

#### Comprehensive Error Handling
```php
// app/Exceptions/SocialMediaException.php
<?php

namespace App\Exceptions;

use Exception;

class SocialMediaException extends Exception
{
    protected $platform;
    protected $postId;
    protected $retryable;
    
    public function __construct($message, $platform = null, $postId = null, $retryable = true)
    {
        parent::__construct($message);
        $this->platform = $platform;
        $this->postId = $postId;
        $this->retryable = $retryable;
    }
    
    public function getPlatform()
    {
        return $this->platform;
    }
    
    public function getPostId()
    {
        return $this->postId;
    }
    
    public function isRetryable()
    {
        return $this->retryable;
    }
}

// Enhanced ProcessScheduledPosts command
private function processPost(Post $post)
{
    $maxRetries = 3;
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            $this->publishToSocialMedia($post);
            $post->markAsPublished("Published successfully on attempt " . ($attempt + 1));
            return;
        } catch (SocialMediaException $e) {
            $attempt++;
            
            if (!$e->isRetryable() || $attempt >= $maxRetries) {
                $post->markAsFailed("Failed after {$attempt} attempts: " . $e->getMessage());
                throw $e;
            }
            
            // Exponential backoff
            sleep(pow(2, $attempt));
        }
    }
}
```

### Production Environment Variables
```env
# Twitter API
TWITTER_CONSUMER_KEY=your_consumer_key
TWITTER_CONSUMER_SECRET=your_consumer_secret
TWITTER_ACCESS_TOKEN=your_access_token
TWITTER_ACCESS_TOKEN_SECRET=your_access_token_secret
TWITTER_WEBHOOK_SECRET=your_webhook_secret

# Facebook Graph API
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret
FACEBOOK_ACCESS_TOKEN=your_access_token
FACEBOOK_WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token

# Instagram API
INSTAGRAM_APP_ID=your_app_id
INSTAGRAM_APP_SECRET=your_app_secret
INSTAGRAM_ACCESS_TOKEN=your_access_token

# LinkedIn API
LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
LINKEDIN_ACCESS_TOKEN=your_access_token

# Third-Party Services
BUFFER_ACCESS_TOKEN=your_buffer_token
HOOTSUITE_CLIENT_ID=your_hootsuite_client_id
HOOTSUITE_CLIENT_SECRET=your_hootsuite_client_secret
HOOTSUITE_ACCESS_TOKEN=your_hootsuite_access_token

# Webhook URLs
WEBHOOK_BASE_URL=https://your-domain.com/api/webhooks
```

## 📝 Development

### Code Quality
```bash
# Run Laravel Pint for code formatting
composer run pint

# Run PHPStan for static analysis
composer run phpstan
```

### Database Management
```bash
# Reset database with fresh data
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_new_table

# Rollback migrations
php artisan migrate:rollback
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🆘 Support

For support, please open an issue in the GitHub repository or contact the development team.

---

**Note**: This is the backend API for the Social Scheduler application. For the frontend, see the Vue.js repository.
