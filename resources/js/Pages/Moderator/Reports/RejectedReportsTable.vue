<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    reports: Object
});

const confirm = useConfirm();
const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const rows = ref(10);
const rowsPerPageOptions = [10, 20, 50];

const unlistItem = (id) => {
    confirm.require({
        message: 'Are you sure you want to unlist this item?',
        header: 'Unlist Item',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.post(`/moderator/reports/post/${id}/unlist`, {}, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Item has been unlisted', life: 3000 });
                }
            });
        }
    });
};

const dismissReport = (id) => {
    confirm.require({
        message: 'Are you sure you want to dismiss this report?',
        header: 'Dismiss Report',
        icon: 'pi pi-info-circle',
        accept: () => {
            router.post(`/moderator/reports/post/${id}/dismiss`, {}, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Report has been dismissed', life: 3000 });
                }
            });
        }
    });
};

onMounted(() => {
    console.log(props.reports);
});
</script>

<template>
    <section v-if="props.reports.data.length === 0" class="py-4">
        <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon icon="ic:baseline-pending-actions" class="w-24 h-24 text-gray-300" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Pending Reports
                </h3>
                <p class="mb-6 text-gray-500">
                    There are no pending reports available at the moment.
                </p>
            </div>
        </div>
    </section>

    <section v-else>
        <DataTable
            v-model:filters="filters"
            :value="props.reports.data"
            removableSort
            tableStyle="min-width: 100%"
            class="overflow-hidden"
            :globalFilterFields="['item.name', 'concern', 'description']"
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
                                placeholder="Search by item, type, or description"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="item.name" header="Reported Item" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <div v-if="data.images && data.images.length > 0" class="overflow-hidden w-12 h-12 rounded">
                            <img :src="'/storage/' + data.images[0]?.image" :alt="data.item.name" class="object-cover w-full h-full">
                        </div>
                        <div v-else class="flex justify-center items-center w-12 h-12 bg-gray-200 rounded">
                            <Icon icon="lucide:image" class="w-6 h-6 text-gray-400" />
                        </div>
                        <div class="font-medium text-gray-900">{{ data.item.name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="concern" header="Report Type" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.concern }}</div>
                </template>
            </Column>

            <Column field="description" header="Description" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.description }}</div>
                </template>
            </Column>

            <Column field="created_at" header="Date Reported" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.created_at) }}</div>
                </template>
            </Column>

            <Column header="Actions" class="text-sm">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-ban"
                            severity="danger"
                            text
                            rounded
                            @click="unlistItem(data.item.id)"
                            tooltip="Unlist Item"
                        />
                        <Button
                            icon="pi pi-times"
                            severity="secondary"
                            text
                            rounded
                            @click="dismissReport(data.id)"
                            tooltip="Dismiss Report"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>
    </section>

    <Toast />
    <ConfirmDialog>
        <template #message="slotProps">
            <div class="flex flex-col gap-4 items-center w-full">
                <div class="flex justify-center items-center w-16 h-16 bg-orange-100 rounded-full">
                    <Icon icon="heroicons:exclamation-circle" class="w-10 h-10 text-[#F5A623]" />
                </div>
                <div class="text-center">
                    <h3 class="mb-1 text-lg font-medium text-gray-900">{{ slotProps.message }}</h3>
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>
<style scoped>
/* Copy all styles from UserTable.vue */
</style>
