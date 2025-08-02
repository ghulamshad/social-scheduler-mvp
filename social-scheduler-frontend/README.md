# Social Scheduler Frontend

A modern Vue 3 frontend application for the Social Scheduler, built with TypeScript, Vite, Tailwind CSS, and Pinia for state management. Features a responsive dashboard interface for managing social media posts and accounts.

## 🚀 Features

### Authentication & User Experience
- **Vue Router** - Client-side routing with authentication guards
- **Pinia State Management** - Reactive authentication store
- **Token Management** - Automatic token storage and refresh
- **Protected Routes** - Secure navigation with auth checks
- **Responsive Design** - Mobile-first approach with Tailwind CSS

### Dashboard Interface
- **Real-time Statistics** - Live post and account counts
- **Post Management** - Create, edit, delete scheduled posts
- **Account Management** - Add, edit, delete social media accounts
- **Status Tracking** - Visual status indicators for posts
- **Image Support** - Optional image URLs for posts

### UI/UX Components
- **Modern Design** - Professional dashboard layout
- **Ant Design Icons** - Consistent iconography
- **Loading States** - Smooth loading animations
- **Error Handling** - User-friendly error messages
- **Form Validation** - Real-time input validation

## 🛠 Tech Stack

- **Vue 3** - Progressive JavaScript framework
- **TypeScript** - Type-safe development
- **Vite** - Fast build tool and dev server
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Axios** - HTTP client for API communication
- **Ant Design Icons** - Professional icon set

## 📋 Requirements

- Node.js 18+
- npm or yarn
- Modern web browser

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd social-scheduler-frontend
```

### 2. Install Dependencies
```bash
npm install
```

### 3. Environment Configuration
Create a `.env` file in the root directory:
```env
VITE_API_URL=http://127.0.0.1:8000/api
```

### 4. Start Development Server
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

## 📁 Project Structure

```
src/
├── components/          # Reusable Vue components
│   ├── AccountManager.vue
│   ├── PostList.vue
│   ├── SchedulerForm.vue
│   └── StatCard.vue
├── pages/              # Route components
│   ├── Dashboard.vue
│   └── Login.vue
├── router/             # Vue Router configuration
│   └── index.ts
├── services/           # API and external services
│   └── api.ts
├── store/              # Pinia state management
│   └── auth.ts
├── assets/             # Static assets
├── style.css           # Global styles
└── main.ts            # Application entry point
```

## 🎨 Components

### Pages

#### Login.vue
- **Purpose**: Authentication interface
- **Features**: 
  - User registration and login
  - Form validation
  - Error handling
  - Responsive design
  - Demo credentials display

#### Dashboard.vue
- **Purpose**: Main application interface
- **Features**:
  - Navigation header with user info
  - Statistics cards
  - Component integration
  - Logout functionality

### Components

#### AccountManager.vue
- **Purpose**: Social media account management
- **Features**:
  - List all user accounts
  - Add new accounts
  - Edit existing accounts
  - Delete accounts
  - Platform-specific icons
  - Form validation

#### PostList.vue
- **Purpose**: Display and manage scheduled posts
- **Features**:
  - Paginated post listing
  - Status indicators
  - Edit/delete functionality
  - Image previews
  - Publish logs
  - Refresh capability

#### SchedulerForm.vue
- **Purpose**: Create new scheduled posts
- **Features**:
  - Content editor
  - Schedule time picker
  - Account selection
  - Image URL input
  - Form validation
  - Success/error feedback

#### StatCard.vue
- **Purpose**: Display statistics
- **Features**:
  - Dynamic icon rendering
  - Color-coded backgrounds
  - Responsive design
  - Reusable component

## 🔐 Authentication Flow

### Login Process
1. User enters credentials
2. API call to `/api/login`
3. Token stored in localStorage
4. User redirected to dashboard
5. Token automatically included in future requests

### Route Protection
- **Navigation Guards**: Check authentication before route access
- **Automatic Redirects**: Unauthenticated users redirected to login
- **Token Validation**: Automatic token validation on app load

### State Management
```typescript
// Auth store structure
interface AuthStore {
  user: User | null
  token: string
  loading: boolean
  login(email: string, password: string): Promise<void>
  register(name: string, email: string, password: string): Promise<void>
  logout(): Promise<void>
  checkAuth(): Promise<void>
}
```

## 🌐 API Integration

### Axios Configuration
```typescript
// Base configuration
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - adds auth token
api.interceptors.request.use((config) => {
  const authStore = useAuthStore();
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }
  return config;
});

// Response interceptor - handles auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore();
      authStore.logout();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### API Endpoints Used
- `POST /api/login` - User authentication
- `POST /api/register` - User registration
- `GET /api/user` - Get current user
- `GET /api/posts` - List user posts
- `POST /api/posts` - Create new post
- `PUT /api/posts/{id}` - Update post
- `DELETE /api/posts/{id}` - Delete post
- `GET /api/accounts` - List user accounts
- `POST /api/accounts` - Create new account
- `PUT /api/accounts/{id}` - Update account
- `DELETE /api/accounts/{id}` - Delete account

## 🎨 Styling & Design

### Tailwind CSS Configuration
```javascript
// tailwind.config.js
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'bounce-in': 'bounceIn 0.6s ease-out',
      },
    },
  },
  plugins: [],
} satisfies Config;
```

### Custom CSS Classes
```css
/* Global utility classes */
.btn-primary {
  background-color: theme(colors.blue.600);
  color: white;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.input-field {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid theme(colors.gray.300);
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px theme(colors.gray.100);
}
```

## 📱 Responsive Design

### Breakpoints
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

### Mobile-First Approach
- All components designed for mobile first
- Progressive enhancement for larger screens
- Touch-friendly interface elements
- Optimized navigation for mobile

## 🧪 Development

### Available Scripts
```bash
# Development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Type checking
npm run type-check

# Linting
npm run lint
```

### TypeScript Configuration
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["src/*"]
    }
  }
}
```

### Vite Configuration
```typescript
// vite.config.ts
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  esbuild: {
    target: 'esnext',
  },
  optimizeDeps: {
    include: ['vue', 'vue-router', 'pinia'],
  },
});
```

## 🚀 Deployment

### Build for Production
```bash
npm run build
```

### Environment Variables for Production
```env
VITE_API_URL=https://your-api-domain.com/api
VITE_APP_TITLE=Social Scheduler
VITE_APP_VERSION=1.0.0
```

### Deployment Options

#### 1. Static Hosting (Recommended)
**Netlify:**
```bash
# Install Netlify CLI
npm install -g netlify-cli

# Build and deploy
npm run build
netlify deploy --prod --dir=dist
```

**Vercel:**
```bash
# Install Vercel CLI
npm install -g vercel

# Deploy
vercel --prod
```

**GitHub Pages:**
```bash
# Add to package.json
{
  "scripts": {
    "deploy": "npm run build && gh-pages -d dist"
  }
}

# Install gh-pages
npm install --save-dev gh-pages

# Deploy
npm run deploy
```

#### 2. CDN Deployment
**AWS CloudFront:**
```bash
# Upload to S3
aws s3 sync dist/ s3://your-bucket-name

# Configure CloudFront distribution
# Point to S3 bucket as origin
```

**Cloudflare Pages:**
```bash
# Connect GitHub repository
# Configure build settings:
# Build command: npm run build
# Build output directory: dist
```

#### 3. Traditional Web Server
**Apache Configuration:**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/dist
    
    <Directory /var/www/html/dist>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Handle Vue Router
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</VirtualHost>
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Build Output
The build process creates a `dist` folder containing:
- Optimized HTML, CSS, and JavaScript
- Static assets
- Service worker (if configured)
- Source maps (for debugging)

### Performance Optimization

#### Code Splitting
```typescript
// Lazy load components
const Dashboard = () => import('@/pages/Dashboard.vue')
const Login = () => import('@/pages/Login.vue')
```

#### Image Optimization
```bash
# Install image optimization
npm install --save-dev vite-plugin-imagemin

# Configure in vite.config.ts
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { ViteImageOptimize } from 'vite-plugin-imagemin'

export default defineConfig({
  plugins: [
    vue(),
    ViteImageOptimize({
      gifsicle: { optimizationLevel: 7 },
      mozjpeg: { quality: 80 },
      pngquant: { quality: [0.65, 0.8] },
      svgo: {
        plugins: [
          { name: 'removeViewBox', active: false },
          { name: 'removeEmptyAttrs', active: false }
        ]
      }
    })
  ]
})
```

#### Bundle Analysis
```bash
# Install bundle analyzer
npm install --save-dev rollup-plugin-visualizer

# Analyze bundle
npm run build
npx vite-bundle-analyzer dist
```

### Security Considerations

#### Content Security Policy
```html
<!-- index.html -->
<meta http-equiv="Content-Security-Policy" 
      content="default-src 'self'; 
               script-src 'self' 'unsafe-inline' 'unsafe-eval'; 
               style-src 'self' 'unsafe-inline'; 
               img-src 'self' data: https:; 
               connect-src 'self' https://your-api-domain.com;">
```

#### Environment Variable Security
```typescript
// Only expose necessary variables
// vite.config.ts
export default defineConfig({
  define: {
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
  }
})
```

### Monitoring & Analytics

#### Error Tracking
```typescript
// Install Sentry
npm install @sentry/vue @sentry/tracing

// main.ts
import * as Sentry from '@sentry/vue'

Sentry.init({
  app,
  dsn: 'your-sentry-dsn',
  integrations: [
    new Sentry.BrowserTracing()
  ],
  tracesSampleRate: 1.0
})
```

#### Performance Monitoring
```typescript
// Install web-vitals
npm install web-vitals

// Report Core Web Vitals
import { getCLS, getFID, getFCP, getLCP, getTTFB } from 'web-vitals'

getCLS(console.log)
getFID(console.log)
getFCP(console.log)
getLCP(console.log)
getTTFB(console.log)
```

## 🔧 Configuration

### Environment Variables
```env
# API Configuration
VITE_API_URL=http://localhost:8000/api

# Build Configuration
VITE_APP_TITLE=Social Scheduler
VITE_APP_VERSION=1.0.0
```

### Development vs Production
- **Development**: Hot module replacement, source maps
- **Production**: Minified code, optimized assets

## 🐛 Troubleshooting

### Common Issues

#### Build Errors
```bash
# Clear cache and reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

#### API Connection Issues
- Verify `VITE_API_URL` in `.env`
- Check CORS configuration on backend
- Ensure backend server is running

#### TypeScript Errors
```bash
# Run type checking
npm run type-check

# Fix auto-fixable issues
npm run lint -- --fix
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

**Note**: This is the frontend application for the Social Scheduler. For the backend API, see the Laravel repository.
