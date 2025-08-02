// 📁 src/store/theme.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';

export type Theme = 'light' | 'dark';

export const useThemeStore = defineStore('theme', () => {
  const theme = ref<Theme>('light');

  const initTheme = () => {
    // Check localStorage for saved theme
    const savedTheme = localStorage.getItem('theme') as Theme;
    if (savedTheme) {
      theme.value = savedTheme;
    } else {
      // Check system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      theme.value = prefersDark ? 'dark' : 'light';
    }
    applyTheme();
  };

  const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', theme.value);
    applyTheme();
  };

  const applyTheme = () => {
    const root = document.documentElement;
    if (theme.value === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  };

  return {
    theme,
    initTheme,
    toggleTheme,
    applyTheme,
  };
}); 