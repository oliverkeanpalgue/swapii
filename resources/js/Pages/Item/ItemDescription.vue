<script setup>
import Image from 'primevue/image';
import Galleria from 'primevue/galleria';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Aut from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CardItems from '@/Components/CardItems.vue';
import Dialog from 'primevue/dialog';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const toast = useToast();
const props = defineProps({
    item: Object,
    relatedItems: Object
})

onMounted(() => {
    console.log(props.relatedItems);
    console.log(props.item);
})

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

const reportModal = ref(false);
const reportForm = useForm({
    concern: '',
    description: '',
    proofs: []
});

const selectedReason = ref('');
const reportReasons = [
    { label: 'Rude/Abusive', value: 'Rude/Abusive' },
    { label: 'Spam', value: 'Spam' },
    { label: 'Exposing Personal Information', value: 'Exposing Personal Information' },
    { label: 'Offensive Content', value: 'Offensive Content' },
    { label: 'Other', value: 'Other' }
];

const submitReport = () => {
    reportForm.post(route('reports.post.store', props.item.id), {
        onSuccess: () => {
            reportModal.value = false;
            reportForm.reset();
            selectedReason.value = '';
        },
        onError: (e) => {
            toast.add({ severity: 'error', summary: 'Error', detail: e, life: 3000 });
        }
    });
};

const handleFileUpload = (event) => {
    console.log('Upload event:', event);

    // Convert FileList to array and append each file
    Array.from(event.files).forEach(file => {
        reportForm.proofs.push(file);
    });

    toast.add({
        severity: 'success',
        summary: 'Success',
        detail: `${event.files.length} file(s) selected`,
        life: 3000
    });
};

const onUploadError = () => {
    // This is just to prevent console errors, as we're not actually uploading to a server yet
    console.log('Upload handled client-side');
};

const clearUpload = () => {
    reportForm.proofs = [];
};

const onTemplateRemove = (file) => {
    const index = reportForm.proofs.indexOf(file);
    if (index !== -1) {
        const newProofs = [...reportForm.proofs];
        newProofs.splice(index, 1);
        reportForm.proofs = newProofs;
    }
};

const selectedPreviewIndex = ref(0);
</script>

<template>

    <Head>
        <title>{{ props.item.item_name }} </title>
    </Head><!-- ... header section ... -->
    <AuthenticatedLayout>
        <section class="py-8 bg-gray-50">
            <div class="px-4 mx-auto max-w-screen-xl">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                <!-- Image Gallery Section -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="flex justify-center w-full">
                        <Image :src="'/storage/'+props.item.images[selectedImage]?.image"
                            :pt="{
                                image: { class: 'w-[400px] h-[400px] object-cover rounded' },
                                preview: { class: 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-75' },
                                previewImage: { class: 'max-w-[90vw] max-h-[90vh] object-contain' }
                            }"
                            preview
                            :zoomInDisabled="false"
                            :zoomOutDisabled="false"
                            :zoomInLabel="'Zoom In'"
                            :zoomOutLabel="'Zoom Out'"
                        />
                    </div>

                    <!-- Thumbnails -->
                    <div class="flex gap-2 justify-center">
                        <div v-for="(image, index) in props.item.images"
                            :key="image.id"
                            @click="changeImage(index)"
                            :class="['w-[80px] h-[80px] cursor-pointer rounded border bg-white p-1',
                                    selectedImage === index ? 'border-primary ring-2 ring-primary' : 'border-gray-200']">
                            <img class="object-cover w-full h-full rounded"
                                :src="'/storage/'+image.image">
                        </div>
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="mt-6 lg:mt-0">
                    <div class="space-y-6">
                        <!-- Product Title & Seller -->
                        <div class="flex justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">
                                {{ props.item.item_name }}
                            </h1>
                            <Link :href="'/view-user/'+props.item.user.id"
                                class="inline-flex items-center mt-1 text-sm text-primary hover:underline">
                                <Icon icon="heroicons:user-circle-20-solid" class="mr-1 w-5 h-5" />
                                {{ props.item.user.name }}
                            </Link>
                            </div>

                              <!-- Actions -->
                            <div class="flex justify-end" v-if="$page.props.auth.user.id !== props.item.user_id">
                                <SecondaryButton
                                    @click="reportModal = true"
                                    class="inline-flex gap-2 items-center px-4 h-10 text-sm"
                                >
                                <Icon icon="ic:outline-report-problem" class="w-4 h-4" />
                                    Report Post
                                </SecondaryButton>
                            </div>

                        </div>

                        <!-- Description -->
                        <div class="prose prose-sm">
                            <h3 class="flex gap-2 items-center text-lg font-medium text-gray-900">
                                <Icon icon="heroicons:document-text-20-solid" class="w-5 h-5" />
                                Description
                            </h3>
                            <p class="text-gray-600">{{props.item.item_description}}</p>
                        </div>


                        <!-- Categories & Tags -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="flex gap-2 items-center text-sm font-medium text-gray-900">
                                    <Icon icon="heroicons:folder-20-solid" class="w-5 h-5" />
                                    Category
                                </h3>
                                <span class="inline-flex items-center px-3 py-1 mt-1 text-sm bg-gray-100 rounded-full">
                                    {{ props.item.category.category }}
                                </span>
                            </div>

                            <div>
                                <h3 class="flex gap-2 items-center text-sm font-medium text-gray-900">
                                    <Icon icon="heroicons:tag-20-solid" class="w-5 h-5" />
                                    Tags
                                </h3>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span v-for="tag in props.item.tags" :key="tag.id"
                                        class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-50 rounded-full">
                                        {{ tag.tag }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Preferred Items -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="flex gap-2 items-center text-sm font-medium text-gray-900">
                                <Icon icon="heroicons:gift-20-solid" class="w-5 h-5" />
                                Preferred Items for Trade
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">{{ props.item.preferred_items }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="props.item.user_id !== $page.props.auth.user.id" class="flex gap-4">
                            <Link :href="'/chat/'+props.item.user_id" class="flex-1">
                                <SecondaryButton class="gap-2 justify-center py-3 w-full">
                                    <Icon icon="heroicons:chat-bubble-left-20-solid" class="w-5 h-5" />
                                    Message Trader
                                </SecondaryButton>
                            </Link>

                            <Link v-if="props.item.status !== 'traded'" :href="'/request-trade/'+props.item.id" class="flex-1">
                                <PrimaryButton class="gap-2 justify-center py-3 w-full">
                                    <Icon icon="heroicons:arrow-path-20-solid" class="w-5 h-5" />
                                    Request Trade
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </section>

        <section class="py-8 bg-white" v-if="$page.props.auth.user.id !== props.item.user_id">
            <div class="px-4 mx-auto max-w-screen-xl">
                <div class="mb-6">
                    <h2 class="flex gap-2 items-center text-xl font-bold text-gray-900">
                        <Icon icon="heroicons:squares-2x2-20-solid" class="w-5 h-5" />
                        Related Items
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Similar items like <span class="font-bold">{{ props.item.item_name }}</span>
                    </p>
                </div>

                <div v-if="props.relatedItems?.length"
                    class="grid grid-cols-1 gap-4 xs:grid-cols-2 lg:grid-cols-4 sm:gap-6">
                    <CardItems
                        v-for="item in props.relatedItems"
                        :key="item.id"
                        :item="item"
                    />
                </div>
                <div v-else class="py-8 text-center text-gray-500">
                    No related items found
                </div>
            </div>
        </section>

        <!-- Report Dialog -->
        <Dialog
            v-model:visible="reportModal"
            modal
            header="Report Post"
            :style="{ width: '500px' }"
            :closable="true"
            class="report-dialog"
        >
            <form @submit.prevent="submitReport" class="flex flex-col gap-6 p-4">
                <!-- Report Reason Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">What's wrong with this post?</h3>
                    <div class="space-y-3">
                        <div v-for="reason in reportReasons" :key="reason.value"
                            class="flex gap-2 items-center">
                            <RadioButton
                                v-model="reportForm.concern"
                                :value="reason.value"
                                :inputId="reason.value"
                            />
                            <label :for="reason.value" class="text-sm text-gray-700">
                                {{ reason.label }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <Textarea
                        v-model="reportForm.description"
                        rows="3"
                        placeholder="Please provide more details about your report..."
                        class="w-full text-sm"
                    />
                </div>

                <!-- Proof Upload -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Proof (Optional)
                    </label>
                    <Toast />
                    <FileUpload
                        :multiple="true"
                        accept="image/*"
                        :maxFileSize="1000000"
                        @select="handleFileUpload"
                        @clear="clearUpload"
                        @remove="onTemplateRemove"
                        @error="onUploadError"
                        :auto="false"
                        :showUploadButton="false"
                        :showCancelButton="false"
                        class="w-full"
                        :pt="{
                            content: 'border border-gray-300 rounded-lg',
                            file: {
                                class: ['py-4']
                            }
                        }"
                    >
                        <template #empty>
                            <div class="p-4 text-center rounded-lg border-2 border-gray-300 border-dashed">
                                <Icon icon="heroicons:photo-20-solid" class="mx-auto w-8 h-8 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-600">
                                    Drag and drop images here or click to browse
                                </p>
                            </div>
                        </template>
                        <template #file="{ files, uploadedFiles, removeUploadedFile, removeFile }">
                            <div v-if="files.length > 0">
                                <div v-for="(file, index) of files" :key="file.name + file.type + file.size" class="flex gap-4 items-center py-2">
                                    <img :src="file.objectURL" class="object-cover w-16 h-16 rounded" />
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ file.name }}</div>
                                        <div class="text-xs text-gray-500">{{ (file.size / 1024).toFixed(2) }} KB</div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile(index)"
                                        class="p-1 text-gray-400 hover:text-red-500"
                                    >
                                        <Icon icon="heroicons:x-mark-20-solid" class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </FileUpload>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end pt-4 border-t">
                    <SecondaryButton
                        type="button"
                        @click="reportModal = false"
                        class="px-4 py-2"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :disabled="reportForm.processing || !reportForm.concern"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700"
                    >
                        {{ reportForm.processing ? 'Submitting...' : 'Submit Report' }}
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
.p-image-preview-container {
    width: 400px;
    height: 400px;
}

/* Report Dialog Styles */
.report-dialog :deep(.p-dialog-header) {
    padding: 1.25rem;
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
}

.report-dialog :deep(.p-dialog-content) {
    padding: 0;
    background-color: white;
}

.report-dialog :deep(.p-radiobutton .p-radiobutton-box.p-highlight) {
    border-color: #ef4444;
    background: #ef4444;
}

.report-dialog :deep(.p-radiobutton .p-radiobutton-box:not(.p-disabled):not(.p-highlight):hover) {
    border-color: #ef4444;
}

.report-dialog :deep(.p-fileupload-choose) {
    width: 100%;
    background: #f3f4f6;
    border: 1px dashed #d1d5db;
    color: #4b5563;
    transition: all 0.2s ease;
}

.report-dialog :deep(.p-fileupload-choose:hover) {
    background: #e5e7eb;
    border-color: #9ca3af;
}

.report-dialog :deep(.p-inputtextarea) {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
}

.report-dialog :deep(.p-inputtextarea:focus) {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.report-dialog :deep(.p-dialog) {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}
</style>
