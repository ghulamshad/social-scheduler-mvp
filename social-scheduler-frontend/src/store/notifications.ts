import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export interface Notification {
  id: number;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  category: 'post' | 'account' | 'system' | 'api' | 'general';
  data?: Record<string, any>;
  read_at?: string;
  sent_at?: string;
  channel: 'database' | 'email' | 'sms' | 'push';
  status: 'pending' | 'sent' | 'failed' | 'read';
  created_at: string;
}

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const unreadCount = ref(0);

  // Computed properties
  const unreadNotifications = computed(() => 
    notifications.value?.filter(n => !n.read_at) || []
  );
  
  const readNotifications = computed(() => 
    notifications.value?.filter(n => n.read_at) || []
  );
  
  const successNotifications = computed(() => 
    notifications.value?.filter(n => n.type === 'success') || []
  );
  
  const errorNotifications = computed(() => 
    notifications.value?.filter(n => n.type === 'error') || []
  );
  
  const warningNotifications = computed(() => 
    notifications.value?.filter(n => n.type === 'warning') || []
  );

  // Load notifications from API
  const loadNotifications = async (limit: number = 50) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/notifications', {
        params: { limit }
      });
      notifications.value = response.data.data;
      updateUnreadCount();
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to load notifications';
      console.error('Notifications load error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Mark notification as read
  const markAsRead = async (notificationId: number) => {
    try {
      await axios.patch(`/api/notifications/${notificationId}/read`);
      
      const notification = notifications.value.find(n => n.id === notificationId);
      if (notification) {
        notification.read_at = new Date().toISOString();
        updateUnreadCount();
      }
    } catch (err: any) {
      console.error('Mark as read error:', err);
    }
  };

  // Mark all notifications as read
  const markAllAsRead = async () => {
    try {
      await axios.patch('/api/notifications/mark-all-read');
      
      notifications.value.forEach(n => {
        if (!n.read_at) {
          n.read_at = new Date().toISOString();
        }
      });
      updateUnreadCount();
    } catch (err: any) {
      console.error('Mark all as read error:', err);
    }
  };

  // Delete notification
  const deleteNotification = async (notificationId: number) => {
    try {
      await axios.delete(`/api/notifications/${notificationId}`);
      
      const index = notifications.value.findIndex(n => n.id === notificationId);
      if (index > -1) {
        notifications.value.splice(index, 1);
        updateUnreadCount();
      }
    } catch (err: any) {
      console.error('Delete notification error:', err);
    }
  };

  // Add notification locally (for real-time updates)
  const addNotification = (notification: Notification) => {
    notifications.value.unshift(notification);
    updateUnreadCount();
  };

  // Update unread count
  const updateUnreadCount = () => {
    unreadCount.value = unreadNotifications.value.length;
  };

  // Get notifications by category
  const getNotificationsByCategory = (category: string) => {
    return notifications.value.filter(n => n.category === category);
  };

  // Get notifications by type
  const getNotificationsByType = (type: string) => {
    return notifications.value.filter(n => n.type === type);
  };

  // Clear all notifications
  const clearAll = async () => {
    try {
      await axios.delete('/api/notifications/clear-all');
      notifications.value = [];
      updateUnreadCount();
    } catch (err: any) {
      console.error('Clear all notifications error:', err);
    }
  };

  // Initialize notifications
  const initialize = async () => {
    try {
      await loadNotifications();
    } catch (error) {
      // If API fails, initialize with empty array to prevent undefined errors
      notifications.value = [];
      updateUnreadCount();
    }
  };

  return {
    // State
    notifications,
    loading,
    error,
    unreadCount,
    
    // Computed
    unreadNotifications,
    readNotifications,
    successNotifications,
    errorNotifications,
    warningNotifications,
    
    // Actions
    loadNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    addNotification,
    getNotificationsByCategory,
    getNotificationsByType,
    clearAll,
    initialize,
  };
}); 