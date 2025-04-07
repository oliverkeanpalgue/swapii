<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import vueFilePond from "vue-filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm.js";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import { Icon } from "@iconify/vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { reactive, ref, onMounted, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import Calendar from "primevue/calendar";
import Image from "primevue/image";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    trade: {
        type: Object,
        required: true
    }
});

const toast = useToast();
function formatDateTime(dateTimeString) {
    try {
        // Parse the UTC time and adjust to Manila time (UTC+8)
        const utcDate = new Date(dateTimeString);
        if (isNaN(utcDate.getTime())) {
            throw new Error('Invalid date');
        }
        const year = utcDate.getUTCFullYear();
        const month = utcDate.getUTCMonth();
        const day = utcDate.getUTCDate();
        const hours = utcDate.getUTCHours();
        const minutes = utcDate.getUTCMinutes();
        const seconds = utcDate.getUTCSeconds();

        return new Date(year, month, day, hours, minutes, seconds);
    } catch (error) {
        console.error('Error formatting date:', error);
        return new Date();
    }
}

const form = useForm({
    trade_item: props.trade.name,
    item_id: props.trade.item_id,
    trade_meetup: props.trade.place,
    trade_date_meetup: formatDateTime(props.trade.time),
    trade_description: props.trade.description,
    trade_image: [props.trade.images.image],
});

console.log('Parsed date:', form.trade_date_meetup);

const page = usePage();
const csrf_token = page.props.csrf_token;

// filepond
const FilePond = vueFilePond(FilePondPluginImagePreview);
const pond = ref(null);

function handleFilePondLoad(response) {
    form.trade_image.push(response);
    return response;
}

const handleFileRevert = (uniqueId, load, error) => {
    form.trade_image = form.trade_image.filter((image) => image !== uniqueId);
    router.delete("/revert/" + uniqueId, { preserveScroll: true });
    console.log(uniqueId);
    load();
};

const formattedDate = computed({
    get: () => form.trade_date_meetup,
    set: (value) => {
        form.trade_date_meetup = value;
    }
});

const updateTrade = () => {
    // Check if there are no existing images and no new images being uploaded
    if (!props.trade.images?.length && (!form.trade_image || form.trade_image.length === 0)) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'At least one image is required for the trade request',
            life: 3000,
            icon: 'pi pi-times-circle',
            contentStyleClass: 'custom-toast-content',
            style: {
                fontFamily: 'Inter, sans-serif',
            }
        });
        return;
    }

    form.put(`/edit-request/${props.trade.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Trade request updated successfully',
                life: 3000,
                icon: 'pi pi-check-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
        },
        onError: (errors) => {
            console.error('Update errors:', errors);
        }
    });
};

const deleteImage = (id) => {
    router.delete(`/trade-image/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Image deleted successfully', life: 3000 });
        }
    });
};
</script>

<template>

    <Head title="Edit Trade Request" />
    <AuthenticatedLayout>
        <section class="container mx-auto">
            <div class="flex flex-row justify-between items-center my-4">
                <h2 class="text-2xl font-bold text-gray-900">
                    Edit Trade Request
                </h2>
                <Link href="/trade-requests">
                <SecondaryButton class="px-3 h-8">
                    <Link :href="route('trade.index')" class="flex items-center">
                        <Icon icon="mdi:arrow-left" class="mr-1 w-5 h-5" />
                        Back
                    </Link>
                </SecondaryButton>
                </Link>
            </div>
        </section>

        <section class="container mx-auto bg-white rounded-lg shadow">
            <div class="px-4 w-full lg:py-8">
                <form @submit.prevent="updateTrade">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-4">
                            <!-- Item Name -->
                            <div>
                                <InputLabel for="trade_item" value="Item Name"
                                    class="mb-1 text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                        <Icon icon="mdi:package" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <TextInput id="trade_item" type="text" class="block pl-10 w-full"
                                        v-model="form.trade_item" required />
                                </div>
                                <InputError :message="form.errors.trade_item" class="mt-2" />
                            </div>

                            <!-- Meetup Location -->
                            <div>
                                <InputLabel for="trade_meetup" value="Meetup Location"
                                    class="mb-1 text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                        <Icon icon="mdi:map-marker" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <TextInput id="trade_meetup" type="text" class="block pl-10 w-full"
                                        v-model="form.trade_meetup" required />
                                </div>
                                <InputError :message="form.errors.trade_meetup" class="mt-2" />
                            </div>

                            <!-- Meetup Date -->
                            <div>
                                <InputLabel for="date-time" value="Date and Time of Meetup" class="mb-1 text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <div class="flex absolute inset-y-0 left-0 z-10 items-center pl-3 pointer-events-none">
                                        <Icon icon="mdi:calendar-clock" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <Calendar
                                        v-model="form.trade_date_meetup"
                                        :minDate="new Date()"
                                        showTime
                                        hourFormat="12"
                                        :stepMinute="15"
                                        placeholder="Select Date and Time"
                                        class="w-full calendar-input"
                                        :showButtonBar="true"
                                        dateFormat="MM d, yy at"
                                        :manualInput="false"
                                        :showTime="true"
                                        :timeOnly="false"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_date_meetup" :message="form.errors.trade_date_meetup" class="mt-1.5" />
                            </div>

                            <!-- Description -->
                            <div>
                                <InputLabel for="trade_description" value="Description"
                                    class="mb-1 text-sm font-medium text-gray-700" />
                                <TextAreaInput id="trade_description" v-model="form.trade_description" class="w-full"
                                    rows="4" />
                                <InputError :message="form.errors.trade_description" class="mt-2" />
                            </div>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="space-y-4">
                            <InputLabel value="Trade Images" class="mb-1 text-sm font-medium text-gray-700" />

                            <!-- Existing Images Section -->
                            <div v-if="props.trade.images" class="mb-4">
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-6">
                                    <div v-for="image in props.trade.images" :key="image.id" class="relative group">
                                        <Image
                                            :src="`/storage/${image.image}`"
                                            class="object-cover w-20 h-20 border-2 border-gray-300"
                                            :alt="props.trade.name"
                                            preview
                                        />
                                        <button
                                            @click.prevent="deleteImage(image.id)"
                                            class="hidden absolute -top-2 left-16 p-1.5 text-white bg-red-500 rounded-full shadow-md transition-all duration-200 transform group-hover:block hover:bg-red-600 hover:scale-110 animate"
                                            title="Delete Image"
                                        >
                                            <Icon icon="mdi:delete" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="h-64">
                                <div class="overflow-y-auto h-64">
                                    <file-pond
                                        name="image"
                                        ref="pond"
                                        class="h-64"
                                        allow-multiple="true"
                                        accepted-file-types="image/jpeg, image/png"
                                        :server="{
                                            url: '',
                                            process: {
                                                url: '/upload-temp-images',
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': page.props.csrf_token
                                                },
                                                onload: handleFilePondLoad
                                            },
                                            revert: handleFileRevert
                                        }"
                                        label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>"
                                    />
                                </div>
                                <InputError v-if="form.errors.trade_image" :message="form.errors.trade_image" class="mt-1" />

                            </div>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex gap-3 mt-6">
                        <PrimaryButton type="submit" class="flex justify-center w-1/2" :disabled="form.processing">
                            <Icon v-if="!form.processing" icon="mdi:content-save" class="mr-1 w-5 h-5" />
                            <span v-if="form.processing" class="loader"></span>
                            {{ form.processing ? 'Updating...' : 'Update Trade Request' }}
                        </PrimaryButton>
                        <Link href="/trade-requests" class="w-1/2">
                        <SecondaryButton type="button" class="w-full">
                            Cancel
                        </SecondaryButton>
                        </Link>
                    </div>
                </form>
            </div>
        </section>
        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>
<style scoped>
.filepond--root {
    height: 100% !important;
}

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
</style>
