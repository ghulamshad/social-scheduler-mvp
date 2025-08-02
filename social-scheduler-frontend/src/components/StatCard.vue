<template>
  <div class="card p-4 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center">
      <div :class="`p-2 rounded-lg ${iconBgColor}`">
        <component :is="iconComponent" :class="`w-5 h-5 ${iconColor}`" />
      </div>
      <div class="ml-3">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="text-xl font-bold text-gray-900">{{ value }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  ClockCircleOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  TeamOutlined,
  FileTextOutlined,
} from '@ant-design/icons-vue';

interface Props {
  title: string;
  value: string | number;
  icon: 'clock' | 'check' | 'x' | 'users' | 'file';
  color: 'blue' | 'green' | 'red' | 'purple' | 'orange';
}

const props = defineProps<Props>();

const iconComponent = computed(() => {
  switch (props.icon) {
    case 'clock':
      return ClockCircleOutlined;
    case 'check':
      return CheckCircleOutlined;
    case 'x':
      return CloseCircleOutlined;
    case 'users':
      return TeamOutlined;
    case 'file':
      return FileTextOutlined;
    default:
      return ClockCircleOutlined;
  }
});

const iconBgColor = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'bg-blue-100';
    case 'green':
      return 'bg-green-100';
    case 'red':
      return 'bg-red-100';
    case 'purple':
      return 'bg-purple-100';
    case 'orange':
      return 'bg-orange-100';
    default:
      return 'bg-gray-100';
  }
});

const iconColor = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'text-blue-600';
    case 'green':
      return 'text-green-600';
    case 'red':
      return 'text-red-600';
    case 'purple':
      return 'text-purple-600';
    case 'orange':
      return 'text-orange-600';
    default:
      return 'text-gray-600';
  }
});
</script>
  