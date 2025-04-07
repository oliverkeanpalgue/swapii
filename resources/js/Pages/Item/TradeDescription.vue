<script setup>
import Image from 'primevue/image';
import Galleria from 'primevue/galleria';
import Tag from 'primevue/tag';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref, onMounted } from 'vue';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import axios from "axios";
import Dialog from 'primevue/dialog';

const props = defineProps({
    trade_request: Object
});

onMounted(() => {
    console.log('Trade Request Data:', props.trade_request);
});

// Gallery options
const selectedImage = ref(0);
const responsiveOptions = ref([
    {
        breakpoint: '1024px',
        numVisible: 5
    },
    {
        breakpoint: '768px',
        numVisible: 3
    },
    {
        breakpoint: '560px',
        numVisible: 1
    }
]);

// Function to change selected image
const changeImage = (index) => {
    selectedImage.value = index;
}

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
    if (status === 'cancelled') return "danger";
    if (status === 'rejected') return "secondary";
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

const confirm = useConfirm();
const toast = useToast();

const isRequestor = (item) => {
    return item.requestor_id === usePage().props.auth.user.id;
};

const isOwner = () => {
    return props.trade_request.user_id === usePage().props.auth.user.id;
};

const acceptRequest = (id) => {
    router.patch('/trade-accept/' + id, {}, {
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
            toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 });
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

const deleteConfirm = (id) => {
    confirm.require({
        message: 'Are you sure you want to cancel this request?',
        header: 'CANCEL REQUEST APPROVAL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'No',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Yes',
            severity: 'danger'
        },
        accept: () => {
            router.patch('/trade-cancel/' + id, {}, {
                onSuccess: () => {
                    setTimeout(() => {
                        router.visit('/post-management', {
                            preserveState: true,
                            preserveScroll: true
                        });
                    });
                }
            });
        },
        reject: () => {
            toast.add({ severity: 'info', summary: 'Cancelled', detail: 'Operation cancelled', life: 3000 });
        }
    });
};

const markComplete = (id) => {
    router.post(route('trade-requests.mark-complete', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: usePage().props.flash.message || 'Trade marked as complete',
                life: 3000,
            });
        },
        onError: (error) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.message || 'Failed to mark trade as complete',
                life: 3000,
            });
        }
    });
};

const completeDialog = (id) => {
    confirm.require({
        message: 'Are you sure you want to mark this trade as complete? The trade will be finalized when both parties mark it as complete.',
        header: 'Complete Trade',
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-success',
        accept: () => markComplete(id),
        reject: () => { }
    });
};

const completeTrade = (id) => {
    router.patch('/trade-complete/' + id, {}, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Trade completed successfully', life: 3000 });
            setTimeout(() => {
                router.visit('/post-management', {
                    preserveState: true,
                    preserveScroll: true
                });
            }, 1000);
        },
        onError: (error) => {
            toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 });
        }
    });
};

const markAsReceived = (id) => {
    router.patch('/trade-requests/mark-complete/' + id, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Item marked as received', life: 3000 });
            setTimeout(() => {
                router.visit('/post-management', {
                    preserveState: true,
                    preserveScroll: true
                });
            }, 1000);
        },
        onError: (error) => {
            toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 });
        }
    });
};

const reasonDialog = ref(false);
</script>

<template>

    <Head title="Trade Request Details" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <h1 class="text-2xl font-bold text-gray-900">Trade Request Details</h1>
                        </div>
                        <Link :href="`/trade-list/${trade_request.item_id}`">
                        <button class="btn btn-secondary">
                            <Icon icon="heroicons:arrow-left-20-solid" class="w-5 h-5" />
                            Back to List
                        </button>
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Tag :value="getStatusLabel(trade_request.status)"
                                    :severity="getStatusSeverity(trade_request.status)" class="text-sm" />
                                <template v-if="trade_request.status === 'cancelled'">
                                    <button @click="reasonDialog = true" 
                                        class="inline-flex items-center text-gray-500 hover:text-gray-700">
                                        <Icon icon="heroicons:information-circle" class="w-5 h-5" />
                                    </button>
                                    <Dialog v-model:visible="reasonDialog" modal header="Cancellation Reason" :style="{ width: '30rem' }">
                                        <div class="p-4">
                                            <p class="text-gray-700">{{ trade_request.cancel_reason }}</p>
                                        </div>
                                    </Dialog>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <template v-if="trade_request.status === 'pending'">
                                    <button @click="acceptDialog(trade_request.id)"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded hover:bg-primary-200 transition-colors">
                                    <Icon icon="heroicons:check-circle-20-solid" class="w-5 h-5" />
                                        Accept Request
                                    </button>
                                    <button @click="deleteConfirm(trade_request.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                                        <Icon icon="heroicons:x-circle-20-solid" class="w-5 h-5" />
                                        Cancel Request
                                    </button>
                                    <Link :href="'/chat/' + trade_request.requestor_id">
                                        <button class="btn btn-secondary">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else-if="trade_request.status === 'processing'">
                                    <template v-if="!trade_request.owner_completed">
                                        <button @click="completeDialog(trade_request.id)"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded hover:bg-primary-200 transition-colors">
                                            <Icon icon="heroicons:check-circle-20-solid" class="w-5 h-5" />
                                            Mark as Done
                                        </button>
                                    </template>
                                    <button @click="deleteConfirm(trade_request.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                                        <Icon icon="heroicons:x-circle-20-solid" class="w-5 h-5" />
                                        Cancel Request
                                    </button>
                                    <Link :href="'/chat/' + trade_request.requestor_id">
                                        <button class="btn btn-secondary">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link :href="'/chat/' + trade_request.requestor_id">
                                        <button class="btn btn-secondary">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-8 p-6">
                        <div class="col-span-12 lg:col-span-5">
                            <div class="space-y-4">
                                <div class="aspect-square rounded-md overflow-hidden bg-gray-100 border">
                                    <Image :src="`/storage/${trade_request.images[selectedImage]?.image}`" :pt="{
                                        image: { class: 'w-full h-full object-cover' }
                                    }" preview />
                                </div>

                                <!-- Thumbnails -->
                                <div class="grid grid-cols-5 gap-2">
                                    <div v-for="(image, index) in trade_request.images" :key="image.id"
                                        @click="changeImage(index)"
                                        :class="['aspect-square cursor-pointer rounded border bg-white p-1',
                                            selectedImage === index ? 'border-primary ring-2 ring-primary' : 'border-gray-200']">
                                        <img class="w-full h-full object-cover rounded"
                                            :src="`/storage/${image.image}`">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-7">
                            <div class="space-y-6">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">
                                        {{ trade_request.name }}
                                    </h1>
                                    <Link v-if="trade_request.user" :href="'/view-user/' + trade_request.user.id"
                                        class="mt-1 inline-flex items-center text-sm text-primary hover:underline">
                                    <Icon icon="heroicons:user-circle-20-solid" class="mr-1 w-5 h-5" />
                                    {{ trade_request.user.name }}
                                    </Link>
                                    <span v-else class="mt-1 inline-flex items-center text-sm text-gray-500">
                                        <Icon icon="heroicons:user-circle-20-solid" class="mr-1 w-5 h-5" />
                                        User not found
                                    </span>
                                </div>

                                <div class="prose prose-sm">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                        <Icon icon="heroicons:document-text-20-solid" class="w-5 h-5" />
                                        Description
                                    </h3>
                                    <p class="text-gray-600">{{ trade_request.description }}</p>
                                </div>

                                <!-- Trade Details -->
                                <div class="space-y-4">
                                    <div class="rounded-md bg-gray-50 p-4">
                                        <h3 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                            <Icon icon="heroicons:calendar-20-solid" class="w-5 h-5" />
                                            Proposed Date & Time
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ formatDateTime(trade_request.time) }}
                                        </p>
                                    </div>

                                    <div class="rounded-md bg-gray-50 p-4">
                                        <h3 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                            <Icon icon="heroicons:map-pin-20-solid" class="w-5 h-5" />
                                            Meetup Location
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ trade_request.place }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <Toast />
    <ConfirmDialog />
</template>

<style scoped>
.p-image-preview-container {
    width: 400px;
}

/* Common button styles */
.btn {
    @apply inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors min-w-[120px];
}

:deep(.p-tag) {
    font-size: 0.75rem;
}
</style>