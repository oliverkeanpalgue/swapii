<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from './PrimaryButton.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    item: Object
});

// Add date formatting function
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <div
        class="group relative bg-white rounded-md border border-gray-300 overflow-hidden hover:border-primary hover:shadow-lg transition-all duration-200">
        <div class="aspect-[4/3] sm:aspect-[5/3] overflow-hidden p-2 sm:p-3 relative">
            <img class="w-full h-full object-cover transition-transform duration-200 rounded-md"
                :src="'/storage/' + props.item.images[0]?.image" :alt="props.item.item_name" />
            <div class="absolute bottom-4 left-4 bg-black/50 backdrop-blur-sm rounded-full px-2.5 py-1 text-white text-xs flex items-center">
                <Icon icon="heroicons:clock" class="mr-1" width="14" height="14" />
                {{ formatDate(props.item.created_at) }}
            </div>
            <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm rounded-full px-2.5 py-1 text-white text-xs flex items-center">
                <Icon icon="heroicons:user-group" class="mr-1" width="14" height="14" />
                {{ props.item.trade_requests_count ?? 0 }} requests
            </div>
        </div>

        <div class="p-3 sm:p-4">
            <div class="mb-3 sm:mb-4">
                <div class="flex flex-row justify-between xs:flex-row xs:items-start xs:justify-between gap-2 xs:gap-0 mb-2">
                    <h3 class="font-semibold text-sm sm:text-base text-gray-900">
                        {{ props.item.item_name }}
                    </h3>
                </div>
                <div class="flex flex-wrap gap-1 sm:gap-1.5">
                    <span v-for="(tag, index) in (props.item.tags || []).slice(0, 3)" :key="tag.id"
                        class="px-1.5 sm:px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded-full">
                        {{ tag.tag }}
                    </span>
                </div>
            </div>

            <div class="mb-3 sm:mb-4 flex items-center">
                <p class="text-xs font-medium text-black flex items-center gap-2">
                    <Icon icon="heroicons:gift" width="14" height="14" />
                    Preferred Item:
                    <span class="text-primary font-semibold"> {{ props.item.preferred_items }}</span>
                </p>
            </div>

            <div class="space-y-2">
                <Link :href="'/request-trade/' + props.item.id"
                    class="block w-full bg-primary hover:bg-primary-200 text-white text-xs sm:text-sm font-medium px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition-colors duration-200 text-center">
                <span class="flex items-center justify-center">
                    <Icon icon="heroicons:arrow-path" class="mr-1.5" width="14" height="14" />
                    Request Trade
                </span>
                </Link>

                <div class="grid grid-cols-2 gap-1.5 sm:gap-2">
                    <Link :href="`/item-description/${props.item.id}`"
                        class="flex items-center justify-center px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-md transition-colors duration-200 border border-gray-300">
                    <Icon icon="heroicons:eye" class="mr-1" width="14" height="14" />
                    Details
                    </Link>

                    <Link :href="route('chat.messages', props.item.user_id)"
                        class="flex items-center justify-center px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 active:bg-gray-100 rounded-md transition-colors duration-200">
                    <Icon icon="heroicons:chat-bubble-left-right" class="mr-1" width="14" height="14" />
                    Message
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
