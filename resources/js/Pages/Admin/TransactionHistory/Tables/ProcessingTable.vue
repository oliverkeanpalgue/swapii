<script setup>
import { ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    transactions: {
        type: Array,
        required: true
    }
});

// Filter setup
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});


// Pagination setup
const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

// Date formatter from RequestsTable
function formatDateTime(dateTimeString) {
    try {
        if (!dateTimeString) return '';
        
        const date = new Date(dateTimeString);
        
        if (isNaN(date.getTime())) {
            throw new Error('Invalid date');
        }

        const formattedDate = date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const formattedTime = date.toLocaleString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });

        return `${formattedDate} at ${formattedTime}`;
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateTimeString;
    }
}
</script>

<template>
    <section v-if="!transactions.length" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl sm:px-4">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon
                        icon="carbon:ibm-data-product-exchange"
                        class="w-24 h-24 text-gray-300"
                    />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Transactions
                </h3>
                <p class="mb-6 text-gray-500">
                    There are no transactions available at the moment.
                </p>
            </div>
        </div>
    </section>

    <section v-else>
        <DataTable
            v-model:filters="filters"
            :value="transactions"
            removableSort
            tableStyle="min-width: 100%"
            class="overflow-hidden"
            :globalFilterFields="['trader', 'recipient', 'item', 'place']"
            paginator
            :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions"
            responsiveLayout="scroll"
        >
            <template #header>
                <div class="flex gap-4 justify-between items-center mb-4">
                    <div class="relative w-96 max-w-xs">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                            </div>
                            <TextInput
                                v-model="filters['global'].value"
                                type="text"
                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search transactions"
                            />
                        </div>
                    </div>
                </div>
            </template>
            <Column field="trader" header="Trader" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <div class="font-medium text-gray-900">{{ data.trader }}</div>
                        <div class="text-xs text-gray-500">Trading: {{ data.trading_item }}</div>
                    </div>
                </template>
            </Column>

            <Column field="recipient" header="Recipient" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <div class="font-medium text-gray-900">{{ data.recipient }}</div>
                        <div class="text-xs text-gray-500">Receiving: {{ data.offered_item }}</div>
                    </div>
                </template>
            </Column>


            <Column field="place" header="Location" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.place }}</div>
                </template>
            </Column>

            <Column field="time" header="Transaction Date & Time" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDateTime(data.time) }}</div>
                </template>
            </Column>
        </DataTable>
    </section>
</template>

<style scoped>
:deep(.p-datatable) {
    border-radius: 0.5rem;
    overflow: hidden;
}

:deep(.p-dropdown) {
    min-width: 150px;
}

:deep(.p-tag) {
    font-size: 0.75rem;
}
</style>
