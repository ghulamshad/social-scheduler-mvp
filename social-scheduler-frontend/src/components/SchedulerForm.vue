<template>
  <div class="card p-6">
    <div class="flex items-center mb-6">
      <PlusCircleOutlined class="w-6 h-6 text-blue-600 mr-3" />
      <div>
        <h2 class="text-xl font-bold text-gray-900">Schedule New Post</h2>
        <p class="text-sm text-gray-600">Create and schedule your social media content</p>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Content -->
      <div>
        <label for="content" class="block text-sm font-semibold text-gray-700 mb-3">
          <MessageOutlined class="w-4 h-4 mr-2 inline" />
          Post Content
        </label>
        <textarea
          id="content"
          v-model="form.content"
          rows="4"
          required
          class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          placeholder="What's on your mind? Share your thoughts..."
        ></textarea>
      </div>

      <!-- Schedule Time -->
      <div>
        <label for="schedule_time" class="block text-sm font-semibold text-gray-700 mb-3">
          <ClockCircleOutlined class="w-4 h-4 mr-2 inline" />
          Schedule Time
        </label>
        <input
          id="schedule_time"
          v-model="form.schedule_time"
          type="datetime-local"
          required
          class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>

      <!-- Account Selection -->
      <div>
        <label for="account_id" class="block text-sm font-semibold text-gray-700 mb-3">
          <UserOutlined class="w-4 h-4 mr-2 inline" />
          Social Account
        </label>
        <select
          id="account_id"
          v-model="form.account_id"
          class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Select an account</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">
            {{ account.name }} (@{{ account.username }}) - {{ formatPlatform(account.platform) }}
          </option>
        </select>
      </div>

      <!-- Image URL (Optional) -->
      <div>
        <label for="image_path" class="block text-sm font-semibold text-gray-700 mb-3">
          <PictureOutlined class="w-4 h-4 mr-2 inline" />
          Image URL (Optional)
        </label>
        <input
          id="image_path"
          v-model="form.image_path"
          type="url"
          class="input-field focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="https://example.com/image.jpg"
        />
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-bounce-in flex items-center">
        <ExclamationCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
        {{ error }}
      </div>

      <!-- Success Message -->
      <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm animate-bounce-in flex items-center">
        <CheckCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
        {{ success }}
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full btn-primary py-3 text-base font-semibold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <div v-if="loading" class="flex items-center justify-center">
          <LoadingOutlined class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
          Scheduling post...
        </div>
        <span v-else class="flex items-center justify-center">
          <ScheduleOutlined class="w-5 h-5 mr-2" />
          Schedule Post
        </span>
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import {
  PlusCircleOutlined,
  MessageOutlined,
  ClockCircleOutlined,
  UserOutlined,
  PictureOutlined,
  ExclamationCircleOutlined,
  CheckCircleOutlined,
  LoadingOutlined,
  ScheduleOutlined,
} from '@ant-design/icons-vue';
import { api } from '@/services/api';

interface Account {
  id: number;
  name: string;
  username: string;
  platform: string;
}

const emit = defineEmits<{
  'post-created': [];
}>();

const form = reactive({
  content: '',
  schedule_time: '',
  account_id: '',
  image_path: '',
});

const accounts = ref<Account[]>([]);
const loading = ref(false);
const error = ref('');
const success = ref('');

const loadAccounts = async () => {
  try {
    const response = await api.get('/accounts');
    accounts.value = response.data.data;
  } catch (error) {
    console.error('Failed to load accounts:', error);
  }
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    await api.post('/posts', form);
    success.value = 'Post scheduled successfully!';
    
    // Reset form
    form.content = '';
    form.schedule_time = '';
    form.account_id = '';
    form.image_path = '';
    
    // Emit event to refresh posts list
    emit('post-created');
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to schedule post';
  } finally {
    loading.value = false;
  }
};

const formatPlatform = (platform: string) => {
  const platformMap: Record<string, string> = {
    twitter: 'Twitter',
    facebook: 'Facebook',
    instagram: 'Instagram',
    linkedin: 'LinkedIn',
  };
  return platformMap[platform] || platform;
};

onMounted(() => {
  loadAccounts();
});
</script> 