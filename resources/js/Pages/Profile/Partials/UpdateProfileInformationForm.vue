<script setup>
import { ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "primevue/usetoast";
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import { Icon } from '@iconify/vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;
const toast = useToast();
const previewImage = ref(null);
const fileUploadRef = ref(null);
const profilePictureUploaded = ref(false);

const form = useForm({

    profile_picture: null
});

const showSuccessToast = (message) => {
    toast.add({
        severity: 'success',
        summary: 'Success',
        detail: message,
        life: 3000,
        group: 'br'
    });
};

const showErrorToast = (message) => {
    toast.add({
        severity: 'error',
        summary: 'Error',
        detail: message,
        life: 3000,
        group: 'br'
    });
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            showSuccessToast('Profile updated successfully');
            profilePictureUploaded.value = true;

            // Reset form state
            form.profile_picture = null;
            if (fileUploadRef.value) {
                fileUploadRef.value.clear();
            }
        },
        onError: (errors) => {
            Object.keys(errors).forEach(key => {
                showErrorToast(errors[key]);
            });
        }
    });
};

const onFileSelect = (event) => {
    const file = event.files[0];
    if (file) {
        if (file.size > 2000000) {
            showErrorToast('File size should not exceed 2MB');
            fileUploadRef.value.clear();
            return;
        }

        form.profile_picture = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
        showSuccessToast('Profile picture selected');
        profilePictureUploaded.value = false;
    }
};

const onFileRemove = () => {
    form.profile_picture = null;
    previewImage.value = null;
    profilePictureUploaded.value = false;
    showSuccessToast('Profile picture removed');
};

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash.message) {
        showSuccessToast(flash.message);
    }
    if (flash.error) {
        showErrorToast(flash.error);
    }
}, { deep: true });
</script>

<template>
    <section class="py-6 min-h-screen bg-gray-50 sm:py-12">
        <Toast position="top-right" group="br" />

        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Profile Information</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Manage your profile information and account settings
                </p>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Profile Picture Section -->
                <div class="lg:col-span-4">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Profile Picture</h3>
                            </div>

                            <div class="flex flex-col items-center space-y-4">
                                <!-- Profile Picture Display -->
                                <div class="relative group">
                                    <div class="overflow-hidden w-40 h-40 bg-gray-100 rounded-full border-4 border-white shadow-lg transition-transform duration-300 transform group-hover:scale-105">
                                        <img
                                            v-if="previewImage || $page.props.auth.user.profile_image"
                                            :src="previewImage ? previewImage : `/storage/${$page.props.auth.user.profile_image}`"
                                            class="object-cover w-full h-full"
                                            alt="Profile Preview"
                                        />
                                        <div v-else class="flex justify-center items-center w-full h-full bg-primary">
                                            <span class="text-4xl font-semibold text-white">
                                                {{ form.name ? form.name.charAt(0).toUpperCase() : 'U' }}
                                            </span>
                                        </div>
                                    </div>


                                </div>

                                <!-- File Upload -->
                                <div class="w-full">
                                    <FileUpload
                                        ref="fileUploadRef"
                                        mode="basic"
                                        :auto="true"
                                        accept="image/*"
                                        :maxFileSize="2000000"
                                        @select="onFileSelect"
                                        @remove="onFileRemove"
                                        chooseLabel="Choose Picture"
                                        class="w-full"
                                        :class="{ 'p-button-outlined': !previewImage && !user.profile_picture }"
                                    />
                                </div>

                                <div v-if="(previewImage || form.profile_picture) && !profilePictureUploaded">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md border border-transparent shadow-sm bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        :class="{ 'opacity-75 cursor-not-allowed': form.processing || profilePictureUploaded }"
                                        :disabled="form.processing || profilePictureUploaded"
                                        @click="submit"
                                    >
                                        <Icon icon="mdi:check" class="mr-2 w-5 h-5" />
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Information Section -->
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center space-x-2">
                                <Icon icon="heroicons:user-circle" class="w-6 h-6 text-gray-600" />
                                <p class="text-lg font-semibold text-gray-900">{{ user.name }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Icon icon="heroicons:envelope" class="w-6 h-6 text-gray-600" />
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                            </div>

                            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="flex items-center mt-4 space-x-2">
                                <Icon icon="heroicons:exclamation-circle" class="w-6 h-6 text-yellow-700" />
                                <p class="text-sm text-yellow-700">Your email address is unverified.</p>
                                <Link :href="route('verification.send')" method="post" as="button" class="text-yellow-600 underline hover:text-yellow-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">Re-send verification email</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
