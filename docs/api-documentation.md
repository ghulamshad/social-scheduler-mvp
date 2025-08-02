# 📖 API Documentation - Social Scheduler

> **Complete REST API reference for the Social Scheduler platform**

## 🚀 Base URL

```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

## 🔐 Authentication

All API endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```http
Authorization: Bearer {your-token}
```

### Authentication Endpoints

#### Register User
```http
POST /api/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    },
    "token": "1|abc123def456..."
  },
  "message": "User registered successfully"
}
```

#### Login User
```http
POST /api/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123def456..."
  },
  "message": "Login successful"
}
```

#### Logout User
```http
POST /api/logout
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /api/user
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

#### Update User Profile
```http
PUT /api/profile
```

**Request Body:**
```json
{
  "name": "John Smith",
  "email": "johnsmith@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "updated_at": "2024-01-15T11:00:00Z"
  },
  "message": "Profile updated successfully"
}
```

## 📝 Posts Management

### Get All Posts
```http
GET /api/posts
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status` (optional): Filter by status (draft, scheduled, published, failed)
- `platform` (optional): Filter by platform (twitter, facebook, instagram, linkedin)
- `team_id` (optional): Filter by team ID
- `approval_status` (optional): Filter by approval status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "Exciting news! Our new feature is live! 🚀",
      "schedule_time": "2024-01-15T14:30:00Z",
      "status": "scheduled",
      "platform": "twitter",
      "hashtags": ["#innovation", "#tech"],
      "tone": "professional",
      "approval_status": "approved",
      "character_count": 45,
      "passes_validation": true,
      "account": {
        "id": 1,
        "name": "Company Twitter",
        "username": "@company",
        "platform": "twitter"
      },
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### Create Post
```http
POST /api/posts
```

**Request Body:**
```json
{
  "content": "Exciting news! Our new feature is live! 🚀",
  "schedule_time": "2024-01-15T14:30:00Z",
  "account_id": 1,
  "hashtags": ["#innovation", "#tech"],
  "tone": "professional",
  "team_id": 1,
  "requires_approval": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "content": "Exciting news! Our new feature is live! 🚀",
    "schedule_time": "2024-01-15T14:30:00Z",
    "status": "draft",
    "approval_status": "pending_approval",
    "created_at": "2024-01-15T10:00:00Z"
  },
  "message": "Post created successfully"
}
```

### Get Single Post
```http
GET /api/posts/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "content": "Exciting news! Our new feature is live! 🚀",
    "schedule_time": "2024-01-15T14:30:00Z",
    "status": "scheduled",
    "platform": "twitter",
    "hashtags": ["#innovation", "#tech"],
    "tone": "professional",
    "approval_status": "approved",
    "character_count": 45,
    "passes_validation": true,
    "validation_errors": [],
    "performance_metrics": {
      "engagement_rate": 5.2,
      "reach": 1200,
      "impressions": 1500
    },
    "account": {
      "id": 1,
      "name": "Company Twitter",
      "username": "@company",
      "platform": "twitter"
    },
    "team": {
      "id": 1,
      "name": "Marketing Team"
    },
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

### Update Post
```http
PUT /api/posts/{id}
```

**Request Body:**
```json
{
  "content": "Updated content with new hashtags! 🚀 #innovation #tech #startup",
  "schedule_time": "2024-01-15T15:00:00Z",
  "hashtags": ["#innovation", "#tech", "#startup"],
  "tone": "casual"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "content": "Updated content with new hashtags! 🚀 #innovation #tech #startup",
    "schedule_time": "2024-01-15T15:00:00Z",
    "updated_at": "2024-01-15T11:00:00Z"
  },
  "message": "Post updated successfully"
}
```

### Delete Post
```http
DELETE /api/posts/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Post deleted successfully"
}
```

### Approve Post
```http
POST /api/posts/{id}/approve
```

**Request Body:**
```json
{
  "comments": "Looks good! Approved for publishing."
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "approval_status": "approved",
    "approved_at": "2024-01-15T11:30:00Z",
    "approved_by": {
      "id": 2,
      "name": "Jane Manager"
    }
  },
  "message": "Post approved successfully"
}
```

### Reject Post
```http
POST /api/posts/{id}/reject
```

**Request Body:**
```json
{
  "comments": "Please revise the tone to be more professional.",
  "changes_requested": {
    "tone": "professional",
    "content": "Please make it more formal"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "approval_status": "rejected",
    "comments": "Please revise the tone to be more professional."
  },
  "message": "Post rejected successfully"
}
```

## 👥 Team Management

### Get User's Teams
```http
GET /api/teams
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Marketing Team",
      "description": "Main marketing team for social media",
      "logo_url": "https://example.com/logo.png",
      "subdomain": "marketing",
      "owner_id": 1,
      "is_active": true,
      "members_count": 5,
      "pending_approvals": 3,
      "created_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

### Create Team
```http
POST /api/teams
```

**Request Body:**
```json
{
  "name": "New Marketing Team",
  "description": "Team for handling social media marketing",
  "subdomain": "new-marketing"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "New Marketing Team",
    "description": "Team for handling social media marketing",
    "subdomain": "new-marketing",
    "owner_id": 1,
    "created_at": "2024-01-15T12:00:00Z"
  },
  "message": "Team created successfully"
}
```

### Get Team Details
```http
GET /api/teams/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Marketing Team",
    "description": "Main marketing team for social media",
    "logo_url": "https://example.com/logo.png",
    "subdomain": "marketing",
    "owner_id": 1,
    "is_active": true,
    "members": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "role": "owner",
        "joined_at": "2024-01-15T10:00:00Z"
      }
    ],
    "posts": [
      {
        "id": 1,
        "content": "Team post content",
        "status": "scheduled"
      }
    ],
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

### Invite Team Member
```http
POST /api/teams/{id}/invite
```

**Request Body:**
```json
{
  "email": "newmember@example.com",
  "role": "editor"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Invitation sent successfully"
}
```

### Update Member Role
```http
PUT /api/teams/{team_id}/members/{member_id}
```

**Request Body:**
```json
{
  "role": "admin"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "role": "admin",
    "updated_at": "2024-01-15T12:30:00Z"
  },
  "message": "Member role updated successfully"
}
```

### Remove Team Member
```http
DELETE /api/teams/{team_id}/members/{member_id}
```

**Response:**
```json
{
  "success": true,
  "message": "Member removed successfully"
}
```

## 🤖 AI Content Generation

### Generate Content
```http
POST /api/ai/generate-content
```

**Request Body:**
```json
{
  "topic": "Product launch announcement",
  "platform": "twitter",
  "tone": "professional",
  "industry": "technology",
  "target_audience": "developers",
  "ai_model": "gpt-4"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "content": "🚀 Exciting news! We're thrilled to announce the launch of our revolutionary new platform that's going to transform how developers work. Built with cutting-edge technology and designed for maximum efficiency. #TechLaunch #Innovation #DeveloperTools",
    "hashtags": ["#TechLaunch", "#Innovation", "#DeveloperTools"],
    "character_count": 280,
    "platform_optimized": true,
    "usage": {
      "tokens_used": 150,
      "cost": 0.003,
      "model": "gpt-4"
    }
  }
}
```

### Generate Hashtags
```http
POST /api/ai/generate-hashtags
```

**Request Body:**
```json
{
  "content": "Our new AI-powered tool helps developers write better code",
  "platform": "twitter",
  "industry": "technology"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "hashtags": ["#AI", "#DeveloperTools", "#Coding", "#TechInnovation", "#Programming"],
    "trending": ["#AI", "#DeveloperTools"],
    "niche": ["#Coding", "#Programming"],
    "usage": {
      "tokens_used": 50,
      "cost": 0.001,
      "model": "gpt-4"
    }
  }
}
```

### Generate Content Ideas
```http
POST /api/ai/generate-ideas
```

**Request Body:**
```json
{
  "industry": "technology",
  "platform": "linkedin",
  "content_type": "thought_leadership",
  "target_audience": "senior_developers"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "ideas": [
      "The Future of AI in Software Development",
      "Why Code Quality Matters More Than Speed",
      "Building Scalable Systems: Lessons Learned",
      "The Impact of Microservices on Development Teams"
    ],
    "usage": {
      "tokens_used": 200,
      "cost": 0.004,
      "model": "gpt-4"
    }
  }
}
```

### Get AI Usage Statistics
```http
GET /api/ai/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_generations": 150,
    "total_tokens": 25000,
    "total_cost": 0.50,
    "this_month": {
      "generations": 45,
      "tokens": 7500,
      "cost": 0.15
    },
    "by_model": {
      "gpt-4": {
        "generations": 100,
        "tokens": 20000,
        "cost": 0.40
      },
      "claude": {
        "generations": 50,
        "tokens": 5000,
        "cost": 0.10
      }
    }
  }
}
```

## 📊 Analytics

### Get Analytics Summary
```http
GET /api/analytics/summary
```

**Query Parameters:**
- `start_date` (optional): Start date for analytics
- `end_date` (optional): End date for analytics
- `platform` (optional): Filter by platform

**Response:**
```json
{
  "success": true,
  "data": {
    "total_posts": 150,
    "total_engagement": 25000,
    "total_reach": 150000,
    "avg_engagement_rate": 16.7,
    "platform_breakdown": {
      "twitter": {
        "posts": 60,
        "engagement": 12000,
        "rate": 20.0
      },
      "linkedin": {
        "posts": 40,
        "engagement": 8000,
        "rate": 20.0
      },
      "facebook": {
        "posts": 30,
        "engagement": 3000,
        "rate": 10.0
      },
      "instagram": {
        "posts": 20,
        "engagement": 2000,
        "rate": 10.0
      }
    },
    "top_posts": [
      {
        "id": 1,
        "content": "Our revolutionary new feature is here! 🚀",
        "platform": "twitter",
        "engagement": 2500,
        "engagement_rate": 25.0,
        "published_at": "2024-01-10T10:00:00Z"
      }
    ]
  }
}
```

### Get Post Analytics
```http
GET /api/posts/{id}/analytics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 1,
    "platform": "twitter",
    "platform_post_id": "123456789",
    "likes": 150,
    "shares": 25,
    "comments": 10,
    "clicks": 45,
    "impressions": 2000,
    "reach": 1800,
    "engagement_rate": 10.3,
    "additional_metrics": {
      "retweets": 15,
      "quote_tweets": 5
    },
    "last_updated": "2024-01-15T12:00:00Z"
  }
}
```

## 🔔 Notifications

### Get Notifications
```http
GET /api/notifications
```

**Query Parameters:**
- `page` (optional): Page number
- `type` (optional): Filter by type (success, error, warning, info)
- `category` (optional): Filter by category (post, account, system, api)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "success",
      "title": "Post Published",
      "message": "Your post has been successfully published to Twitter",
      "category": "post",
      "data": {
        "post_id": 1,
        "platform": "twitter"
      },
      "read_at": null,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "unread_count": 5,
    "total_count": 25
  }
}
```

### Mark Notification as Read
```http
PATCH /api/notifications/{id}/read
```

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### Mark All Notifications as Read
```http
PATCH /api/notifications/mark-all-read
```

**Response:**
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

### Delete Notification
```http
DELETE /api/notifications/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

### Clear All Notifications
```http
DELETE /api/notifications/clear-all
```

**Response:**
```json
{
  "success": true,
  "message": "All notifications cleared"
}
```

## ⚙️ Settings

### Get User Settings
```http
GET /api/settings
```

**Query Parameters:**
- `category` (optional): Filter by category (appearance, notifications, analytics, general)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key": "theme",
      "value": "dark",
      "type": "string",
      "category": "appearance",
      "description": "Application theme preference"
    },
    {
      "id": 2,
      "key": "notifications_enabled",
      "value": true,
      "type": "boolean",
      "category": "notifications",
      "description": "Enable push notifications"
    }
  ]
}
```

### Save Setting
```http
POST /api/settings
```

**Request Body:**
```json
{
  "key": "theme",
  "value": "light",
  "type": "string",
  "category": "appearance",
  "description": "Application theme preference"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "key": "theme",
    "value": "light",
    "type": "string",
    "category": "appearance",
    "description": "Application theme preference"
  },
  "message": "Setting saved successfully"
}
```

### Update Multiple Settings
```http
PUT /api/settings/bulk
```

**Request Body:**
```json
{
  "settings": [
    {
      "key": "theme",
      "value": "dark"
    },
    {
      "key": "notifications_enabled",
      "value": true
    },
    {
      "key": "timezone",
      "value": "America/New_York"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "updated_count": 3
  },
  "message": "Settings updated successfully"
}
```

### Get Settings by Category
```http
GET /api/settings/category/{category}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key": "theme",
      "value": "dark",
      "type": "string",
      "category": "appearance",
      "description": "Application theme preference"
    },
    {
      "id": 2,
      "key": "compact_mode",
      "value": false,
      "type": "boolean",
      "category": "appearance",
      "description": "Use compact layout"
    }
  ]
}
```

## 📱 Social Media Accounts

### Get User's Accounts
```http
GET /api/accounts
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Company Twitter",
      "username": "@company",
      "platform": "twitter",
      "platform_user_id": "123456789",
      "is_active": true,
      "profile_image": "https://example.com/profile.jpg",
      "follower_count": 5000,
      "created_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

### Create Account
```http
POST /api/accounts
```

**Request Body:**
```json
{
  "name": "Company LinkedIn",
  "username": "company-linkedin",
  "platform": "linkedin",
  "platform_user_id": "987654321",
  "profile_image": "https://example.com/linkedin-profile.jpg"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Company LinkedIn",
    "username": "company-linkedin",
    "platform": "linkedin",
    "platform_user_id": "987654321",
    "is_active": true,
    "created_at": "2024-01-15T12:00:00Z"
  },
  "message": "Account created successfully"
}
```

### Update Account
```http
PUT /api/accounts/{id}
```

**Request Body:**
```json
{
  "name": "Updated Company Twitter",
  "is_active": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Company Twitter",
    "is_active": false,
    "updated_at": "2024-01-15T12:30:00Z"
  },
  "message": "Account updated successfully"
}
```

### Delete Account
```http
DELETE /api/accounts/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```

## 🔍 Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Error
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Not Found Error
```json
{
  "success": false,
  "message": "Resource not found."
}
```

### Server Error
```json
{
  "success": false,
  "message": "Internal server error.",
  "error_code": "INTERNAL_ERROR"
}
```

## 📊 Rate Limiting

The API implements rate limiting to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **General endpoints**: 60 requests per minute
- **AI endpoints**: 10 requests per minute

When rate limited, you'll receive:

```json
{
  "success": false,
  "message": "Too many requests.",
  "retry_after": 60
}
```

## 🔐 Security Headers

All API responses include security headers:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`

## 📝 SDK Examples

### JavaScript/TypeScript
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://your-domain.com/api',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

// Create a post
const createPost = async (postData) => {
  try {
    const response = await api.post('/posts', postData);
    return response.data;
  } catch (error) {
    console.error('Error creating post:', error.response.data);
  }
};

// Generate AI content
const generateContent = async (prompt) => {
  try {
    const response = await api.post('/ai/generate-content', prompt);
    return response.data;
  } catch (error) {
    console.error('Error generating content:', error.response.data);
  }
};
```

### PHP
```php
<?php

$token = 'your-api-token';
$baseUrl = 'https://your-domain.com/api';

// Create a post
function createPost($postData) {
    global $token, $baseUrl;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/posts');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

### Python
```python
import requests

class SocialSchedulerAPI:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    
    def create_post(self, post_data):
        response = requests.post(
            f'{self.base_url}/posts',
            json=post_data,
            headers=self.headers
        )
        return response.json()
    
    def generate_content(self, prompt):
        response = requests.post(
            f'{self.base_url}/ai/generate-content',
            json=prompt,
            headers=self.headers
        )
        return response.json()

# Usage
api = SocialSchedulerAPI('https://your-domain.com/api', 'your-token')
post = api.create_post({
    'content': 'Hello World!',
    'schedule_time': '2024-01-15T14:30:00Z',
    'account_id': 1
})
```

## 📞 Support

For API support and questions:

- **Email**: api-support@your-domain.com
- **Documentation**: https://your-domain.com/docs/api
- **Status Page**: https://status.your-domain.com
- **GitHub Issues**: https://github.com/yourusername/social-scheduler/issues

---

**Last Updated**: January 15, 2024  
**API Version**: v1.0  
**Base URL**: https://your-domain.com/api 