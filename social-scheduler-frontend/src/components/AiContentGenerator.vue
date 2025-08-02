<template>
  <div class="ai-content-generator">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        AI Content Generator
      </h2>
      <p class="text-gray-600 dark:text-gray-400">
        Generate engaging content, hashtags, and ideas using AI
      </p>
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

    <!-- Content Generation Tab -->
    <div v-if="activeTab === 'content'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generate Post Content
        </h3>
        
        <form @submit.prevent="generateContent" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Topic/Theme
              </label>
              <input
                v-model="contentForm.topic"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                placeholder="e.g., Product launch, Industry insights"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Platform
              </label>
              <select
                v-model="contentForm.platform"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="">Select platform</option>
                <option value="twitter">Twitter</option>
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="linkedin">LinkedIn</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tone
              </label>
              <select
                v-model="contentForm.tone"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="professional">Professional</option>
                <option value="casual">Casual</option>
                <option value="friendly">Friendly</option>
                <option value="humorous">Humorous</option>
                <option value="formal">Formal</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Industry
              </label>
              <input
                v-model="contentForm.industry"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                placeholder="e.g., Technology, Healthcare"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Target Audience
              </label>
              <input
                v-model="contentForm.target_audience"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                placeholder="e.g., Young professionals"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              AI Model
            </label>
            <select
              v-model="contentForm.model"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            >
              <option value="gpt-4">GPT-4 (Most capable)</option>
              <option value="gpt-4-turbo">GPT-4 Turbo (Fast & capable)</option>
              <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Fast & affordable)</option>
              <option value="claude-3-opus">Claude 3 Opus (Most capable)</option>
              <option value="claude-3-sonnet">Claude 3 Sonnet (Balanced)</option>
              <option value="claude-3-haiku">Claude 3 Haiku (Fast & affordable)</option>
            </select>
          </div>

          <button
            type="submit"
            :disabled="generating"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <LoadingOutlined v-if="generating" class="w-4 h-4 animate-spin mr-2" />
            {{ generating ? 'Generating...' : 'Generate Content' }}
          </button>
        </form>
      </div>

      <!-- Generated Content -->
      <div v-if="generatedContent" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generated Content
        </h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Post Content
            </label>
            <textarea
              v-model="generatedContent.content"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              readonly
            ></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Hashtags
            </label>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="hashtag in generatedContent.hashtags"
                :key="hashtag"
                class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm"
              >
                #{{ hashtag }}
              </span>
            </div>
          </div>
          
          <div class="flex gap-2">
            <button
              @click="useContent"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              Use This Content
            </button>
            <button
              @click="regenerateContent"
              class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              Regenerate
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hashtag Generation Tab -->
    <div v-if="activeTab === 'hashtags'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generate Hashtags
        </h3>
        
        <form @submit.prevent="generateHashtags" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Post Content
            </label>
            <textarea
              v-model="hashtagForm.content"
              rows="4"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="Paste your post content here..."
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Platform
              </label>
              <select
                v-model="hashtagForm.platform"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="">Select platform</option>
                <option value="twitter">Twitter</option>
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="linkedin">LinkedIn</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Industry
              </label>
              <input
                v-model="hashtagForm.industry"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                placeholder="e.g., Technology, Healthcare"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="generating"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <LoadingOutlined v-if="generating" class="w-4 h-4 animate-spin mr-2" />
            {{ generating ? 'Generating...' : 'Generate Hashtags' }}
          </button>
        </form>
      </div>

      <!-- Generated Hashtags -->
      <div v-if="generatedHashtags.length > 0" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generated Hashtags
        </h3>
        
        <div class="flex flex-wrap gap-2 mb-4">
          <span
            v-for="hashtag in generatedHashtags"
            :key="hashtag"
            class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
            @click="copyHashtag(hashtag)"
          >
            #{{ hashtag }}
          </span>
        </div>
        
        <button
          @click="copyAllHashtags"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
        >
          Copy All Hashtags
        </button>
      </div>
    </div>

    <!-- Content Ideas Tab -->
    <div v-if="activeTab === 'ideas'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generate Content Ideas
        </h3>
        
        <form @submit.prevent="generateIdeas" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Industry
              </label>
              <input
                v-model="ideasForm.industry"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                placeholder="e.g., Technology, Healthcare"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Platform
              </label>
              <select
                v-model="ideasForm.platform"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="">Select platform</option>
                <option value="twitter">Twitter</option>
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="linkedin">LinkedIn</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tone
              </label>
              <select
                v-model="ideasForm.tone"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="professional">Professional</option>
                <option value="casual">Casual</option>
                <option value="friendly">Friendly</option>
                <option value="humorous">Humorous</option>
                <option value="formal">Formal</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Number of Ideas
              </label>
              <input
                v-model.number="ideasForm.count"
                type="number"
                min="1"
                max="10"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="generating"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <LoadingOutlined v-if="generating" class="w-4 h-4 animate-spin mr-2" />
            {{ generating ? 'Generating...' : 'Generate Ideas' }}
          </button>
        </form>
      </div>

      <!-- Generated Ideas -->
      <div v-if="generatedIdeas.length > 0" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Content Ideas
        </h3>
        
        <div class="space-y-4">
          <div
            v-for="(idea, index) in generatedIdeas"
            :key="index"
            class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
          >
            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
              {{ idea.topic }}
            </h4>
            <p class="text-gray-600 dark:text-gray-400 mb-3">
              {{ idea.content_angle }}
            </p>
            <div class="flex flex-wrap gap-2 mb-3">
              <span
                v-for="hashtag in idea.hashtags"
                :key="hashtag"
                class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs"
              >
                #{{ hashtag }}
              </span>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              <p>Best time: {{ idea.posting_time }}</p>
              <p>Expected engagement: {{ idea.expected_engagement }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Usage Stats -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        AI Usage Statistics
      </h3>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total_generations || 0 }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">Total Generations</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.total_tokens || 0 }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">Tokens Used</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">${{ (stats.total_cost || 0).toFixed(4) }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">Total Cost</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-orange-600">{{ Object.keys(stats.by_type || {}).length }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">Types Used</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { LoadingOutlined } from '@ant-design/icons-vue';
import { useAuthStore } from '@/store/auth';
import { api } from '@/services/api';

// Types
interface GeneratedContent {
  content: string;
  hashtags: string[];
}

interface ContentIdea {
  topic: string;
  content_angle: string;
  hashtags: string[];
  posting_time: string;
  expected_engagement: string;
}

interface AiStats {
  total_generations: number;
  total_tokens: number;
  total_cost: number;
  by_type: Record<string, number>;
  by_model: Record<string, number>;
}

// State
const activeTab = ref('content');
const generating = ref(false);
const generatedContent = ref<GeneratedContent | null>(null);
const generatedHashtags = ref<string[]>([]);
const generatedIdeas = ref<ContentIdea[]>([]);
const stats = ref<AiStats>({
  total_generations: 0,
  total_tokens: 0,
  total_cost: 0,
  by_type: {},
  by_model: {},
});

// Forms
const contentForm = reactive({
  topic: '',
  platform: '',
  tone: 'professional',
  industry: '',
  target_audience: '',
  model: 'gpt-4',
});

const hashtagForm = reactive({
  content: '',
  platform: '',
  industry: '',
  tone: 'professional',
  model: 'gpt-4',
});

const ideasForm = reactive({
  industry: '',
  platform: '',
  tone: 'professional',
  count: 5,
  model: 'gpt-4',
});

// Tabs
const tabs = [
  { id: 'content', name: 'Generate Content' },
  { id: 'hashtags', name: 'Generate Hashtags' },
  { id: 'ideas', name: 'Content Ideas' },
];

// Methods
const generateContent = async () => {
  generating.value = true;
  try {
    const response = await api.post('/ai/generate-content', contentForm);
    generatedContent.value = {
      content: response.data.data.content,
      hashtags: response.data.data.hashtags,
    };
  } catch (error) {
    console.error('Failed to generate content:', error);
  } finally {
    generating.value = false;
  }
};

const generateHashtags = async () => {
  generating.value = true;
  try {
    const response = await api.post('/ai/generate-hashtags', hashtagForm);
    generatedHashtags.value = response.data.data.hashtags;
  } catch (error) {
    console.error('Failed to generate hashtags:', error);
  } finally {
    generating.value = false;
  }
};

const generateIdeas = async () => {
  generating.value = true;
  try {
    const response = await api.post('/ai/generate-ideas', ideasForm);
    generatedIdeas.value = response.data.data.ideas;
  } catch (error) {
    console.error('Failed to generate ideas:', error);
  } finally {
    generating.value = false;
  }
};

const useContent = () => {
  if (generatedContent.value) {
    emit('use-content', generatedContent.value);
  }
};

const regenerateContent = () => {
  generatedContent.value = null;
  generateContent();
};

const copyHashtag = (hashtag: string) => {
  navigator.clipboard.writeText(`#${hashtag}`);
};

const copyAllHashtags = () => {
  const hashtagsText = generatedHashtags.value.map(h => `#${h}`).join(' ');
  navigator.clipboard.writeText(hashtagsText);
};

const loadStats = async () => {
  try {
    const response = await api.get('/ai/stats');
    stats.value = response.data.data;
  } catch (error) {
    console.error('Failed to load AI stats:', error);
  }
};

// Lifecycle
onMounted(() => {
  loadStats();
});

// Emits
const emit = defineEmits<{
  'use-content': [content: GeneratedContent];
}>();
</script> 