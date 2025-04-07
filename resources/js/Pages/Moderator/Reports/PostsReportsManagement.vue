<script setup>
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from '@iconify/vue';
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import PendingReportsTable from "./PendingReportsTable.vue";
import ApprovedReportsTable from "./ApprovedReportsTable.vue";
import RejectedReportsTable from "./RejectedReportsTable.vue";

const props = defineProps({
    pendingReports: Object,
    processedReports: Object,
    dismissedReports: Object,
});
</script>

<template>
    <Head title="Post Reports Management" />

    <ModeratorLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Post Reports Management</h1>
                <p class="mt-2 text-base text-gray-600">Manage and monitor posts reports</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md border border-gray-200 shadow">
                    <div class="px-4 py-2 sm:px-6">
                        <Tabs value="0" class="custom-tabs">
                            <TabList class="flex flex-wrap gap-2 justify-between mb-6 w-full sm:gap-8">
                                <Tab value="0" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon icon="heroicons:clipboard-document-list" class="text-base sm:text-xl tab-icon" />
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Pending Reports</span>
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon icon="heroicons:check-circle" class="text-base sm:text-xl tab-icon" />
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Approved Reports</span>
                                        </span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="w-4/12 custom-tab">
                                    <div class="flex gap-2 justify-center items-center sm:gap-3">
                                        <Icon icon="heroicons:x-circle" class="text-base sm:text-xl tab-icon" />
                                        <span class="text-xs font-medium sm:text-base">
                                            <span class="md:font-[12px] lg:font-[15px] font-medium hidden sm:block text-wrap">Rejected Reports</span>
                                        </span>
                                    </div>
                                </Tab>
                            </TabList>
                            <TabPanels>
                                <TabPanel value="0">
                                    <transition name="tab-panel" mode="out-in">
                                        <PendingReportsTable :reports="props.pendingReports" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="1">
                                    <transition name="tab-panel" mode="out-in">
                                        <ApprovedReportsTable :reports="props.processedReports" />
                                    </transition>
                                </TabPanel>
                                <TabPanel value="2">
                                    <transition name="tab-panel" mode="out-in">
                                        <RejectedReportsTable :reports="props.dismissedReports" />
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
<!--
<style scoped>
:deep(.custom-tabs) {
    [role="tablist"] {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1px;
    }
    [role="tab"] {
        padding: 0.75rem 1rem;
        color: #6b7280;
        transition: all 0.2s ease;
        position: relative;
    }
    .tab-icon {
        color: #6b7280;
        transition: color 0.2s ease;
    }
    [role="tab"][aria-selected="true"] {
        color: #F5A623;
    }
    [role="tab"][aria-selected="true"]::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #F5A623;
        border-radius: 2px;
    }
    [role="tab"][aria-selected="true"] .tab-icon {
        color: #F5A623;
    }
    [role="tab"]:hover {
        color: #F5A623;
    }
    [role="tab"]:hover .tab-icon {
        color: #F5A623;
    }
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
    padding: 0.75rem 1rem;
    font-weight: 500;
    color: #475569;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

:deep(.p-datatable .p-datatable-thead > tr > th:hover) {
    background: #e0dfdf;
    cursor: pointer;
    color: #000000;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #E2E8F0;
}

:deep(.p-datatable .p-sortable-column:not(.p-highlight):hover) {
    background: #e0dfdf;
    color: #475569;
    cursor: pointer;
}

:deep(.p-datatable .p-sortable-column.p-highlight) {
    background: #F8FAFC;
    color: #475569;
}

:deep(.p-datatable .p-sortable-column.p-highlight:hover) {
    background: #F1F5F9;
    color: #475569;
    cursor: pointer;
}

:deep(.p-datatable .p-sortable-column:focus) {
    box-shadow: inset 0 0 0 0.15rem #E2E8F0;
}

:deep(.p-link:focus) {
    box-shadow: none;
}

:deep(.p-button.p-button-text) {
    padding: 0;
}

:deep(.p-button.p-button-text:hover) {
    background: transparent;
}

:deep(.p-button.p-button-text:focus) {
    box-shadow: none;
}

:deep(.p-inputtext) {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
}
</style> -->
