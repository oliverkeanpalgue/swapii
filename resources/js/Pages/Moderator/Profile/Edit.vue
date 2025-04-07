<script setup>
import { ref } from 'vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import ModeratorLayout from '@/Layouts/ModeratorLayout.vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profile Settings" />

    <ModeratorLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-2 md:py-4 border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your account preferences and security settings</p>
            </div>
            
            <div class="py-2">
                <div class="bg-white rounded-md shadow border border-gray-200">
                    <div class="p-6 md:p-8">
                        <Tabs value="0" class="custom-tabs">
                            <TabList>
                                <Tab value="0" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center">
                                        <Icon 
                                            icon="heroicons:user-circle" 
                                            class="text-xl md:mr-2 tab-icon"
                                        />
                                        <span class="hidden sm:block text-wrap">Profile Information</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center">
                                        <Icon 
                                            icon="heroicons:shield-check" 
                                            class="text-xl md:mr-2 tab-icon"
                                        />
                                        <span class="hidden sm:block text-wrap">Security Settings</span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center">
                                        <Icon 
                                            icon="heroicons:trash" 
                                            class="text-xl md:mr-2 tab-icon"
                                        />
                                        <span class="hidden sm:block text-wrap">Delete Account</span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <div class="space-y-6">
                                        <div class="border-b pb-4">
                                            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                                            <p class="mt-1 text-sm text-gray-600">
                                            <span class="">Update your account's profile information and email address.</span>
                                            </p>
                                        </div>
                                        <transition name="tab-panel" mode="out-in">
                                            <UpdateProfileInformationForm
                                                :must-verify-email="mustVerifyEmail"
                                                :status="status"
                                            />
                                        </transition>
                                    </div>
                                </TabPanel>
                                <TabPanel value="1">
                                    <div class="space-y-6">
                                        <div class="border-b pb-4">
                                            <h2 class="text-lg font-medium text-gray-900">Security Settings</h2>
                                            <p class="mt-1 text-sm text-gray-600">
                                                Ensure your account is using a secure password to keep your account protected.
                                            </p>
                                        </div>
                                        <transition name="tab-panel" mode="out-in">
                                            <UpdatePasswordForm />
                                        </transition>
                                    </div>
                                </TabPanel>
                                <TabPanel value="2">
                                    <div class="space-y-6">
                                        <div class="border-b pb-4">
                                            <h2 class="text-lg font-medium flex items-center text-red-600">
                                                <Icon icon="heroicons:exclamation-triangle" class="w-5 h-5 mr-2" />
                                                Delete Account
                                            </h2>
                                            <p class="mt-1 text-sm text-gray-600">
                                                Once your account is deleted, all of its resources and data will be permanently deleted. 
                                                Before deleting your account, please download any data or information that you wish to retain.
                                            </p>
                                        </div>
                                        <transition name="tab-panel" mode="out-in">
                                            <DeleteUserForm />
                                        </transition>
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </ModeratorLayout>
</template>

<style scoped>
:deep(.custom-tabs) {
    [role="tablist"] {
        border-bottom: 1px solid #dee2e6;
    }
    [role="tab"] {
        padding: 1rem;
        color: #495057;
        transition: color 0.3s;
    }
    .tab-icon {
        color: #495057;
        transition: color 0.3s;
    }
    [role="tab"][aria-selected="true"] .tab-icon {
        color: #F5A623;
    }
    [role="tab"]:hover .tab-icon {
        color: #F5A623;
    }
    [role="tab"][aria-selected="true"] {
        color: #F5A623;
        border-bottom: 2px solid #F5A623;
    }
    [role="tab"]:hover {
        color: #F5A623;
    }
}
</style>


