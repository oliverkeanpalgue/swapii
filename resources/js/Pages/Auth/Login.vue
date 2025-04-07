<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Icon } from '@iconify/vue';
import { onMounted } from 'vue';
import { ref } from 'vue';

const props = defineProps({
    flash: {
        success: String,
        error: String,
    },

    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showMessage = ref(false);

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => {
            // You can redirect or show a success message here
            
        },
        onError: () => {
            // Handle errors, e.g., show an error message
            showMessage.value = true;
            setTimeout(() => {
                showMessage.value = false;
            }, 5000);
        }
    });
};

onMounted(() => {
    if (props.flash.success || props.flash.error) {
        showMessage.value = true;
        setTimeout(() => {
            showMessage.value = false;
        }, 5000);
    }
});
</script>

<template>
    <Head title="Log in" />

    <div class="overflow-hidden h-screen">
        <nav class="fixed top-0 right-0 left-0 z-50 px-4 py-2 h-14 backdrop-blur-sm bg-white/80">
            <div class="mx-auto max-w-7xl">
                <Link href="/" class="flex items-center mt-2 space-x-2 text-xl font-semibold transition text-primary hover:text-primary-700">
                    <span>Swapii</span>
                </Link>
            </div>
        </nav>
        <div class="pt-4 md:pt-0 md:flex md:flex-col justify-center px-4 h-screen md:px-8">
            <div class="mx-auto mt-14 w-full max-w-md">
                <div v-if="showMessage" class="p-3 mb-4 rounded-lg" :class="props.flash.success ? 'bg-green-50' : 'bg-red-50'">
                    <div class="flex justify-center items-center">
                        <Icon v-if="props.flash.success" icon="mdi:check-circle" class="w-5 h-5 text-green-400" />
                        <Icon v-if="props.flash.error" icon="mdi:alert-circle" class="w-5 h-5 text-red-400" />
                        <p class="ml-3 text-sm font-medium" :class="props.flash.success ? 'text-green-800' : 'text-red-800'">{{ props.flash.success || props.flash.error }}</p>
                    </div>
                </div>
                <div class="mb-4 text-center">
                    <div class="inline-block mb-1.5 rounded-full bg-primary-100">
                        <img src="assets/images/Logo1.png" alt="Swapii" class="w-12 h-12">
                    </div>
                    <h2 class="mb-1 text-2xl font-bold text-gray-900">Welcome Back</h2>
                    <p class="text-sm text-gray-600">Login to your account</p>
                </div>

                <div class="p-5 bg-white rounded-md rounded-2xl border border-gray-300 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Email Field -->
                        <div>
                            <InputLabel for="email" value="Email" class="mb-1 text-sm font-medium text-gray-700" />
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full"
                                :class="{ 'border-red-500': form.errors.email }"
                                v-model="form.email"
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-gray-500 transition-all hover:text-primary-600 hover:underline"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="block w-full"
                                :class="{ 'border-red-500': form.errors.password }"
                                v-model="form.password"
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-primary-600 focus:ring-primary-500" />
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <PrimaryButton
                            class="flex justify-center items-center py-2 w-full text-sm font-medium rounded-lg transition-colors"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <Icon v-if="form.processing" icon="mdi:loading" class="mr-2 -ml-1 w-4 h-4 animate-spin" />
                            Sign in
                        </PrimaryButton>
                    </form>

                    <p class="mt-4 text-sm text-center text-gray-600">
                        Don't have an account?
                        <Link
                            href="/register"
                            class="ml-1 font-bold transition-all text-primary hover:text-primary-700 hover:underline"
                        >
                            Sign up
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
