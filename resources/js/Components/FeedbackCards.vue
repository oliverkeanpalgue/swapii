<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

defineProps({
    title: String,
    feedback: String,
    user: String,
    created_at: String
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <div class="bg-white rounded-md overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-primary cursor-pointer">        
        <div class="p-5">
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-medium text-white">
                        {{ getInitials(user) }}
                    </span>
                </div>
                    <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-medium text-base text-gray-900 truncate pr-4">{{ title }}</h3>
                        <span class="text-time text-gray-500 whitespace-nowrap flex-shrink-0">{{ created_at }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3 truncate">{{ user }}</p>
                    <p class="text-sm text-gray-600 line-clamp-2 whitespace-normal break-words">{{ feedback }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pl-13 {
    padding-left: 3.25rem;
}

.text-time {
    font-size: 0.67rem;
}
</style>
