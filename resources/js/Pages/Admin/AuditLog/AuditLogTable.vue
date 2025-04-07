<script setup>
import { onMounted, ref } from 'vue'
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    logs: Object,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function getStatusLabel(status) {
    if (status === 'accept') return "Accepted";
    if (status === 'reject') return "Rejected";
    return "Unknown";
}

function getStatusSeverity(status) {
    if (status === 'accept') return "success";
    if (status === 'reject') return "danger";
    return "info";
}

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
};

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];
</script>

<template>
    <section v-if="items?.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-md">
        <div class="flex flex-col justify-center items-center">
            <p class="text-sm text-gray-500">No audit logs available at the moment.</p>
        </div>
    </section>

    <section v-else>
        <DataTable
            v-model:filters="filters"
            :value="props.logs"
            removableSort
            tableStyle="min-width: 100%"
            class="overflow-hidden"
            :globalFilterFields="['admin.name', 'action_type', 'recipient', 'details']"
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
                                placeholder="Search logs..."
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="created_at" header="Date/Time" sortable class="text-sm">
                <template #body="{ data }">
                    <div class="text-gray-600">
                        {{ formatDate(data.created_at) }} at {{ formatTime(data.created_at) }}
                    </div>
                </template>
            </Column>

            <Column field="user" header="Moderator" sortable class="text-sm">
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <!-- <div v-if="data.admin?.avatar" class="overflow-hidden w-8 h-8 rounded-full">
                            <img :src="data.admin.avatar" :alt="data.admin.name" class="object-cover w-full h-full">
                        </div> -->
                        <div class="h-8 w-8 rounded-full bg-[#F5A623] flex items-center justify-center">
                            <span class="font-medium text-white">{{ data.user.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="font-medium text-gray-900">{{ data.user }}</div>
                    </div>
                </template>
            </Column>
            
            <Column field="item" header="Item name" sortable class="text-sm">
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.item }}</div>
                </template>
            </Column>

            <!-- <Column field="details" header="Details" class="text-sm">
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.details }}</div>
                </template>
            </Column> -->

            <Column field="status" header="Status" sortable class="text-sm">
                <template #body="{ data }">
                    <Tag :value="getStatusLabel(data.action)"
                         :severity="getStatusSeverity(data.action)" />
                </template>
            </Column>
        </DataTable>
    </section>
</template>

<style scoped>
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
    background: #000000;
    color: #000000;
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

:deep(.p-inputtext::placeholder) {
    color: #9ca3af;
}

:deep(.p-inputtext:focus) {
    box-shadow: 0 0 0 2px #e2e8f0;
    border-color: #64748b;
}

:deep(.p-paginator) {
    padding: 1rem;
    font-size: 0.875rem;
    background: transparent;
    border: none;
}

:deep(.p-paginator .p-paginator-pages .p-paginator-page) {
    min-width: 2rem;
    height: 2rem;
}

:deep(.p-paginator .p-paginator-pages .p-paginator-page.p-highlight) {
    background: #E2E8F0;
    color: #475569;
}

:deep(.p-paginator .p-paginator-first),
:deep(.p-paginator .p-paginator-prev),
:deep(.p-paginator .p-paginator-next),
:deep(.p-paginator .p-paginator-last) {
    min-width: 2rem;
    height: 2rem;
}
</style>
