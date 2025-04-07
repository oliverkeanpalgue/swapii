<script setup>
import NavLink from './NavLink.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import ChatMenu from '@/Pages/Chat/components/ChatMenu.vue';
import { ref, watch, computed, onMounted } from 'vue';
import OverlayBadge from 'primevue/overlaybadge';
import ToggleSwitch from 'primevue/toggleswitch';
import { useDark } from '@/Presets/dark';
import Drawer from 'primevue/drawer';
import axios from 'axios';
import ChatLink from '@/Pages/Chat/components/ChatLink.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    notifications: {
        type: Array,
    },
    chatNotifications: {
        type: Array,
        default: () => []
    }
})

const notifs = ref(props.notifications || []);
const chatRooms = ref([]);
const unreadChatCount = ref(0);

const unreadNotifCount = computed(() => {
    return notifs.value?.filter(n => !n.is_read)?.length || 0;
});

const fetchChatRooms = () => {
    axios.get('/friends')
        .then(response => {
            chatRooms.value = response.data;
            updateUnreadCount();
        })
        .catch(error => console.error(error));
};

const updateUnreadCount = () => {
    unreadChatCount.value = chatRooms.value.reduce((total, room) => {
        const unreadMessages = room.messages?.filter(msg =>
            msg.user_id !== page.props.auth.user.id && !msg.is_read
        )?.length || 0;
        return total + unreadMessages;
    }, 0);
};

const markAsRead = (chatRoomId) => {
    axios.post('/messages/mark-as-read', { chat_room_id: chatRoomId })
        .then(() => {
            const room = chatRooms.value.find(r => r.id === chatRoomId);
            if (room) {
                room.messages = room.messages.map(msg => ({ ...msg, is_read: true }));
                updateUnreadCount();
            }
        })
        .catch(error => console.error(error));
};

onMounted(() => {
    fetchChatRooms();

    window.Echo.channel('my-channel')
        .listen('newMessageEvent', (e) => {
            const { message } = e;
            const roomIndex = chatRooms.value.findIndex(room => room.id === message.chat_room_id);

            if (roomIndex !== -1) {
                chatRooms.value[roomIndex].messages.unshift(message);
                if (message.user_id !== page.props.auth.user.id) {
                    updateUnreadCount();
                }
            }
        });
});

// Watch for changes in props.notifications and update notifs accordingly
watch(() => props.notifications, (newNotifs) => {
    notifs.value = newNotifs;
}, { immediate: true });

const { isDark, toggleDark } = useDark();

const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};
const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <nav class="block sticky top-0 z-50 bg-white border-b border-gray-300 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <Link href="/" class="flex-shrink-0">
                    <span class="text-xl font-bold transition md:text-2xl text-primary hover:text-primary-600">Swapii</span>
                </Link>

                <div class="hidden md:flex md:items-center md:space-x-8">
                    <ul class="flex space-x-6" v-if="$page.props.auth.user">
                        <li>
                            <NavLink href="/dashboard" class="flex gap-2 items-center">
                                <Icon icon="heroicons:home" class="w-5 h-5" />
                                <span>Home</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/smart-suggestions" class="flex gap-2 items-center">
                                <Icon icon="heroicons:light-bulb" class="w-5 h-5" />
                                <span>Smart Suggestion</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/trade" class="flex gap-2 items-center">
                                <Icon icon="carbon:ibm-data-product-exchange" class="w-5 h-5"  />
                                <span>Trade Requests</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/post-management" class="flex gap-2 items-center">
                                <Icon icon="solar:box-minimalistic-outline" class="w-5 h-5" />
                                <span>Posts</span>
                            </NavLink>
                        </li>
                    </ul>
                </div>

                <div class="hidden md:flex md:items-center md:space-x-6">
                    <template v-if="!$page.props.auth.user">
                        <NavLink href="/login" class="nav-button">Login</NavLink>
                        <NavLink href="/register" class="nav-button-primary">Register</NavLink>
                    </template>

                    <template v-else>
                        <!-- Chat Dropdown -->
                        <Dropdown align="right" width="96">
                            <template #trigger>
                                <button class="mt-2 nav-icon-button">
                                    <template v-if="unreadChatCount > 0">
                                        <OverlayBadge :value="unreadChatCount" severity="danger">
                                            <Icon icon="material-symbols:chat-outline" class="w-6 h-6" />
                                        </OverlayBadge>
                                    </template>
                                    <Icon v-else icon="material-symbols:chat-outline" class="w-6 h-6" />
                                </button>
                            </template>
                            <template #content>
                                <ChatMenu :chat-rooms="chatRooms" @message-read="updateUnreadCount" />
                            </template>
                        </Dropdown>

                        <Dropdown align="right" width="96">
                            <template #trigger>

                                <button class="mt-2 nav-icon-button">
                                    <template v-if="unreadNotifCount > 0">
                                        <OverlayBadge
                                            :value="unreadNotifCount"
                                            severity="danger"
                                            size="small"
                                            class="transition-all duration-300"
                                        >
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

                                    <DropdownLink v-for="notif in notifs" :key="notif.id"
                                        :href="route('notif.show', notif.id)"
                                        :class="notif.is_read ? '' : 'bg-gray-100 hover:bg-gray-200'" class="flex items-center">
                                        <div class="px-4 max-w-xs">

                                            <p :class="{
                                                'font-bold text-blue-500 text-md': notif.type == 'trade_req',
                                                'font-bold text-teal-500 text-md': notif.type == 'trade_success',
                                                'font-bold text-red-500 text-md': notif.type == 'trade_reject',
                                                'font-bold text-orange-500 text-md': notif.type == 'trade_cancel',
                                                'font-bold text-green-500 text-md': notif.type == 'trade_confirm',
                                                'font-bold text-purple-500 text-md': notif.type == 'item_unlist',
                                            }">
                                                {{
                                                    {
                                                        trade_req: 'Requested',
                                                        trade_success: 'Traded',
                                                        trade_reject: 'Rejected',
                                                        trade_cancel: 'Cancelled',
                                                        trade_confirm: 'Accepted',
                                                        item_unlist: 'Unlisted',
                                                    }[notif.type]
                                                }}
                                            </p>
                                            <small class="text-xs font-semibold text-gray-500">{{ notif.created_at
                                                }}</small>
                                            <p class="text-xs text-gray-500 truncate font-regular ...">
                                                {{ notif.data }}
                                            </p>
                                        </div>
                                    </DropdownLink>
                                    <DropdownLink href="/notification"
                                        class="flex justify-center items-center mt-2 hover:bg-transparent hover:underline">
                                        <p class="text-gray-600">
                                            See more
                                        </p>
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>

                        <Dropdown align="right" width="64">
                            <template #trigger>
                                <button class="flex items-center transition hover:opacity-80">
                                    <div class="flex overflow-hidden justify-center items-center w-8 h-8 rounded-full bg-primary">
                                        <template v-if="$page.props.auth.user.profile_image">
                                            <img
                                                :src="`/storage/${$page.props.auth.user.profile_image}`"
                                                :alt="$page.props.auth.user.name"
                                                class="object-cover w-full h-full"
                                            />
                                        </template>
                                        <template v-else>
                                            <span class="text-sm text-white">{{ getInitials($page.props.auth.user.name) }}</span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                            <template #content>
                                <div @click.stop>
                                    <div class="flex flex-col justify-center items-center">
                                        <div
                                            class="flex overflow-hidden justify-center items-center mt-4 w-10 h-10 rounded-full border-2 border-gray-200 bg-primary">
                                            <template v-if="$page.props.auth.user.profile_image">
                                                <img
                                                    :src="`/storage/${$page.props.auth.user.profile_image}`"
                                                    :alt="$page.props.auth.user.name"
                                                    class="object-cover w-full h-full"
                                                />
                                            </template>
                                            <template v-else>
                                                <span class="text-sm text-white">{{ getInitials($page.props.auth.user.name) }}</span>
                                            </template>
                                        </div>
                                        <div class="px-4 mt-4 max-w-xs">
                                            <p class="text-sm font-bold">
                                                {{ $page.props.auth.user.name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-4 font-semibold">
                                        <DropdownLink :href="route('profile.edit')" class="flex gap-2 items-center text-xs">
                                            <Icon icon="heroicons:user-circle" class="w-4 h-4" />
                                            Profile
                                        </DropdownLink>
                                        <hr>
                                        <DropdownLink href="/acquired-items" class="flex gap-2 items-center text-xs">
                                            <Icon icon="mage:box-3d-check" class="w-4 h-4" />
                                            Acquired Items
                                        </DropdownLink>
                                        <hr>
                                        <button
                                            @click="toggleDark"
                                            class="flex hidden gap-2 items-center px-4 py-2 w-full text-xs text-left text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            <Icon
                                                :icon="isDark ? 'ph:sun-bold' : 'ph:moon-bold'"
                                                class="w-4 h-4"
                                            />
                                            {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                                        </button>
                                        <hr>
                                        <DropdownLink href="/feedback" class="flex gap-2 items-center text-xs">
                                            <Icon icon="heroicons:chat-bubble-left-right" class="w-4 h-4" />
                                            Submit a Feedback
                                        </DropdownLink>
                                        <hr>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="flex gap-2 items-center w-full text-xs">
                                            <Icon icon="heroicons:arrow-right-on-rectangle" class="w-4 h-4" />
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </div>


                <div class="flex md:hidden">
                    <template v-if="!$page.props.auth.user">
                        <NavLink href="/login" class="nav-button">Login |</NavLink>
                        <NavLink href="/register" class="nav-button-primary">Register</NavLink>
                    </template>
                    <!-- chat menu for mobile -->
                    <Dropdown v-if="$page.props.auth.user" align="right" width="64" class="md:hidden">
                            <template #trigger>
                                <span class="rounded-md">
                                    <button type="button"
                                        class="items-center px-3 py-2 text-sm font-medium leading-4 bg-white rounded-md border border-transparent transition duration-150 ease-in-out text-primary hover:text-primary-200 focus:outline-none">
                                        <Icon icon="jam:message" width="1.5rem" height="1.5rem" />
                                    </button>
                                </span>
                            </template>
                            <template #content>
                                <div @click.stop>
                                    <ChatMenu />
                                </div>
                            </template>
                    </Dropdown>

                    <button v-if="$page.props.auth.user" @click="toggleMobileMenu"
                        class="inline-flex justify-center items-center p-2 rounded-md transition text-primary hover:text-primary-600 hover:bg-gray-100">
                        <Icon :icon="mobileMenuOpen ? 'heroicons:x-mark' : 'heroicons:bars-3'" class="w-6 h-6" />
                    </button>

                </div>
            </div>
        </div>

        <!-- Mobile Sidebar dark-800 = #1F2937-->
        <Drawer v-model:visible="mobileMenuOpen" :modal="true" :showCloseIcon="false" position="left" class="w-screen h-screen" style="background: #F3F4F6;">
            <div class="flex flex-col h-full bg-gray-100 dark:bg-gray-800">
                <div class="flex justify-start items-center p-2 space-x-4">
                    <!-- Header -->
                     <img src="/assets/images/Logo1.png" class="object-contain w-10 h-10" alt="Swapii"/>
                    <span class="text-xl font-bold text-primary">Swapii</span>
                </div>


                <ul class="flex flex-col gap-4 px-2 font-medium">
                    <!-- Menu Items -->
                    <template v-if="$page.props.auth.user">
                        <li>
                            <NavLink href="/" class="flex gap-2 items-center py-2">
                                <Icon icon="heroicons:home" class="w-5 h-5" />
                                <span>Home</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/trade" class="flex gap-2 items-center py-2">
                                <Icon icon="carbon:ibm-data-product-exchange" class="w-5 h-5" />
                                <span>Trade Requests</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/post-management" class="flex gap-2 items-center py-2">
                                <Icon icon="solar:box-minimalistic-outline" class="w-5 h-5" />
                                <span>Posts</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/notification" class="flex gap-2 items-center py-2">
                                <Icon icon="heroicons:bell" class="w-5 h-5" />
                                <span>Notifications</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/profile" class="flex gap-2 items-center py-2">
                                <Icon icon="heroicons:user-circle" class="w-5 h-5" />
                                <span>Profile</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/acquired-items" class="flex gap-2 items-center py-2">
                                <Icon icon="mage:box-3d-check" class="w-5 h-5" />
                                <span>Acquired Items</span>
                            </NavLink>
                        </li>
                        <li>
                            <NavLink href="/feedback" class="flex gap-2 items-center py-2">
                                <Icon icon="material-symbols:rate-review-outline" class="w-5 h-5" />
                                <span>Submit Feedback</span>
                            </NavLink>
                        </li>
                        <li>
                            <Link :href="route('logout')" method="post" class="flex gap-2 items-center py-2 w-full text-left text-red-600">
                                <Icon icon="heroicons:arrow-right-on-rectangle" class="w-5 h-5" />
                                <span>Logout</span>
                            </Link>
                        </li>
                    </template>
                </ul>
            </div>
        </Drawer>



    </nav>

</template>
