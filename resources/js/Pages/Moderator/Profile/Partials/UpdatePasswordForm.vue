<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
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
    <Toast />
    <div class="space-y-8">
        <div class="flex justify-center w-full">
            <div class="max-w-xl space-y-6">
                <div>
                    <InputLabel for="current_password" value="Current Password" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Icon icon="heroicons:key" class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="pl-10 w-full"
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError :message="form.errors.current_password" class="mt-1" />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <InputLabel for="password" value="New Password" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon icon="heroicons:lock-closed" class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="pl-10 w-full"
                                autocomplete="new-password"
                            />
                        </div>
                        <InputError :message="form.errors.password" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon icon="heroicons:check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="pl-10 w-full"
                                autocomplete="new-password"
                            />
                        </div>
                        <InputError :message="form.errors.password_confirmation" class="mt-1" />
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Action Buttons -->
        <div class="flex justify-end gap-1 md:gap-4 pt-4 border-t">
            <SecondaryButton
                type="button"
                class="btn-secondary"
                @click="form.reset()"
            >
                Cancel
            </SecondaryButton>
            <PrimaryButton
                class="btn-primary"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="updatePassword"
            >
                <Icon icon="heroicons:shield-check" class="md:mr-2" />
                Update Password
            </PrimaryButton>
        </div>
    </div>
</template>
