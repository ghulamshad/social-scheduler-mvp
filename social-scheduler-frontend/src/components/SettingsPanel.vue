<template>
  <div class="settings-panel">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        Settings
      </h2>
      <p class="text-gray-600 dark:text-gray-400">
        Customize your application preferences
      </p>
    </div>

    <div class="space-y-6">
      <!-- Appearance Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Appearance</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Choose your preferred theme</p>
            </div>
            <select
              v-model="settings.theme"
              @change="updateSetting('theme', settings.theme)"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            >
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="auto">Auto</option>
            </select>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Compact Mode</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Use compact layout for more content</p>
            </div>
            <button
              @click="toggleSetting('compactMode')"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="settings.compactMode ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="settings.compactMode ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notifications</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Notifications</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Receive notifications for important events</p>
            </div>
            <button
              @click="toggleSetting('notificationsEnabled')"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="settings.notificationsEnabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="settings.notificationsEnabled ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Notifications</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Receive notifications via email</p>
            </div>
            <button
              @click="toggleSetting('emailNotifications')"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="settings.emailNotifications ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="settings.emailNotifications ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Refresh</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Automatically refresh data</p>
            </div>
            <button
              @click="toggleSetting('autoRefresh')"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="settings.autoRefresh ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="settings.autoRefresh ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Refresh Interval</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">How often to refresh data (seconds)</p>
            </div>
            <input
              v-model.number="settings.refreshInterval"
              @change="updateSetting('refreshInterval', settings.refreshInterval)"
              type="number"
              min="30"
              max="300"
              class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            />
          </div>
        </div>
      </div>

      <!-- Analytics Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Analytics</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Analytics</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Display analytics dashboard</p>
            </div>
            <button
              @click="toggleSetting('showAnalytics')"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="settings.showAnalytics ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="settings.showAnalytics ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
        </div>
      </div>

      <!-- General Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Your local timezone</p>
            </div>
            <select
              v-model="settings.timezone"
              @change="updateSetting('timezone', settings.timezone)"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            >
              <option value="UTC">UTC</option>
              <option value="America/New_York">Eastern Time</option>
              <option value="America/Chicago">Central Time</option>
              <option value="America/Denver">Mountain Time</option>
              <option value="America/Los_Angeles">Pacific Time</option>
              <option value="Europe/London">London</option>
              <option value="Europe/Paris">Paris</option>
              <option value="Asia/Tokyo">Tokyo</option>
            </select>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
              <p class="text-xs text-gray-500 dark:text-gray-400">Interface language</p>
            </div>
            <select
              v-model="settings.language"
              @change="updateSetting('language', settings.language)"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            >
              <option value="en">English</option>
              <option value="es">Español</option>
              <option value="fr">Français</option>
              <option value="de">Deutsch</option>
              <option value="ja">日本語</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between">
        <button
          @click="resetSettings"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
        >
          Reset to Defaults
        </button>
        
        <button
          @click="saveSettings"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Save Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

// Types
interface Settings {
  theme: string;
  compactMode: boolean;
  notificationsEnabled: boolean;
  emailNotifications: boolean;
  autoRefresh: boolean;
  refreshInterval: number;
  showAnalytics: boolean;
  timezone: string;
  language: string;
}

// State
const settings = ref<Settings>({
  theme: 'auto',
  compactMode: false,
  notificationsEnabled: true,
  emailNotifications: false,
  autoRefresh: true,
  refreshInterval: 60,
  showAnalytics: true,
  timezone: 'UTC',
  language: 'en',
});

// Methods
const loadSettings = async () => {
  try {
    // TODO: Load settings from API
    const savedSettings = localStorage.getItem('userSettings');
    if (savedSettings) {
      settings.value = { ...settings.value, ...JSON.parse(savedSettings) };
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

const saveSettings = async () => {
  try {
    // TODO: Save settings to API
    localStorage.setItem('userSettings', JSON.stringify(settings.value));
    console.log('Settings saved successfully');
  } catch (error) {
    console.error('Failed to save settings:', error);
  }
};

const updateSetting = (key: keyof Settings, value: any) => {
  (settings.value as any)[key] = value;
  saveSettings();
};

const toggleSetting = (key: keyof Settings) => {
  const currentValue = settings.value[key];
  if (typeof currentValue === 'boolean') {
    (settings.value as any)[key] = !currentValue;
    saveSettings();
  }
};

const resetSettings = () => {
  settings.value = {
    theme: 'auto',
    compactMode: false,
    notificationsEnabled: true,
    emailNotifications: false,
    autoRefresh: true,
    refreshInterval: 60,
    showAnalytics: true,
    timezone: 'UTC',
    language: 'en',
  };
  saveSettings();
};

// Lifecycle
onMounted(() => {
  loadSettings();
});
</script> 