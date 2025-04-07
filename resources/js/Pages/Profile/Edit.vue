<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import ProfileRating from './Partials/ProfileRating.vue';

const props = defineProps({
    // mustVerifyAccount: Boolean,
    status: String,
    ratings: Array,
    averageRating: [Number, String],
    ratingsCount: Number,
    activeTab: {
        type: Number,
        default: 0
    }
});

const page = usePage();
const activeTabRef = ref(props.activeTab || 0);

watch(() => page.props.activeTab, (newValue) => {
    if (newValue !== undefined) {
        activeTabRef.value = newValue;
    }
});

const tabs = [
    {
        label: 'Profile Information',
        icon: 'mdi:account-circle',
        component: UpdateProfileInformationForm,
    },
    {
        label: 'Rating and Reviews',
        icon: 'mdi:star',
        component: ProfileRating,
        props: {
            ratings: props.ratings,
            averageRating: props.averageRating,
            ratingsCount: props.ratingsCount
        }
    },
    {
        label: 'Security',
        icon: 'mdi:shield-lock',
        component: UpdatePasswordForm
    },
    {
        label: 'Delete Account',
        icon: 'mdi:account-remove',
        component: DeleteUserForm
    }
];

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabFromUrl = urlParams.get('activeTab');
    if (tabFromUrl !== null) {
        activeTabRef.value = parseInt(tabFromUrl);
        // Clean up URL
        window.history.replaceState({}, '', route('profile.edit'));
    }
});
</script>

<template>
    <Head title="Profile Settings" />

    <AuthenticatedLayout>
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="py-4 md:py-8">
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                            <p class="mt-1 text-sm text-gray-500">Manage your account preferences and settings</p>
                        </div>

                    </div>
                </div>

                <div class="flex flex-col gap-6 md:flex-row">

                    <div class="flex flex-col flex-shrink-0 md:block md:w-64">
                        <nav class="flex overflow-auto flex-row gap-2 space-y-1 md:flex-col md:gap-0">
                            <button
                                v-for="(tab, index) in tabs"
                                :key="index"
                                @click="activeTabRef = index"
                                class="flex gap-3 justify-center items-center px-4 py-2 w-full text-sm font-medium rounded-md hover:bg-primary/10 sm:justify-start md:text-base md:w-auto"
                                :class="[
                                    activeTabRef === index
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                <Icon :icon="tab.icon" class="text-xlsm:mr-3" />
                                <span class="hidden sm:block">{{ tab.label }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Main content area -->
                    <div class="flex-1 min-w-0">
                        <!-- tab label on mobile -->
                        <div class="bg-white rounded-md shadow-sm md:hidden">
                            <div class="flex gap-3 items-center p-4 md:p-6">
                                <Icon :icon="tabs[activeTabRef].icon" class="text-xl" />
                                <span class="text-lg font-medium text-gray-900">{{ tabs[activeTabRef].label }}</span>
                            </div>
                        </div>
                        <div class="mt-4 bg-white rounded-md shadow-sm md:mt-0">
                            <div class="p-4 md:p-6">
                                <component
                                    :is="tabs[activeTabRef].component"
                                    v-bind="tabs[activeTabRef].props || {}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Optional styling to enhance responsiveness */
@media (max-width: 768px) {
    /* Increase spacing and adjust font sizes for mobile */
    .text-xl {
        font-size: 1.25rem;
    }
}
</style>
