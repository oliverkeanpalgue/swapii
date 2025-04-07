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
        class="overflow-hidden relative bg-white rounded-md border border-gray-300 transition-all duration-200 group hover:border-primary hover:shadow-lg">
        <div class="aspect-[4/3] sm:aspect-[5/3] overflow-hidden p-2 sm:p-3 relative">
            <img class="object-cover w-full h-full rounded-md transition-transform duration-200"
                :src="'/storage/' + props.item.images[0]?.image" :alt="props.item.item_name" />
            <div class="flex absolute bottom-4 left-4 items-center px-2.5 py-1 text-xs text-white rounded-full backdrop-blur-sm bg-black/50">
                <Icon icon="heroicons:clock" class="mr-1" width="14" height="14" />
                {{ formatDate(props.item.created_at) }}
            </div>
            <div class="flex absolute top-4 right-4 items-center px-2.5 py-1 text-xs text-white rounded-full backdrop-blur-sm bg-black/50">
                <Icon icon="heroicons:user-group" class="mr-1" width="14" height="14" />
                {{ props.item.trade_requests_count ?? 0 }} requests
            </div>
        </div>

        <div class="p-3 sm:p-4">
            <div class="mb-3 sm:mb-4">
                <div class="flex flex-row gap-2 justify-between mb-2 xs:flex-row xs:items-start xs:justify-between xs:gap-0">
                    <h3 class="text-sm font-semibold text-gray-900 sm:text-base">
                        {{ props.item.item_name }}
                    </h3>
                    <span class="view-count flex items-center">
                        <Icon icon="heroicons:eye" class="mr-1" width="14" height="14" />
                        {{props.item.view_count}}
                    </span>
                </div>
                <div class="flex flex-wrap gap-1 sm:gap-1.5">
                    <span v-for="(tag, index) in (props.item.tags || []).slice(0, 3)" :key="tag.id"
                        class="px-1.5 py-0.5 text-xs font-medium rounded-full sm:px-2 bg-primary/10 text-primary">
                        {{ tag.tag }}
                    </span>
                </div>
            </div>

            <div class="flex items-center mb-3 sm:mb-4">
                <p class="flex gap-2 items-center text-xs font-medium text-black">
                    <Icon icon="heroicons:gift" width="14" height="14" />
                    Preferred Item:
                    <span class="font-semibold text-primary"> {{ props.item.preferred_items }}</span>
                </p>
            </div>

            <div class="space-y-2">
                <Link :href="'/request-trade/' + props.item.id"
                    class="block px-3 py-2 w-full text-xs font-medium text-center text-white rounded-md transition-colors duration-200 bg-primary hover:bg-primary-200 sm:text-sm sm:px-4 sm:py-2.5">
                <span class="flex justify-center items-center">
                    <Icon icon="heroicons:arrow-path" class="mr-1.5" width="14" height="14" />
                    Request Trade
                </span>
                </Link>

                <div class="grid grid-cols-2 gap-1.5 sm:gap-2">
                    <Link :href="`/item-description/${props.item.id}`"
                        class="flex justify-center items-center px-2 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md border border-gray-300 transition-colors duration-200 sm:px-4 sm:py-2 sm:text-sm hover:bg-gray-200 active:bg-gray-300">
                    <Icon icon="heroicons:eye" class="mr-1" width="14" height="14" />
                    Details
                    </Link>

                    <Link :href="route('chat.messages', props.item.user_id)"
                        class="flex justify-center items-center px-2 py-1.5 text-xs font-medium text-gray-700 rounded-md border border-gray-300 transition-colors duration-200 sm:px-4 sm:py-2 sm:text-sm hover:bg-gray-50 active:bg-gray-100">
                    <Icon icon="heroicons:chat-bubble-left-right" class="mr-1" width="14" height="14" />
                    Message
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
