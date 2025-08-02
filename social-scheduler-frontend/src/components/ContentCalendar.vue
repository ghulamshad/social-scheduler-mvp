<template>
  <div class="content-calendar">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        Content Calendar
      </h2>
      <p class="text-gray-600 dark:text-gray-400">
        Visualize and manage your scheduled content
      </p>
    </div>

    <!-- Calendar Controls -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button @click="previousMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
          <LeftOutlined class="w-4 h-4" />
        </button>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ currentMonthYear }}
        </h3>
        <button @click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
          <RightOutlined class="w-4 h-4" />
        </button>
      </div>
      
      <div class="flex items-center space-x-4">
        <select v-model="selectedPlatform" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md">
          <option value="">All Platforms</option>
          <option value="twitter">Twitter</option>
          <option value="facebook">Facebook</option>
          <option value="instagram">Instagram</option>
          <option value="linkedin">LinkedIn</option>
        </select>
        
        <select v-model="selectedStatus" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="scheduled">Scheduled</option>
          <option value="published">Published</option>
          <option value="failed">Failed</option>
        </select>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
      <!-- Calendar Header -->
      <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-700">
        <div v-for="day in weekDays" :key="day" class="p-3 text-center text-sm font-medium">
          {{ day }}
        </div>
      </div>
      
      <!-- Calendar Body -->
      <div class="grid grid-cols-7">
        <div
          v-for="date in calendarDates"
          :key="date.date"
          class="min-h-[120px] border-r border-b border-gray-200 dark:border-gray-700 p-2"
          :class="{
            'bg-gray-50 dark:bg-gray-900': !isCurrentMonth(date),
            'hover:bg-gray-50 dark:hover:bg-gray-700': isCurrentMonth(date)
          }"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium"
                  :class="{
                    'text-gray-900 dark:text-white': isCurrentMonth(date),
                    'text-gray-400 dark:text-gray-500': !isCurrentMonth(date),
                    'text-blue-600 dark:text-blue-400': isToday(date)
                  }">
              {{ date.day }}
            </span>
            
            <button v-if="isCurrentMonth(date)" @click="addPost(date)"
                    class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800 flex items-center justify-center">
              <PlusOutlined class="w-3 h-3" />
            </button>
          </div>
          
          <div class="space-y-1">
            <div v-for="post in getPostsForDate(date)" :key="post.id"
                 class="p-2 rounded text-xs cursor-pointer"
                 :class="getPostStatusClass(post)"
                 @click="viewPost(post)">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-1">
                  <div class="w-2 h-2 rounded-full" :class="getPlatformColor(post.account?.platform)"></div>
                  <span class="truncate">{{ post.content.substring(0, 20) }}...</span>
                </div>
                <span class="text-xs opacity-75">{{ formatTime(post.schedule_time) }}</span>
              </div>
              
              <div v-if="post.approval_status === 'pending_approval'" class="mt-1">
                <span class="px-1 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">
                  Pending
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { LeftOutlined, RightOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { api } from '@/services/api';

// Types
interface CalendarDate {
  date: string;
  day: number;
  month: number;
  year: number;
  isCurrentMonth: boolean;
}

interface Post {
  id: number;
  content: string;
  schedule_time: string;
  approval_status: string;
  account?: {
    platform: string;
  };
}

// State
const currentDate = ref(new Date());
const posts = ref<Post[]>([]);
const selectedPlatform = ref('');
const selectedStatus = ref('');

// Computed
const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const calendarDates = computed<CalendarDate[]>(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - firstDay.getDay());
  
  const endDate = new Date(lastDay);
  endDate.setDate(endDate.getDate() + (6 - lastDay.getDay()));
  
  const dates: CalendarDate[] = [];
  const current = new Date(startDate);
  
  while (current <= endDate) {
    dates.push({
      date: current.toISOString().split('T')[0],
      day: current.getDate(),
      month: current.getMonth(),
      year: current.getFullYear(),
      isCurrentMonth: current.getMonth() === month,
    });
    current.setDate(current.getDate() + 1);
  }
  
  return dates;
});

// Methods
const loadPosts = async () => {
  try {
    const params: any = {};
    if (selectedPlatform.value) params.platform = selectedPlatform.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    
    const response = await api.get('/posts', { params });
    posts.value = response.data.data;
  } catch (error) {
    console.error('Failed to load posts:', error);
  }
};

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const isCurrentMonth = (date: CalendarDate) => date.isCurrentMonth;

const isToday = (date: CalendarDate) => {
  const today = new Date();
  return date.day === today.getDate() && date.month === today.getMonth() && date.year === today.getFullYear();
};

const getPostsForDate = (date: CalendarDate) => {
  return posts.value.filter((post: Post) => {
    const postDate = new Date(post.schedule_time);
    return postDate.toDateString() === new Date(date.date).toDateString();
  });
};

const getPostStatusClass = (post: Post) => {
  const classes = {
    draft: 'bg-gray-100 dark:bg-gray-700 border-l-4 border-gray-400',
    scheduled: 'bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-400',
    published: 'bg-green-100 dark:bg-green-900 border-l-4 border-green-400',
    failed: 'bg-red-100 dark:bg-red-900 border-l-4 border-red-400',
  };
  return classes[post.approval_status as keyof typeof classes] || classes.draft;
};

const getPlatformColor = (platform?: string) => {
  const colors = {
    twitter: 'bg-blue-500',
    facebook: 'bg-blue-600',
    instagram: 'bg-pink-500',
    linkedin: 'bg-blue-700',
  };
  return colors[platform as keyof typeof colors] || 'bg-gray-500';
};

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};

const addPost = (date: CalendarDate) => {
  // TODO: Implement add post functionality
  console.log('Add post for date:', date);
};

const viewPost = (post: Post) => {
  // TODO: Implement view post functionality
  console.log('View post:', post);
};

// Watchers
watch([selectedPlatform, selectedStatus], () => {
  loadPosts();
});

// Lifecycle
onMounted(() => {
  loadPosts();
});
</script> 