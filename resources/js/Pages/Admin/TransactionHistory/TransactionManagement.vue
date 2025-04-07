<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import TransactionTable from './Tables/TransactionTable.vue';
import PendingTable from './Tables/PendingTable.vue';
import ProcessingTable from './Tables/ProcessingTable.vue';
import SuccessTable from './Tables/SuccessTable.vue';
import CancelledTable from './Tables/CancelledTable.vue';
import RejectedTable from './Tables/RejectedTable.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    transactions: {
        type: Array,
        required: true
    },
    success: {
        type: Array,
        required: true
    },
    cancelled: {
        type: Array,
        required: true
    },
    rejected: {
        type: Array,
        required: true
    },
    processing: {
        type: Array,
        required: true
    },
    pending: {
        type: Array,
        required: true
    },


});

const isLoading = ref(false);
</script>

<template>
    <Head title="Transaction Management" />
    <AdminLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-3 lg:px-8">
            <!-- Header Section -->
            <div class="py-3 border-b border-gray-200 sm:py-4 md:py-6">
                <h1 class="flex gap-2 items-center text-xl font-bold text-gray-900 sm:text-2xl">
                    <Icon icon="carbon:ibm-data-product-exchange" class="w-5 h-5 sm:w-6 sm:h-6" />
                    Transaction Management
                </h1>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">View and manage all trade transactions</p>
            </div>

            <!-- Main Content -->
            <div class="py-4 sm:py-6">
                <div class="bg-white rounded-md shadow">
                    <div class="p-1 sm:p-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex overflow-x-auto border-b border-gray-200">
                                <Tab value="0" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="heroicons:clipboard-document-list" class="w-5 h-5 mr-2" />
                                        <span>All</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="heroicons:clock" class="w-5 h-5 mr-2" />
                                        <span>Pending</span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="akar-icons:gear" class="w-5 h-5 mr-2" />
                                        <span>Processing</span>
                                    </div>
                                </Tab>
                                <Tab value="3" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="heroicons:check-circle" class="w-5 h-5 mr-2" />
                                        <span>Success</span>
                                    </div>
                                </Tab>
                                <Tab value="4" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="heroicons:x-circle" class="w-5 h-5 mr-2" />
                                        <span>Cancelled</span>
                                    </div>
                                </Tab>
                                <Tab value="5" class="custom-tab">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[90px]">
                                        <Icon icon="heroicons:exclamation-circle" class="w-5 h-5 mr-2" />
                                        <span>Rejected</span>
                                    </div>
                                </Tab>
                            </TabList>

                            <TabPanels>
                                <TabPanel value="0">
                                    <div class="mt-4">
                                        <TransactionTable :transactions="transactions" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="1">
                                    <div class="mt-4">
                                        <PendingTable :transactions="pending" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="2">
                                    <div class="mt-4">
                                        <ProcessingTable :transactions="processing" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="3">
                                    <div class="mt-4">
                                        <SuccessTable :transactions="success" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="4">
                                    <div class="mt-4">
                                        <CancelledTable :transactions="cancelled" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="5">
                                    <div class="mt-4">
                                        <RejectedTable :transactions="rejected" />
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="isLoading"
             class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
            <div class="w-12 h-12 rounded-full border-t-2 border-b-2 animate-spin border-primary"></div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

:deep(.custom-tabs .p-tabview-nav) {
    border: none;
}

:deep(.custom-tab) {
    margin-right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

:deep(.custom-tab:hover) {
    background-color: #f3f4f6;
}

:deep(.custom-tab.p-highlight) {
    background-color: #f3f4f6;
    border-bottom: 2px solid #F5A623;
}
</style>
