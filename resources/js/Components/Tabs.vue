<script setup>
import { defineEmits, defineProps } from 'vue';
import TabLink from '@/Components/TabButton.vue';

const props = defineProps({
    activeTab: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['tab-change']);

const changeTab = (tab) => {
    emit('tab-change', tab);
};
</script>

<template>
    <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px">
            <li class="me-2">
                <button
                    :href="'#'"
                    @click.prevent="changeTab('profile')"
                    :class="['inline-block p-4 border-b-2 rounded-t-lg hover:text-primary hover:border-primary hover:border-gray-300  focus:text-primary ',
                    activeTab === 'profile' ? 'text-primary border-primary' : '']"
                >
                    Profile
                </button>
            </li>
            <li class="me-2">
                <button
                    :href="'#'"
                    @click.prevent="changeTab('managepost')"
                    :class="['inline-block p-4 border-b-2 rounded-t-lg hover:text-primary hover:border-primary hover:border-gray-300 focus:text-primary',
                    activeTab === 'managepost' ? 'text-primary border-primary' : '']"
                >
                    Manage Post
            </button>
            </li>
        </ul>
    </div>
</template>
