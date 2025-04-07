<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { Icon } from '@iconify/vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserTable from "./UserTable.vue";
import TabButton from "@/Components/TabButton.vue";
import ModeratorTable from "./ModeratorTable.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

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

    <Head title="User Management" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">User Management</h1>
                <p class="mt-2 text-base text-gray-600">Manage and monitor user accounts and moderators</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md shadow border border-gray-200">
                    <div class="py-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex gap-8 mb-6">
                                <Tab value="0" class="custom-tab w-6/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="heroicons:users" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Users</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab w-6/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="heroicons:shield-check" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Moderators</span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <UserTable :users="props.users" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <ModeratorTable :users="props.moderators" />
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
