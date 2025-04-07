<script setup>
    import {
        Icon
    } from '@iconify/vue';
    import {
        onMounted,
        reactive,
        ref,
        defineProps,
        defineEmits
    } from 'vue';
    import DropdownLink from '@/Components/DropdownLink.vue';
    import Modal from '@/Components/Modal.vue';
    import ChatLink from './ChatLink.vue';
    import TextInput from '@/Components/TextInput.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextAreaInput from '@/Components/TextAreaInput.vue';
    import {
        Head,
        useForm,
        Link,
        router,
        usePage
    } from '@inertiajs/vue3';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import axios from 'axios';

    const page = usePage();

    const props = defineProps({
        chatRooms: {
            type: Array,
            default: () => [],
            required: false
        }
    });

    const emit = defineEmits(['message-read']);

    const getInitials = (name) => {
        if (!name) return '';
        return name.split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const markAsRead = (chatRoomId) => {
        axios.post('/messages/mark-as-read', { chat_room_id: chatRoomId })
            .then(() => {
                emit('message-read');
                const room = props.chatRooms.find(r => r.id === chatRoomId);
                if (room) {
                    const targetUserId = room.sender.id === page.props.auth.user.id ? room.receiver.id : room.sender.id;
                    router.visit(`/chat/${targetUserId}`);
                }
            })
            .catch(error => console.error(error));
    };

    const handleChatClick = (chatRoomId) => {
        markAsRead(chatRoomId);
    };

    onMounted(() => {
        window.Echo.channel('my-channel')
            .listen('my-event', (message) => {
                const roomIndex = props.chatRooms.findIndex(room => room.id === message.chat_room_id)
                if (roomIndex !== -1) {
                    props.chatRooms[roomIndex].messages = [{ ...message }]
                }
            });
    })
</script>

<template>
    <div class="flex justify-between items-center px-3">
        <h1 class="text-sm font-semibold">Messages</h1>
    </div>

    <div>
        <div class="py-2">
            <div v-if="!props.chatRooms?.length" class="px-4 py-2 text-center">
                <Icon icon="carbon:chat-off" class="mb-2 w-10 h-10 text-gray-400" />
                <h3 class="font-medium text-gray-900 text-md">No messages</h3>
            </div>
            <div v-else>
                <div v-for="room in props.chatRooms" :key="room.id" 
                     class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                     @click="markAsRead(room.id)">
                    <div class="flex items-center space-x-3">
                        <div class="relative flex-1">
                            <div v-if="room.messages?.some(msg => msg.user_id !== page.props.auth.user.id && !msg.is_read)" 
                                 class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 text-xs text-white bg-red-500 rounded-full">
                                {{ room.messages?.filter(msg => msg.user_id !== page.props.auth.user.id && !msg.is_read).length }}
                            </div>
                            <ChatLink
                                v-if="room.sender.id === page.props.auth.user.id"
                                :user="room.receiver"
                                :message="room.messages?.[0]?.message || ''"
                                :sender="room.sender"
                            />
                            <ChatLink
                                v-else
                                :user="room.sender"
                                :message="room.messages?.[0]?.message || ''"
                                :sender="room.sender"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Message Modal -->
    <Modal :show="addMessageModal" @close="addMessageModal = false" maxWidth="xl">
        <template #default>
            <div class="p-4">
                <form>
                    <div>
                        <InputLabel for="user" value="To: " />
                        <TextInput id="user" type="text" class="block mt-1 w-full" autofocus
                            autocomplete="Title" />
                        <InputError class="mt-2" />
                    </div>

                    <div class="mt-2">
                        <InputLabel for="message" value="Message" />
                        <TextAreaInput id="description" type="text" class="block mt-1 w-full" autofocus
                            autocomplete="Title" />
                        <InputError class="mt-2" />
                    </div>

                    <div class="flex gap-4 justify-end items-center mt-4">
                        <SecondaryButton @click="addMessageModal = false" class="hover:bg-gray-200">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton>
                            Send Message
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </template>
    </Modal>
</template>
