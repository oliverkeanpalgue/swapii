<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TermsOfUse from './Contents/TermsOfUse.vue';
import PrivacyPolicy from './Contents/PrivacyPolicy.vue';
import CommunityGuidelines from './Contents/CommunityGuidelines.vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const activeTab = ref(0);

const tabs = [
    { 
        label: 'Terms of Conditions', 
        icon: 'fa6-solid:file-contract',
        component: TermsOfUse
    },
    { 
        label: 'Privacy Policy', 
        icon: 'fluent:shield-lock-20-filled',
        component: PrivacyPolicy
    },
    { 
        label: 'Community Guidelines', 
        icon: 'solar:clipboard-list-bold',
        component: CommunityGuidelines
    }
];
</script>

<template>
    <Head title="Legal Information" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-4 md:py-8">
                <div class="hidden md:block mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">SWAPII Policies</h1>
                </div>

                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex flex-col md:block md:w-64 flex-shrink-0">
                        <nav class="flex-row space-y-1 flex md:flex-col gap-2 md:gap-0 overflow-auto">
                            <button 
                                v-for="(tab, index) in tabs" 
                                :key="index"
                                @click="activeTab = index"
                                class="flex items-center justify-center sm:justify-start gap-3 px-1 py-2 text-sm md:text-base font-medium rounded-md w-full md:w-auto"
                                :class="[
                                    activeTab === index
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                <Icon :icon="tab.icon" class="text-xlsm:mr-3" />
                                <span class="hidden sm:block">{{ tab.label }}</span>
                            </button>
                        </nav>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white rounded-md shadow-sm">
                            <div class="p-4 md:p-6">
                                <component 
                                    :is="tabs[activeTab].component"
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


