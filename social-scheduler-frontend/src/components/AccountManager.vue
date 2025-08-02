<template>
  <div class="card p-6">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center">
        <TeamOutlined class="w-6 h-6 text-purple-600 mr-3" />
        <div>
          <h2 class="text-xl font-bold text-gray-900">Social Accounts</h2>
          <p class="text-sm text-gray-600">Manage your connected accounts</p>
        </div>
      </div>
      <button
        @click="showModal = true"
        class="btn-primary text-sm"
      >
        <PlusOutlined class="w-4 h-4 mr-2" />
        Add Account
      </button>
    </div>

    <!-- Accounts List -->
    <div v-if="loading" class="text-center py-8">
      <LoadingOutlined class="animate-spin w-8 h-8 text-blue-600 mx-auto mb-4" />
      <p class="text-gray-500">Loading accounts...</p>
    </div>

    <div v-else-if="!accounts || accounts.length === 0" class="text-center py-8">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <TeamOutlined class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No accounts yet</h3>
      <p class="text-gray-500">Add your first social media account to get started!</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="account in accounts"
        :key="account.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
              <component :is="getPlatformIcon(account.platform)" class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ account.name }}</h3>
              <p class="text-sm text-gray-600">@{{ account.username }} • {{ formatPlatform(account.platform) }}</p>
            </div>
          </div>
          
          <div class="flex space-x-2">
            <button
              @click="editAccount(account)"
              class="btn-secondary text-xs"
            >
              <EditOutlined class="w-4 h-4 mr-1" />
              Edit
            </button>
            <button
              @click="deleteAccount(account.id)"
              class="btn-danger text-xs"
            >
              <DeleteOutlined class="w-4 h-4 mr-1" />
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Account Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-gray-900">
            {{ editingAccount ? 'Edit Account' : 'Add New Account' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <CloseOutlined class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Account Name -->
          <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
              <UserOutlined class="w-4 h-4 mr-2 inline" />
              Account Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              placeholder="My Twitter Account"
            />
          </div>

          <!-- Platform -->
          <div>
            <label for="platform" class="block text-sm font-semibold text-gray-700 mb-2">
              <GlobalOutlined class="w-4 h-4 mr-2 inline" />
              Platform
            </label>
            <select
              id="platform"
              v-model="form.platform"
              required
              class="input-field"
            >
              <option value="">Select platform</option>
              <option value="twitter">Twitter</option>
              <option value="facebook">Facebook</option>
              <option value="instagram">Instagram</option>
              <option value="linkedin">LinkedIn</option>
            </select>
          </div>

          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
              <MailOutlined class="w-4 h-4 mr-2 inline" />
              Username
            </label>
            <input
              id="username"
              v-model="form.username"
              type="text"
              required
              class="input-field"
              placeholder="username"
            />
          </div>

          <!-- Avatar URL -->
          <div>
            <label for="avatar_url" class="block text-sm font-semibold text-gray-700 mb-2">
              <PictureOutlined class="w-4 h-4 mr-2 inline" />
              Avatar URL (Optional)
            </label>
            <input
              id="avatar_url"
              v-model="form.avatar_url"
              type="url"
              class="input-field"
              placeholder="https://example.com/avatar.jpg"
            />
          </div>

          <!-- Error Message -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center">
            <ExclamationCircleOutlined class="w-5 h-5 mr-2 flex-shrink-0" />
            {{ error }}
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
                {{ editingAccount ? 'Updating...' : 'Adding...' }}
              </div>
              <span v-else>
                {{ editingAccount ? 'Update Account' : 'Add Account' }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import {
  TeamOutlined,
  PlusOutlined,
  LoadingOutlined,
  EditOutlined,
  DeleteOutlined,
  CloseOutlined,
  UserOutlined,
  GlobalOutlined,
  MailOutlined,
  PictureOutlined,
  ExclamationCircleOutlined,
  TwitterOutlined,
  FacebookOutlined,
  InstagramOutlined,
  LinkedinFilled,
} from '@ant-design/icons-vue';
import { api } from '@/services/api';

interface Account {
  id: number;
  name: string;
  username: string;
  platform: string;
  avatar_url?: string;
}

const accounts = ref<Account[]>([]);
const loading = ref(false);
const showModal = ref(false);
const editingAccount = ref<Account | null>(null);
const error = ref('');

const form = reactive({
  name: '',
  platform: '',
  username: '',
  avatar_url: '',
});

const loadAccounts = async () => {
  loading.value = true;
  try {
    console.log('Loading accounts...');
    const response = await api.get('/accounts');
    console.log('Accounts response:', response.data);
    accounts.value = response.data.data || [];
    console.log('Accounts loaded:', accounts.value);
  } catch (error) {
    console.error('Failed to load accounts:', error);
    accounts.value = [];
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';

  try {
    if (editingAccount.value) {
      await api.put(`/accounts/${editingAccount.value.id}`, form);
    } else {
      await api.post('/accounts', form);
    }
    
    await loadAccounts();
    closeModal();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save account';
  } finally {
    loading.value = false;
  }
};

const editAccount = (account: Account) => {
  editingAccount.value = account;
  form.name = account.name;
  form.platform = account.platform;
  form.username = account.username;
  form.avatar_url = account.avatar_url || '';
  showModal.value = true;
};

const deleteAccount = async (accountId: number) => {
  if (!confirm('Are you sure you want to delete this account?')) {
    return;
  }

  try {
    await api.delete(`/accounts/${accountId}`);
    await loadAccounts();
  } catch (error) {
    console.error('Failed to delete account:', error);
  }
};

const closeModal = () => {
  showModal.value = false;
  editingAccount.value = null;
  error.value = '';
  // Reset form
  form.name = '';
  form.platform = '';
  form.username = '';
  form.avatar_url = '';
};

const getPlatformIcon = (platform: string) => {
  switch (platform) {
    case 'twitter':
      return TwitterOutlined;
    case 'facebook':
      return FacebookOutlined;
    case 'instagram':
      return InstagramOutlined;
    case 'linkedin':
      return LinkedinFilled;
    default:
      return GlobalOutlined;
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