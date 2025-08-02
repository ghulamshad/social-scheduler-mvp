// 📁 src/store/auth.ts
import { defineStore } from 'pinia';
import {api} from '@/services/api';
import { ref } from 'vue';

export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string>('');
  const loading = ref(false);

  const login = async (email: string, password: string) => {
    loading.value = true;
    try {
      const response = await api.post('/login', { email, password });
      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('token', token.value);
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Login failed');
    } finally {
      loading.value = false;
    }
  };

  const register = async (name: string, email: string, password: string, password_confirmation: string) => {
    loading.value = true;
    try {
      const response = await api.post('/register', { 
        name, 
        email, 
        password, 
        password_confirmation 
      });
      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('token', token.value);
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Registration failed');
    } finally {
      loading.value = false;
    }
  };

  const logout = async () => {
    try {
      await api.post('/logout');
    } catch (error) {
      // Ignore logout errors
    } finally {
      user.value = null;
      token.value = '';
      localStorage.removeItem('token');
    }
  };

  const checkAuth = async () => {
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
      token.value = savedToken;
      try {
        const response = await api.get('/user');
        user.value = response.data;
      } catch (error) {
        localStorage.removeItem('token');
        token.value = '';
      }
    }
  };

  return {
    user,
    token,
    loading,
    login,
    register,
    logout,
    checkAuth,
  };
});


