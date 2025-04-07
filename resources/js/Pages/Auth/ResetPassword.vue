<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

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
                <div class="text-center mb-4">
                    <div class="inline-block rounded-full bg-primary-100 mb-1.5">
                        <Icon icon="mdi:lock-reset" class="w-12 h-12 text-primary-600" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Reset Your Password</h2>
                    <p class="text-sm text-gray-600">Please enter your new password below</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-300 p-5 rounded-md">
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Email Field -->
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
                                    disabled
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- New Password Field -->
                        <div>
                            <InputLabel for="password" value="New Password" class="text-sm font-medium text-gray-700 mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Icon icon="mdi:lock" class="h-5 w-5 text-gray-400" />
                                </div>
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                    v-model="form.password"
                                    autocomplete="new-password"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700 mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Icon icon="mdi:lock" class="h-5 w-5 text-gray-400" />
                                </div>
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                                    v-model="form.password_confirmation"
                                    autocomplete="new-password"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                        </div>

                        <PrimaryButton 
                            class="w-full flex justify-center items-center py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin -ml-1 mr-2 h-4 w-4" />
                            Reset Password
                        </PrimaryButton>
                    </form>

                    <p class="mt-4 text-center text-sm text-gray-600">
                        <Link 
                            href="/login" 
                            class="flex items-center justify-center font-bold text-gray-500 hover:text-primary-700 hover:underline transition-all"
                        >
                            <Icon icon="mdi:arrow-left" class="mr-1 h-4 w-4 text-primary" />
                            Back to Login
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
