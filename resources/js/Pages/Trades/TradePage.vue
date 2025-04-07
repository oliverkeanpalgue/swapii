<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Icon } from '@iconify/vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TabButton from '@/Components/TabButton.vue';
import RequestsTable from './Tables/RequestsTable.vue';
import SuccessfulTrades from './Tables/SuccessfulTrades.vue';
import TransactionsTable from './Tables/TransactionsTable.vue';
import { onMounted } from 'vue';
import { computed } from 'vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import CancelledTable from './Tables/CancelledTable.vue';
import ProcessingTable from './Tables/ProcessingTable.vue';
import RejectedReqTable from './Tables/RejectedReqTable.vue';

const props = defineProps({

    items: Object,
    pending: Object,
    cancelled: Object,
    rejected: Object,
    processing: Object,
    success: Object,

    filters: Object
})


onMounted(() => {

})
const page = ref('your_req');

// Add isLoading ref
const isLoading = ref(false);
</script>

<template>
    <Head title="Trades" />
    <AuthenticatedLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-3 lg:px-8">
            <!-- Added Header Section -->
            <div class="py-3 border-b border-gray-200 sm:py-4 md:py-6">
                <h1 class="flex gap-2 items-center text-xl font-bold text-gray-900 sm:text-2xl">
                    <Icon icon="carbon:ibm-data-product-exchange" class="w-5 h-5 sm:w-6 sm:h-6" />
                    Trade Requests
                </h1>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">View and manage all your trade requests and transactions</p>
            </div>

            <!-- Main Content -->
            <div class="py-4 sm:py-6">
                <div class="bg-white rounded-md shadow">
                    <div class="p-1 sm:p-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex overflow-x-auto border-b border-gray-200">
                                <Tab value="0" class="custom-tab" @click="page = 'your_req'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="heroicons:clipboard-document-list" class="w-5 h-5 mr-2" />
                                        <span>All</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="custom-tab " @click="page = 'your_req'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="heroicons:clock" class="w-5 h-5 mr-2" />
                                        <span>Pending</span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="custom-tab" @click="page = 'transactions'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="akar-icons:gear" class="w-5 h-5 mr-2" />
                                        <span>Processing</span>
                                    </div>
                                </Tab>
                                <Tab value="3" class="custom-tab" @click="page = 'success'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="heroicons:check-circle" class="w-5 h-5 mr-2" />
                                        <span>Success</span>
                                    </div>
                                </Tab>
                                <Tab value="4" class="custom-tab" @click="page = 'success'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="heroicons:x-circle" class="w-5 h-5 mr-2" />
                                        <span>Cancelled</span>
                                    </div>
                                </Tab>
                                <Tab value="5" class="custom-tab" @click="page = 'success'">
                                    <div class="flex items-center justify-center px-4 py-2.5 text-sm font-medium min-w-[150px]">
                                        <Icon icon="heroicons:exclamation-circle" class="w-5 h-5 mr-2" />
                                        <span>Rejected</span>
                                    </div>
                                </Tab>
                            </TabList>

                            <TabPanels>
                                <TabPanel value="0">
                                    <div class="mt-4">
                                        <TransactionsTable :filters="props.filters" :items="props.items" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="1">
                                    <div class="mt-4">
                                        <RequestsTable :filters="props.filters" :items="props.pending" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="2">
                                    <div class="mt-4">
                                        <ProcessingTable :filters="props.filters" :items="props.processing" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="3">
                                    <div class="mt-4">
                                        <SuccessfulTrades :filters="props.filters" :items="props.success" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="4">
                                    <div class="mt-4">
                                        <CancelledTable :filters="props.filters" :items="props.cancelled" />
                                    </div>
                                </TabPanel>
                                <TabPanel value="5">
                                    <div class="mt-4">
                                        <RejectedReqTable :filters="props.filters" :items="props.rejected" />
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>

        <!-- Added Loading Overlay -->
        <div v-if="isLoading"
             class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
            <div class="w-12 h-12 rounded-full border-t-2 border-b-2 animate-spin border-primary"></div>
        </div>

        <!-- Added Transition Effects -->
        <TransitionGroup
            name="fade"
            mode="out-in"
            class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        </TransitionGroup>
    </AuthenticatedLayout>
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
</style>
