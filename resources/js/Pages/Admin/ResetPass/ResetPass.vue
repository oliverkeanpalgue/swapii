<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

import Card from 'primevue/card';
import Button from 'primevue/button';
import { onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    resetRequests: Object
})

const resetPass = (id) => {
    router.patch(route('admin.reset-password', id))
}


</script>


<template>
    <Head title="Reset Password" />

    <AdminLayout>
        <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Reset Password Requests</h1>
                <p class="mt-2 text-base text-gray-600">Manage and process reset password requests</p>
            </div>

        <div class="grid grid-cols-2 gap-2 mt-3 md:grid-cols-3 lg:grid-cols-4">
            <template v-for="request in resetRequests" :key="request.id">
                <Card class="w-auto" style="overflow: hidden">
                    <template #header>
                        <img class="w-full h-32"  alt="user header" src="/assets/images/Logo1.png" />
                    </template>

                    <template #title class="text-[10px]">Reset Password Request</template>

                    <template #subtitle>{{request.created_at}}</template>

                    <template #content>
                        <p class="m-0">
                        {{request.name}}
                        {{ request.email }}

                        </p>

                        <span class="text-sm font-bold text-orange-600" v-if="request.is_set == false">pending</span>
                        <span class="text-sm font-bold text-blue-600" v-else>new token sent</span>
                    </template>

                    <template #footer>
                        <div class="flex gap-4 mt-1">
                            <Button label="Reject" severity="secondary" outlined class="w-full" />
                            <Button label="Reset" @click="resetPass(request.id)" class="w-full" />
                        </div>
                    </template>
                </Card>
            </template>
        </div>
    </AdminLayout>
</template>
