<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

</script>

<template>
    <Head title="Verify Email" />

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
                        <p class="ml-3 text-sm font-medium text-green-800">
                            {{ status }}
                        </p>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <div class="inline-block rounded-full bg-primary-100 mb-1.5">
                        <Icon icon="mdi:email-check" class="w-12 h-12 text-primary-600" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Verify Your Email</h2>
                    <p class="text-sm text-gray-600">Please verify your email address to continue</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-300 p-5">
                    <p class="text-sm text-gray-600 mb-4">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking
                        on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </p>

                    <!-- Actions -->
                    <form @submit.prevent="submit" class="space-y-4">
                        <PrimaryButton 
                            class="w-full flex justify-center items-center py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin -ml-1 mr-2 h-4 w-4" />
                            Resend Verification Email
                        </PrimaryButton>

                        <div class="flex justify-center">
                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button"
                                class="inline-flex items-center font-bold text-gray-500 hover:text-primary-700 hover:underline transition-all"
                            >
                                <Icon icon="mdi:logout" class="mr-1 h-4 w-4 text-primary" />
                                Log Out
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>