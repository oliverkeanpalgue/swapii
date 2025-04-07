<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { onMounted, ref, computed } from 'vue';
import CardItems from '@/Components/CardItems.vue';
import Modal from '@/Components/Modal.vue';
import Dialog from 'primevue/dialog';
import Rating from 'primevue/rating';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import RadioButton from 'primevue/radiobutton';
import InputText from 'primevue/inputtext';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToggleSwitch from 'primevue/toggleswitch';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const imageNum = ref(5);
const num = ref(4);
const reportModal = ref(false)
const props = defineProps({
    user: Object,
    averageRating: Number,
    ratingsCount: Number,
    successfulTrades: Number,
})



const ratingDialog = ref(false);
const form = useForm({
    rated_user_id: props.user.id,
    rating: 0,
    description: '',
    is_anonymous: false
});

const submitRating = () => {


    form.post(route('ratings.store'), {
        onSuccess: () => {
            ratingDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Rating submitted successfully',
                life: 3000
            });
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        }
    });


};

const reportForm = useForm({
    concern: '',
    other_concern: '',
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
    reportForm.post(route('reports.store', props.user.id), {
        onSuccess: () => {
            reportModal.value = false;
            reportForm.reset();
            selectedReason.value = '';
        }
    });
};

const formattedDate = computed(() => {
    const date = new Date(props.user.created_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handleFileUpload = (event) => {
    console.log('Upload event:', event);
    reportForm.proofs = event.files;
    
    toast.add({ 
        severity: 'success', 
        summary: 'Success', 
        detail: `${event.files.length} file(s) selected`, 
        life: 3000 
    });
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

const onUploadError = () => {
    // This is just to prevent console errors, as we're not actually uploading to a server yet
    console.log('Upload handled client-side');
};

</script>

<template>

    <Head title="User" />

    <AuthenticatedLayout>
        <section class="py-8 bg-gradient-to-b from-primary/10 to-white">
            <div class="max-w-screen-xl px-4 mx-auto lg:px-8">
                <div class="grid gap-8 lg:grid-cols-12">
                    <!-- Profile Sidebar -->
                    <div class="lg:col-span-3">
                        <div
                            class="flex flex-col items-center p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="my-4 flex justify-center items-center w-36 h-36 rounded-full bg-primary">
                                <img v-if="$page.props.profilePicture" :src="`/storage/${$page.props.profilePicture}`" class="w-full h-full object-cover rounded-full" :alt="$page.props.auth.user.name" />

                                <span v-else class="text-4xl font-medium text-white">
                                    {{ getInitials($page.props.auth.user.name) }}
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900 mb-1">{{ user.name }}</h2>
                            <div class="flex items-center gap-1 mb-4">
                                <Icon icon="material-symbols:circle" class="w-3 h-3 text-green-500" />
                                <span class="text-sm font-medium text-gray-600">Online</span>
                            </div>
                            <div class="w-full pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Member since: </span>
                                    <span class="text-xs font-medium text-gray-900">{{ formattedDate }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <!-- Trader Stats -->
                        <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100 mb-8">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-900">Trader Profile</h2>
                                <div class="flex items-center gap-4">
                                    <PrimaryButton 
                                        class="flex items-center px-4 py-2 h-10 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary-dark focus:bg-primary-dark active:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150">
                                        <Link :href="'/chat/' + props.user.id" class="flex items-center">
                                            <Icon icon="material-symbols:chat" class="w-5 h-5 mr-2" />
                                            Message
                                        </Link>
                                    </PrimaryButton>
                                    <SecondaryButton @click="ratingDialog = true"
                                        class="text-sm font-medium hover:text-primary-700 transition-colors h-10">
                                        <Icon icon="material-symbols:star" class="w-5 h-5 mr-2" />
                                        Rate User
                                    </SecondaryButton>
                                    <button @click="reportModal = true"
                                        class="flex items-center border-b border-gray-400 hover:border-primary text-sm font-medium text-gray-600 hover:text-primary transition-colors h-7">
                                        <Icon icon="material-symbols:flag" class="w-5 h-5 mr-2" />
                                        Report
                                    </button>
                                </div>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-3">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="p-2 bg-primary/10 rounded-lg">
                                            <Icon icon="gridicons:product" class="w-5 h-5 text-primary" />
                                        </div>
                                        <h3 class="font-medium text-gray-900">Items Listed</h3>
                                    </div>
                                    <p class="text-2xl font-bold text-primary">{{ user.items.length }}</p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="p-2 bg-primary/10 rounded-lg">
                                            <Icon icon="material-symbols:star" class="w-5 h-5 text-primary" />
                                        </div>
                                        <h3 class="font-medium text-gray-900">Rating</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-2xl font-bold text-primary">{{ props.averageRating }}</p>
                                        <Link :href="route('trader.ratings', props.user.id)"
                                            class="text-sm underline font-medium text-gray-600 hover:text-primary transition-colors">
                                        ({{ props.ratingsCount }} reviews)
                                        </Link>
                                    </div>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="p-2 bg-primary/10 rounded-lg">
                                            <Icon icon="material-symbols:handshake" class="w-5 h-5 text-primary" />
                                        </div>
                                        <h3 class="font-medium text-gray-900">Successful Trades</h3>
                                    </div>
                                    <p class="text-2xl font-bold text-primary">{{ props.successfulTrades }}</p>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 mb-2">Trader Verification</h3>
                                        <div class="flex items-center gap-2">
                                            <Icon :icon="props.user.is_verified ? 'mdi:check-circle' : 'mdi:alert-circle'" 
                                                  :class="props.user.is_verified ? 'text-green-500' : 'text-yellow-500'" 
                                                  class="w-5 h-5" />
                                            <span class="text-sm text-gray-600">
                                                {{ props.user.is_verified ? 'Verified Trader' : 'Not Verified' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h3>
                                        <div class="flex items-center gap-2">
                                            <Icon icon="material-symbols:mail" class="w-5 h-5 text-gray-400" />
                                            <span class="text-sm text-gray-600">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Items Section -->
        <section class="py-12 bg-gray-50" v-if="props.user.items.length > 0">
            <div class="max-w-screen-xl px-4 mx-auto lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 mb-8 md:flex-row">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Available Items
                    </h2>
                    <div class="w-full md:w-auto">
                        <div class="relative">
                            <input type="search"
                                class="w-full md:w-80 pl-10 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="Search items..." />
                            <Icon icon="material-symbols:search"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        </div>
                    </div>
                </div>
                <!-- Items Grid -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template v-for="item in props.user.items" :key="item.id">
                        <CardItems :item="item" />
                    </template>
                </div>

                <!-- Load More
                <div class="mt-8 text-center">
                    <SecondaryButton @click="num += 4" 
                        class="px-8 py-2.5 text-sm font-medium">
                        Load More
                    </SecondaryButton>
                </div> -->
            </div>
        </section>

        <!-- Report Modal -->
        <Dialog v-model:visible="reportModal" modal header="Report User" :style="{ width: '500px', margin: '20px' }"
            :closable="true">
            <form @submit.prevent="submitReport" class="flex flex-col md:gap-4 p-2 sm:p-4">
                <div class="flex flex-col gap-4">
                    <!-- Standard radio options -->
                    <div class="space-y-3">
                        <div v-for="reason in reportReasons" :key="reason.value"
                            class="flex items-center gap-2">
                            <RadioButton 
                                v-model="reportForm.concern" 
                                :value="reason.value"
                                :inputId="reason.value"
                            />
                            <label :for="reason.value" class="text-sm text-gray-700">
                                {{ reason.label }}
                            </label>
                            <template v-if="reason.value === 'Other'">
                                <InputText 
                                    v-model="reportForm.other_concern" 
                                    placeholder="Please specify..."
                                    :disabled="reportForm.concern !== 'Other'"
                                    class="flex-1 h-8 text-sm"
                                    @click="reportForm.concern = 'Other'"
                                />
                            </template>
                        </div>
                    </div>

                    <small class="text-red-500" v-if="reportForm.errors.concern">
                        {{ reportForm.errors.concern }}
                    </small>
                    <small class="text-red-500"
                        v-if="reportForm.concern === 'Other' && reportForm.errors.other_concern">
                        {{ reportForm.errors.other_concern }}
                    </small>
                </div>

                <div v-if="reportForm.concern" class="flex flex-col gap-4 mt-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Description</label>
                        <Textarea v-model="reportForm.description" rows="3"
                            placeholder="Please provide details about your report..." autoResize />
                        <small class="text-red-500" v-if="reportForm.errors.description">
                            {{ reportForm.errors.description }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Upload Proof (Optional)</label>
                        <Toast />
                        <FileUpload
                            :multiple="true"
                            accept="image/*"
                            :maxFileSize="1000000"
                            @select="handleFileUpload"
                            @clear="clearUpload"
                            @remove="onTemplateRemove"
                            @error="onUploadError"
                            :auto="true"
                            url="/dummy-url"
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
                                <div class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center">
                                    <Icon icon="heroicons:photo-20-solid" class="w-8 h-8 mx-auto text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-600">
                                        Drag and drop images here or click to browse
                                    </p>
                                </div>
                            </template>
                            <template #file="{ files, uploadedFiles, removeUploadedFile, removeFile }">
                                <div v-if="files.length > 0">
                                    <div v-for="(file, index) of files" :key="file.name + file.type + file.size" class="flex items-center gap-4 py-2">
                                        <img :src="file.objectURL" class="w-16 h-16 object-cover rounded" />
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
                        <small class="text-red-500" v-if="reportForm.errors.proofs">
                            {{ reportForm.errors.proofs }}
                        </small>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <SecondaryButton type="button" @click="reportModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit"
                        :disabled="reportForm.processing || !reportForm.concern || (reportForm.concern === 'Other' && !reportForm.other_concern)"
                        class="bg-red-500 hover:bg-red-600" :class="{ 'opacity-75': reportForm.processing }">
                        {{ reportForm.processing ? 'Submitting...' : 'Submit Report' }}
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>

        <!-- Update the Dialog component -->
        <Dialog v-model:visible="ratingDialog" modal header="Rate This User" :style="{ width: '450px', margin: '20px' }"
            :closable="true">
            <form @submit.prevent="submitRating" class="flex flex-col gap-4 p-2 sm:p-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Rating</label>
                    <Rating v-model="form.rating" :cancel="false" />
                    <small class="text-red-500" v-if="form.errors.rating">
                        {{ form.errors.rating }}
                    </small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Description</label>
                    <Textarea v-model="form.description" rows="3" placeholder="Tell us about your experience..."
                        autoResize />
                    <small class="text-red-500" v-if="form.errors.description">
                        {{ form.errors.description }}
                    </small>
                </div>

                <div class="flex items-center gap-2">
                <ToggleSwitch 
                    v-model="form.is_anonymous" 
                    class="w-16 h-8"
                    on-label="Yes"
                    off-label="No"
                />
                <span class="text-sm text-gray-600">I want to be anonymous</span>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <SecondaryButton type="button" @click="ratingDialog = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="form.processing || !form.rating"
                        :class="{ 'opacity-75': form.processing }">
                        {{ form.processing ? 'Submitting...' : 'Submit Rating' }}
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>

        <Toast/>
        
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add these styles for the rating component */
:deep(.p-rating .p-rating-item.p-rating-item-active .p-rating-icon) {
    color: #FFA41C;
}

:deep(.p-rating:not(.p-disabled):not(.p-readonly) .p-rating-item:hover .p-rating-icon) {
    color: #FFA41C;
}

/* Optional: Add loading state styles */
.opacity-75 {
    opacity: 0.75;
    cursor: not-allowed;
}

/* Add these styles for the input field */
:deep(.p-inputtext) {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
}

:deep(.p-inputtext:disabled) {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
