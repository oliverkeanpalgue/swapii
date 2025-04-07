<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const resetPass = () => {
    form.post(route('user.new-password'), {
        onFinish: () => console.log('finished'),
    });
};
</script>

<template>
    <Head title="Change Password" />

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
                <div v-if="status" class="p-3 mb-4 bg-green-50 rounded-lg">
                    <div class="flex justify-center items-center">
                        <Icon icon="mdi:check-circle" class="w-5 h-5 text-green-400" />
                        <p class="ml-3 text-sm font-medium text-green-800">{{ status }}</p>
                    </div>
                </div>
                <div class="mb-4 text-center">
                    <div class="inline-block mb-1.5 rounded-full bg-primary-100">
                        <img src="assets/images/Logo1.png" alt="Swapii" class="w-12 h-12">
                    </div>
                    <h2 class="mb-1 text-2xl font-bold text-gray-900">Welcome Back</h2>
                    <p class="text-sm text-gray-600">Change Password to Login</p>
                </div>
                <div class="p-5 bg-white rounded-md rounded-2xl border border-gray-300 shadow-sm">
                    <form @submit.prevent="resetPass" class="">
                        <div class="">
                            <InputLabel for="password" value="New Password" />
                            <TextInput
                                id="password"
                                type="password"
                                class="block mt-1 w-full"
                                v-model="form.password"
                    
                                autocomplete="new-password"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="block mt-1 w-full"
                                v-model="form.password_confirmation"

                                autocomplete="new-password"
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <div class="flex justify-end mt-4">
                            <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Change Password
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
</template>
