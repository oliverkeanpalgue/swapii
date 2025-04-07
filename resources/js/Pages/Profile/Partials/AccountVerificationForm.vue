<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Icon } from '@iconify/vue';
import FileUpload from 'primevue/fileupload';

const props = defineProps({
    hasVerifiedDetails: Boolean,
    // verificationStatus: {
    //     type: String,
    //     default: 'unverified' //unverified, pending, verified, rejected
    // }
});

const emit = defineEmits(['verificationSubmitted']);
const isSubmitted = ref(false);
const isLoading = ref(false);

const form = useForm({
    image: null,
    id_image: null,
    school: null,
    other_school: null,
});

const schools = [
    { name: 'University of Baguio', code: 'UB' },
    { name: 'University of the Cordilleras', code: 'UC' },
    { name: 'Saint Louis University', code: 'SLU' },
    { name: 'Baguio Central University', code: 'BCU' },
];

const selfiePreview = ref(null);
const idPreview = ref(null);

const onSelfieSelect = (event) => {
    const file = event.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            selfiePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        form.image = file;
    }
};

const onIdSelect = (event) => {
    const file = event.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            idPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        form.id_image = file;
    }
};

const submitForm = () => {
    isLoading.value = true;
    form.post(route('profile.verify'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selfiePreview.value = null;
            idPreview.value = null;
            isSubmitted.value = true;
            emit('verificationSubmitted', true);
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        }
    });
};

onMounted(( ) => {
    console.log(props.hasVerifiedDetails);
});
</script>

<template>
    <div class="space-y-6">
        <div class="pb-6 border-b">
            <h2 class="text-2xl font-semibold text-gray-900">Account Verification</h2>
            <p class="mt-1 text-sm text-gray-500">Please provide the required information to fully verify your account.</p>
        </div>

        <div v-if="$page.props.auth.user.is_verified === 1"
             class="bg-emerald-50 border border-emerald-200 p-6 mb-4 rounded-lg flex items-center justify-between"
        >
            <div class="flex items-center space-x-3">
                <div class="bg-emerald-100 rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-emerald-800">Verified Account</h3>
                    <p class="text-sm text-emerald-600">Your account has been fully verified</p>
                </div>
            </div>
            <div class="bg-emerald-100 rounded-full p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <div v-if="hasVerifiedDetails && $page.props.auth.user.is_verified === 0"
             class="p-4 mb-4 bg-yellow-50 rounded-md border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <Icon icon="heroicons:clock"
                          class="w-5 h-5 text-yellow-400"
                          aria-hidden="true" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Verification in Progress</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Your verification request has been submitted and is currently under review. We'll notify you once the process is complete.</p>
                    </div>
                </div>
            </div>
        </div>

        <form v-if="!hasVerifiedDetails && !isSubmitted" @submit.prevent="submitForm" class="space-y-8">
            <div class="space-y-4">
                <InputLabel value="Selfie Photo" />
                <div class="flex justify-center items-center w-full">
                    <div class="w-full max-w-md">
                        <div class="relative p-6 text-center rounded-md border-2 border-dashed transition-colors hover:border-primary">
                            <transition name="fade-scale">
                                <div v-if="!selfiePreview" class="space-y-2">
                                    <Icon icon="heroicons:camera" class="mx-auto w-12 h-12 text-gray-400" />
                                    <div class="text-sm text-gray-600">Take a clear selfie photo</div>
                                </div>
                                <img v-else :src="selfiePreview" class="mx-auto max-h-48 rounded" />
                            </transition>
                            <FileUpload
                                mode="basic"
                                accept="image/*"
                                :maxFileSize="5000000"
                                @select="onSelfieSelect"
                                :auto="true"
                                chooseLabel="Choose Selfie"
                                class="mt-2 p-button"
                            />
                        </div>
                    </div>
                </div>
                <InputError :message="form.errors.image" />
            </div>

            <!-- Student ID Upload Section -->
            <div class="space-y-4">
                <InputLabel value="Student ID" />
                <div class="flex justify-center items-center w-full">
                    <div class="w-full max-w-md">
                        <div class="relative p-6 text-center rounded-md border-2 border-dashed transition-colors hover:border-primary">
                            <transition name="fade-scale">
                                <div v-if="!idPreview" class="space-y-2">
                                    <Icon icon="heroicons:identification" class="mx-auto w-12 h-12 text-gray-400" />
                                    <div class="text-sm text-gray-600">Upload your student ID</div>
                                </div>
                                <img v-else :src="idPreview" class="mx-auto max-h-48 rounded" />
                            </transition>
                            <FileUpload
                                mode="basic"
                                accept="image/*"
                                :maxFileSize="5000000"
                                @select="onIdSelect"
                                :auto="true"
                                chooseLabel="Choose ID"
                                class="mt-2 p-button"
                            />
                        </div>
                    </div>
                </div>
                <InputError :message="form.errors.id_image" />
            </div>

            <!-- Department Selection -->
            <div class="space-y-4">
                <InputLabel value="School" />
                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <Icon icon="heroicons:building-office-2" class="w-5 h-5 text-gray-400" />
                    </div>
                    <select
                        v-model="form.school"
                        class="pl-10 w-full text-sm rounded-md border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    >
                        <option value="" disabled>Select School</option>
                        <option v-for="school in schools" :key="school.code" :value="school.name">
                            {{ school.name }}
                        </option>
                        <option value="Other">Other School</option>
                    </select>
                </div>
                <div v-if="form.school === 'Other'" class="mt-2">
                    <input
                        type="text"
                        v-model="form.other_school"
                        class="w-full text-sm rounded-md border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                        placeholder="Enter school name"
                    />
                </div>
                <InputError :message="form.errors.school" />
                <InputError :message="form.errors.other_school" />
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md border border-transparent shadow-sm bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    :class="{ 'opacity-75': form.processing || isLoading }"
                    :disabled="form.processing || isLoading"
                >
                    <Icon v-if="isLoading"
                          icon="heroicons:arrow-path"
                          class="mr-2 w-5 h-5 spinner" />
                    <Icon v-else
                          icon="heroicons:paper-airplane"
                          class="mr-2 w-5 h-5" />
                    {{ isLoading ? 'Submitting...' : 'Submit for Verification' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.3s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

.spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.success-checkmark {
    animation: checkmark 0.3s ease-in-out forwards;
}

@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
