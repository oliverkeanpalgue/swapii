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
import { onMounted, reactive, ref, defineEmits } from "vue";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Image from "primevue/image";
import Button from "primevue/button";
import Checkbox from 'primevue/checkbox';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";


defineEmits(['testEmit'])

const props = defineProps({
    item: Object
})

const formatDateTime = (dateTime) => {
    const date = new Date(dateTime);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12;
    
    return `${month} ${day}, ${year}, ${hours}:${minutes} ${ampm}`;
};

const form = useForm({
    
    item_name: props.item.item_name,
    preferred_item: props.item.preferred_items,
    item_category: props.item.category_id,
    item_description: props.item.item_description,
    item_tags: ref([props.item.tags]),
    item_image: [],
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

const postItem = () => {
    form.item_tags = selectedTags.value.map(tag => tag.id)
    
    // If the item was rejected, use the resubmit endpoint
    const endpoint = props.item.approval === 'rejected' 
        ? `/post-management/${props.item.id}/resubmit`
        : `/post-management/${props.item.id}`;
        
    form.put(endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: props.item.approval === 'rejected' 
                    ? 'Item resubmitted for approval'
                    : 'Item updated successfully',
                life: 3000,
                icon: 'pi pi-check-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
            // emits("testEmit")
        },
        onError: (e) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to update item',
                life: 3000,
                icon: 'pi pi-times-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
            console.log(e);
        }
    });
};
const selectedTags = ref(props.item.tags || [])
const newTags = ref([]);
const options = ref([]);

function addTag(newTag) {
    const tag = {
        tag: newTag,
        code: newTag.substring(0, 2) + Math.floor(Math.random() * 10000000),
    };
    newTags.value.push(tag);
    router.post("/add-new-tags", tag, {
        preserveScroll: true,
        preserveState: true,

    });
    axios.get("/tags").then((response) => {
        options.value = response.data;
    });

    value.value.push(tag);
}

function nameWithLang({
    category
}) {
    return `${category}`;
}
const categories = ref([]);
const deleteImage = (id) =>
{
    router.delete('/edit-image-delete/'+id);
}

onMounted(() => {
    console.log(selectedTags)
    axios.get("/categories_list").then((response) => {
        categories.value = response.data;
    });

    axios.get("/tags").then((response) => {
        options.value = response.data;
    });

});

// filepond
const FilePond = vueFilePond(FilePondPluginImagePreview);
const pond = ref(null);

function handleFilePondLoad(response) {
    form.item_image.push(response);
    return response;

    // console.log(response);
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
        pond.value.removeFiles(); 
    }

    form.item_image = []; 
    revertAllImages();
};
</script>
<template>
    <Head title="Edit Product" />
    <AuthenticatedLayout>
        <section class="antialiased p-2">
            <section class="container mx-auto ">
                <div class="flex flex-row items-center justify-end my-4">
                    <Link href="/post-management">
                        <SecondaryButton class="h-8 px-3">
                            <Icon icon="mingcute:back-fill" width="1.2rem" height="1.2rem" class="me-2" />
                            Go Back
                        </SecondaryButton>
                    </Link>
                </div>
            </section>

            <section class="container mx-auto">
                <Card class="w-full lg:py-8 px-2">
                    <template #title>
                        <h2 class="mb-4 text-2xl font-bold text-gray-900">Edit Product</h2>
                    </template>
                    <template #content>
                        <form @submit.prevent="postItem">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-4">
                                    <!-- Item Name -->
                                    <div>
                                        <InputLabel for="name" value="Item Name" class="text-sm font-medium text-gray-700 mb-1" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Icon icon="mdi:package" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <TextInput v-model="form.item_name" id="name" type="text"
                                                class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                                placeholder="Enter Item Name" />
                                        </div>
                                        <InputError v-if="form.errors.item_name" :message="form.errors.item_name" class="mt-1.5" />
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <InputLabel for="category" value="Select Category" class="text-sm font-medium text-gray-700 mb-1" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Icon icon="mdi:folder" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <select v-model="form.item_category"
                                                class="pl-10 w-full rounded-md border border-gray-300 focus:ring-primary-200 focus:border-primary-200 bg-gray-50 text-sm p-2.5">
                                                <option value="" selected>Choose category</option>
                                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                                    {{ category.category }}</option>
                                            </select>
                                        </div>
                                        <InputError v-if="form.errors.item_category" :message="form.errors.item_category" class="mt-1.5" />
                                    </div>

                                    <!-- Tags -->
                                    <div>
                                        <InputLabel for="tags" value="Tags" class="text-sm font-medium text-gray-700 mb-1" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                <Icon icon="mdi:tag" class="h-5 w-5 text-gray-400" />
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
                                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-primary-200 focus:border-primary-200 multiselect-with-icon"
                                            />
                                        </div>
                                        <InputError v-if="form.errors.item_tags" :message="form.errors.item_tags" class="mt-1.5" />
                                    </div>

                                    <!-- Preferred Item -->
                                    <div>
                                        <InputLabel for="preferred_item" value="Preferred Item" class="text-sm font-medium text-gray-700 mb-1" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Icon icon="mdi:swap-horizontal" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <TextInput
                                                v-model="form.preferred_item"
                                                id="preferred_item"
                                                type="text"
                                                class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                                placeholder="Enter Preferred Item for Trading"
                                            />
                                        </div>
                                        <InputError v-if="form.errors.preferred_item" :message="form.errors.preferred_item" class="mt-1.5" />
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <InputLabel for="description" value="Item Description" class="text-sm font-medium text-gray-700 mb-1" />
                                        <div class="relative">
                                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                                <Icon icon="mdi:text" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <TextAreaInput
                                                v-model="form.item_description"
                                                id="prod_description"
                                                class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                                placeholder="Describe the item you are trading"
                                            />
                                        </div>
                                        <InputError v-if="form.errors.item_description" :message="form.errors.item_description" class="mt-1.5" />
                                    </div>
                                </div>

                                <!-- Image Upload Section -->
                                <div class="mb-4 h-64 sm:h-80">
                                    <InputLabel for="description" value="Item Images" class="mb-2" />
                                    
                                    <!-- Existing Images Section -->
                                    <div v-if="props.item.images && props.item.images.length > 0" class="mb-2">
                                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
                                            <div v-for="image in props.item.images" :key="image.id" class="relative group">
                                                <Image 
                                                    :src="'../'+image.image" 
                                                    :alt="props.item.item_name"
                                                    imageClass="rounded-md object-cover h-28 w-32"
                                                    preview
                                                />
                                                <button 
                                                    @click.prevent="deleteImage(image.id)"
                                                    class="absolute -top-2 left-14 bg-red-500 hidden group-hover:block hover:bg-red-600 text-white rounded-full p-1.5 
                                                           shadow-md transform transition-all duration-200 hover:scale-110 animate"
                                                    title="Delete Image"
                                                >
                                                    <Icon icon="mdi:delete" class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FilePond for New Images -->
                                    <div class="h-36 md:h-full overflow-y-auto">
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
                                    <InputError v-if="form.errors.item_image" :message="form.errors.item_image" class="mt-1" />
                                </div>
                            </div>

                            <!-- Form Buttons -->
                            <div class="flex gap-3 mt-10">
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
        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<style>
/* Copy the styles from AddProductForm.vue */
.filepond--root {
    height: 100% !important;
    margin-bottom: 0 !important;
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
