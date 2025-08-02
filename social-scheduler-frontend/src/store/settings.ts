import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export interface Setting {
  id?: number;
  key: string;
  value: any;
  type: 'string' | 'boolean' | 'integer' | 'json';
  category: 'general' | 'notifications' | 'api' | 'theme';
  description?: string;
}

export interface SettingsState {
  [key: string]: any;
}

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref<SettingsState>({});
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Computed properties for common settings
  const theme = computed(() => settings.value.theme || 'light');
  const notificationsEnabled = computed(() => settings.value.notifications_enabled !== false);
  const emailNotifications = computed(() => settings.value.email_notifications !== false);
  const timezone = computed(() => settings.value.timezone || 'UTC');
  const language = computed(() => settings.value.language || 'en');

  // Load settings from API
  const loadSettings = async (category?: string) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/settings', {
        params: { category }
      });
      settings.value = response.data.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to load settings';
      console.error('Settings load error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Save setting
  const saveSetting = async (key: string, value: any, category: string = 'general') => {
    loading.value = true;
    error.value = null;
    
    try {
      const type = typeof value === 'boolean' ? 'boolean' : 
                   typeof value === 'number' ? 'integer' : 
                   typeof value === 'object' ? 'json' : 'string';
      
      await axios.post('/api/settings', {
        key,
        value,
        type,
        category
      });
      
      settings.value[key] = value;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to save setting';
      console.error('Setting save error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Save multiple settings
  const saveMultipleSettings = async (newSettings: Record<string, any>, category: string = 'general') => {
    loading.value = true;
    error.value = null;
    
    try {
      await axios.post('/api/settings/bulk', {
        settings: newSettings,
        category
      });
      
      Object.assign(settings.value, newSettings);
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to save settings';
      console.error('Settings save error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Get setting value with fallback
  const getSetting = (key: string, defaultValue: any = null) => {
    return settings.value[key] ?? defaultValue;
  };

  // Set setting locally (without API call)
  const setSetting = (key: string, value: any) => {
    settings.value[key] = value;
  };

  // Initialize default settings
  const initializeDefaults = () => {
    const defaults = {
      theme: 'light',
      notifications_enabled: true,
      email_notifications: true,
      timezone: 'UTC',
      language: 'en',
      auto_refresh: true,
      refresh_interval: 30,
      show_analytics: true,
      compact_mode: false,
    };
    
    Object.entries(defaults).forEach(([key, value]) => {
      if (!(key in settings.value)) {
        settings.value[key] = value;
      }
    });
  };

  return {
    // State
    settings,
    loading,
    error,
    
    // Computed
    theme,
    notificationsEnabled,
    emailNotifications,
    timezone,
    language,
    
    // Actions
    loadSettings,
    saveSetting,
    saveMultipleSettings,
    getSetting,
    setSetting,
    initializeDefaults,
  };
}); 