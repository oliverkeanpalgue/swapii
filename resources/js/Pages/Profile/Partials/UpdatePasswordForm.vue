<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const toast = useToast();
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Password updated successfully',
                life: 3000
            });
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="max-w-xl">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Update Password</h2>

            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-sm font-medium text-gray-700 mb-1" />
                <p class="text-xs text-gray-500 mb-2">Enter your current password to verify your identity.</p>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Icon icon="mdi:lock" class="h-5 w-5 text-gray-400" />
                    </div>
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                        autocomplete="current-password"
                    />
                </div>
                <InputError :message="form.errors.current_password" class="mt-1.5" />
            </div>

            <div>
                <InputLabel for="password" value="New Password" class="text-sm font-medium text-gray-700 mb-1" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Icon icon="mdi:lock-plus" class="h-5 w-5 text-gray-400" />
                    </div>
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                        autocomplete="new-password"
                    />
                </div>
                <InputError :message="form.errors.password" class="mt-1.5" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700 mb-1" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Icon icon="mdi:lock-check" class="h-5 w-5 text-gray-400" />
                    </div>
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="pl-10 w-full rounded-lg border-gray-300 focus:ring-primary-500"
                        autocomplete="new-password"
                    />
                </div>
                <InputError :message="form.errors.password_confirmation" class="mt-1.5" />
            </div>

            <div class="flex justify-end gap-4">
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    @click="form.reset()"
                >
                    Cancel
                </button>
                <button
                    class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-medium text-sm text-white shadow-sm hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="updatePassword"
                >
                    <Icon icon="mdi:lock" class="mr-2" />
                    Update Password
                </button>
            </div>
        </form>
    </section>

    <Toast />
</template>
