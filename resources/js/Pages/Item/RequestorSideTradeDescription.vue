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
import Rating from 'primevue/rating';
import ToggleSwitch from 'primevue/toggleswitch';
import { useForm } from '@inertiajs/vue3';

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

const ratingDialog = ref(false);

const ratingForm = useForm({
    rated_user_id: null,
    trade_request_id: null,
    rating: 0,
    description: '',
    is_anonymous: false
});

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
                        router.visit('/trade', {
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
        onError: () => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to mark item as received', life: 3000 });
        }
    });
};

const openRatingModal = () => {
    ratingForm.reset();
    ratingForm.trade_request_id = props.trade_request.id;
    ratingForm.rated_user_id = props.trade_request.item.user_id;
    ratingDialog.value = true;
};

const submitRating = () => {
    ratingForm.post(route('ratings.store'), {
        preserveScroll: true,
        onSuccess: () => {
            ratingDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Rating submitted successfully',
                life: 3000
            });
        }
    });
};
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
                        <Link :href="`/trade`">
                        <SecondaryButton class="flex items-center gap-2">
                            <Icon icon="heroicons:arrow-left-20-solid" class="w-5 h-5" />
                            Back to Trade
                        </SecondaryButton>
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Tag :value="getStatusLabel(trade_request.status)"
                                    :severity="getStatusSeverity(trade_request.status)" class="text-sm" />
                            </div>
                            <div class="flex items-center gap-2">
                                <template v-if="trade_request.status === 'pending'">
                                    <Link :href="`/edit-request/${trade_request.id}`">
                                        <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded hover:bg-primary-200 transition-colors">
                                            <Icon icon="material-symbols:edit" class="w-5 h-5" />
                                            Edit
                                        </button>
                                    </Link>
                                    <button
                                        @click="deleteConfirm(trade_request.id)" 
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                                        <Icon icon="material-symbols:cancel" class="w-5 h-5" />
                                        Cancel Request
                                    </button>
                                    <Link :href="`/chat/${trade_request.item.user_id}`">
                                        <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else-if="trade_request.status === 'cancelled' || trade_request.status === 'rejected'">
                                    <Link :href="`/chat/${trade_request.item.user_id}`">
                                        <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else-if="trade_request.status === 'processing'">
                                    <button
                                        @click="deleteConfirm(trade_request.id)" 
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                                        <Icon icon="material-symbols:cancel" class="w-5 h-5" />
                                        Cancel Request
                                    </button>
                                    <Link :href="`/chat/${trade_request.item.user_id}`">
                                        <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else-if="trade_request.status === 'success'">
                                    <button
                                        @click="openRatingModal()" 
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded hover:bg-primary-200 transition-colors">
                                        <Icon icon="material-symbols:star" class="w-5 h-5" />
                                        Rate User
                                    </button>
                                    <Link :href="`/chat/${trade_request.item.user_id}`">
                                        <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                            <Icon icon="jam:message" class="w-5 h-5" />
                                            Message
                                        </button>
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link :href="`/chat/${trade_request.item.user_id}`">
                                        <button class="border border-gray-200 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
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
    <Dialog v-model:visible="ratingDialog" modal header="Rate User" :style="{ width: '30rem' }">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <Rating v-model="ratingForm.rating" :cancel="false" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                <textarea v-model="ratingForm.description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"></textarea>
            </div>
            <div class="flex items-center">
                <ToggleSwitch v-model="ratingForm.is_anonymous" class="mr-2" />
                <label class="text-sm text-gray-700">I want to be anonymous</label>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-2">
                <button @click="ratingDialog = false" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="submitRating" 
                    class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-md hover:bg-primary-200">
                    Submit
                </button>
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.p-image-preview-container {
    width: 400px;
    height: 400px;
}

:deep(.p-tag) {
    font-size: 0.75rem;
}

.p-button {
    border-radius: 0.5rem !important;
}

:deep(.p-button) {
    border-radius: 0.5rem !important;
}

:deep(.p-button.p-component) {
    border-radius: 0.5rem !important;
}

button {
    border-radius: 0.5rem !important;
}
</style>