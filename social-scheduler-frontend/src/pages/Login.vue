// 📁 src/pages/Login.vue
<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Decorative Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-100 rounded-full opacity-20"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-100 rounded-full opacity-20"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full opacity-10"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
      <!-- Logo/Brand Section -->
      <div class="text-center mb-8 animate-fade-in">
        <div class="mx-auto w-20 h-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl flex items-center justify-center mb-6 shadow-xl transform hover:scale-105 transition-transform duration-300">
          <ClockCircleOutlined class="w-10 h-10 text-white" />
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-3 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
          Social Scheduler
        </h1>
        <p class="text-gray-600 text-lg">Manage your social media posts with ease</p>
      </div>

      <!-- Auth Card -->
      <div class="card p-8 shadow-2xl backdrop-blur-sm bg-white/95 animate-slide-up">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-3">
            {{ isLogin ? 'Welcome back' : 'Create your account' }}
          </h2>
          <p class="text-gray-600">
            {{ isLogin ? 'Sign in to continue to your dashboard' : 'Start scheduling your social media posts' }}
          </p>
        </div>

        <!-- Toggle Mode -->
        <div class="flex bg-gray-100 rounded-xl p-1 mb-8 shadow-inner">
          <button
            @click="toggleMode"
            :class="[
              'flex-1 py-3 px-6 rounded-lg text-sm font-semibold transition-all duration-300 transform',
              isLogin
                ? 'bg-white text-gray-900 shadow-md scale-105'
                : 'text-gray-600 hover:text-gray-900 hover:scale-102'
            ]"
          >
            <LoginOutlined class="w-4 h-4 mr-2 inline" />
            Sign In
          </button>
          <button
            @click="toggleMode"
            :class="[
              'flex-1 py-3 px-6 rounded-lg text-sm font-semibold transition-all duration-300 transform',
              !isLogin
                ? 'bg-white text-gray-900 shadow-md scale-105'
                : 'text-gray-600 hover:text-gray-900 hover:scale-102'
            ]"
          >
            <UserAddOutlined class="w-4 h-4 mr-2 inline" />
            Sign Up
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Name field (only for registration) -->
          <div v-if="!isLogin" class="animate-fade-in">
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">
              <UserOutlined class="w-4 h-4 mr-2 inline" />
              Full Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="John Doe"
            />
          </div>

          <!-- Email field -->
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-3">
              <MailOutlined class="w-4 h-4 mr-2 inline" />
              Email address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="you@example.com"
            />
          </div>

          <!-- Password field -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-3">
              <LockOutlined class="w-4 h-4 mr-2 inline" />
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="••••••••"
            />
          </div>

          <!-- Confirm Password field (only for registration) -->
          <div v-if="!isLogin" class="animate-fade-in">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-3">
              <CheckCircleOutlined class="w-4 h-4 mr-2 inline" />
              Confirm Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="••••••••"
            />
          </div>

          <!-- Error Message -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-bounce-in flex items-center">
            <ExclamationCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
            {{ error }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary py-4 text-base font-semibold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <div v-if="loading" class="flex items-center justify-center">
              <LoadingOutlined class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
              {{ isLogin ? 'Signing in...' : 'Creating account...' }}
            </div>
            <span v-else class="flex items-center justify-center">
              <LoginOutlined class="w-5 h-5 mr-2" />
              {{ isLogin ? 'Sign In' : 'Create Account' }}
            </span>
          </button>
        </form>

        <!-- Demo Credentials -->
        <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
          <div class="flex items-center mb-3">
            <InfoCircleOutlined class="w-5 h-5 text-blue-600 mr-2" />
            <p class="text-sm font-semibold text-blue-800">Demo Account</p>
          </div>
          <div class="space-y-1">
            <p class="text-sm text-blue-700 flex items-center">
              <span class="font-medium mr-2">Email:</span> demo@example.com
            </p>
            <p class="text-sm text-blue-700 flex items-center">
              <span class="font-medium mr-2">Password:</span> password
            </p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center mt-8 animate-fade-in">
        <p class="text-sm text-gray-500">
          Built with ❤️ using <span class="font-semibold text-blue-600">Laravel</span> & <span class="font-semibold text-green-600">Vue.js</span>
        </p>
        <p class="text-xs text-gray-400 mt-2">Professional Portfolio Project</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/auth';
import {
  ClockCircleOutlined,
  LoginOutlined,
  UserAddOutlined,
  UserOutlined,
  MailOutlined,
  LockOutlined,
  CheckCircleOutlined,
  ExclamationCircleOutlined,
  LoadingOutlined,
  InfoCircleOutlined,
} from '@ant-design/icons-vue';

const router = useRouter();
const auth = useAuthStore();

const isLogin = ref(true);
const loading = ref(false);
const error = ref('');

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const toggleMode = () => {
  isLogin.value = !isLogin.value;
  error.value = '';
  // Reset form
  form.name = '';
  form.email = '';
  form.password = '';
  form.password_confirmation = '';
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';

  try {
    if (isLogin.value) {
      await auth.login(form.email, form.password);
    } else {
      await auth.register(form.name, form.email, form.password, form.password_confirmation);
    }
    router.push('/dashboard');
  } catch (err: any) {
    error.value = err.message || 'An error occurred';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  // Check if user is already authenticated
  await auth.checkAuth();
  if (auth.user) {
    router.push('/dashboard');
  }
});
</script>


