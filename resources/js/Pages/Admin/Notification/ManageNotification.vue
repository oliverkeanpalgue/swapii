<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { Icon } from '@iconify/vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Notifications from "./Notifications.vue";
import UnreadNotifications from "./UnreadNotifications.vue";

const num = ref(8);
const props = defineProps({
    moderators: Object,
    users: Object
});

onMounted(() => {
    console.log(props.admins);
});
</script>

<template>

    <Head title="Notifications" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Notifications</h1>
                <p class="mt-2 text-base text-gray-600">View and monitor your notifications</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md shadow border border-gray-200">
                    <div class="py-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex gap-8 mb-6">
                                <Tab value="0" class="custom-tab w-6/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="ic:round-notifications-none" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">All Notifications</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab w-6/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="material-symbols:notifications-unread-outline" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Unread Notification</span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <Notifications />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <UnreadNotifications />
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
