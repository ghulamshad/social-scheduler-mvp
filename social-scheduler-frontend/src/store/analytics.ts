import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export interface Analytics {
  id: number;
  post_id: number;
  user_id: number;
  platform: string;
  platform_post_id: string;
  likes: number;
  shares: number;
  comments: number;
  clicks: number;
  impressions: number;
  reach: number;
  engagement_rate: number;
  additional_metrics?: Record<string, any>;
  last_updated?: string;
  created_at: string;
}

export interface AnalyticsSummary {
  total_posts: number;
  total_engagement: number;
  total_reach: number;
  total_impressions: number;
  average_engagement_rate: number;
  top_performing_post?: Analytics;
  platform_breakdown: Record<string, number>;
}

export const useAnalyticsStore = defineStore('analytics', () => {
  const analytics = ref<Analytics[]>([]);
  const summary = ref<AnalyticsSummary | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Computed properties
  const totalEngagement = computed(() => 
    analytics.value.reduce((sum, a) => sum + a.likes + a.shares + a.comments, 0)
  );
  
  const totalReach = computed(() => 
    analytics.value.reduce((sum, a) => sum + a.reach, 0)
  );
  
  const totalImpressions = computed(() => 
    analytics.value.reduce((sum, a) => sum + a.impressions, 0)
  );
  
  const averageEngagementRate = computed(() => {
    if (analytics.value.length === 0) return 0;
    const total = analytics.value.reduce((sum, a) => sum + a.engagement_rate, 0);
    return total / analytics.value.length;
  });

  const platformBreakdown = computed(() => {
    const breakdown: Record<string, number> = {};
    analytics.value.forEach(a => {
      breakdown[a.platform] = (breakdown[a.platform] || 0) + 1;
    });
    return breakdown;
  });

  const topPerformingPost = computed(() => {
    if (analytics.value.length === 0) return null;
    return analytics.value.reduce((top, current) => 
      current.engagement_rate > top.engagement_rate ? current : top
    );
  });

  // Load analytics from API
  const loadAnalytics = async (params?: {
    platform?: string;
    startDate?: string;
    endDate?: string;
    limit?: number;
  }) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/api/analytics', { params });
      analytics.value = response.data.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to load analytics';
      console.error('Analytics load error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Load analytics summary
  const loadSummary = async () => {
    try {
      const response = await axios.get('/api/analytics/summary');
      summary.value = response.data.data;
    } catch (err: any) {
      console.error('Analytics summary load error:', err);
    }
  };

  // Get analytics by platform
  const getAnalyticsByPlatform = (platform: string) => {
    return analytics.value.filter(a => a.platform === platform);
  };

  // Get analytics by date range
  const getAnalyticsByDateRange = (startDate: string, endDate: string) => {
    return analytics.value.filter(a => {
      const date = new Date(a.created_at);
      return date >= new Date(startDate) && date <= new Date(endDate);
    });
  };

  // Update analytics for a post
  const updateAnalytics = async (postId: number, platform: string, metrics: Partial<Analytics>) => {
    try {
      const response = await axios.put(`/api/analytics/${postId}/${platform}`, metrics);
      const updatedAnalytics = response.data.data;
      
      const index = analytics.value.findIndex(a => 
        a.post_id === postId && a.platform === platform
      );
      
      if (index > -1) {
        analytics.value[index] = updatedAnalytics;
      } else {
        analytics.value.push(updatedAnalytics);
      }
    } catch (err: any) {
      console.error('Update analytics error:', err);
    }
  };

  // Get performance metrics for a post
  const getPostAnalytics = (postId: number) => {
    return analytics.value.filter(a => a.post_id === postId);
  };

  // Get platform-specific metrics
  const getPlatformMetrics = (platform: string) => {
    const platformAnalytics = getAnalyticsByPlatform(platform);
    
    return {
      total_posts: platformAnalytics.length,
      total_engagement: platformAnalytics.reduce((sum, a) => 
        sum + a.likes + a.shares + a.comments, 0
      ),
      total_reach: platformAnalytics.reduce((sum, a) => sum + a.reach, 0),
      total_impressions: platformAnalytics.reduce((sum, a) => sum + a.impressions, 0),
      average_engagement_rate: platformAnalytics.length > 0 ? 
        platformAnalytics.reduce((sum, a) => sum + a.engagement_rate, 0) / platformAnalytics.length : 0,
    };
  };

  // Export analytics data
  const exportAnalytics = async (format: 'csv' | 'json' = 'csv') => {
    try {
      const response = await axios.get('/api/analytics/export', {
        params: { format },
        responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `analytics-${new Date().toISOString().split('T')[0]}.${format}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err: any) {
      console.error('Export analytics error:', err);
    }
  };

  // Initialize analytics
  const initialize = async () => {
    await Promise.all([
      loadAnalytics(),
      loadSummary()
    ]);
  };

  return {
    // State
    analytics,
    summary,
    loading,
    error,
    
    // Computed
    totalEngagement,
    totalReach,
    totalImpressions,
    averageEngagementRate,
    platformBreakdown,
    topPerformingPost,
    
    // Actions
    loadAnalytics,
    loadSummary,
    getAnalyticsByPlatform,
    getAnalyticsByDateRange,
    updateAnalytics,
    getPostAnalytics,
    getPlatformMetrics,
    exportAnalytics,
    initialize,
  };
}); 