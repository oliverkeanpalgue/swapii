<script setup>
import ChatHistory from './components/ChatHistory.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Link,
    router,
    Head
} from '@inertiajs/vue3';
import {
    Icon
} from '@iconify/vue';
import axios from 'axios';
import {
    onMounted,
    onUpdated,
    reactive,
    ref,
    watch
} from 'vue';


const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    chatRoom: {
        type: Object,
        required: true
    },
})

onMounted(() => {

})

</script>

<template>

    <Head title="Chat Message" />

    <AuthenticatedLayout :hide-footer="true" :no-container="true">
        <div class="relative h-[calc(100vh-4rem)] bg-white">
            <div class="h-full">
                <ChatHistory :chatRoom="props.chatRoom" :user="props.user" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
