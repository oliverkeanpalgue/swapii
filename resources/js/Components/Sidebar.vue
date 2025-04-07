<script setup>
import NavLink from './NavLink.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const unreadCount = computed(() => {
    return props.notifications.filter(notif => !notif.is_read).length;
});

const emit = defineEmits(['toggleSidebar']);
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
                                </div>
                                <DropdownLink class="flex items-center">
                                    <div class="px-4 max-w-xs">
                                        <p class="font-bold text-md">
                                            Offer
                                        </p>
                                        <p class="text-xs text-gray-500 truncate font-regular ...">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur
                                            neque error laudantium libero sunt quasi consequatur. Perferendis
                                            ab, iusto laudantium iure fugit nam ad distinctio quisquam ipsam ea
                                            error sequi!
                                        </p>
                                    </div>
                                </DropdownLink>
                                <DropdownLink href="/admin/notification"
                                    class="flex justify-center items-center mt-2 hover:bg-transparent hover:underline">
                                    <p class="text-gray-600">
                                        See more
                                    </p>
                                </DropdownLink>
                            </div>
                        </template>
                    </Dropdown>


                    <!-- Profile -->
                    <Dropdown align="right" width="64">
                        <template #trigger>
                            <button class="flex items-center mr-3 transition hover:opacity-80">
                                <div v-if="$page.props.auth.user.profile_picture" class="overflow-hidden w-8 h-8 rounded-full">
                                    <img :src="$page.props.auth.user.profile_picture"
                                         class="object-cover w-full h-full"
                                         :alt="$page.props.auth.user.name" />
                                </div>
                                <div v-else class="flex justify-center items-center w-8 h-8 rounded-full bg-primary">
                                    <span class="text-sm font-medium text-white">
                                        {{ getInitials($page.props.auth.user.name) }}
                                    </span>
                                </div>
                            </button>
                        </template>

                        <template #content>
                            <div @click.stop>
                                <div class="flex flex-col justify-center items-center">
                                    <div v-if="$page.props.auth.user.profile_picture"
                                         class="overflow-hidden mt-3 w-16 h-16 rounded-full border-2 border-gray-200">
                                        <img :src="$page.props.auth.user.profile_picture"
                                             class="object-cover w-full h-full"
                                             :alt="$page.props.auth.user.name" />
                                    </div>
                                    <div v-else class="flex justify-center items-center mt-3 w-16 h-16 rounded-full border-2 border-gray-200 bg-primary">
                                        <span class="text-lg font-medium text-white">
                                            {{ getInitials($page.props.auth.user.name) }}
                                        </span>
                                    </div>
                                    <div class="px-4 mt-4 max-w-xs">
                                        <p class="text-sm font-bold text-center">
                                            {{ $page.props.auth.user.name }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 font-semibold">
                                    <DropdownLink class="text-xs" :href="route('admin.profile.edit')">
                                        Profile
                                    </DropdownLink>
                                    <hr>

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
    </nav>

    <aside id="logo-sidebar" :class="{ 'w-0 sm:w-16': !isSidebarOpen, 'w-48 z-50': isSidebarOpen }"
        class="fixed top-0 left-0 pt-6 h-screen bg-white border-r border-gray-200 transition-all duration-300"
        aria-label="Sidebar">
        <div class="overflow-y-auto pb-4 h-full bg-white ms-4 md:ms-0 md:px-3">
            <div class="flex justify-center">
                <Link href="/admin" :class="{ 'justify-center': !isSidebarOpen }" class="flex items-center">
                <img src="/assets/images/Logo1.png" :class="{ 'me-0': !isSidebarOpen, 'me-3': isSidebarOpen }"
                    class="h-10 transition-all duration-300" alt="Swapii" />
                <span v-show="isSidebarOpen"
                    class="self-center text-xs font-bold whitespace-nowrap transition-all duration-300 sm:text-2xl text-primary">
                    Swapii
                </span>
                </Link>
            </div>
            <div class="pt-6">
                <ul class="space-y-4 font-medium">
                    <!-- Dashboard -->
                    <li>
                        <Link
                            :class="{ 'bg-primary-50 text-primary border-l-2 border-primary': $page.url === '/admin' }"
                            href="/admin"
                            class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                            <Icon icon="ic:round-dashboard" width="1.5rem" height="1.5rem" />
                            <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Dashboard</span>
                        </Link>
                    </li>

                    <!-- User Management Section -->
                    <li>
                        <div :class="{ 'hidden': !isSidebarOpen }" class="px-2 text-xs font-semibold text-gray-500 uppercase">User Management</div>
                        <ul class="mt-2 space-y-1">
                            <li>
                                <Link :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/user-management' }"
                                    href="/admin/user-management"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:users" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Users</span>
                                </Link>
                            </li>
                            <!-- <li>
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/user-verification' }"
                                    href="/admin/user-verification"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:account-check" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Verify Users</span>
                                </Link>
                            </li> -->
                            <li>
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/reports-management' }"
                                    href="/admin/reports-management"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:warning" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Reported Users</span>
                                </Link>
                            </li>
                        </ul>
                    </li>

                    <!-- Monitoring Section -->
                    <li>
                        <div :class="{ 'hidden': !isSidebarOpen }" class="px-2 text-xs font-semibold text-gray-500 uppercase">Monitoring</div>
                        <ul class="mt-2 space-y-1">
                            <li>
                                <Link :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/audit-logs' }"
                                    href="/admin/audit-logs"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="icon-park-solid:audit" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Audit Logs</span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/transaction-history' }"
                                    href="/admin/transaction-history"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:exchange" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Transaction History</span>
                                </Link>
                            </li>
                        </ul>
                    </li>

                    <!-- System Section -->
                    <li>
                        <div :class="{ 'hidden': !isSidebarOpen }" class="px-2 text-xs font-semibold text-gray-500 uppercase">System</div>
                        <ul class="mt-2 space-y-1">
                            <li>
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/suggestion-management' }"
                                    href="/admin/suggestion-management"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:feedback" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Suggestions</span>
                                </Link>
                            </li>
                            <li class="block md:hidden">
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary md:hidden': $page.url === '/admin/notification' }"
                                    href="/admin/notification"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:bell" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Notifications</span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :class="{ 'text-primary border-l-2 border-primary': $page.url === '/admin/reset-password' }"
                                    href="/admin/reset-password-page"
                                    class="flex items-center p-2 text-sm text-gray-900 rounded hover:bg-gray-100 group">
                                    <Icon icon="mdi:lock-reset" width="1.5rem" height="1.5rem" />
                                    <span :class="{ 'hidden': !isSidebarOpen }" class="ms-3">Reset Password</span>
                                </Link>
                            </li>

                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
</template>

<style scoped>
nav {
    position: fixed;
    top: 0;
    width: auto;
    z-index: 50;
}
</style>
