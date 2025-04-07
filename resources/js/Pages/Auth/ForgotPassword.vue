<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";
import Toast from "primevue/toast";

const toast = useToast();

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            form.reset('email');
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'We sent a request to the admin, please check your email for your temporary password once processed!',
                life: 5000
            });
        },
    });
};
</script>

<template>
    <Head title="Forgot Password" />
    <Toast />

    <div class="h-screen overflow-hidden">
        <nav class="fixed top-0 left-0 right-0 h-14 bg-white/80 backdrop-blur-sm z-50 py-2 px-4">
            <div class="max-w-7xl mx-auto">
                <Link href="/" class="flex items-center space-x-2 text-xl text-primary font-semibold hover:text-primary-700 transition mt-2">
                    <span>Swapii</span>
                </Link>
            </div>
        </nav>

        <div class="h-screen flex flex-col justify-center px-4 md:px-8">
            <div class="w-full max-w-md mx-auto mt-14">
                <div v-if="status" class="mb-4 rounded-lg bg-green-50 p-3">
                    <div class="flex items-center justify-center">
                        <Icon icon="mdi:check-circle" class="h-5 w-5 text-green-400" />
                        <p class="ml-3 text-sm font-medium text-green-800">{{ status }}</p>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <div class="inline-block rounded-full bg-primary-100 mb-1.5">
                        <img src="assets/images/Logo1.png" alt="Swapii" class="w-12 h-12">
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Reset Password</h2>
                    <p class="text-sm text-gray-600">Enter your email address and we'll send you a reset link</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-300 p-5 rounded-md">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="email" value="Email" class="text-sm font-medium text-gray-700 mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Icon icon="mdi:email" class="h-5 w-5 text-gray-400" />
                                </div>
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                    v-model="form.email"
                                    autofocus
                                    autocomplete="username"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <PrimaryButton 
                            class="w-full flex justify-center items-center py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin -ml-1 mr-2 h-4 w-4" />
                            Send Reset Link
                        </PrimaryButton>
                    </form>

                    <p class="mt-4 text-center text-sm text-gray-600">
                        Remember your password?
                        <Link 
                            href="/login" 
                            class="font-bold text-primary hover:text-primary-700 hover:underline transition-all ml-1"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
