<script setup>
import Image from 'primevue/image';
import Galleria from 'primevue/galleria';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import ModeratorLayout from '@/Layouts/ModeratorLayout.vue';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { router } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    item: Object
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

// Add new state management for moderation
const showRejectDialog = ref(false);
const moderationForm = useForm({
    reason: ''
});

const toast = useToast();
const acceptDialogVisible = ref(false);

// Moderation actions
const openAcceptDialog = () => {
    acceptDialogVisible.value = true;
};

const handleAccept = () => {
    router.patch(route('mod.post.accept', props.item.id), {}, {
        onSuccess: () => {
            acceptDialogVisible.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Post accepted successfully', 
                life: 3000 
            });
            // Optionally redirect to the pending posts page
            router.visit(route('moderator.posts.pending'));
        },
        onError: () => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Failed to accept post', 
                life: 3000 
            });
        }
    });
};

const openRejectDialog = () => {
    showRejectDialog.value = true;
};

const rejectPost = () => {
    moderationForm.clearErrors();
    
    moderationForm.patch(route('mod.post.reject', props.item.id), {
        onSuccess: () => {
            showRejectDialog.value = false;
            moderationForm.reset();
            toast.add({ 
                severity: 'info', 
                summary: 'Success', 
                detail: 'Post has been rejected', 
                life: 3000 
            });
            // Redirect to pending posts page after successful rejection
            router.visit(route('moderator.posts.pending'));
        },
        onError: () => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Failed to reject post', 
                life: 3000 
            });
        }
    });
};
</script>

<template>
    <Head title="Post Review" />
    <ModeratorLayout>
        <Toast position="top-right" />
        <div class="max-w-screen-xl px-4 mx-auto">
            <!-- Status Banner -->
            <div class="mb-6 flex flex-col md:flex-row items-center justify-between bg-white p-4 rounded-md border border-gray-200 shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Post Review</h1>
                    <p class="text-sm text-gray-600">Review details and moderate this post</p>
                </div>
                <div v-if="props.item.approval != 'approved' && props.item.approval != 'rejected'" class="flex gap-3 mt-2 md:mt-0">
                    <SecondaryButton @click="openRejectDialog" class="bg-red-50 text-red-600 hover:bg-red-100">
                        <Icon icon="heroicons:x-mark-20-solid" class="w-5 h-5 mr-1" />
                        Reject
                    </SecondaryButton>
                    <PrimaryButton @click="openAcceptDialog">
                        <Icon icon="heroicons:check-20-solid" class="w-5 h-5 mr-1" />
                        Accept
                    </PrimaryButton>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-md shadow-sm border border-gray-200">
                <div class="lg:grid lg:grid-cols-2 lg:gap-8 p-6">
                    <!-- Image Gallery Section -->
                    <div class="space-y-4">
                        <!-- Main Image -->
                        <div class="w-full flex justify-center">
                            <Image :src="'/storage/' + props.item.images[selectedImage]?.image" 
                                :pt="{
                                    image: { class: 'w-[400px] h-[400px] object-cover rounded' }
                                }"
                                preview />
                        </div>
                        
                        <!-- Thumbnails -->
                        <div class="flex gap-2 justify-center">
                            <div v-for="(image, index) in props.item.images" 
                                :key="image.id"
                                @click="changeImage(index)"
                                :class="['w-[80px] h-[80px] cursor-pointer rounded border bg-white p-1',
                                        selectedImage === index ? 'border-primary ring-2 ring-primary' : 'border-gray-200']">
                                <img class="w-full h-full object-cover rounded"
                                    :src="'/storage/' + image.image">
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="mt-6 lg:mt-0 space-y-6">
                        <!-- Post Details -->
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-bold text-gray-900">
                                    {{ props.item.item_name }} <br><span class="text-sm font-normal text-gray-600">by {{ props.item.user.name }}</span>
                                </h2>
                                <span v-if="props.item.approval == 'pending'" class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    Pending Review
                                </span>
                                <span v-if="props.item.approval == 'rejected'" class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                    Rejected
                                </span>
                                <span v-if="props.item.approval == 'approved'" class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                    Approved
                                </span>
                            </div>
                            <div class="space-y-2">
                                <!-- Posted Date -->
                                <div class="flex items-center text-sm text-gray-500">
                                    <Icon icon="heroicons:clock-20-solid" class="mr-1 w-5 h-5" />
                                    Posted {{ new Date(props.item.created_at).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2 mb-2">
                                <Icon icon="heroicons:document-text-20-solid" class="w-5 h-5" />
                                Description
                            </h3>
                            <p class="text-gray-600">{{ props.item.item_description }}</p>
                        </div>

                        <!-- Category & Tags -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900 flex items-center gap-2 mb-2">
                                    <Icon icon="heroicons:folder-20-solid" class="w-5 h-5" />
                                    Category
                                </h3>
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800">
                                    {{ props.item.category.category }}
                                </span>
                            </div>
                            
                            <div class="bg-gray-50 p-4 mt-2 md:mt-0 rounded-md border border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900 flex items-center gap-2 mb-2">
                                    <Icon icon="heroicons:tag-20-solid" class="w-5 h-5" />
                                    Tags
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="tag in props.item.tags" :key="tag.id"
                                        class="inline-flex items-center rounded-full bg-yellow-50 px-3 py-1 text-sm font-medium text-yellow-800">
                                        {{ tag.tag }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Preferred Items -->
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900 flex items-center gap-2 mb-2">
                                <Icon icon="heroicons:gift-20-solid" class="w-5 h-5" />
                                Preferred Items for Trade
                            </h3>
                            <p class="text-gray-600">{{ props.item.preferred_items }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Dialog -->
        <Dialog 
            v-model:visible="showRejectDialog" 
            modal 
            header="Reject Post" 
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
                    Confirm Post Rejection
                </h3>
                <p class="text-sm text-gray-500 text-left mb-6">
                    Please provide a reason for rejecting this post. This information will be sent to the user.
                </p>

                <form @submit.prevent="rejectPost" class="w-full">
                    <div class="mb-3">
                        <InputLabel for="reason" value="Rejection Reason" />
                        <Textarea
                            v-model="moderationForm.reason"
                            id="reason"
                            rows="3"
                            class="block w-full mt-1"
                            :class="{ 'border-red-500': moderationForm.errors.reason }"
                            placeholder="Enter your reason for rejection here..."
                        />
                        <div v-if="moderationForm.errors.reason" class="text-sm text-red-600 mt-2">
                            {{ moderationForm.errors.reason }}
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="showRejectDialog = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            @click="rejectPost"
                            :disabled="moderationForm.processing"
                            class="bg-red-600 hover:bg-red-700"
                        >
                            Confirm Rejection
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Dialog>

        <!-- Accept Dialog -->
        <Dialog 
            v-model:visible="acceptDialogVisible" 
            modal 
            header="Accept Post" 
            :style="{ width: '30rem' }"
            :closable="true" 
            :dismissableMask="true"
            class="mx-4 md:mx-0"
        >
            <div class="flex flex-col items-center p-4">
                <Icon 
                    icon="mdi:help-circle-outline" 
                    class="text-primary-500 mb-4" 
                    width="4rem" 
                    height="4rem" 
                />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Confirm Post Acceptance
                </h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Are you sure you want to accept this post? This action will make the post visible to all users.
                </p>
                
                <div class="flex gap-4 w-full">
                    <SecondaryButton 
                        class="flex-1 justify-center"
                        @click="acceptDialogVisible = false"
                    >
                        <Icon icon="mdi:close" class="mr-2" />
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton 
                        class="flex-1 justify-center"
                        @click="handleAccept"
                    >
                        <Icon icon="mdi:check" class="mr-2" />
                        Accept Post
                    </PrimaryButton>
                </div>
            </div>
        </Dialog>
    </ModeratorLayout>
</template>

<style scoped>
.p-image-preview-container {
    width: 400px;
    height: 400px;
}
</style>
