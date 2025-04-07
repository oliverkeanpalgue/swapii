<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { Icon } from '@iconify/vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Notifications from "./Notifications.vue";
import UnreadNotifications from "./UnreadNotifications.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    notifications: Object,
});

onMounted(() => {
    console.log(props.notifications);
});
const num = ref(8);
</script>

<template>

    <Head title="Notifications" />

    <AuthenticatedLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Notifications</h1>
                <p class="mt-2 text-base text-gray-600">View and monitor your notifications</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md border border-gray-200 shadow">
                    <div class="py-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex gap-8 mb-6">
                                <Tab value="0" class="w-6/12 custom-tab">
                                    <div class="flex gap-3 justify-center items-center">
                                        <Icon
                                            icon="ic:round-notifications-none"
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">All Notifications</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="w-6/12 custom-tab">
                                    <div class="flex gap-3 justify-center items-center">
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
                                        <Notifications :notifications="props.notifications" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <UnreadNotifications :notifications="props.notifications" />
                                    </transition>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
