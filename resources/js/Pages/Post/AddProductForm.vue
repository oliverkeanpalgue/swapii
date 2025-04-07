<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import Multiselect from "vue-multiselect";
import vueFilePond from "vue-filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm.js";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import Card from 'primevue/card';
import { Icon } from "@iconify/vue";
import { Link, useForm, usePage, router, Head } from "@inertiajs/vue3";
import { onMounted, reactive, ref, defineEmits, onUpdated, computed } from "vue";
import axios from "axios";
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";



defineEmits(['testEmit', 'formSubmitted'])


const form = useForm({
    item_name: "",
    preferred_item: "",
    item_category: null,
    item_description: "",
    item_tags: ref([]),
    item_image: [],
    url: null,
});


const data = ref([]);


const addtest = () => {
    // Check if the form is empty


    const formData = {
        ...form,
    };
    data.value.push(formData);
    resetForm();
    resetFields()
    console.log(data);

};

const resetForm = () => {
    form.reset();
    form.item_image = null;
    form.url = null;
};

const toast = useToast();

const props = defineProps({
    flash: Object
});

const postItem = () => {
        form.item_tags = selectedTags.value;
        form.post('/post-management', {
            preserveScroll: true,
            onSuccess: (response) => {
                if (response?.props?.flash?.error) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Warning (Account not verified)',
                        detail: response.props.flash.error,
                        life: 3000,
                        icon: 'pi pi-times-circle',
                        contentStyleClass: 'custom-toast-content',
                        style: {
                            fontFamily: 'Inter, sans-serif',
                        }
                    });
                } else {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Item added successfully',
                        life: 3000,
                        icon: 'pi pi-check-circle'
                    });

                }
                emit('formSubmitted');

                resetFields();
            },
            onError: (errors) => {
                const errorMessage = props.flash?.error || errors?.response?.data?.error || 'Failed to add item';
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: errorMessage,
                    life: 3000,
                    icon: 'pi pi-times-circle',
                    contentStyleClass: 'custom-toast-content',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                    }
                });
                console.log(errors);
            }
        });
};

onMounted(() => {
    axios.get("/categories_list").then((response) => {
        categories.value = response.data;
    });

    axios.get("/tags").then((response) => {
        options.value = response.data;
    });
});

const selectedTags = ref([])
const newTags = ref([]);
const options = ref([]);

function addTag(newTag) {
    const tag = {
        tag: newTag,
        code: newTag.substring(0, 2) + Math.floor(Math.random() * 10000000),
    };
    newTags.value.push(tag);
    selectedTags.value.push(tag); // Add the newly created tag to selectedTags
    router.post("/add-new-tags", tag, {
        preserveScroll: true,
        preserveState: true,
    });
    axios.get("/tags").then((response) => {
        options.value = response.data;
    });
}

function nameWithLang({
    category
}) {
    return `${category}`;
}
const categories = ref([]);


// filepond
const FilePond = vueFilePond(FilePondPluginImagePreview);
const pond = ref(null);

const page = usePage();

function handleFilePondLoad(response) {
    if (typeof response === 'object' && response.message) {
        // Show error message using toast
        toast.add({
            severity: 'error',
            summary: 'Upload Error',
            detail: response.message,
            life: 5000,
            icon: 'pi pi-times-circle',
            contentStyleClass: 'custom-toast-content',
            style: {
                fontFamily: 'Inter, sans-serif',
            }
        });
        return null;
    }
    form.item_image.push(response);
    return response;
}

const handleFileRevert = (uniqueId, load, error) => {
    form.item_image = form.item_image.filter((image) => image !== uniqueId);
    router.delete('/revert/' + uniqueId, { preserveScroll: true });
    console.log(uniqueId);
    load();
}

const revertAllImages = () => {
    router.delete('/revert-all');
    load();
}

const resetFields = () => {
    resetForm();
    selectedTags.value = [];
    form.item_tags = [];

    if (pond.value) {
        pond.value.removeFiles(); // Clear FilePond file previews
    }

    form.item_image = [];  // Ensure the form's image data is also cleared
    revertAllImages();
};


</script>

<template>
    <Head title="Add Product" />
    <Toast class="z-50" />

    <AuthenticatedLayout>
        
        <section class="p-2 antialiased bg-gray-50 min-h-screen">
            <!-- Add back button section -->
            <section class="container mx-auto">
                <div class="flex flex-row justify-end items-center my-4">
                    <Link href="/post-management">
                        <SecondaryButton class="px-4 py-2 hover:bg-gray-100 transition-colors duration-200">
                            <Icon icon="mingcute:back-fill" width="1.2rem" height="1.2rem" class="me-2" />
                            Go Back
                        </SecondaryButton>
                    </Link>
                </div>
            </section>

            <section class="container mx-auto">
                <Card class="w-full lg:py-8 md:px-6">
                    <template #title>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between sm:space-x-4 mb-6">
                            <div class="flex items-center space-x-2 sm:w-1/2">
                                <Icon icon="mdi:plus-circle" class="w-6 h-6 text-primary-600" />
                                <h2 class="text-2xl font-bold text-gray-900">Add a new product</h2>
                            </div>
                            <p class="text-sm font-medium text-gray-600 sm:text-right sm:w-1/2 mt-2 text-center">
                                Please fill in the required information to add a new product.
                            </p>
                        </div>
                    </template>
                    <template #content>
                        <form @submit.prevent="postItem" class="mx-auto space-y-6">
                            <!-- Name and Category Section -->
                            <div class="px-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Item Name -->
                                    <div class="group">
                                        <InputLabel for="name" value="Item Name" class="mb-1.5 text-sm font-medium text-gray-700" />
                                        <div class="relative">
                                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                                <Icon icon="mdi:package" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500" />
                                            </div>
                                            <TextInput
                                                v-model="form.item_name"
                                                id="name"
                                                type="text"
                                                class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm transition-all duration-200"
                                                placeholder="Enter Item Name"
                                            />
                                        </div>
                                        <InputError v-if="form.errors.item_name" :message="form.errors.item_name" class="mt-1.5" />
                                    </div>

                                    <!-- Category -->
                                    <div class="group">
                                        <InputLabel for="category" value="Select Category" class="mb-1.5 text-sm font-medium text-gray-700" />
                                        <div class="relative">
                                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                                <Icon icon="mdi:folder" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500" />
                                            </div>
                                            <select
                                                v-model="form.item_category"
                                                :class="[
                                                    'pl-10 w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary bg-gray-50 text-sm p-2.5 shadow-sm transition-all duration-200',
                                                    form.item_category ? 'text-gray-900' : 'text-gray-500'
                                                ]"
                                            >
                                                <option value="null" disabled selected>Choose category</option>
                                                <option
                                                    v-for="category in categories"
                                                    :key="category.id"
                                                    :value="category.id"
                                                    class="text-gray-900 py-1.5"
                                                >
                                                    {{ category.category }}
                                                </option>
                                            </select>
                                        </div>
                                        <InputError v-if="form.errors.item_category" :message="form.errors.item_category" class="mt-1.5" />
                                    </div>
                                </div>
                            </div>

                            <!-- Other Fields Section -->
                            <div class="px-6 space-y-6">
                                
                                <!-- Tags -->
                                <div class="group">
                                    <InputLabel for="tags" value="Tags" class="mb-1.5 text-sm font-medium text-gray-700" />
                                    <div class="relative">
                                        <div class="flex absolute inset-y-0 left-0 z-10 items-center pl-3 pointer-events-none">
                                            <Icon icon="mdi:tag" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500" />
                                        </div>
                                        <multiselect
                                            v-model="selectedTags"
                                            tag-placeholder="Add this as a new tag"
                                            placeholder="Search or add a tag"
                                            label="tag"
                                            track-by="code"
                                            :options="options"
                                            :multiple="true"
                                            :taggable="true"
                                            @tag="addTag"
                                            class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus-within:ring-primary-500 focus-within:border-primary-500 multiselect-with-icon shadow-sm transition-all duration-200"
                                        />
                                    </div>
                                    <InputError v-if="form.errors.item_tags" :message="form.errors.item_tags" class="mt-1.5" />
                                </div>

                                <!-- Preferred Item -->
                                <div class="group">
                                    <InputLabel for="preferred_item" value="Preferred Item" class="mb-1.5 text-sm font-medium text-gray-700" />
                                    <div class="relative">
                                        <div class="flex absolute left-0 top-3 items-start pl-3 pointer-events-none">
                                            <Icon icon="mdi:swap-horizontal" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500" />
                                        </div>
                                        <TextAreaInput
                                            v-model="form.preferred_item"
                                            id="preferred_item"
                                            class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm transition-all duration-200"
                                            placeholder="Describe the items you would like to receive in exchange"
                                        />
                                    </div>
                                    <InputError v-if="form.errors.preferred_item" :message="form.errors.preferred_item" class="mt-1.5" />
                                </div>

                                <!-- Item Description -->
                                <div class="group">
                                    <InputLabel for="description" value="Item Description" class="mb-1.5 text-sm font-medium text-gray-700" />
                                    <div class="relative">
                                        <div class="flex absolute left-0 top-3 items-start pl-3 pointer-events-none">
                                            <Icon icon="mdi:text" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500" />
                                        </div>
                                        <TextAreaInput
                                            v-model="form.item_description"
                                            id="prod_description"
                                            class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm transition-all duration-200"
                                            placeholder="Describe the item you are trading"
                                        />
                                    </div>
                                    <InputError v-if="form.errors.item_description" :message="form.errors.item_description" class="mt-1.5" />
                                </div>

                                <!-- Item Images -->
                                <div>
                                    <InputLabel for="images" value="Item Images" class="mb-2 text-sm font-medium text-gray-700" />
                                    <div class="rounded-lg border-gray-300">
                                        <FilePond
                                            ref="pond"
                                            name="image"
                                            class="h-64 sm:h-80"
                                            :allow-multiple="true"
                                            :max-files="4"
                                            :max-file-size="'25MB'"
                                            :label-max-file-size-exceeded="'File size should not exceed 25MB'"
                                            accepted-file-types="image/jpeg, image/png"
                                            :credits="false"
                                            :server="{
                                                process: {
                                                    url: '/upload-temp-images',
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': page.props.csrf_token
                                                    },
                                                    onload: handleFilePondLoad,
                                                    onerror: (response) => {
                                                        try {
                                                            // First try to parse the response as JSON
                                                            let error;
                                                            if (typeof response === 'string') {
                                                                error = JSON.parse(response);
                                                            } else if (response instanceof Error) {
                                                                error = { message: response.message };
                                                            } else {
                                                                error = response;
                                                            }

                                                            // Check if we have a message in the response
                                                            const errorMessage = error.message ||
                                                                (error.data && error.data.message) ||
                                                                'Failed to upload image';

                                                            toast.add({
                                                                severity: 'error',
                                                                summary: 'Upload Error',
                                                                detail: errorMessage,
                                                                life: 5000,
                                                                icon: 'pi pi-times-circle',
                                                                contentStyleClass: 'custom-toast-content',
                                                                style: {
                                                                    fontFamily: 'Inter, sans-serif',
                                                                }
                                                            });

                                                            // Log the error for debugging
                                                            console.error('Upload error:', error);
                                                        } catch (e) {
                                                            console.error('Error parsing response:', response);
                                                            toast.add({
                                                                severity: 'error',
                                                                summary: 'Upload Error',
                                                                detail: 'Failed to upload image',
                                                                life: 5000,
                                                                icon: 'pi pi-times-circle',
                                                                contentStyleClass: 'custom-toast-content',
                                                                style: {
                                                                    fontFamily: 'Inter, sans-serif',
                                                                }
                                                            });
                                                        }
                                                    }
                                                },
                                                revert: handleFileRevert
                                            }"
                                            label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>"
                                        />
                                    </div>
                                    <InputError v-if="form.errors.item_image" :message="form.errors.item_image" class="mt-1" />
                                </div>
                            </div>

                            <div class="flex gap-3 mt-8">
                                <PrimaryButton type="submit" class="flex justify-center w-1/2">
                                    <Icon icon="mdi:add-bold" width="1.2rem" height="1.2rem" class="mr-2" />
                                    Submit
                                </PrimaryButton>

                                <SecondaryButton @click="resetFields" class="flex justify-center w-1/2">
                                    <Icon icon="material-symbols:cancel-outline" width="1.2rem" height="1.2rem" class="mr-2" />
                                    Reset
                                </SecondaryButton>
                            </div>
                        </form>
                    </template>
                </Card>
            </section>
        </section>
    </AuthenticatedLayout>
</template>

<style>
.filepond--root {
    height: 100% !important;
    margin-bottom: 0 !important;
    overflow: hidden !important;
}

.filepond--list-scroller {
    display: flex !important;
    justify-content: center !important;
    width: 100% !important;
}

.filepond--list {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 150px)) !important;
    gap: 1rem !important;
    padding: 1rem !important;
    width: 100% !important;
    max-width: 100% !important;
    justify-content: center !important;
}

.filepond--item {
    width: 150px !important;
    height: 150px !important;
}

.filepond--image-preview-wrapper {
    border-radius: 0.5rem !important;
    width: 150px !important;
    height: 150px !important;
}

.filepond--image-preview {
    background: #fff !important;
    width: 150px !important;
    height: 150px !important;
    object-fit: cover !important;
}

.filepond--panel-root {
    background-color: #fff7ed !important;
    border: 2px dashed #fdba74 !important;
    border-radius: 0.75rem !important;
}

.filepond--drop-label {
    color: #ce4e4e !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
}

.filepond--drop-label label {
    cursor: pointer;
}

.filepond--drip {
    background-color: #f97316 !important;
}

.filepond--item-panel {
    background-color: #f97316 !important;
}

/* Progress indicator styles */
.filepond--progress-indicator {
    color: #f97316 !important;
}

.filepond--processing-complete-indicator {
    color: #f97316 !important;
}

.filepond--file-action-button {
    background-color: rgba(255, 255, 255, 0.95) !important;
    color: #c2410c !important;
}

.filepond--file-status {
    color: #ffffff !important;
}

.filepond--file-info {
    color: #ffffff !important;
}

.filepond--file-status-sub {
    color: #ffffff !important;
}

.filepond--panel-root:hover {
    border-color: #f97316 !important;
    transition: all 0.2s ease-in-out;
}

.filepond--drop-label:hover {
    color: #ea580c !important;
    transition: color 0.2s ease-in-out;
}

.multiselect-with-icon .multiselect__tags {
    padding-left: 40px !important;
    position: relative;
}

.multiselect-with-icon .multiselect__tags::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background-image: url('path/to/icon.svg');
    background-size: contain;
    background-repeat: no-repeat;
    pointer-events: none;
    z-index: 1;
}

option:hover{
    background-color: #F5A623 !important;
    color: white !important;
}
</style>
