<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ModeratorSidebar from '@/Components/ModeratorSidebar.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(localStorage.getItem('moderatorSidebarOpen') !== 'false');
const notifications = ref([]);

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('mod.notif'));
        notifications.value = response.data;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
    localStorage.setItem('moderatorSidebarOpen', isSidebarOpen.value);
};

onMounted(async () => {
    await fetchNotifications();

    const page = usePage();

    window.Echo.channel('mod-post-channel')
        .listen('.mod-post-event', (data) => {
            notifications.value = [data.notif, ...notifications.value];
        });

});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('mod-post-channel');
    }
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50">
            <ModeratorSidebar
                :is-sidebar-open="isSidebarOpen"
                :notifications="notifications"
                @toggle-sidebar="toggleSidebar"
            />
            <main class="transition-all duration-300"
                  :class="{
                      'sm:ml-48': isSidebarOpen,
                      'sm:ml-16': !isSidebarOpen
                  }">
                <div class="p-4 mt-16">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
