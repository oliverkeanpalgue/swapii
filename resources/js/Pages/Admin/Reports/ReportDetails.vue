<script setup>
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from '@iconify/vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';

const props = defineProps({
    report: Object
});

const imageDialog = ref(false);
const selectedImage = ref(null);
const warningDialog = ref(false);
const warningLevel = ref(null);
const adminNotes = ref('');

const showImage = (image) => {
    selectedImage.value = image;
    imageDialog.value = true;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const warningLevels = [
    { label: '1st Warning', value: 1 },
    { label: '2nd Warning', value: 2 },
    { label: '3rd Warning', value: 3 }
];

const issueWarning = () => {
    console.log('Issuing warning:', {
        level: warningLevel.value,
        notes: adminNotes.value,
        userId: props.report.user.id
    });
    warningDialog.value = false;
    warningLevel.value = null;
    adminNotes.value = '';
};
</script>

<template>
    <Head title="Report Details" />

    <AdminLayout>
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="py-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-900">Report Details</h1>
                        <p class="mt-2 text-sm text-gray-600">Detailed information about the report</p>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-exclamation-circle"
                            label="Approve Report"
                            severity="warning"
                            @click="warningDialog = true"
                            v-if="props.report.status === 'pending'"
                        />
                        <Button
                            icon="pi pi-arrow-left"
                            label="Back to Reports"
                            text
                            @click="$inertia.visit(route('admin.reports.index'))"
                        />
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mt-6 bg-white rounded-lg shadow">
                <!-- Report Information -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Report Information</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Reported User</h3>
                            <div class="flex items-center mt-2">
                                <div v-if="report.user.avatar" class="overflow-hidden w-10 h-10 rounded-full">
                                    <img :src="report.user.avatar" :alt="report.user.name" class="object-cover w-full h-full">
                                </div>
                                <div v-else class="h-10 w-10 rounded-full bg-[#F5A623] flex items-center justify-center">
                                    <span class="font-medium text-white">{{ report.user.name.charAt(0).toUpperCase() }}</span>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-900">{{ report.user.name }}</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date Reported</h3>
                            <p class="mt-2 text-sm text-gray-900">{{ formatDate(report.created_at) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Report Type</h3>
                            <p class="mt-2 text-sm text-gray-900">{{ report.concern }}</p>
                        </div>
                        <div v-if="report.other_concern">
                            <h3 class="text-sm font-medium text-gray-500">Other Concern</h3>
                            <p class="mt-2 text-sm text-gray-900">{{ report.other_concern }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Description</h2>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ report.description }}</p>
                </div>

                <!-- Evidence -->
                <div class="p-6">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Evidence</h2>
                    <div v-if="report.images && report.images.length > 0" class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        <div v-for="image in report.images" :key="image.id"
                            class="overflow-hidden relative rounded-lg cursor-pointer aspect-square hover:opacity-90"
                            @click="showImage(`/storage/${image.image}`)">
                            <img :src="`/storage/${image.image}`" :alt="'Evidence image'" class="object-cover w-full h-full">
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No evidence images provided</p>
                </div>
            </div>
        </div>

        <!-- Image Dialog -->
        <Dialog v-model:visible="imageDialog" :modal="true" :dismissableMask="true"
            :style="{ width: '90vw', maxWidth: '1000px' }" class="p-0">
            <div class="relative">
                <img v-if="selectedImage" :src="selectedImage"
                    :alt="'Evidence image'" class="w-full h-auto">
                <Button icon="pi pi-times"
                    class="absolute top-2 right-2"
                    rounded text severity="secondary"
                    @click="imageDialog = false" />
            </div>
        </Dialog>

        <!-- Warning Dialog -->
        <Dialog
            v-model:visible="warningDialog"
            modal
            header="Issue Warning"
            :style="{ width: '500px' }"
        >
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Warning Level</label>
                    <Dropdown
                        v-model="warningLevel"
                        :options="warningLevels"
                        optionLabel="label"
                        placeholder="Select Warning Level"
                        class="w-full"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium">Admin Notes</label>
                    <Textarea
                        v-model="adminNotes"
                        rows="3"
                        placeholder="Enter any additional notes about this warning"
                        class="w-full"
                    />
                </div>

                <div class="p-4 mt-4 bg-gray-50 rounded-lg">
                    <h3 class="mb-2 font-medium">Preview Message to User</h3>
                    <p class="text-sm text-gray-600">
                        Dear {{ props.report.user.name }},<br><br>
                        This is a {{ warningLevel?.label?.toLowerCase() || 'warning' }} regarding a violation of our community guidelines.
                        Further violations may result in account suspension or termination.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button
                        label="Cancel"
                        text
                        @click="warningDialog = false"
                    />
                    <Button
                        label="Approve Report"
                        severity="warning"
                        :disabled="!warningLevel"
                        @click="issueWarning"
                    />
                </div>
            </template>
        </Dialog>
    </AdminLayout>
</template>
