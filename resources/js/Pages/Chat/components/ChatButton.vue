<script setup>
import { defineProps } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  icon: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: '24px', 
  },

  type: String
});
</script>

<template>
  <button type="{{ props.type }}"  class="inline-flex justify-center p-2 rounded-full cursor-pointer text-primary hover:bg-gray-200">
    <!-- Use Iconify's Icon component with dynamic icon and size -->
    <Icon :icon="icon" width="1.5rem" height="1.5rem" />
    <slot></slot>
  </button>
</template>
