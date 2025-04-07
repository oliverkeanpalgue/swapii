<script setup>
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from '@iconify/vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PendingVerif from "./PendingVerif.vue";
import ApprovedVerif from "./ApprovedVerif.vue";
import RejectedVerif from "./RejectedVerif.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

const props = defineProps({
    verifications: Object
});
</script>

<template>
    <Head title="User Verification" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">User Verification</h1>
                <p class="mt-2 text-base text-gray-600">Review and manage user verification requests</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md shadow border border-gray-200">
                    <div class="py-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex gap-8 mb-6">
                                <Tab value="0" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="mdi:clock-outline" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">
                                            Pending
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="mdi:check-circle-outline" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">
                                            Approved
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="custom-tab w-4/12">
                                    <div class="flex items-center justify-center gap-3">
                                        <Icon 
                                            icon="mdi:close-circle-outline" 
                                            class="text-xl tab-icon"
                                        />
                                        <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">
                                            Rejected
                                        </span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <PendingVerif :users="props.verifications.pending" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <ApprovedVerif :users="props.verifications.approved" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="2">
                                    <transition name="tab-panel" mode="out-in">
                                        <RejectedVerif :users="props.verifications.rejected" />
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