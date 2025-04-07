<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { onUpdated } from "vue";
import { Icon } from '@iconify/vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextAreaInput from '@/Components/TextAreaInput.vue';
import { useToast } from 'primevue/usetoast';
import SelectInput from '@/Components/SelectInput.vue';
import Toast from 'primevue/toast';

const toast = useToast();
const props = defineProps({
    flash: Object
});

const form = useForm({
    title: '',
    feedback: '',
});

const showToast = () => {
    if (props.flash.success) {
        toast.add({
            severity: 'success',
            summary: 'Thank You',
            detail: props.flash.success,
            life: 3000,
            icon: 'pi pi-check-circle',
            contentStyleClass: 'custom-toast-content',
            style: {
                fontFamily: 'Inter, sans-serif',
            }
        });
    }
    if (props.flash.error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: props.flash.error,
            life: 3000,
            icon: 'pi pi-times-circle',
            contentStyleClass: 'custom-toast-content',
            style: {
                fontFamily: 'Inter, sans-serif',
            }
        });
    }
}

const submit = () => {
    form.post(route('feedback.store'), 
    {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            
        },
        onError: () => {
        }
    });
};

onUpdated(() => {
    showToast();
});

const titleOptions = [
    { label: 'Customer Service', value: 'Customer Service' },
    { label: 'Website Usability', value: 'Website Usability' },
    { label: 'Website Policies', value: 'Website Policies' },
    { label: 'Technical Issues', value: 'Technical Issues' },
    { label: 'Suggestions for Improvement', value: 'Suggestions for Improvement' },
    { label: 'General Inquiry', value: 'General Inquiry' },
    { label: 'Others', value: 'Others' }
];

</script>

<template>
    <Head title="Feedback - Swapii" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto md:px-4 py-6 md:py-12">
            <div class="text-center max-w-xl mx-auto mb-8">
                <Icon 
                    icon="material-symbols:rate-review-outline" 
                    class="w-14 h-14 text-primary mx-auto mb-6" 
                />
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl mb-3">
                    Help Us Improve Your Experience
                </h1>
                <p class="text-base text-gray-600">
                    Your feedback is valuable to us and helps make Swapii better for everyone.
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 rounded border border-gray-200">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel 
                            for="title" 
                            value="What would you like to give feedback about?" 
                            class="text-sm font-medium text-gray-900"
                        />
                        <select
                            v-model="form.title"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-200 focus:border-primary-200 block w-full p-2.5 mt-1.5"
                        >
                            <option value="" disabled selected>Choose a category</option>
                            <option 
                                v-for="option in titleOptions" 
                                :key="option.value" 
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.title" class="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel 
                            for="description" 
                            value="Your detailed feedback" 
                            class="text-sm font-medium text-gray-900"
                        />
                        <p class="text-sm text-gray-500 mt-1 mb-1.5">
                            Please provide specific details to help us understand your feedback better.
                        </p>
                        <TextAreaInput 
                            v-model="form.feedback" 
                            id="description" 
                            rows="6"
                            class="block w-full rounded-lg border-gray-300 shadow-sm" 
                            placeholder="Share your thoughts, suggestions, or concerns..."
                        />
                        <InputError :message="form.errors.feedback" class="mt-1.5" />
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <PrimaryButton 
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                            :disabled="form.processing"
                            class="px-6 py-2.5 text-sm inline-flex items-center gap-2"
                        >
                            Submit Feedback
                            <Icon 
                                icon="material-symbols:send" 
                                class="w-4 h-4" 
                            />
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>