<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
              Social Scheduler
            </h1>
          </div>
          
          <div class="flex items-center gap-4">
            <ThemeToggle />
            <NotificationCenter />
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <button
                @click="showProfileModal = true"
                class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors"
              >
                <UserOutlined class="w-4 h-4" />
                {{ user?.name || 'User' }}
              </button>
            </div>
            <button
              @click="logout"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
            >
              <LogoutOutlined class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          title="Total Posts"
          :value="stats.totalPosts"
          icon="file"
          color="blue"
        />
        <StatCard
          title="Scheduled"
          :value="stats.scheduledPosts"
          icon="clock"
          color="orange"
        />
        <StatCard
          title="Published"
          :value="stats.publishedPosts"
          icon="check"
          color="green"
        />
        <StatCard
          title="Failed"
          :value="stats.failedPosts"
          icon="x"
          color="red"
        />
      </div>

      <!-- Tabs -->
      <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
              :class="activeTab === tab.id
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="space-y-8">
        <!-- Posts Tab -->
        <div v-if="activeTab === 'posts'">
          <PostList />
        </div>

        <!-- Accounts Tab -->
        <div v-if="activeTab === 'accounts'">
          <AccountManager />
        </div>

        <!-- AI Content Generator Tab -->
        <div v-if="activeTab === 'ai'">
          <AiContentGenerator @use-content="useGeneratedContent" />
        </div>

        <!-- Team Management Tab -->
        <div v-if="activeTab === 'teams'">
          <TeamManager />
        </div>

        <!-- Content Calendar Tab -->
        <div v-if="activeTab === 'calendar'">
          <ContentCalendar />
        </div>

        <!-- Analytics Tab -->
        <div v-if="activeTab === 'analytics'">
          <AnalyticsDashboard />
        </div>

        <!-- Settings Tab -->
        <div v-if="activeTab === 'settings'">
          <SettingsPanel />
        </div>
      </div>
    </div>

    <!-- Profile Modal -->
    <ProfileModal
      :show="showProfileModal"
      @close="showProfileModal = false"
      @updated="handleProfileUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  UserOutlined, 
  LogoutOutlined 
} from '@ant-design/icons-vue';
import { useAuthStore } from '@/store/auth';
import StatCard from '@/components/StatCard.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import NotificationCenter from '@/components/NotificationCenter.vue';
import ProfileModal from '@/components/ProfileModal.vue';
import PostList from '@/components/PostList.vue';
import AccountManager from '@/components/AccountManager.vue';
import AiContentGenerator from '@/components/AiContentGenerator.vue';
import TeamManager from '@/components/TeamManager.vue';
import ContentCalendar from '@/components/ContentCalendar.vue';
import AnalyticsDashboard from '@/components/AnalyticsDashboard.vue';
import SettingsPanel from '@/components/SettingsPanel.vue';

// State
const router = useRouter();
const authStore = useAuthStore();
const activeTab = ref('posts');
const showProfileModal = ref(false);
const stats = ref({
  totalPosts: 0,
  scheduledPosts: 0,
  publishedPosts: 0,
  failedPosts: 0,
});

// Computed
const user = computed(() => authStore.user);

// Tabs
const tabs = [
  { id: 'posts', name: 'Posts' },
  { id: 'accounts', name: 'Accounts' },
  { id: 'ai', name: 'AI Generator' },
  { id: 'teams', name: 'Teams' },
  { id: 'calendar', name: 'Calendar' },
  { id: 'analytics', name: 'Analytics' },
  { id: 'settings', name: 'Settings' },
];

// Methods
const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

const handleProfileUpdated = () => {
  showProfileModal.value = false;
  // Optionally refresh user data
};

const useGeneratedContent = (content: any) => {
  // TODO: Implement using generated content in post creation
  console.log('Using generated content:', content);
};

const loadStats = async () => {
  try {
    // TODO: Load actual stats from API
    stats.value = {
      totalPosts: 12,
      scheduledPosts: 5,
      publishedPosts: 6,
      failedPosts: 1,
    };
  } catch (error) {
    console.error('Failed to load stats:', error);
  }
};

// Lifecycle
onMounted(() => {
  loadStats();
});
</script>
