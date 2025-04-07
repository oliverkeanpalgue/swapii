<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

// Format date helper function
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <div
        class="overflow-hidden relative bg-white rounded-md border border-gray-300 transition-all duration-200 group dark:bg-gray-800 dark:border-gray-700 hover:border-primary hover:shadow-lg">
        <!-- Image Section -->
        <div class="aspect-[4/3] sm:aspect-[5/3] overflow-hidden p-2 sm:p-3 relative">
            <img class="object-cover w-full h-full rounded-md transition-transform duration-200"
                :src="`/storage/${item.item_image}`" :alt="item.item_name" />
            <div
                class="flex absolute bottom-4 left-4 items-center px-2.5 py-1 text-xs text-white rounded-full backdrop-blur-sm bg-black/50">
                <Icon icon="heroicons:clock" class="mr-1" width="14" height="14" />
                {{ formatDate(item.created_at) }}
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-3 sm:p-4">
            <div class="mb-3 sm:mb-4">
                <div class="flex flex-row gap-2 justify-between mb-2 xs:flex-row xs:items-start xs:justify-between xs:gap-0">
                    <h3 class="text-sm font-semibold text-gray-900 sm:text-base dark:text-white">
                        {{ item.item_name }}
                    </h3>
                </div>
                <div class="flex flex-wrap gap-1 sm:gap-1.5">
                    <!-- <span v-for="tag in item.tags.slice(0, 3)" :key="tag.id"
                        class="px-1.5 py-0.5 text-xs font-medium rounded-full sm:px-2 bg-primary/10 text-primary">
                        {{ tag.tag }}
                    </span> -->
                </div>
            </div>

            <div class="mb-3 sm:mb-4">
                <p class="flex gap-2 items-center text-xs font-medium text-gray-600 dark:text-gray-300">
                    <Icon icon="heroicons:user" width="14" height="14" />
                    From: {{ item.trader }}
                </p>
            </div>
        </div>
    </div>
</template>
