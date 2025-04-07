<script setup>
import { onMounted, onUpdated, ref } from "vue";
import { Icon } from "@iconify/vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { Link, Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import Dialog from 'primevue/dialog';
import Rating from 'primevue/rating';
import Textarea from 'primevue/textarea';
import { useForm } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';


const props = defineProps({
    item: Object,
    flash: Object,
    csrf_token: String
});

const items = ref(props.item.trade_requests); // for data table component
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS }
});
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



const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const menu = ref();
const actions = ref([]);
const confirm = useConfirm();
const toast = useToast();
const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

const cancelDialogVisible = ref(false);
const cancelItemId = ref(null);
const cancelReason = ref('');

function getStatusLabel(status) {
    if (status === 'pending') return "Pending";
    if (status === 'processing') return "Processing";
    if (status === 'success') return "Success";
    if (status === 'rejected') return "Rejected";
    if (status === 'cancelled') return "Cancelled";
    return "Unknown";
}

function getStatusSeverity(status) {
    if (status === 'pending') return "warn";
    if (status === 'processing') return "info";
    if (status === 'success') return "success";
    if (status === 'rejected') return "secondary";
    if (status === 'cancelled') return "danger";
    return "warning";
}

const ratingDialog = ref(false);
const selectedUserId = ref(null);

const ratingForm = useForm({
    rated_user_id: null,
    rating: 0,
    description: '',
    is_anonymous: false
});

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

const openRatingModal = (userId) => {
    selectedUserId.value = userId;
    ratingForm.rated_user_id = userId;
    ratingDialog.value = true;
};

const toggleMenu = (event, id, user_id, status) => {
    actions.value = [{
        label: 'Actions',
        items: [
            {
                label: 'View Request',
                icon: 'heroicons:eye-20-solid',
                href: '/trade-description/' + id
            },
            {
                label: 'Message',
                icon: 'jam:message',
                href: '/chat/' + user_id,
            },
            ...(status === 'pending' ? [
                {
                    label: 'Accept Trade',
                    icon: 'uil:check',
                    command: () => acceptDialog(id)
                },
                {
                    label: 'Reject Trade',
                    icon: 'material-symbols:cancel',
                    command: () => deleteConfirm(id)
                }
            ] : []),
            ...(status === 'processing' ? [
                {
                    label: 'Complete Trade',
                    icon: 'uil:check',
                    command: () => completeDialog(id)
                },
                {
                    label: 'Cancel Trade',
                    icon: 'lets-icons:cancel',
                    command: () => showCancelDialog(id)
                }
            ] : []),
            ...(status === 'success' ? [
                {
                    label: 'Rate User',
                    icon: 'mingcute:star-fill',
                    command: () => openRatingModal(user_id)
                },
            ] : [])
        ]
    }];
    menu.value.toggle(event);
};

const showCancelDialog = (id) => {
    cancelItemId.value = id;
    cancelDialogVisible.value = true;
};

const cancelRequest = (id) => {
    router.patch(route('trade-cancel', id), {
        cancel_reason: cancelReason.value
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const index = items.value.findIndex(item => item.id === id);
            if (index !== -1) {
                items.value[index].status = 'cancelled';
            }
            cancelDialogVisible.value = false;
            cancelReason.value = '';
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Request cancelled successfully',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to cancel request',
                life: 3000,
            });
        }
    });
};

const acceptRequest = (id) => {
    router.patch(`/trade-accept/${id}`, {}, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Confirmed', detail: 'Request has been approved', life: 3000 });
            setTimeout(() => {
                router.visit('/post-management', {
                    preserveState: true,
                    preserveScroll: true
                });
            }, 1000);
        },
        onError: (error) => {
            const message = error.response?.data?.message || 'An error occurred while processing your request';
            toast.add({ severity: 'error', summary: 'Error', detail: message, life: 3000 });
            console.error('Trade accept error:', error);
        }
    });
};

const acceptDialog = (id) => {
    confirm.require({
        message: 'Are you sure you want to approve this request?',
        header: 'REQUEST APPROVAL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Accept',
            severity: 'primary'
        },
        accept: () => acceptRequest(id),
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'Delete Cancelled', life: 3000 });
        }
    });
};

const completeRequest = (id) => {
    router.patch(route('trade-complete', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Update the status of the completed item in the items array
            const index = items.value.findIndex(item => item.id === id);
            if (index !== -1) {
                items.value[index].status = 'success';

                // Automatically reject all other pending requests
                items.value.forEach((item, idx) => {
                    if (idx !== index && item.status === 'pending') {
                        item.status = 'rejected';
                        // Call the API to update the status in the backend
                        router.patch('/trade-reject/' + item.id, {}, {
                            preserveScroll: true,
                        });
                    }
                });
            }

            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Trade completed successfully',
                life: 3000,
            });

            // Open rating dialog after completing trade
            const trade = items.value.find(item => item.id === id);
            if (trade) {
                openRatingModal(trade.user_id);
            }
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to complete trade',
                life: 3000,
            });
        }
    });
};

const completeDialog = (id) => {
    confirm.require({
        message: 'Are you sure you want to complete this trade?',
        header: 'COMPLETE TRADE?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Complete',
            severity: 'primary'
        },
        accept: () => completeRequest(id),
        reject: () => {
            toast.add({ severity: 'info', summary: 'Cancelled', detail: 'Operation cancelled', life: 3000 });
        }
    });
};

const deleteConfirm = (id) => {
    confirm.require({
        message: 'Do you want to reject this request?',
        header: 'Reject Request',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Yes, Reject',
        rejectLabel: 'No, Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Reject',
            severity: 'danger'
        },
        accept: () => {
            router.patch(route('trade-reject', id), {}, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Request rejected successfully', life: 3000 });
                    setTimeout(() => {
                        router.visit('/post-management', {
                            preserveState: true,
                            preserveScroll: true
                        });
                    });
                }
            });
        },
    });
};

const showReasonDialog = ref(false);
const selectedReason = ref(null);

const showCancellationReason = (reason) => {
    selectedReason.value = reason;
    showReasonDialog.value = true;
};

</script>
<template>

    <Head title="Product Request List" />
    <AuthenticatedLayout>
        <!-- Header Section -->
        <section class="container px-4 mx-auto mt-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-2 items-center">
                    <Icon icon="ic:baseline-pending-actions" width="1.5rem" height="1.5rem" />
                    <h2 class="text-xl font-semibold text-gray-900">
                        Requests for Item
                        <span class="text-primary">{{ props.item.item_name }}</span>
                    </h2>
                </div>
                <Link href="/post-management">
                <SecondaryButton class="flex gap-2 items-center">
                    <Icon icon="solar:arrow-left-linear" width="1.2rem" height="1.2rem" />
                    Back to Items
                </SecondaryButton>
                </Link>
            </div>

            <!-- Item Details Card -->
            <div class="overflow-hidden bg-white rounded-md border shadow-sm">
                <div class="p-6">
                    <div class="flex flex-col gap-6 md:flex-row">
                        <!-- Left side - Image Gallery -->
                        <div class="md:w-1/5">
                            <div class="overflow-hidden bg-gray-100 rounded-md aspect-square">
                                <img :src="`/storage/${props.item.images[0].image}`" :alt="props.item.item_name"
                                    class="object-cover w-full h-full" />
                            </div>
                        </div>

                        <!-- Right side - Item Details -->
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ props.item.item_name }}</h3>
                                    <div class="flex gap-2 items-center mt-1">
                                        <Icon icon="solar:calendar-linear" class="w-4 h-4 text-gray-400" />
                                        <p class="text-sm text-gray-500">Posted {{ formatDate(props.item.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="flex gap-2 items-center text-sm font-medium text-gray-500">
                                            <Icon icon="material-symbols:category-outline"
                                                class="w-4 h-4 text-gray-400" />
                                            Category
                                        </h4>
                                        <p class="text-sm text-gray-900">{{ props.item.category.category }}</p>

                                    </div>
                                    <div>
                                        <h4 class="flex gap-2 items-center text-sm font-medium text-gray-500">
                                            <Icon icon="solar:gift-linear" class="w-4 h-4 text-gray-400" />
                                            Preferred Item:
                                        </h4>
                                        <p class="text-sm text-gray-900">{{ props.item.preferred_items }}</p>
                                    </div>
                                </div>

                                <!-- Stats and Description -->
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="flex gap-2 items-center text-sm font-medium text-gray-500">
                                            <Icon icon="solar:tag-linear" class="w-4 h-4 text-gray-400" />
                                            Tags
                                        </h4>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span v-for="(tag, index) in (props.item.tags || []).slice(0, 3)"
                                                :key="tag.id"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full hover:bg-gray-200">
                                                #
                                                {{ tag.tag }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="flex gap-2 items-center text-sm font-medium text-gray-500">
                                            <Icon icon="fluent:text-description-32-regular" width="1.2rem"
                                                height="1.2rem" />
                                            Description
                                        </h4>
                                        <p class="pr-4 text-sm text-gray-900">{{ props.item.item_description }}asda</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="py-6">
            <div class="bg-white rounded-md shadow">
                <div class="p-1 sm:p-2">
                    <section v-if="items.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
                        <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl sm:px-4">
                            <div class="text-center">
                                <div class="flex justify-center mb-4">
                                    <Icon icon="solar:box-minimalistic-broken" class="w-24 h-24 text-gray-300" />
                                </div>
                                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                                    No Requests Found
                                </h3>
                                <p class="mb-6 text-gray-500">
                                    There are no requests available at the moment.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section v-else>
                        <DataTable v-model:filters="filters" :value="items" removableSort tableStyle="min-width: 100%"
                            class="overflow-hidden" :globalFilterFields="['name', 'place', 'status']" paginator
                            :rows="rows" :rowsPerPageOptions="rowsPerPageOptions" responsiveLayout="scroll">

                            <template #header>
                                <div class="flex gap-4 justify-between items-center mb-4">
                                    <div class="relative w-96 max-w-xs">
                                        <div class="relative">
                                            <div
                                                class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                                            </div>
                                            <TextInput v-model="filters.global.value" type="text"
                                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                                placeholder="Search requests" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <Column field="name" header="Item to be Traded" class="text-sm" sortable>
                                <template #body="{ data }">
                                    <div class="flex gap-3 items-center">
                                        <div class="overflow-hidden w-10 h-10 rounded-md">
                                            <img :src="`/storage/${data.images[0]?.image}`" :alt="data.name"
                                                class="object-cover w-full h-full">
                                        </div>
                                        <div class="font-medium text-gray-900">{{ data.name }}</div>
                                    </div>
                                </template>
                            </Column>

                            <Column field="user_id.name" header="Item by" class="text-sm" sortable>
                                <template #body="{ data }">
                                    <div class="flex gap-3 items-center">
                                        <div class="font-medium text-gray-900">{{ data.user?.name }}</div>
                                    </div>
                                </template>
                            </Column>

                            <Column field="time" header="Proposed Date and Time" class="text-sm" sortable>
                                <template #body="{ data }">
                                    <div class="text-gray-600">{{ formatDateTime(data.time) }}</div>
                                </template>
                            </Column>

                            <Column field="place" header="Meetup Location" class="text-sm" sortable>
                                <template #body="{ data }">
                                    <div class="text-gray-600">{{ data.place }}</div>
                                </template>
                            </Column>

                            <Column field="status" header="Status" class="text-sm" sortable>
                                <template #body="slotProps">
                                    <div class="flex gap-2 items-center">
                                        <Tag :severity="getStatusSeverity(slotProps.data.status)"
                                            :value="getStatusLabel(slotProps.data.status)" :rounded="true"
                                            class="status-badge" />
                                        <Icon
                                            v-if="slotProps.data.status === 'cancelled'"
                                            icon="material-symbols:info-outline"
                                            class="w-6 h-6 text-gray-500 transition-colors cursor-pointer hover:text-gray-700"
                                            @click="showCancellationReason(slotProps.data.cancellation[0].reason)"
                                        />
                                    </div>
                                </template>
                            </Column>

                            <Column field="id" header="Actions" class="text-sm">
                                <template #body="slotProps">
                                    <div class="flex justify-start items-center">
                                        <button type="button"
                                            @click="(event) => toggleMenu(event, slotProps.data.id, slotProps.data.user_id, slotProps.data.status)"
                                            class="text-center text-black hover:text-gray-700" text>
                                            <Icon icon="lucide:more-horizontal" width="1.25rem" height="1.25rem" />
                                        </button>
                                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                                            <template #item="{ item, props }">
                                                <Link v-if="item.href" v-ripple
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    v-bind="props.action" :href="item.href">
                                                <Icon :icon="item.icon" class="mr-2 w-4 h-4" />
                                                <span>{{ item.label }}</span>
                                                </Link>
                                                <button v-else v-ripple v-bind="props.action"
                                                    class="flex items-center px-4 py-2 w-full text-sm text-gray-700 hover:bg-gray-50">
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
                </div>
            </div>
        </div>
        <Toast />
        <ConfirmDialog class="mx-4 md:mx-0"></ConfirmDialog>

        <!-- Cancel Dialog -->
        <Dialog v-model:visible="cancelDialogVisible" modal header="Cancel Request" :style="{ width: '30rem' }"
            :closable="true" :dismissableMask="true" class="mx-4 md:mx-0">
            <div class="flex flex-col items-center p-4">
                <Icon icon="material-symbols:warning-outline" class="mb-4 text-red-500" width="4rem" height="4rem" />
                <h3 class="mb-2 text-lg font-medium text-gray-900">
                    Confirm Request Cancellation
                </h3>
                <p class="mb-6 text-sm text-gray-500">
                    Are you sure you want to cancel this trade request? This action cannot be undone.
                </p>

                <div class="mb-6 w-full">
                    <label for="cancelReason" class="block mb-2 text-sm font-medium text-gray-700">
                        Please state your reason for cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea id="cancelReason" v-model="cancelReason" rows="3"
                        class="px-3 py-2 w-full text-sm rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your reason for cancellation..." required></textarea>
                </div>

                <div class="flex gap-4 w-full">
                    <SecondaryButton class="flex-1 justify-center" @click="() => {
                        cancelDialogVisible = false;
                        cancelReason = '';
                    }">
                        <Icon icon="mdi:close" class="mr-2" />
                        Keep Request
                    </SecondaryButton>

                    <PrimaryButton class="flex gap-2 justify-center bg-red-600 hover:bg-red-500 focus:bg-red-500"
                        :disabled="!cancelReason.trim()" @click="() => cancelRequest(cancelItemId)">
                        <Icon icon="mdi:check" />
                        <span class="text-xs">Cancel Request</span>
                    </PrimaryButton>
                </div>
            </div>
        </Dialog>


        <Dialog v-model:visible="ratingDialog" modal header="Rate This User" :style="{ width: '450px', margin: '20px' }"
            :closable="true">
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
                    <Textarea v-model="ratingForm.description" rows="3" placeholder="Tell us about your experience..."
                        autoResize />
                    <small class="text-red-500" v-if="ratingForm.errors.description">
                        {{ ratingForm.errors.description }}
                    </small>
                </div>

                <div class="flex gap-2 items-center">
                    <ToggleSwitch v-model="ratingForm.is_anonymous" class="w-16 h-8" on-label="Yes" off-label="No" />
                    <span class="text-sm text-gray-600">I want to be anonymous</span>
                </div>

                <div class="flex gap-2 justify-end mt-4">
                    <SecondaryButton type="button" @click="ratingDialog = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="ratingForm.processing || !ratingForm.rating"
                        :class="{ 'opacity-75': ratingForm.processing }">
                        {{ ratingForm.processing ? 'Submitting...' : 'Submit Rating' }}
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>

        <Dialog
            v-model:visible="showReasonDialog"
            modal
            header="Cancellation Reason"
            :style="{ width: '30rem' }"
            :closable="true"
            :dismissableMask="true"
            class="mx-4 md:mx-0"
        >
            <div class="p-4">
                <div v-if="selectedReason" class="text-gray-700">
                    {{ selectedReason }}
                </div>
                <div v-else class="italic text-gray-500">
                    No reason provided
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end">
                    <PrimaryButton
                        @click="showReasonDialog = false"
                        class="px-4 py-2"
                    >
                        Close
                    </PrimaryButton>
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
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
