<script setup>
import {
    Icon
} from '@iconify/vue';
import {
    Link
} from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    user: Object,
    message: String,
    sender: Object
});

const getInitials = (name) => {
    if (!name) return '';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

onMounted(() => {
    console.log('User with profile:', props.user);
});
</script>

<template>
    <Link class="z-40 flex items-center rounded-md sm:h-14 h-11" :href="route('chat.messages', props.user.id)">
        <div class="relative">
            <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                <img 
                    v-if="props.user.profilePicture" 
                    :src="props.user.profilePicture" 
                    class="w-full h-full object-cover rounded-full" 
                    :alt="props.user.name"
                />
                <span v-else class="text-sm font-medium text-white">
                    {{ getInitials(props.user?.name) }}
                </span>
            </div>
            <span class="top-0 start-6 absolute w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
        </div>
        <div class="px-3.5 w-72 max-w-xs">
            <p class="text-sm font-semibold">{{ props.user?.name }}</p>
            <p class="truncate text-xs text-gray-500 font-regular">
                {{ message }}
            </p>
        </div>
    </Link>
</template>
