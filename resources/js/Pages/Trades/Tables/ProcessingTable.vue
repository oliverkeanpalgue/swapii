<script setup>
import { onMounted, ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import Dialog from 'primevue/dialog';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';

const props = defineProps({
    items: {
        type: Array,
        required: true
    }
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const items = ref(props.items); // for data table component
const cancelDialogVisible = ref(false);
const cancelItemId = ref(null);
const cancelReason = ref('');

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

const cancelRequest = (id) => {
    if (!cancelReason.value.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Please provide a reason for cancellation',
            life: 3000
        });
        return;
    }

    console.log('Sending cancel request:', {
        id,
        cancel_reason: cancelReason.value
    });

    router.patch(route('trade-cancel', id), {
        cancel_reason: cancelReason.value
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('Cancel success:', response);
            const index = items.value.findIndex(item => item.id === id);
            if (index !== -1) {
                items.value[index].status = 'cancelled';
            }
            cancelDialogVisible.value = false;
            cancelReason.value = ''; // Reset the reason
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Request cancelled successfully',
                life: 3000,
            });
        },
        onError: (error) => {
            console.error('Cancel error:', error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.message || 'Failed to cancel request',
                life: 3000,
            });
        }
    });
};

const showCancelDialog = (id) => {
    cancelItemId.value = id;
    cancelDialogVisible.value = true;
};

const toggleMenu = (event, id, userId) => {
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
                    href: '/chat/' + userId,
                },
                {
                    label: 'Cancel Trade',
                    icon: 'material-symbols:cancel',
                    visible: props.items.filter(item => item.id === id)[0].status === 'processing',
                    command: () => showCancelDialog(id)
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

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending':
            return 'Pending';
        case 'processing':
            return 'Processing';
        case 'success':
            return 'Success';
        case 'cancelled':
            return 'Cancelled';
        default:
            return 'Unknown';
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'success':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'info';
    }
};

watch(() => props.items, (newItems) => {
    items.value = newItems;
}, { deep: true });

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
            <Column field="time" header="Date and Time to Receive" class="text-sm" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600">{{ formatDateTime(slotProps.data.time) }}</span>
                </template>
            </Column>
            <Column field="place" header="Meetup Place" class="text-sm" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600 capitalize">{{ slotProps.data.place }}</span>
                </template>
            </Column>
            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center gap-2">
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
    <Dialog 
        v-model:visible="cancelDialogVisible" 
        modal 
        header="Cancel Request" 
        :style="{ width: '30rem' }"
        :closable="true" 
        :dismissableMask="true"
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col items-center p-4">
            <Icon 
                icon="material-symbols:warning-outline" 
                class="text-red-500 mb-4" 
                width="4rem" 
                height="4rem" 
            />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                Confirm Request Cancellation
            </h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Are you sure you want to cancel this trade request? This action cannot be undone.
            </p>
            
            <div class="w-full mb-6">
                <label for="cancelReason" class="block text-sm font-medium text-gray-700 mb-2">
                    Please state your reason for cancellation
                </label>
                <textarea
                    id="cancelReason"
                    v-model="cancelReason"
                    rows="3"
                    class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    placeholder="Enter your reason for cancellation..."
                    required
                ></textarea>
            </div>
            
            <div class="flex gap-4 w-full">
                <SecondaryButton 
                    class="flex-1 justify-center"
                    @click="cancelDialogVisible = false"
                >
                    <Icon icon="mdi:close" class="mr-2" />
                    Keep Request
                </SecondaryButton>

                <PrimaryButton 
                    class="flex gap-2 justify-center bg-red-600 hover:bg-red-500 focus:bg-red-500"
                    @click="() => cancelRequest(cancelItemId)"
                >
                    <Icon icon="mdi:check" />
                    <span class="text-xs">Cancel Request</span>
                </PrimaryButton>
            </div>
        </div>
    </Dialog>
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
