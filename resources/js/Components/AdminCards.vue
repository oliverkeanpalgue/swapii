<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    entity: {
        type: String,
    },
    icon: {
        type: String,
    },
    value: {
        type: [String, Number],
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    statsConfig: {
        type: Array,
        default: () => []
    },
    linkName: {
        type: String
    },
    href: {
        type: String
    },
    totalLabel: {
        type: String,
        default: 'total'
    },
    widgetType: {
        type: String,
        default: 'admin'
    }
});
</script>

<template>
    <div class="rounded-md bg-white border border-gray-300 border-s-primary border-s-8 shadow-lg p-4 cursor-default h-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold text-gray-800">{{ entity }}</h2>
                <Icon :icon="icon" class="w-5 h-5 text-gray-600" />
            </div>
            <div class="text-right flex items-center gap-2">
                <div class="text-xs text-gray-600 capitalize">{{ totalLabel }}</div>
                <span class="text-lg font-bold text-gray-900">{{ value }}</span>
            </div>
        </div>

        <div v-if="statsConfig.length > 0 && widgetType === 'two_column'" class="grid grid-cols-2 gap-4 mb-4">
            <div 
                v-for="(config, index) in statsConfig" 
                :key="index"
                class="flex flex-col items-center p-3 bg-gray-100 border-2 border-gray-200 rounded-md"
            >
                <Icon :icon="config.icon" :class="`w-6 h-6 ${config.iconColor} mb-1`" />
                <span class="text-xs font-medium text-gray-700">{{ config.label }}</span>
                <span class="font-semibold text-gray-900">{{ stats[config.key] }}</span>
            </div>
        </div>

        <div v-if="statsConfig.length > 0 && widgetType === 'three_column'" class="grid grid-cols-3 gap-4 mb-4">
            <div 
                v-for="(config, index) in statsConfig" 
                :key="index"
                class="flex flex-col items-center p-3 bg-gray-100 border-2 border-gray-200 rounded-md"
            >
                <Icon :icon="config.icon" :class="`w-6 h-6 ${config.iconColor} mb-1`" />
                <span class="text-xs font-medium text-gray-700">{{ config.label }}</span>
                <span class="font-semibold text-gray-900">{{ stats[config.key] }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-4">
            <Link :href="href" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-primary">
                {{ linkName }}
                <Icon icon="formkit:arrowright" class="w-6 h-6 ml-1 text-primary font-bold"/>
            </Link>
        </div>
    </div>
</template>