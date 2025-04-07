<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    reports: Object
});

const toast = useToast();
const menu = ref();
const actions = ref([]);
const unlistDialog = ref(false);
const dismissDialog = ref(false);
const selectedReportId = ref(null);

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

const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'Unlist Item',
                    icon: 'heroicons:archive-box-x-mark',
                    command: () => showUnlistDialog(id)
                },
                {
                    label: 'Dismiss Report',
                    icon: 'heroicons:x-mark',
                    command: () => showDismissDialog(id)
                }
            ]
        }
    ];
    menu.value.toggle(event);
};

const showUnlistDialog = (reportId) => {
    selectedReportId.value = reportId;
    unlistDialog.value = true;
};

const confirmUnlist = () => {
    router.patch(route('mod.post.reports.unlist', selectedReportId.value), {}, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Item has been unlisted', life: 3000 });
            unlistDialog.value = false;
        }
    });
};

const showDismissDialog = (id) => {
    selectedReportId.value = id;
    dismissDialog.value = true;
};

const confirmDismiss = () => {
    router.patch(route('mod.post.reports.dismiss', selectedReportId.value), {}, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Report has been dismissed', life: 3000 });
            dismissDialog.value = false;
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
            :globalFilterFields="['item.item_name', 'concern', 'description']"
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

            <Column field="item.images" header="Reported Proof" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <img :src="`/storage/${data.images[0]?.image}`" alt="Reported item" class="object-cover w-16 h-16 rounded-md">
                        <!-- <div class="font-medium text-gray-900">{{ data.images[0]?.image }}</div> -->
                    </div>
                </template>
            </Column>

            <Column field="item.item_name" header="Reported Item" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <div class="font-medium text-gray-900">{{ data.item.item_name }}</div>
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
                    <div class="flex justify-start items-center">
                        <button type="button"
                            @click="(event) => toggleMenu(event, data.id)"
                            class="text-center text-black hover:text-gray-700">
                            <Icon icon="lucide:more-horizontal" width="1.25rem" height="1.25rem" />
                        </button>
                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                            <template #item="{ item }">
                                <button
                                    class="flex items-center px-4 py-2 w-full text-sm text-gray-700 hover:bg-gray-50"
                                    @click="item.command">
                                    <Icon :icon="item.icon" class="mr-2 w-4 h-4" />
                                    <span>{{ item.label }}</span>
                                </button>
                            </template>
                        </Menu>
                    </div>
                </template>
            </Column>
        </DataTable>
    </section>

    <Toast />
    <Dialog
        v-model:visible="unlistDialog"
        modal
        :style="{ width: '450px' }"
        header="Unlist Item"
        :closable="false"
        class="p-fluid"
    >
        <div class="flex flex-col gap-4 items-center p-4 w-full">
            <div class="flex justify-center items-center w-16 h-16 bg-orange-100 rounded-full">
                <Icon icon="heroicons:exclamation-circle" class="w-10 h-10 text-[#F5A623]" />
            </div>
            <div class="text-center">
                <h3 class="mb-1 text-lg font-medium text-gray-900">Are you sure you want to unlist this item?</h3>
                <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
        </div>
        <template #footer>
            <div class="flex gap-2 justify-end">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    @click="unlistDialog = false"
                    class="p-button-text"
                />
                <Button
                    label="Unlist"
                    icon="pi pi-check"
                    @click="confirmUnlist"
                    class="p-button-danger"
                    autofocus
                />
            </div>
        </template>
    </Dialog>

    <Dialog
        v-model:visible="dismissDialog"
        modal
        :style="{ width: '450px' }"
        header="Dismiss Report"
        :closable="false"
        class="p-fluid"
    >
        <div class="flex flex-col gap-4 items-center p-4 w-full">
            <div class="flex justify-center items-center w-16 h-16 bg-blue-100 rounded-full">
                <Icon icon="heroicons:information-circle" class="w-10 h-10 text-blue-500" />
            </div>
            <div class="text-center">
                <h3 class="mb-1 text-lg font-medium text-gray-900">Are you sure you want to dismiss this report?</h3>
                <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
        </div>
        <template #footer>
            <div class="flex gap-2 justify-end">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    @click="dismissDialog = false"
                    class="p-button-text"
                />
                <Button
                    label="Dismiss"
                    icon="pi pi-check"
                    @click="confirmDismiss"
                    class="p-button-primary"
                    autofocus
                />
            </div>
        </template>
    </Dialog>
</template>
<style scoped>
/* Copy all styles from UserTable.vue */
</style>
