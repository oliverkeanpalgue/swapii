<script setup>
import { onMounted, ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    items: Object, // The paginated items passed from Inertia
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const items = ref(props.items); // for data table component

watch(() => props.items, (newItems) => {
    items.value = newItems;
}, { deep: true });

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});

const statusOptions = ref([
    { label: 'Pending', value: 'pending' },
    { label: 'Success', value: 'success' },
    { label: 'Approved', value: 'approved' },
    { label: 'Cancelled', value: 'cancelled' }
]);

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

function getStatusLabel(status) {
    if (status === 'pending') return "Pending";
    if (status === 'success') return "Success";
    if (status === 'approved') return "Approved";
    if (status === 'cancelled') return "Cancelled";
    return "Unknown";
}

function getStatusSeverity(status) {
    if (status === 'pending') return "warn";
    if (status === 'success') return "success";
    if (status === 'approved') return "info";
    if (status === 'cancelled') return "danger";
    return "warning";
}

function formatDateTime(dateTimeString) {
    try {
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

const toggleMenu = (event, id, user_id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'View Request',
                    icon: 'heroicons:eye-20-solid',
                    href: route('requestor-trade-description.show', id)
                },
                {
                    label: 'Message',
                    icon: 'jam:message',
                    href: '/chat/' + user_id,
                    row: user_id
                },
            ],
        },
    ];

    menu.value.toggle(event);
};

const deleteConfirm = (id) => {
    confirm.require({
        message: 'Do you want to delete this record?' + id,
        header: 'Delete Trade ID NUMBER ' + id,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            toast.add({ severity: 'success', summary: 'Confirmed', detail: 'Record deleted', life: 3000 });
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'Delete Cancelled', life: 3000 });
        }
    });
};

onMounted(() => {
    console.log(props.items);
});
</script>

<template>
   <section v-if="props.items.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
    <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl sm:px-4">
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <Icon icon="carbon:ibm-data-product-exchange" class="w-24 h-24 text-gray-300" />
            </div>
            <h3 class="mb-2 text-xl font-semibold text-gray-900">
                No Trade Requests
            </h3>
            <p class="mb-6 text-gray-500">
                You have no trade requests available at the moment.
            </p>
        </div>
    </div>
</section>

    <section v-else>
        <DataTable 
            v-model:filters="filters"
            :value="items" 
            :key="items.id"
            removableSort 
            tableStyle="min-width: 100%"
            class="transactions-table" 
            :globalFilterFields="['item.item_name', 'name', 'place']"
            paginator 
            :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions"
            responsiveLayout="scroll">
            <template #header>
                <div class="flex items-center justify-between gap-4 mb-3">
                    <div class="relative max-w-xs w-96">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon icon="ri:search-line" class="h-3.5 w-3.5 text-gray-400" />
                            </div>
                            <TextInput 
                                v-model="filters['global'].value" 
                                type="text"
                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search trades" 
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="item.item_name" header="Item" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-lg overflow-hidden">
                            <img :src="`/storage/${data.item.images[0]?.image}`" :alt="data.item.item_name" class="h-full w-full object-cover">
                        </div>
                        <div class="text-sm font-medium text-gray-900">{{ data.item.item_name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="name" header="Item to be Traded" class="text-sm" sortable>
                <template #body="slotProps">
                    <div class="flex items-center gap-3">
                        <img :src="`/storage/${slotProps.data.images[0]?.image}`" class="w-10 h-10 rounded-lg object-cover"/>
                        <span class="text-sm">{{ slotProps.data.name }}</span>
                    </div>
                </template>
            </Column>
            <Column field="time" header="Date and Timeto Receive" class="text-sm" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600">{{ formatDateTime(slotProps.data.time) }}</span>
                </template>
            </Column>
            <Column field="place" header="Meetup Place" class="text-sm" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600 capitalize">{{ slotProps.data.place }}</span>
                </template>
            </Column>
            <Column field="cancellation.reason" header="Cancellation Reason" class="text-sm" sortable>
                <template #body="slotProps">
                    <div class="max-w-md">
                        <span v-if="slotProps.data.cancellation" class="text-sm text-gray-600">
                            {{ slotProps.data.cancellation[0]?.reason }}
                        </span>
                        <span v-else class="text-sm text-gray-400 italic">
                            No reason provided
                        </span>
                    </div>
                </template>
            </Column>
            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center">
                        <button type="button" @click="(event) => toggleMenu(event, slotProps.data.id, slotProps.data.item.user_id)"
                            class="text-gray-600 text-center hover:text-gray-900 transition-colors" text>
                            <Icon icon="lucide:more-horizontal" width="1.15rem" height="1.15rem" />
                        </button>
                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                            <template #item="{ item, props }">
                                <Link v-if="item.href" v-ripple
                                    class="flex items-center px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                    v-bind="props.action" :href="item.href">
                                    <Icon :icon="item.icon" class="mr-2 h-3.5 w-3.5" />
                                    <span>{{ item.label }}</span>
                                </Link>
                                <button v-else v-ripple v-bind="props.action"
                                    class="flex items-center w-full px-3 py-1.5 text-sm text-red-500 hover:bg-gray-50">
                                    <Icon :icon="item.icon" class="mr-2 h-3.5 w-3.5" />
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
    <ConfirmDialog />
</template>

<style scoped>
:deep(.p-datatable) {
    border-radius: 0.5rem;
    overflow: hidden;
    font-size: 0.875rem;
}

:deep(.p-datatable .p-datatable-header) {
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-datatable .p-paginator) {
    background-color: white;
    border-top: 1px solid #e5e7eb;
    padding: 0.5rem;
}

:deep(.p-dropdown) {
    min-width: 150px;
}

:deep(.status-badge) {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f9fafb;
}

:deep(.p-datatable .p-sortable-column:hover) {
    background-color: #f3f4f6;
}

:deep(.p-datatable .p-sortable-column.p-highlight) {
    background-color: #f3f4f6;
    color: #374151;
}

:deep(.p-paginator .p-paginator-pages .p-paginator-page) {
    min-width: 2rem;
    height: 2rem;
}

:deep(.p-dropdown-panel .p-dropdown-items .p-dropdown-item) {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>
