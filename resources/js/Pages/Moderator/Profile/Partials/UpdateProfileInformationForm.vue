<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Avatar from 'primevue/avatar';
import FileUpload from 'primevue/fileupload';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

const toast = useToast();

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    profile_picture: null,
});

const previewImage = ref(null);
const showPreview = ref(false);

const handleProfilePictureChange = (e) => {
    const file = e.files[0];
    if (file) {
        if (file.size > 2000000) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'File size should not exceed 2MB',
                life: 3000,
                group: 'br'
            });
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
            showPreview.value = true;
            form.profile_picture = file;
        };
        reader.readAsDataURL(file);
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Profile picture selected',
            life: 3000,
            group: 'br'
        });
    }
};

const resetProfilePicture = () => {
    form.profile_picture = null;
    previewImage.value = null;
    showPreview.value = false;
    
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Profile picture reset',
        life: 3000,
        group: 'br'
    });
};

const submit = () => {
    form.post(route('mod.profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showPreview.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Profile updated successfully', 
                life: 3000,
                group: 'br'
            });
        },
        onError: (errors) => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: Object.values(errors).join('\n'), 
                life: 3000,
                group: 'br'
            });
        }
    });
};

const getInitials = (name) => {
    const initials = name.match(/\b\w/g) || [];
    return ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
};
</script>

<template>
    <section class="min-h-screen">
        <Toast position="top-right" group="br" />
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Content Grid -->
            <div class="">
                <div class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <!-- Profile Picture Section -->
                        <div class="md:col-span-4">
                            <div class="space-y-6">
                                <div class="flex flex-col items-center space-y-4">
                                    <!-- Profile Picture Display -->
                                    <div class="relative group">
                                        <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-lg">
                                            <img 
                                                v-if="showPreview || $page.props.auth.user.profile_picture"
                                                :src="showPreview ? previewImage : $page.props.auth.user.profile_picture" 
                                                class="w-full h-full object-cover"
                                                :alt="$page.props.auth.user.name"
                                            />
                                            <div v-else 
                                                class="w-full h-full bg-gray-100 flex items-center justify-center">
                                                <span class="text-2xl font-semibold text-gray-400">
                                                    {{ getInitials($page.props.auth.user.name) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Upload Controls -->
                                    <div class="flex flex-col items-center gap-3 w-full">
                                        <FileUpload
                                            mode="basic"
                                            :auto="true"
                                            accept="image/*"
                                            :maxFileSize="2000000"
                                            @select="handleProfilePictureChange"
                                            class="hidden"
                                            ref="fileUpload"
                                        >
                                            <template #content="{ chooseCallback }">
                                                <button 
                                                    @click="chooseCallback" 
                                                    type="button"
                                                    class="w-full px-4 py-2 text-sm font-medium rounded-md shadow-sm transition-colors duration-200 ease-in-out"
                                                    :class="form.profile_picture ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-primary text-white hover:bg-primary-600'"
                                                >
                                                    <div class="flex items-center justify-center">
                                                        <Icon 
                                                            :icon="form.profile_picture ? 'mdi:check' : ($page.props.auth.user.profile_picture ? 'mdi:pencil' : 'mdi:upload')" 
                                                            class="w-5 h-5 mr-2"
                                                        />
                                                        {{ form.profile_picture 
                                                            ? 'Picture Selected' 
                                                            : ($page.props.auth.user.profile_picture ? 'Change Picture' : 'Upload Picture') 
                                                        }}
                                                    </div>
                                                </button>
                                            </template>
                                        </FileUpload>

                                        <button
                                            v-if="showPreview"
                                            type="button"
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700 transition-colors duration-200 ease-in-out"
                                            @click="resetProfilePicture"
                                        >
                                            <div class="flex items-center justify-center">
                                                <Icon icon="mdi:close" class="w-5 h-5 mr-2" />
                                                Reset
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Information Section -->
                        <div class="md:col-span-8">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Update your account's profile information.
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <!-- Name Input -->
                                    <div>
                                        <InputLabel for="name" value="Full Name" />
                                        <TextInput
                                            id="name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.name"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <!-- Email Input -->
                                    <div>
                                        <InputLabel for="email" value="Email Address" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            v-model="form.email"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <!-- Email Verification Notice -->
                                    <div v-if="mustVerifyEmail && user.email_verified_at === null">
                                        <p class="text-sm mt-2 text-gray-800">
                                            Your email address is unverified.
                                            <Link
                                                :href="route('verification.send')"
                                                method="post"
                                                as="button"
                                                class="underline text-sm text-primary hover:text-primary-700"
                                            >
                                                Click here to re-send verification email.
                                            </Link>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 pt-5 border-t border-gray-200">
                        <div class="flex justify-end items-center gap-4">
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                @click="form.reset()"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-md shadow-sm hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                                @click="submit"
                            >
                                <Icon icon="mdi:check" class="w-5 h-5 mr-2" />
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.p-fileupload-choose {
    display: none !important;
}
</style>