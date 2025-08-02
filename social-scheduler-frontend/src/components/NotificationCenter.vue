<template>
  <div class="notification-center">
    <!-- Notification Bell -->
    <div class="relative">
      <button
        @click="showDropdown = !showDropdown"
        class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
        :class="{ 'text-blue-600 dark:text-blue-400': hasUnread }"
      >
        <BellOutlined class="w-5 h-5" />
        <span
          class="absolute -top-1 -right-1 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          :class="unreadCount > 0 ? 'bg-red-500' : 'bg-gray-400'"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>

      <!-- Dropdown -->
      <div
        v-if="showDropdown"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Notifications
          </h3>
          <div class="flex items-center gap-2">
            <button
              @click="markAllAsRead"
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
              :disabled="unreadCount === 0"
            >
              Mark all read
            </button>
            <button
              @click="clearAll"
              class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            >
              Clear all
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="loading" class="p-4 text-center text-gray-500 dark:text-gray-400">
            <LoadingOutlined class="w-5 h-5 animate-spin mx-auto mb-2" />
            Loading notifications...
          </div>

          <div v-else-if="notifications && notifications.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read_at }"
            >
              <!-- Notification Header -->
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-2">
                  <!-- Type Icon -->
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center"
                    :class="getTypeClasses(notification.type)"
                  >
                    <component :is="getTypeIcon(notification.type)" class="w-4 h-4 text-white" />
                  </div>
                  
                  <!-- Title -->
                  <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ notification.title }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      {{ formatDate(notification.created_at) }}
                    </p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1">
                  <button
                    v-if="!notification.read_at"
                    @click="markAsRead(notification.id)"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                  >
                    Mark read
                  </button>
                  <button
                    @click="deleteNotification(notification.id)"
                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                  >
                    Delete
                  </button>
                </div>
              </div>

              <!-- Message -->
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                {{ notification.message }}
              </p>

              <!-- Category Badge -->
              <div class="mt-2">
                <span
                  class="inline-block px-2 py-1 text-xs rounded-full"
                  :class="getCategoryClasses(notification.category)"
                >
                  {{ notification.category }}
                </span>
              </div>
            </div>
          </div>

          <div v-else class="p-4 text-center text-gray-500 dark:text-gray-400">
            <BellOutlined class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
            <p>No notifications</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="loadMore"
            class="w-full text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
            :disabled="loading"
          >
            Load more
          </button>
        </div>
      </div>
    </div>

    <!-- Click outside to close -->
    <div
      v-if="showDropdown"
      @click="showDropdown = false"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  BellOutlined, 
  LoadingOutlined,
  CheckCircleOutlined,
  ExclamationCircleOutlined,
  WarningOutlined,
  InfoCircleOutlined
} from '@ant-design/icons-vue';
import { useNotificationsStore } from '@/store/notifications';

const notificationsStore = useNotificationsStore();
const showDropdown = ref(false);

// Computed properties
const notifications = computed(() => notificationsStore.notifications);
const loading = computed(() => notificationsStore.loading);
const unreadCount = computed(() => notificationsStore.unreadCount);
const hasUnread = computed(() => unreadCount.value > 0);

// Methods
const markAsRead = async (id: number) => {
  await notificationsStore.markAsRead(id);
};

const markAllAsRead = async () => {
  await notificationsStore.markAllAsRead();
};

const deleteNotification = async (id: number) => {
  await notificationsStore.deleteNotification(id);
};

const clearAll = async () => {
  await notificationsStore.clearAll();
};

const loadMore = async () => {
  await notificationsStore.loadNotifications((notifications.value?.length || 0) + 20);
};

// Helper methods
const getTypeIcon = (type: string) => {
  switch (type) {
    case 'success': return CheckCircleOutlined;
    case 'error': return ExclamationCircleOutlined;
    case 'warning': return WarningOutlined;
    case 'info': return InfoCircleOutlined;
    default: return InfoCircleOutlined;
  }
};

const getTypeClasses = (type: string) => {
  switch (type) {
    case 'success': return 'bg-green-500';
    case 'error': return 'bg-red-500';
    case 'warning': return 'bg-yellow-500';
    case 'info': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

const getCategoryClasses = (category: string) => {
  switch (category) {
    case 'post': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    case 'account': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    case 'system': return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    case 'api': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
  }
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
  
  if (diffInHours < 1) return 'Just now';
  if (diffInHours < 24) return `${diffInHours}h ago`;
  if (diffInHours < 48) return 'Yesterday';
  return date.toLocaleDateString();
};

// Lifecycle
onMounted(async () => {
  await notificationsStore.initialize();
});

// Auto-refresh notifications every 30 seconds
let refreshInterval: number;
onMounted(() => {
  refreshInterval = setInterval(() => {
    notificationsStore.loadNotifications();
  }, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script> 