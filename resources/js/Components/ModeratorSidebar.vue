<script setup>
import NavLink from './NavLink.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import OverlayBadge from 'primevue/overlaybadge';

const props = defineProps({
    user_role: {
        type: String,
    },
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    notifications: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['toggleSidebar']);

const unreadCount = computed(() => {
    return props.notifications.filter(notif => !notif.is_read).length;
});

const getInitials = (name) => {
    const initials = name.match(/\b\w/g) || [];
    return ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
};

</script>
<template>
    <nav class="fixed top-0 right-0 z-50 bg-white border-b border-gray-200 transition-all duration-300" :class="{
        'left-48': isSidebarOpen,
        'left-0 sm:left-16': !isSidebarOpen
    }">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex justify-between items-center">
                <button @click="$emit('toggleSidebar')"
                    class="p-2 rounded-lg transition-all duration-300 text-primary hover:bg-gray-100">
                    <Icon :icon="'material-symbols:menu'" class="w-6 h-6" />
                </button>

                <div class="flex gap-6 justify-end items-center">

                    <Dropdown align="right" width="96" class="hidden sm:block">
                        <template #trigger>
                            <button class="mt-3 nav-icon-button">
                                <template v-if="unreadCount > 0">
                                    <OverlayBadge :value="unreadCount" severity="danger" size="small">
                                        <Icon icon="mingcute:notification-line" class="w-6 h-6" />
                                    </OverlayBadge>
                                </template>
                                <Icon v-else icon="mingcute:notification-line" class="w-6 h-6" />
                            </button>
                        </template>

                        <template #content>
                            <div @click.stop>
                                <div class="flex justify-between p-3">
                                    <h1 class="font-semibold text-md">Notifications</h1>
                                    <span class="text-xs text-gray-500">Mark all as read</span>
                                </div>
                                <DropdownLink v-for="msg in notifications" :key="msg.id"
                                    :href="route('mod.notif.show', msg.id)"
                                    :class="[
                                        'flex items-center',
                                        msg.is_read
                                            ? 'hover:bg-gradient-to-t hover:from-orange-100 hover:to-white'
                                            : 'bg-gray-100 hover:bg-gradient-to-t hover:from-black/10 hover:to-white'
                                    ]">
                                    <div class="px-4 max-w-xs">
                                        <p class="font-bold text-blue-500 text-md">
                                            New Item
                                        </p>
                                        <p class="text-xs text-gray-500 truncate font-regular ...">
                                            {{ msg.message }}
                                        </p>
                                    </div>
                                </DropdownLink>
                                <DropdownLink href="/moderator/notification"
                                    class="flex justify-center items-center mt-2 hover:bg-transparent hover:underline">
                                    <p class="text-gray-600">
                                        See more
                                    </p>
                                </DropdownLink>
                            </div>
                        </template>
                    </Dropdown>


                    <!-- Profile -->

                    <!-- Profile Dropdown -->
                    <div>
                        <Dropdown align="right" width="64">
                            <template #trigger>
                                <button class="flex items-center mr-3 transition hover:opacity-80">
                                    <div class="flex overflow-hidden justify-center items-center w-8 h-8 rounded-full border-2 border-gray-200">
                                        <img
                                            v-if="$page.props.auth.user.profile_picture"
                                            :src="$page.props.auth.user.profile_picture"
                                            :alt="$page.props.auth.user.name"
                                            class="object-cover w-full h-full"
                                        />
                                        <div
                                            v-else
                                            class="flex justify-center items-center w-full h-full bg-primary"
                                        >
                                            <span class="text-sm font-medium text-white">
                                                {{ getInitials($page.props.auth.user.name) }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <div @click.stop>
                                    <div class="flex flex-col justify-center items-center">
                                        <div class="flex overflow-hidden justify-center items-center mt-3 w-10 h-10 rounded-full border-2 border-gray-200">
                                            <img
                                                v-if="$page.props.auth.user.profile_picture"
                                                :src="$page.props.auth.user.profile_picture"
                                                :alt="$page.props.auth.user.name"
                                                class="object-cover w-full h-full"
                                            />
                                            <div
                                                v-else
                                                class="flex justify-center items-center w-full h-full bg-primary"
                                            >
                                                <span class="text-sm font-medium text-white">
                                                    {{ getInitials($page.props.auth.user.name) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="px-4 mt-4 max-w-xs">
                                            <p class="text-sm font-bold">
                                                {{ $page.props.auth.user.name }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 font-semibold">
                                        <DropdownLink class="text-xs" :href="route('mod.profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <hr>
                                        <DropdownLink href="/logout" method="post" as="button" class="text-xs">
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

    </nav>

    <!-- Sidebar -->
    <aside id="logo-sidebar"
        :class="{ 'w-0 sm:w-16': !isSidebarOpen, 'w-48': isSidebarOpen }"
        class="fixed top-0 left-0 z-40 pt-6 h-screen bg-white border-r border-gray-200 transition-all duration-300 sm:translate-x-0">
        <div class="overflow-y-auto pb-4 h-full bg-white ms-4 md:ms-0 md:px-3">
            <div class="flex justify-center">
                <Link href="/moderator" :class="{ 'justify-center': !isSidebarOpen }" class="flex items-center">
                    <img src="/assets/images/Logo1.png" :class="{ 'me-0': !isSidebarOpen, 'me-3': isSidebarOpen }"
                        class="h-10 transition-all duration-300" alt="Swapii" />
                    <span v-show="isSidebarOpen"
                        class="self-center text-xs font-bold whitespace-nowrap transition-all duration-300 sm:text-2xl text-primary">
                        Swapii
                    </span>
                </Link>
            </div>

            <!-- Update the sidebar menu items to use the collapsible functionality -->
            <div class="pt-6">
                <ul class="space-y-4 font-medium">
                    <li>
                        <Link :class="{ 'bg-primary-50 text-primary border-l-2 border-primary': $page.url === '/moderator' }"
                            href="/moderator"
                            class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                            <Icon icon="ic:round-dashboard" width="1.5rem" height="1.5rem" />
                            <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Dashboard</span>
                        </Link>
                    </li>

                    <!-- Content Management Section -->
                    <li>
                        <div :class="{ 'hidden': !isSidebarOpen }" class="px-2 text-xs font-semibold text-gray-500 uppercase">Content Management</div>
                        <ul class="mt-2 space-y-1">
                            <!-- <li>
                                <Link :class="{ 'text-primary border-l-2 border-primary': $page.url === '/moderator/post' }"
                                    href="/moderator/post"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="material-symbols:post" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Posts</span>
                                </Link>
                            </li> -->
                            <li>
                                <Link :class="{ 'text-primary border-l-2 border-primary': $page.url === '/moderator/category' }"
                                    href="/moderator/category"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="material-symbols:category" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Categories</span>
                                </Link>
                            </li>
                            <li>
                                <Link :class="{ 'text-primary border-l-2 border-primary': $page.url === '/moderator/post-reports' }"
                                    href="/moderator/post-reports"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="material-symbols:warning" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Reported Posts</span>
                                </Link>
                            </li>
                        </ul>
                    </li>

                    <!-- Notifications (Mobile Only) -->
                    <li class="block md:hidden">
                        <Link :class="{ 'text-primary border-l-2 border-primary md:hidden': $page.url === '/moderator/notification' }"
                            href="/moderator/notification"
                            class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                            <Icon icon="material-symbols:notifications-active" width="1.5rem" height="1.5rem" />
                            <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Notifications</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

</template>
