<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { Icon } from '@iconify/vue';
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import PendingPostTable from "./PendingPostTable.vue";
import ApprovedPostTable from "./ApprovedPostTable.vue";
import RejectedPostTable from "./RejectedPostTable.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

const props = defineProps({
    pending: Object,
    approved: Object,
    rejected: Object
});

onMounted(() => {
    console.log(props.pending);
});
</script>

<template>
    <Head title="Post Management" />

    <ModeratorLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Post Management</h1>
                <p class="mt-2 text-base text-gray-600">Review and manage user submissions</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md border border-gray-200 shadow">
                    <div class="px-4 py-2 sm:px-6">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex flex-wrap gap-2 justify-between mb-6 w-full sm:gap-8">
                                <Tab value="0" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon
                                            icon="heroicons:clock"
                                            class="text-base sm:text-xl tab-icon"
                                        />
                                        <!-- Pending" on small screens -->
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Pending Posts</span>
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon
                                            icon="heroicons:check-circle"
                                            class="text-base sm:text-xl tab-icon"
                                        />
                                        <!-- "Approved" on small screens -->
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Approved Posts</span>
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon
                                            icon="heroicons:x-circle"
                                            class="text-base sm:text-xl tab-icon"
                                        />
                                        <!-- "Rejected" on small screens -->
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Rejected Posts</span>
                                        </span>
                                    </div>
                                </Tab>
                            </TabList>
                            <!-- Tab Panels -->
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <PendingPostTable :items="props.pending" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <ApprovedPostTable :items="props.approved" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="2">
                                    <transition name="tab-panel" mode="out-in">
                                        <RejectedPostTable :items="props.rejected" />
                                    </transition>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </ModeratorLayout>
</template>
