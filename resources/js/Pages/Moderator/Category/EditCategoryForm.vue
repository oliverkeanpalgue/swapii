<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import FileUpload from 'primevue/fileupload';
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    category: props.category.category,
    description: props.category.description,
    category_image: null,
    _method: 'PUT',
});

const imagePreview = ref(null);

const toast = useToast();

// Initialize image preview based on the source
onMounted(() => {
    if (props.category.category_image) {
        if (props.category.category_image.startsWith('category-images')) {
            imagePreview.value = `/storage/${props.category.category_image}`;
        } else {
            imagePreview.value = `/assets/category-images/${props.category.category_image}`;
        }
    }
});

const onFileSelect = (event) => {
    const file = event.files[0];
    form.category_image = file;
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.category_image = null;
    imagePreview.value = null;
};

const submit = () => {
    form.post(`/moderator/category/${props.category.id}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Category updated successfully',
                life: 3000,
                icon: 'pi pi-check-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
        },
        onError: (e) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to update category',
                life: 3000,
                icon: 'pi pi-times-circle',
                contentStyleClass: 'custom-toast-content',
                style: {
                    fontFamily: 'Inter, sans-serif',
                }
            });
            console.log('error:', e);
        }
    });
};

const resetForm = () => {
    form.reset();
    imagePreview.value = null;
};
</script>

<template>
    <Head title="Category Management" />
    <Toast position="top-right" />

    <ModeratorLayout>
        <div class="w-auto md:w-3/4 mx-auto">
            <!-- Back Button -->
            <div class="mb-4 flex justify-end">
                <Link 
                    href='/moderator/category'
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                >
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Go Back
                    </span>
                </Link>
            </div>

            <div class="relative mb-6 overflow-hidden bg-white border shadow-md sm:rounded-md">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Edit Category</h2>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Category Name -->
                        <div>
                            <InputLabel for="category" value="Category Name" />
                            <TextInput
                                id="category"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.category"
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.category" />
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 bg-gray-50 focus:border-primary focus:ring-primary rounded-md shadow-sm text-sm"
                                rows="4"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- Category Image -->
                        <div>
                            <InputLabel for="category_image" value="Category Image" />
                            <div class="mt-2 space-y-4">
                                <!-- Image Preview -->
                                <div v-if="imagePreview" class="relative w-full border-2 border-dashed border-gray-300 rounded-lg p-4">
                                    <div class="flex items-center justify-center">
                                        <div class="relative w-64 h-64">
                                            <img 
                                                :src="imagePreview"
                                                class="rounded-md border border-gray-300 p-2 shadow-sm object-contain w-full h-full"
                                                alt="Category preview"
                                            />
                                            <button 
                                                @click.prevent="removeImage"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="!imagePreview" class="flex flex-col items-center">
                                    <FileUpload
                                        mode="basic"
                                        :auto="false"
                                        accept="image/*"
                                        :maxFileSize="1000000"
                                        @select="onFileSelect"
                                        chooseLabel="Choose Image"
                                        class="w-full"
                                    />
                                </div>
                                <div v-else class="flex justify-center">
                                    <button
                                        @click.prevent="removeImage"
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Remove Image
                                    </button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.category_image" />
                            </div>
                        </div>

                        <!-- Submit and Reset Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-4">
                            <SecondaryButton
                                type="button"
                                @click="resetForm"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Reset Form
                            </SecondaryButton>
                            <PrimaryButton 
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                            >
                                Update Category
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ModeratorLayout>
</template>

<style lang="postcss" scoped>
textarea {
    resize: vertical;
    min-height: 100px;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-family: inherit;
}

:deep(.p-fileupload) {
    @apply w-full;
}

:deep(.p-fileupload-choose) {
    @apply w-full bg-white border border-gray-300 text-gray-700 hover:bg-primary hover:text-white hover:border-primary !important;
    transition: all 0.2s ease;
}

:deep(.p-fileupload-choose:not(.p-disabled):hover) {
    @apply bg-primary border-primary text-white;
}

:deep(.p-fileupload .p-button) {
    @apply bg-white text-gray-700 border border-gray-300 hover:bg-primary hover:text-white hover:border-primary;
    transition: all 0.2s ease;
}
</style>