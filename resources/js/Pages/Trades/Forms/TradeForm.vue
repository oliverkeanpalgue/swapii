<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import DatePicker from "primevue/datepicker";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import vueFilePond from "vue-filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm.js";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import { Icon } from "@iconify/vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import Calendar from 'primevue/calendar';



const props = defineProps({
    item: Object,
});
const form = useForm({
    trade_item: "", //name
    item_id: props.item.id, //fkey for item
    trade_meetup: "",
    trade_date_meetup: "",
    trade_description: "",
    trade_image: [],
});

// filepond
const FilePond = vueFilePond(FilePondPluginImagePreview);
const pond = ref(null);

function handleFilePondLoad(response) {
    form.trade_image.push(response);
    return response;

    // console.log(response);
}

const handleFileRevert = (uniqueId, load, error) => {
    form.trade_image = form.trade_image.filter((image) => image !== uniqueId);
    router.delete("/revert/" + uniqueId, { preserveScroll: true });
    console.log(uniqueId);
    load();
};

const revertAllImages = () => {
    router.delete("/revert-all");
    load();
};

const resetFields = () => {
    form.reset();
    if (pond.value) {
        pond.value.removeFiles();
    }
    form.trade_image = [];
    revertAllImages();
    preserveScroll = true;
};

const toast = useToast();

const sendTradeReq = () => {
    console.log('Date being sent:', form.trade_date_meetup);
    
    form.post("/send-request", {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Trade request sent successfully',
                life: 3000,
                icon: 'pi pi-check-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
            resetFields();
        },
        onError: (e) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to send trade request',
                life: 3000,
                icon: 'pi pi-times-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
        }
    });
};

// Add this function for date formatting
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

// Add these to your script setup
const minDate = new Date(); // For preventing past dates
const timeOptions = ref({
    stepMinute: 15, // 15-minute intervals
});

// Function to format the date for display (if needed)
const formatDateTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
};
</script>

<template>
    <Head title="Request Trade" />
    <AuthenticatedLayout>
        <!-- Header Section -->
        <section class="container md:px-4 mx-auto mt-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <Icon icon="material-symbols:swap-horiz" width="1.5rem" height="1.5rem" />
                    <h2 class="text-xl font-semibold text-gray-900">Request Trade</h2>
                </div>
                <Link href="/">
                    <SecondaryButton class="flex items-center gap-2">
                        <Icon icon="solar:arrow-left-linear" width="1.2rem" height="1.2rem" />
                        Back to Home
                    </SecondaryButton>
                </Link>
            </div>

            <!-- Updated Item Details Card -->
            <div class="bg-white rounded-md shadow-sm border overflow-hidden mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Left side - Image -->
                        <div class="md:w-1/5">
                            <div class="aspect-square rounded-md overflow-hidden bg-gray-100">
                                <img v-if="item.images" :src="`/storage/${item.images[0]?.image}`" :alt="item.item_name"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <!-- Right side - Item Details -->
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ item.item_name }}
                                        by <span class="text-sm font-medium text-primary">{{ item.user?.name }}</span>
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Icon icon="solar:calendar-linear" class="w-4 h-4 text-gray-400" />
                                        <p class="text-sm text-gray-500">Posted {{ formatDate(item.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                            <Icon icon="material-symbols:category-outline" class="w-4 h-4 text-gray-400" />
                                            Category
                                        </h4>
                                        <p class="text-sm text-gray-900">{{ item.category.category }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                            <Icon icon="solar:gift-linear" class="w-4 h-4 text-gray-400" />
                                            Preferred Items
                                        </h4>
                                        <p class="text-sm text-gray-900">{{ item.preferred_items }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                            <Icon icon="solar:tag-linear" class="w-4 h-4 text-gray-400" />
                                            Tags
                                        </h4>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span v-for="(tag, index) in (item.tags || []).slice(0, 3)" :key="tag.id"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200">
                                                #{{ tag.tag }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                            <Icon icon="fluent:text-description-20-regular" class="w-4 h-4 text-gray-400" />
                                            Description
                                        </h4>
                                        <p class="text-sm text-gray-900">{{ item.item_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trade Form -->
            <div class="bg-white rounded-md shadow-sm border p-6">
                <form @submit.prevent="sendTradeReq">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Item Name" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Icon icon="mdi:package" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        v-model="form.trade_item"
                                        id="name"
                                        type="text"
                                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                        placeholder="Enter Item Name"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_item" :message="form.errors.trade_item" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="meetup" value="Meetup Place" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Icon icon="mdi:map-marker" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        v-model="form.trade_meetup"
                                        id="trade_meetup"
                                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                        placeholder="Meeting Place (UB Gym, F Building, UB Square)"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_meetup" :message="form.errors.trade_meetup" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="date-time" value="Date and Time of Meetup" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                        <Icon icon="mdi:calendar-clock" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <Calendar
                                        v-model="form.trade_date_meetup"
                                        :minDate="minDate"
                                        showTime
                                        hourFormat="12"
                                        placeholder="Select Date and Time"
                                        class="w-full calendar-input"
                                        :showButtonBar="true"
                                        dateFormat="M d, yy at"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_date_meetup" :message="form.errors.trade_date_meetup" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Item Description" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <Icon icon="mdi:text" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextAreaInput
                                        v-model="form.trade_description"
                                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                        placeholder="Describe the item you are trading"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_description" :message="form.errors.trade_description" class="mt-1.5" />
                            </div>
                        </div>

                        <div class="mb-3 h-64 sm:h-80">
                            <InputLabel for="images" value="Item Images" class="mb-2" />
                            <div class="h-full overflow-y-auto">
                                <file-pond
                                    name="image"
                                    ref="pond"
                                    class="h-full"
                                    allow-multiple="true"
                                    accepted-file-types="image/jpeg, image/png"
                                    credits="false"
                                    :server="{
                                        url: '',
                                        process: {
                                            url: '/upload-temp-images',
                                            method: 'POST',
                                            onload: handleFilePondLoad,
                                        },
                                        revert: handleFileRevert,
                                        headers: {
                                            'X-CSRF-TOKEN': $page.props.csrf_token,
                                        },
                                    }"
                                />
                            </div>
                            <InputError v-if="form.errors.trade_image" :message="form.errors.trade_image" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <PrimaryButton type="submit" class="flex justify-center w-1/2">
                            <Icon icon="fa-solid:exchange-alt" width="1.2rem" height="1.2rem" class="mr-2" />
                            Request Trade
                        </PrimaryButton>

                        <SecondaryButton @click="resetFields" class="flex justify-center w-1/2">
                            <Icon icon="material-symbols:cancel-outline" width="1.2rem" height="1.2rem" class="mr-2" />
                            Reset
                        </SecondaryButton>
                    </div>
                </form>
            </div>
        </section>
        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.calendar-input) {
    width: 100%;
}

:deep(.p-calendar) {
    width: 100%;
}

:deep(.p-inputtext) {
    width: 100% !important;
    padding: 0.5rem 0.75rem 0.5rem 2.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5rem !important;
    border-color: transparent !important;
    background-color: #f9fafb !important;
    color: #111827 !important;
}

:deep(.p-inputtext:focus) {
    outline: 2px solid transparent !important;
    outline-offset: 2px !important;
    border-color: #F5A623 !important;
    box-shadow: 0 0 0 1px #F5A623 !important;
}

:deep(.p-inputtext:hover) {
    border-color: transparent !important;
}

:deep(.p-datepicker) {
    margin-top: 1px !important;
    border-radius: 0.375rem !important;
    border: 1px solid #d1d5db !important;
    background-color: #fff !important;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
}

:deep(.p-datepicker-header) {
    padding: 0.5rem !important;
    border-bottom: 1px solid #e5e7eb !important;
    background-color: #fff !important;
}

:deep(.p-datepicker-calendar) {
    margin: 0.5rem !important;
}

:deep(.p-timepicker) {
    border-top: 1px solid #e5e7eb !important;
    padding: 0.5rem !important;
}

:deep(.p-datepicker-buttonbar) {
    border-top: 1px solid #e5e7eb !important;
    padding: 0.5rem !important;
    background-color: #fff !important;
}

:deep(.p-button) {
    background-color: #F5A623 !important;
    border-color: #F5A623 !important;
}

:deep(.p-highlight) {
    background-color: #F5A623 !important;
    color: white !important;
}

:deep(.p-datepicker-today > span) {
    border-color: #F5A623 !important;
}

:deep(.p-datepicker-today.p-highlight > span) {
    background-color: #F5A623 !important;
    color: white !important;
}

:deep(.p-calendar .p-button-icon-only) {
    display: none !important;
}

:deep(.p-inputtext),
:deep(.p-calendar),
:deep(.p-datepicker) {
    transition: none !important;
}

.filepond--root {
    height: 100% !important;
    margin-bottom: 0 !important;
}

</style>
