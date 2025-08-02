// 📁 src/components/PostList.vue
<template>
  <div class="card p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Scheduled Posts</h2>
        <p class="text-sm text-gray-600 mt-1">Manage your social media content</p>
      </div>
      <button
        @click="() => loadPosts()"
        :disabled="loading"
        class="btn-secondary text-sm"
      >
        <ReloadOutlined v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" />
        <ReloadOutlined v-else class="w-4 h-4 mr-2" />
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && posts.length === 0" class="text-center py-12">
      <LoadingOutlined class="animate-spin w-8 h-8 text-blue-600 mx-auto mb-4" />
      <p class="text-gray-500">Loading posts...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="posts.length === 0" class="text-center py-12">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <FileTextOutlined class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
      <p class="text-gray-500">Start by scheduling your first post!</p>
    </div>

    <!-- Posts List -->
    <div v-else class="space-y-4">
      <div
        v-for="post in posts"
        :key="post.id"
        class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-all duration-200 animate-fade-in"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="flex-1">
            <p class="text-gray-900 mb-3 leading-relaxed">{{ post.content }}</p>
            
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
              <span class="flex items-center">
                <span class="w-2 h-2 rounded-full mr-2" :class="statusColor(post.status)"></span>
                {{ formatStatus(post.status) }}
              </span>
              
              <span class="flex items-center">
                <CalendarOutlined class="w-4 h-4 mr-1" />
                {{ formatDate(post.schedule_time) }}
              </span>
              
              <span v-if="post.account" class="flex items-center">
                <UserOutlined class="w-4 h-4 mr-1" />
                {{ post.account.name }} (@{{ post.account.username }})
              </span>
            </div>
          </div>

          <div class="flex space-x-2 ml-4">
            <button
              v-if="post.status === 'scheduled'"
              @click="editPost(post)"
              class="btn-secondary text-xs"
            >
              <EditOutlined class="w-4 h-4 mr-1" />
              Edit
            </button>
            
            <button
              v-if="post.status === 'scheduled'"
              @click="deletePost(post.id)"
              class="btn-danger text-xs"
            >
              <DeleteOutlined class="w-4 h-4 mr-1" />
              Delete
            </button>
          </div>
        </div>

        <!-- Image Preview -->
        <div v-if="post.image_path" class="mt-4">
          <img :src="post.image_path" alt="Post image" class="w-32 h-32 object-cover rounded-lg border" />
        </div>

        <!-- Publish Log -->
        <div v-if="post.publish_log" class="mt-4 p-3 bg-gray-50 rounded-lg">
          <div class="flex items-start">
            <InfoCircleOutlined class="w-4 h-4 text-gray-500 mr-2 mt-0.5" />
            <span class="text-sm text-gray-700">{{ post.publish_log }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
      <div class="flex space-x-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="() => loadPosts(page)"
          :class="[
            'px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200',
            page === pagination.current_page
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  ReloadOutlined,
  LoadingOutlined,
  FileTextOutlined,
  CalendarOutlined,
  UserOutlined,
  EditOutlined,
  DeleteOutlined,
  InfoCircleOutlined,
} from '@ant-design/icons-vue';
import { api } from '@/services/api';

interface Account {
  id: number;
  name: string;
  username: string;
  platform: string;
}

interface Post {
  id: number;
  content: string;
  schedule_time: string;
  status: 'draft' | 'scheduled' | 'published' | 'failed';
  image_path?: string;
  publish_log?: string;
  account?: Account;
  created_at: string;
  updated_at: string;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const posts = ref<Post[]>([]);
const loading = ref(false);
const pagination = ref<Pagination | null>(null);

const loadPosts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get(`/posts?page=${page}`);
    posts.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error('Failed to load posts:', error);
  } finally {
    loading.value = false;
  }
};

const deletePost = async (postId: number) => {
  if (!confirm('Are you sure you want to delete this post?')) {
    return;
  }

  try {
    await api.delete(`/posts/${postId}`);
    await loadPosts();
  } catch (error) {
    console.error('Failed to delete post:', error);
  }
};

const editPost = (post: Post) => {
  // TODO: Implement edit functionality
  console.log('Edit post:', post);
};

const formatStatus = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    scheduled: 'Scheduled',
    published: 'Published',
    failed: 'Failed',
  };
  return statusMap[status] || status;
};

const statusColor = (status: string) => {
  const colorMap: Record<string, string> = {
    draft: 'bg-gray-400',
    scheduled: 'bg-blue-500',
    published: 'bg-green-500',
    failed: 'bg-red-500',
  };
  return colorMap[status] || 'bg-gray-400';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString();
};

// Expose loadPosts for parent components
defineExpose({ loadPosts });

onMounted(() => {
  loadPosts();
});
</script>
