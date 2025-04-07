<script setup>
import { ref } from 'vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-2 md:py-4 border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your account preferences and security settings</p>
            </div>
            
            <div class="py-2">
                <div class="bg-white rounded-md shadow border border-gray-200">
                    <div class="p-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList>
                                <Tab value="0" class="custom-tab w-1/2">
                                    <div class="flex items-center justify-center">
                                        <Icon 
                                            icon="heroicons:user-circle" 
                                            class="text-xl md:mr-2 tab-icon"
                                        />
                                        
                                        <span class="hidden sm:block">Profile Information</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab w-1/2">
                                    <div class="flex items-center justify-center">
                                        <Icon 
                                            icon="heroicons:shield-check" 
                                            class="text-xl md:mr-2 tab-icon"
                                        />
                                        <span class="hidden sm:block">Security Settings</span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <UpdateProfileInformationForm
                                            :must-verify-email="mustVerifyEmail"
                                            :status="status"
                                        />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <UpdatePasswordForm />
                                    </transition>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>


