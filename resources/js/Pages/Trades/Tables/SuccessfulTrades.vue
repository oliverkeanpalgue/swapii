<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, useForm } from '@inertiajs/vue3';
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
import Dialog from 'primevue/dialog';
import Rating from 'primevue/rating';
import Textarea from 'primevue/textarea';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    items: Object, // The paginated items passed from Inertia
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const items = ref(props.items); // for data table component
const ratingDialog = ref(false);
const selectedTradeId = ref(null);
const selectedUserId = ref(null);

const ratingForm = useForm({
    rated_user_id: null,
    rating: 0,
    description: '',
    is_anonymous: false
});

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

const submitRating = () => {
    ratingForm.post(route('ratings.store'), {
        onSuccess: () => {
            ratingDialog.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Rating submitted successfully', 
                life: 3000 
            });
            ratingForm.reset();
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

const openRatingModal = (tradeId, userId) => {
    selectedTradeId.value = tradeId;
    selectedUserId.value = userId;
    ratingForm.rated_user_id = userId;
    ratingDialog.value = true;
};

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
                {
                    label: 'Rate User',
                    icon: 'mingcute:star-fill',
                    command: () => openRatingModal(id, user_id)
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

    <Dialog 
        v-model:visible="ratingDialog" 
        modal 
        header="Rate This User" 
        :style="{ width: '450px', margin: '20px' }"
        :closable="true"
    >
        <form @submit.prevent="submitRating" class="flex flex-col gap-4 p-2 sm:p-4">
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700">Rating</label>
                <Rating v-model="ratingForm.rating" :cancel="false" />
                <small class="text-red-500" v-if="ratingForm.errors.rating">
                    {{ ratingForm.errors.rating }}
                </small>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700">Description</label>
                <Textarea 
                    v-model="ratingForm.description" 
                    rows="3" 
                    placeholder="Tell us about your experience..."
                    autoResize
                />
                <small class="text-red-500" v-if="ratingForm.errors.description">
                    {{ ratingForm.errors.description }}
                </small>
            </div>

            <div class="flex items-center gap-2">
                <ToggleSwitch 
                    v-model="ratingForm.is_anonymous" 
                    class="w-16 h-8"
                    on-label="Yes"
                    off-label="No"
                />
                <span class="text-sm text-gray-600">I want to be anonymous</span>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <SecondaryButton type="button" @click="ratingDialog = false">
                    Cancel
                </SecondaryButton>
                <PrimaryButton 
                    type="submit" 
                    :disabled="ratingForm.processing || !ratingForm.rating"
                    :class="{ 'opacity-75': ratingForm.processing }"
                >
                    {{ ratingForm.processing ? 'Submitting...' : 'Submit Rating' }}
                </PrimaryButton>
            </div>
        </form>
    </Dialog>

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

:deep(.p-dialog-header) {
    padding: 1.25rem 1.5rem 1rem;
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-dialog-content) {
    padding: 1rem 1.5rem;
    background-color: white;
}

:deep(.p-rating) {
    gap: 0.5rem;
}

:deep(.p-rating .p-rating-item .p-rating-icon) {
    color: #d1d5db;
    font-size: 1.5rem;
}

:deep(.p-rating .p-rating-item.p-rating-item-active .p-rating-icon) {
    color: #FFA41C;
}

:deep(.p-rating:not(.p-disabled):not(.p-readonly) .p-rating-item:hover .p-rating-icon) {
    color: #FFA41C;
}

:deep(.p-textarea) {
    width: 100%;
}

/* Optional: Add loading state styles */
.opacity-75 {
    opacity: 0.75;
    cursor: not-allowed;
}

/* Add these styles for the input field */
:deep(.p-inputtext) {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
}

:deep(.p-inputtext:disabled) {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
