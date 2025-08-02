<template>
  <div class="analytics-dashboard">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        Analytics Dashboard
      </h2>
      <p class="text-gray-600 dark:text-gray-400">
        Track your social media performance and engagement
      </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Posts</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.totalPosts }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
            <FileTextOutlined class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Engagement</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.totalEngagement }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
            <LikeOutlined class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reach</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.totalReach }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
            <EyeOutlined class="w-6 h-6 text-purple-600 dark:text-purple-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Engagement Rate</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.avgEngagementRate }}%</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
            <RiseOutlined class="w-6 h-6 text-orange-600 dark:text-orange-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Platform Performance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Platform Performance</h3>
      
      <div class="space-y-4">
        <div v-for="platform in analytics.platformBreakdown" :key="platform.name" class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-4 h-4 rounded-full" :class="getPlatformColor(platform.name)"></div>
            <span class="font-medium text-gray-900 dark:text-white">{{ platform.name }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ platform.posts }} posts</span>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ platform.engagement }} engagement</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ platform.rate }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Performing Posts -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performing Posts</h3>
      
      <div class="space-y-4">
        <div v-for="post in analytics.topPosts" :key="post.id" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-sm text-gray-900 dark:text-white mb-2">{{ post.content }}</p>
              <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ post.platform }}</span>
                <span>{{ formatDate(post.published_at) }}</span>
                <span>{{ post.engagement }} engagement</span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-green-600">{{ post.engagement_rate }}%</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Engagement Rate</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
  FileTextOutlined, 
  LikeOutlined, 
  EyeOutlined, 
  RiseOutlined 
} from '@ant-design/icons-vue';

// Types
interface PlatformBreakdown {
  name: string;
  posts: number;
  engagement: number;
  rate: number;
}

interface TopPost {
  id: number;
  content: string;
  platform: string;
  published_at: string;
  engagement: number;
  engagement_rate: number;
}

interface Analytics {
  totalPosts: number;
  totalEngagement: number;
  totalReach: number;
  avgEngagementRate: number;
  platformBreakdown: PlatformBreakdown[];
  topPosts: TopPost[];
}

// State
const analytics = ref<Analytics>({
  totalPosts: 0,
  totalEngagement: 0,
  totalReach: 0,
  avgEngagementRate: 0,
  platformBreakdown: [],
  topPosts: [],
});

// Methods
const loadAnalytics = async () => {
  // Mock data for now
  analytics.value = {
    totalPosts: 24,
    totalEngagement: 15420,
    totalReach: 89200,
    avgEngagementRate: 17.3,
    platformBreakdown: [
      { name: 'Twitter', posts: 8, engagement: 5200, rate: 18.2 },
      { name: 'Facebook', posts: 6, engagement: 4200, rate: 16.8 },
      { name: 'Instagram', posts: 5, engagement: 3800, rate: 19.5 },
      { name: 'LinkedIn', posts: 5, engagement: 2220, rate: 14.7 },
    ],
    topPosts: [
      {
        id: 1,
        content: 'Excited to announce our new product launch! 🚀 #innovation #tech',
        platform: 'Twitter',
        published_at: '2024-01-15T10:30:00Z',
        engagement: 2840,
        engagement_rate: 23.4,
      },
      {
        id: 2,
        content: 'Behind the scenes: Our team working on amazing features for you!',
        platform: 'Instagram',
        published_at: '2024-01-14T15:45:00Z',
        engagement: 2150,
        engagement_rate: 21.8,
      },
      {
        id: 3,
        content: 'Industry insights: The future of social media marketing in 2024',
        platform: 'LinkedIn',
        published_at: '2024-01-13T09:15:00Z',
        engagement: 1890,
        engagement_rate: 18.9,
      },
    ],
  };
};

const getPlatformColor = (platform: string) => {
  const colors = {
    Twitter: 'bg-blue-500',
    Facebook: 'bg-blue-600',
    Instagram: 'bg-pink-500',
    LinkedIn: 'bg-blue-700',
  };
  return colors[platform as keyof typeof colors] || 'bg-gray-500';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  });
};

// Lifecycle
onMounted(() => {
  loadAnalytics();
});
</script> 