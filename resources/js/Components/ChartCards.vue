<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    name: {
        type: String,
    },
    icon: {
        type: String,
    },
    value: {
        type: String,
    },
    linkName: {
        type: String
    },
    href:{
        type: String
    },
});
</script>

<template>
    <div class="bg-white rounded-md border border-gray-200 shadow-sm h-full">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">{{ name }}</h2>
        </div>
        <div class="p-4 flex-1 h-[calc(100%-4rem)]">
            <slot></slot>
        </div>
    </div>
</template>