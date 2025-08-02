<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md mx-4">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          Update Profile
        </h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <CloseOutlined class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            <UserOutlined class="w-4 h-4 mr-2 inline" />
            Full Name
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="input-field"
            placeholder="Enter your full name"
          />
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            <MailOutlined class="w-4 h-4 mr-2 inline" />
            Email Address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="input-field"
            placeholder="Enter your email"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm flex items-center">
          <ExclamationCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
          {{ error }}
        </div>

        <!-- Success Message -->
        <div v-if="success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm flex items-center">
          <CheckCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
          {{ success }}
        </div>

        <!-- Submit Button -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="closeModal"
            class="flex-1 btn-secondary"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 btn-primary"
          >
            <div v-if="loading" class="flex items-center justify-center">
              <LoadingOutlined class="animate-spin -ml-1 mr-2 h-4 w-4" />
              Updating...
            </div>
            <span v-else>
              Update Profile
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { UserOutlined, MailOutlined, CloseOutlined, ExclamationCircleOutlined, CheckCircleOutlined, LoadingOutlined } from '@ant-design/icons-vue';
import { useAuthStore } from '@/store/auth';
import { api } from '@/services/api';

interface Props {
  show: boolean;
}

interface Emits {
  (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const auth = useAuthStore();
const loading = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
  name: '',
  email: '',
});

const closeModal = () => {
  emit('close');
  error.value = '';
  success.value = '';
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await api.put('/profile', form);
    auth.user = response.data.user;
    success.value = 'Profile updated successfully!';
    
    // Auto-close after 2 seconds
    setTimeout(() => {
      closeModal();
    }, 2000);
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update profile';
  } finally {
    loading.value = false;
  }
};

// Initialize form with current user data
watch(() => props.show, (newShow) => {
  if (newShow && auth.user) {
    form.name = auth.user.name;
    form.email = auth.user.email;
  }
});
</script> 