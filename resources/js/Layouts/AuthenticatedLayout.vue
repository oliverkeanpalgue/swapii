<script setup>
import { ref } from 'vue';
import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    hideFooter: {
        type: Boolean,
        default: false
    },
    noContainer: {
        type: Boolean,
        default: false
    }
});

const showingNavigationDropdown = ref(false);
const notifications = ref([]);
const isLoading = ref(true);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/get-notifications');
        notifications.value = response.data;
        isLoading.value = false;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
        notifications.value = [];
        isLoading.value = false;
    }
};

const setupPusher = () => {
    const page = usePage();
    if (!page.props.auth?.user) {
        console.error('User not authenticated');
        return null;
    }

    const userId = page.props.auth.user.id;
    const channelName = `notif-channel.${userId}`;

    console.log('Setting up Pusher for channel:', channelName);

    const channel = window.Echo.channel(channelName);

    channel
        .subscribed(() => {
            console.log('Successfully subscribed to channel:', channelName);
        })
        .listen('.new-notif-event', (data) => {
            console.log('Received notification event:', data);
            if (data.notif) {
                notifications.value = [data.notif, ...notifications.value];
            } else {
                console.error('Received notification without notif data:', data);
            }
        })
        .error((error) => {
            console.error('Echo error:', error);
        });

    return channelName;
};

onMounted(async() => {
    await fetchNotifications();
    const channelName = setupPusher();
    if (channelName) {
        console.log('Pusher setup complete for channel:', channelName);
    }

    console.log(notifications.value);
});

onUnmounted(() => {
    if (window.Echo) {
        const page = usePage();
        if (page.props.auth?.user) {
            const channelName = `notif-channel.${page.props.auth.user.id}`;
            window.Echo.leave(channelName);
        }
    }
});
</script>

<template>
    <div class="flex flex-col min-h-screen transition-colors dark:bg-gray-900">
        <div v-if="isLoading" class="flex fixed inset-0 z-50 justify-center items-center bg-gray-50 dark:bg-gray-900">
            <div class="w-12 h-12 rounded-full border-b-2 animate-spin border-primary"></div>
        </div>

        <Navbar :notifications="notifications" class="sticky top-0" />

        <div class="flex-grow bg-gray-50 dark:bg-gray-900">
            <header v-if="$slots.header" class="bg-gray-50 dark:bg-gray-900">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main :class="[
                noContainer ? '' : 'container px-4 mx-auto sm:px-8 lg:px-10',
                'dark:text-gray-100'
            ]">
                <slot />
            </main>
        </div>

        <Footer v-if="!hideFooter" class="mt-auto dark:bg-gray-900 dark:text-gray-100" />
    </div>
</template>
