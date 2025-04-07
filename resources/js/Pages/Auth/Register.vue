<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Icon } from '@iconify/vue';

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms_accepted: false,
});

const props = defineProps({
    isFirstUser: {
        type: Boolean,
        default: false
    }
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen">
        <nav class="sticky top-0 right-0 left-0 z-50 px-4 py-2 h-14 bg-white">
            <div class="mx-auto max-w-7xl">
                <Link href="/" class="flex items-center mt-2 space-x-2 text-xl font-semibold transition text-primary hover:text-primary-700">
                    <span>Swapii</span>
                </Link>
            </div>
        </nav>

        <div class="flex flex-col justify-center px-4 md:px-8">
            <div class="mx-auto my-4 w-full max-w-md">
                <div class="mb-4 text-center">
                    <div class="inline-block mb-1.5 rounded-full bg-primary-100">
                        <img src="/assets/images/Logo1.png" alt="Swapii" class="w-12 h-12">
                    </div>
                    <h2 class="mb-1 text-2xl font-bold text-gray-900">Create Account</h2>
                    <p class="text-sm text-gray-600">Register your account</p>
                </div>

                <div class="p-5 bg-white rounded-md rounded-2xl border border-gray-300 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Name" class="mb-1 text-sm font-medium text-gray-700" />
                            <div class="relative">
                                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                    <Icon icon="mdi:account" class="w-5 h-5 text-gray-400" />
                                </div>
                                <TextInput id="name" type="text" class="pl-10 w-full rounded-lg focus:ring-primary-500"
                                    :class="{ 'border-red-500': form.errors.name, 'border-gray-300': !form.errors.name }"
                                    v-model="form.name" autofocus autocomplete="name" />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" class="mb-1 text-sm font-medium text-gray-700" />
                            <div class="relative">
                                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                    <Icon icon="mdi:email" class="w-5 h-5 text-gray-400" />
                                </div>
                                <TextInput id="email" type="email" class="pl-10 w-full rounded-lg focus:ring-primary-500"
                                    :class="{ 'border-red-500': form.errors.email, 'border-gray-300': !form.errors.email }"
                                    v-model="form.email" autocomplete="username" />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" class="mb-1 text-sm font-medium text-gray-700" />
                            <div class="relative">
                                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                    <Icon icon="mdi:lock" class="w-5 h-5 text-gray-400" />
                                </div>
                                <TextInput id="password" type="password" class="pl-10 w-full rounded-lg focus:ring-primary-500"
                                    :class="{ 'border-red-500': form.errors.password, 'border-gray-300': !form.errors.password }"
                                    v-model="form.password" autocomplete="new-password" />
                            </div>
                            <div class="mt-1 text-xs text-gray-600">
                                Password must contain at least:
                                <ul class="ml-2 list-disc list-inside">
                                    <li>8 characters</li>
                                    <li>One uppercase letter</li>
                                    <li>One lowercase letter</li>
                                    <li>One number</li>
                                    <li>One special character (@$!%*?&)</li>
                                </ul>
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" class="mb-1 text-sm font-medium text-gray-700" />
                            <div class="relative">
                                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                    <Icon icon="mdi:lock" class="w-5 h-5 text-gray-400" />
                                </div>
                                <TextInput id="password_confirmation" type="password" class="pl-10 w-full rounded-lg focus:ring-primary-500"
                                    :class="{ 'border-red-500': form.errors.password_confirmation, 'border-gray-300': !form.errors.password_confirmation }"
                                    v-model="form.password_confirmation" autocomplete="new-password" />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="terms"
                                        type="checkbox"
                                        v-model="form.terms_accepted"
                                        class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary-500"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.terms_accepted }"
                                        required
                                    />
                                </div>
                                <div class="ml-3">
                                    <label for="terms" class="text-sm text-gray-600">
                                        I accept the
                                        <Link href="/legal-information" class="font-medium text-primary hover:text-primary-700 hover:underline">
                                            Terms and Conditions
                                        </Link>
                                        and confirm with the Privacy Policy and Community Guidelines
                                    </label>
                                    <InputError class="mt-1" :message="form.errors.terms_accepted" />
                                </div>
                            </div>
                        </div>

                        <PrimaryButton
                            class="flex justify-center items-center py-2 w-full text-sm font-medium rounded-lg transition-colors"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <Icon v-if="form.processing" icon="mdi:loading" class="mr-2 -ml-1 w-4 h-4 animate-spin" />
                            Register
                        </PrimaryButton>
                    </form>

                    <p class="mt-4 text-sm text-center text-gray-600">
                        Already have an account?
                        <Link
                            href="/login"
                            class="ml-1 font-bold transition-all text-primary hover:text-primary-700 hover:underline"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
