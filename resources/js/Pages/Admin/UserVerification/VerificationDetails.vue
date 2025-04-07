<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Tag from 'primevue/tag';
import Image from 'primevue/image';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    verification: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const approveDialogVisible = ref(false);
const rejectDialogVisible = ref(false);
const rejectionReason = ref('');

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('en-US', options);
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'approved':
            return 'success';
        case 'rejected':
            return 'danger';
        default:
            return 'info';
    }
};

const approveVerification = () => {
    approveDialogVisible.value = true;
};

const rejectVerification = () => {
    rejectionReason.value = '';
    rejectDialogVisible.value = true;
};

const confirmApprove = () => {
    router.post(route('admin.verification.update-status', props.verification.id));
    toast.add({ severity: 'success', summary: 'Success', detail: 'Verification approved', life: 3000 });
    approveDialogVisible.value = false;
};

const confirmReject = () => {
    router.post(route('admin.verification.update-status', props.verification.id), { 
        status: 'rejected',
        reason: rejectionReason.value 
    });
    toast.add({ severity: 'success', summary: 'Success', detail: 'Verification rejected', life: 3000 });
    rejectDialogVisible.value = false;
};
</script>

<template>
    <Head title="Verification Details" />

    <AdminLayout>
        <div class="p-4 md:p-6">
            <!-- Header with back button and action buttons -->
            <div class="mb-6 flex justify-between items-center">
                <Link :href="route('admin.user-verification')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                    <Icon icon="heroicons:arrow-left-20-solid" class="mr-2 w-5 h-5" />
                    Back to Verifications
                </Link>
                <div class="flex gap-3" v-if="verification.status === 'pending'">
                    <PrimaryButton
                        @click="approveVerification"
                        class="inline-flex items-center h-10 w-13 text-sm font-medium text-white bg-primary hover:bg-primary-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200"
                    >
                        <Icon icon="uil:check" class="mr-2 w-5 h-5" />
                        Approve
                    </PrimaryButton>
                    <SecondaryButton
                        @click="rejectVerification"
                        class="inline-flex items-center h-10 w-13 text-sm font-medium text-red-600 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-primary-200"
                    >
                        <Icon icon="material-symbols:cancel" class="mr-2 w-5 h-5" />
                        Reject
                    </SecondaryButton>
                </div>
            </div>

            <!-- Main content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- User Information -->
                <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                    <h2 class="mb-6 text-xl font-semibold text-primary">User Information</h2>
                    <div class="space-y-5">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label class="block mb-1.5 text-sm font-medium text-primary">Name</label>
                            <p class="text-gray-900 font-medium">{{ verification.user.name }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label class="block mb-1.5 text-sm font-medium text-primary">Email</label>
                            <p class="text-gray-900">{{ verification.user.email }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label class="block mb-1.5 text-sm font-medium text-primary">School</label>
                            <p class="text-gray-900">{{ verification.school }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label class="block mb-1.5 text-sm font-medium text-primary">Status</label>
                            <Tag :value="verification.status" class="border border-gray-400" :severity="getStatusSeverity(verification.status)" />
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label class="block mb-1.5 text-sm font-medium text-primary">Submitted On</label>
                            <p class="text-gray-900">{{ formatDate(verification.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Verification Images -->
                <div class="space-y-6">
                    <!-- Student ID -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                        <h2 class="mb-6 text-xl font-semibold text-primary">Student ID</h2>
                        <div class="flex justify-center p-6 bg-gray-50 rounded-lg">
                            <Image 
                                :src="'/storage/' + verification.id_image" 
                                :alt="verification.user.name + '\'s Student ID'"
                                :pt="{
                                    root: { class: 'w-[150px]' },
                                    image: { class: 'max-h-[150px] w-[150px] object-contain rounded-lg shadow-sm hover:shadow-md transition-all border-2 border-primary-200 hover:border-primary' }
                                }"
                                preview
                                imageClass="w-auto h-auto"
                            />
                        </div>
                    </div>

                    <!-- Selfie -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                        <h2 class="mb-6 text-xl font-semibold text-primary">Selfie</h2>
                        <div class="flex justify-center p-6 bg-gray-50 rounded-lg">
                            <Image 
                                :src="'/storage/' + verification.image" 
                                :alt="verification.user.name + '\'s Selfie'"
                                :pt="{
                                    root: { class: 'w-[150px]' },
                                    image: { class: 'max-h-[150px] w-[150px] object-contain rounded-lg shadow-sm hover:shadow-md transition-all border-2 border-primary-200 hover:border-primary' }
                                }"
                                preview
                                imageClass="w-auto h-auto"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <Toast />

    <!-- Approve Dialog -->
    <Dialog
        v-model:visible="approveDialogVisible"
        modal
        header="Approve Verification"
        :style="{ width: '30rem' }"
        :closable="true"
        :dismissableMask="true"
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col gap-4 items-center pb-4 w-full">
            <div class="flex justify-center items-center w-16 h-16 bg-green-100 rounded-full">
                <Icon icon="heroicons:check-circle" class="w-10 h-10 text-green-600" />
            </div>
            <div class="text-center">
                <h3 class="mb-1 text-lg font-medium text-gray-900">Approve Verification</h3>
                <p class="text-sm text-gray-600">Are you sure you want to approve this verification?</p>
            </div>
        </div>
        <template #footer>
            <div class="flex gap-3 justify-end">
                <button
                    @click="approveDialogVisible = false"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-200"
                >
                    Cancel
                </button>
                <button
                    @click="confirmApprove"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200"
                >
                    Approve
                </button>
            </div>
        </template>
    </Dialog>

    <!-- Reject Dialog -->
    <Dialog
        v-model:visible="rejectDialogVisible"
        modal
        header="Reject Verification"
        :style="{ width: '30rem' }"
        :closable="true"
        :dismissableMask="true"
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col gap-4 items-center pb-4 w-full">
            <div class="flex justify-center items-center w-16 h-16 bg-red-100 rounded-full">
                <Icon icon="heroicons:x-circle" class="w-10 h-10 text-red-600" />
            </div>
            <div class="text-center">
                <h3 class="mb-1 text-lg font-medium text-gray-900">Reject Verification</h3>
                <p class="text-sm text-gray-600">Are you sure you want to reject this verification?</p>
            </div>
            <div class="w-full">
                <textarea
                    v-model="rejectionReason"
                    rows="3"
                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-200 focus:border-primary-200"
                    placeholder="Please provide a reason for rejection..."
                ></textarea>
            </div>
        </div>
        <template #footer>
            <div class="flex gap-3 justify-end">
                <button
                    @click="rejectDialogVisible = false"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-200"
                >
                    Cancel
                </button>
                <button
                    @click="confirmReject"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-200"
                >
                    Reject
                </button>
            </div>
        </template>
    </Dialog>
</template>