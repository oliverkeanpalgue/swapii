<script setup>
import { Link, router } from '@inertiajs/vue3';
import {
    computed,
    defineProps,
    reactive,
    ref,
    watch,
    nextTick
} from 'vue';
import {
    onMounted,
    onUnmounted
} from 'vue';
import {
    Icon
} from '@iconify/vue';
import ChatButton from './ChatButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import { useToast } from "primevue/usetoast";
import { usePrimeVue } from 'primevue/config';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';
import Badge from 'primevue/badge';
import Toast from 'primevue/toast';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Sidebar from 'primevue/sidebar';
import Image from 'primevue/image'; // For image preview

const props = defineProps({
    user: Object,
    chatRoom: Object,

    // messages: JSON
})
const $primevue = usePrimeVue();
const toast = useToast();
const messages = ref(props.chatRoom.messages || [])
const chatContainer = ref(null)
const imageDialog = ref(false)
const totalSize = ref(0);
const totalSizePercent = ref(0);
const uploadedFiles = ref([]);
const selectedFiles = ref([]);
const fileInput = ref(null);

const formatSize = (bytes) => {
    const k = 1024;
    const dm = 3;
    const sizes = $primevue.config.locale.fileSizeTypes || ['B', 'KB', 'MB', 'GB', 'TB'];

    if (bytes === 0) {
        return `0 ${sizes[0]}`;
    }

    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

    return `${formattedSize} ${sizes[i]}`;
};

const src = ref(null);

const isVideo = (filename) => {
    const videoExtensions = ['.mp4', '.mov', '.avi', '.wmv'];
    return videoExtensions.some(ext => filename.toLowerCase().endsWith(ext));
};

const isImage = (filename) => {
    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
    return imageExtensions.some(ext => filename.toLowerCase().endsWith(ext));
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    selectedFiles.value = files.map(file => ({
        file,
        preview: URL.createObjectURL(file)
    }));
    imageDialog.value = true;
};

const removeFile = (index) => {
    URL.revokeObjectURL(selectedFiles.value[index].preview);
    selectedFiles.value.splice(index, 1);
};

const sendFiles = () => {
    if (selectedFiles.value.length === 0) return;

    const formData = new FormData();
    selectedFiles.value.forEach(({ file }) => {
        formData.append('files[]', file);
    });

    router.post(route('chat.send.img', props.chatRoom.id), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Clean up file previews
            selectedFiles.value.forEach(file => {
                URL.revokeObjectURL(file.preview);
            });
            
            // Reset form and close dialog
            selectedFiles.value = [];
            imageDialog.value = false;
            if (fileInput.value) {
                fileInput.value.value = ''; // Reset file input
            }
            
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Files uploaded successfully', 
                life: 3000,
                group: 'top-right'
            });
        },
        onError: (errors) => {
            console.error('Upload error:', errors);
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: errors.error || 'Failed to upload file', 
                life: 3000,
                group: 'top-right'
            });
        }
    });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
    })
}

watch(messages, () => {
    scrollToBottom()
}, { deep: true })

// Message batching
const messageQueue = ref([])
const batchSize = 5
const batchTimeout = 1000 // 1 second

// Throttle message sending
let lastMessageTime = 0
const messageThrottle = 500 // 500ms between messages

const form = useForm({
    message: null,
    images: []
})

const sendMsg = () => {
    if (!form.message.trim()) {
        return;
    }

    // Apply throttling
    const now = Date.now()
    if (now - lastMessageTime < messageThrottle) {
        return
    }
    lastMessageTime = now

    form.post('/send-msg/' + props.user.id, {
        onSuccess: () => {
            form.reset()
            form.images = []; // Clear images after sending
        },
        preserveScroll: true,
    })
}

const sendImages = async () => {
    const formData = new FormData();
    form.images.forEach(file => {
        formData.append('images[]', file);
    });

    try {
        imageDialog.value = false; // Close the dialog
        await axios.post(route('chat.send.img', props.chatRoom.id), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': props.csrf_token,
            }
        });
        toast.add({ severity: 'success', summary: 'Success', detail: 'Images sent successfully', life: 3000 });
        form.images = []; // Clear images after sending
        src.value = null;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 });
    }
};

onMounted(() => {
    scrollToBottom()
    console.log(messages.value);
    // Listen on private channel
    window.Echo.channel('chat.' + props.chatRoom.id)
        .listen('.message.sent', (data) => {
            // Add message to queue
            messageQueue.value.push(data.message)

            // Process queue if it reaches batch size
            if (messageQueue.value.length >= batchSize) {
                processMessageQueue()
            }
        })

    // Set up periodic queue processing
    const queueInterval = setInterval(processMessageQueue, batchTimeout)

    onUnmounted(() => {
        clearInterval(queueInterval)
        if (window.Echo) {
            window.Echo.leave('chat.' + props.chatRoom.id)
        }
    })
})

const processMessageQueue = () => {
    if (messageQueue.value.length === 0) return

    // Add all queued messages to the messages array
    messages.value = [...messages.value, ...messageQueue.value]
    messageQueue.value = []

    // Scroll to bottom after messages are added
    nextTick(() => {
        const container = document.querySelector('.overflow-y-auto')
        if (container) {
            container.scrollTop = container.scrollHeight
        }
    })
}

const showMediaSidebar = ref(false);
const mediaAttachments = computed(() => {
    return messages.value
        .filter(message => message.attachments)
        .map(message => ({
            path: message.attachments,
            type: isVideo(message.attachments) ? 'video' : 'image',
            message: message.message,
            user: message.user
        }));
});

const handleMediaClick = () => {
    showMediaSidebar.value = true;
}

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').slice(0, 2);
}

const formatDate = (date) => {
    const d = new Date(date);
    const hours = d.getHours();
    const minutes = d.getMinutes();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const formattedTime = `${hours % 12 || 12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
    return `${d.toLocaleDateString()} ${formattedTime}`;
};
</script>
<template>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex border-b">
            <div class="flex justify-between items-center px-2 py-2 w-full sm:px-4 sm:py-3">
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="relative">
                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                            <span v-if="!props.user.profile_image" class="text-sm font-medium text-white">
                                {{ props.user.name.charAt(0).toUpperCase() }}
                            </span>
                            <img 
                                v-else 
                                :src="`/storage/${props.user.profile_image}`" 
                                class="h-8 w-8 rounded-full"
                            >
                        </div>
                        <span class="absolute right-0 bottom-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div>
                        <Link :href="'/view-user/' + props.user.id" class="hover:text-primary">
                            <p class="text-lg font-semibold">{{ props.user.name }}</p>
                        </Link>
                    </div>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-4">
                    <Link :href="'/view-user/' + props.user.id" class="hover:text-primary">
                        <ChatButton icon="iconamoon:profile-circle-fill" class="p-2">
                        </ChatButton>
                    </Link>
                    <button @click="handleMediaClick" class="hover:text-primary">
                        <ChatButton icon="pajamas:media" class="p-2">
                        </ChatButton>
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div ref="chatContainer" class="overflow-y-auto flex-1 bg-gray-50">
            <div class="flex flex-col justify-end min-h-full">
                <div v-if="messages.length > 0" class="px-2 py-2 space-y-2 sm:px-4 sm:py-4 sm:space-y-3">
                    <div v-for="message in messages" :key="message.id">
                    
                        <!-- Sent Messages -->
                        <div v-if="message.user_id == $page.props.auth.user.id"
                            class="flex justify-end items-end space-x-2">
                            <div class="flex flex-col max-w-[85%] sm:max-w-[75%] lg:max-w-[65%] space-y-1">
                                <div v-if="message.attachments"
                                    class="flex items-center justify-center p-3.5 rounded-[18px] rounded-tr-[4px] bg-primary-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                    <template v-if="isVideo(message.attachments)">
                                        <video 
                                            :src="`/storage/${message.attachments}`" 
                                            class="w-[300px] h-[200px] object-cover rounded-[18px]"
                                            controls
                                            preload="metadata"
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    </template>
                                    <template v-else-if="isImage(message.attachments)">
                                        <Image 
                                            :src="`/storage/${message.attachments}`" 
                                            :pt="{
                                                image: { class: 'w-[300px] h-[200px] object-cover rounded-[18px]' }
                                            }"
                                            :alt="message.message || 'Image'"
                                            preview
                                        />
                                    </template>
                                </div>
                                <div v-else class="p-3.5 rounded-[18px] rounded-tr-[4px] bg-primary-200 shadow-sm hover:shadow-md transition-shadow">
                                    <p class="text-[15px] text-gray-800 break-words leading-relaxed">{{ message.message }}</p>
                                </div>
                                <span class="self-end mr-1 text-xs text-gray-500">{{ message.created_at }}</span>
                            </div>
                            <div class="flex flex-shrink-0 justify-center items-center ml-2 w-8 h-8 rounded-full bg-primary">
                                <img v-if="$page.props.auth.user.profile_image" :src="`/storage/${$page.props.auth.user.profile_image}`" class="object-cover w-full h-full rounded-full" :alt="$page.props.auth.user.name"/>
                                <span v-else class="text-xs font-medium text-white">
                                    {{ getInitials($page.props.auth.user.name) }}
                                </span>
                            </div>
                        </div>

                        <!-- Received Messages -->
                        <div v-else class="flex items-end space-x-2">
                            <div class="flex flex-shrink-0 justify-center items-center mr-2 w-8 h-8 rounded-full bg-primary">
                                <img v-if="user.profile_image" :src="`/storage/${user.profile_image}`" class="object-cover w-full h-full rounded-full" :alt="user.name"/>
                                <span v-else class="text-xs font-medium text-white">
                                    {{ getInitials(user.name) }}
                                </span>
                            </div>
                            <div class="flex flex-col max-w-[85%] sm:max-w-[75%] lg:max-w-[65%] space-y-1">
                                <div v-if="message.attachments"
                                    class="flex items-center justify-center bg-white rounded-[18px] rounded-tl-[4px] shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                    <template v-if="isVideo(message.attachments)">
                                        <video
                                            :src="`/storage/${message.attachments}`"
                                            class="w-[300px] h-[200px] object-cover rounded-[18px]"
                                            controls
                                            preload="metadata"
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    </template>
                                    <template v-else-if="isImage(message.attachments)">
                                        <Image 
                                            :src="`/storage/${message.attachments}`" 
                                            :pt="{
                                                image: { class: 'w-[300px] h-[200px] object-cover rounded-[18px]' }
                                            }"
                                            :alt="message.message || 'Image'"
                                            preview
                                        />
                                    </template>
                                </div>
                                <div v-else class="p-3.5 bg-white rounded-[18px] rounded-tl-[4px] shadow-sm hover:shadow-md transition-shadow">
                                    <p class="text-[15px] text-gray-800 break-words leading-relaxed">{{ message.message }}</p>
                                </div>
                                <span class="ml-1 text-xs text-gray-500">{{ message.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex justify-center items-center p-4 h-full">
                    <div class="text-center">
                        <p class="text-lg text-gray-500">No messages yet</p>
                        <p class="text-sm text-gray-400">Start a conversation!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="flex-none p-2 bg-white border-t sm:p-4">
            <form @submit.prevent="sendMsg" class="flex items-center space-x-2 sm:space-x-4">
                <button type="button"
                    class="p-2.5 text-gray-500 rounded-full transition-colors hover:bg-gray-100 hover:text-primary"
                    @click="imageDialog = true">
                    <Icon icon="ic:baseline-image" width="1.5rem" height="1.5rem" />
                </button>
                <input v-model="form.message"
                    class="flex-1 px-4 py-2.5 text-sm bg-gray-50 rounded-full border border-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    placeholder="Type your message..."
                    type="text">
                <ChatButton :type="submit"
                    icon="iconamoon:send-fill"
                    class="p-2.5 rounded-full hover:bg-gray-100" />
            </form>
        </div>
    </div>

    <Toast position="top-right" />
    <Dialog
        v-model:visible="imageDialog"
        modal
        header="Send Files"
        :breakpoints="{'960px': '75vw', '640px': '90vw'}"
        :style="{ width: '50vw', maxWidth: '500px', minWidth: '300px' }"
        class="p-dialog-custom dark:bg-gray-800"
    >
        <div class="p-4">
            <div class="flex flex-col gap-2">
                <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileSelect"
                    accept="image/*,video/*"
                    multiple
                    class="hidden"
                />
                <button
                    @click="$refs.fileInput.click()"
                    class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary transition-colors"
                >
                    <span class="text-gray-600 dark:text-gray-300">Click to select files</span>
                </button>
                
                <div v-if="selectedFiles.length > 0" class="grid grid-cols-2 gap-4 mt-4">
                    <div v-for="(file, index) in selectedFiles" :key="index" class="relative">
                        <template v-if="isVideo(file.file.name)">
                            <video 
                                :src="file.preview"
                                class="w-full h-[160px] object-cover rounded-lg"
                                controls
                                preload="metadata"
                            >
                                Your browser does not support the video tag.
                            </video>
                        </template>
                        <template v-else-if="isImage(file.file.name)">
                            <Image 
                                :src="file.preview"
                                :pt="{
                                    image: { class: 'w-full h-[160px] object-cover rounded-lg' }
                                }"
                                :alt="file.file.name"
                                preview
                            />
                        </template>

                        <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/50 to-transparent rounded-b-lg">
                            <div class="flex items-center justify-between text-white">
                                <span class="text-sm truncate">{{ file.file.name }}</span>
                                <button
                                    @click="removeFile(index)"
                                    class="p-1 hover:bg-red-500 rounded-full transition-colors"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2 p-4 bg-gray-50 dark:bg-gray-700">
                <button
                    @click="imageDialog = false"
                    class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="sendFiles"
                    :disabled="selectedFiles.length === 0"
                    class="flex gap-2 items-center px-6 py-2.5 font-medium text-white rounded-lg transition-colors"
                    :class="selectedFiles.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-primary-600'"
                >
                    Send Files
                </button>
            </div>
        </template>
    </Dialog>

    <Sidebar 
        v-model:visible="showMediaSidebar" 
        position="right" 
        :style="{ width: '30rem' }" 
        class="p-4"
        header="Media Files"
    >
        <template #header>
            <div class="flex items-center gap-2 p-3 border-b">
                <span class="text-xl font-semibold">Media Files</span>
                <span class="text-sm text-gray-500">({{ mediaAttachments.length }} items)</span>
            </div>
        </template>

        <div class="grid grid-cols-2 gap-4 p-4">
            <div v-for="(media, index) in mediaAttachments" :key="index" class="relative group">
                <template v-if="media.type === 'video'">
                    <video 
                        :src="`/storage/${media.path}`"
                        class="w-full h-[160px] object-cover rounded-lg"
                        controls
                        preload="metadata"
                    >
                        Your browser does not support the video tag.
                    </video>
                </template>
                <template v-else>
                    <Image 
                        :src="`/storage/${media.path}`"
                        :pt="{
                            image: { class: 'w-full h-[160px] object-cover rounded-lg' }
                        }"
                        preview
                        imageClass="cursor-pointer"
                    />
                </template>

                <!-- Hover overlay with info -->
                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg p-3 flex flex-col justify-end pointer-events-none">
                    <div class="text-white text-sm">
                        <p class="font-medium truncate">{{ media.user?.name }}</p>
                        <p v-if="media.message" class="text-xs opacity-75 truncate mt-1">{{ media.message }}</p>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="mediaAttachments.length === 0">
            <div class="flex flex-col items-center justify-center h-full text-gray-500">
                <i class="pi pi-image text-4xl mb-2"></i>
                <p>No media files yet</p>
            </div>
        </template>
    </Sidebar>
</template>

<style>
/* Custom styling for the sidebar */
:deep(.p-sidebar-header) {
    padding: 0;
}

:deep(.p-sidebar-content) {
    padding: 0;
}

/* Optional: Custom styling for the Image preview */
:deep(.p-image-preview-container) {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep(.p-image-preview-container img) {
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
}

.scroll-smooth {
    scroll-behavior: smooth;
}
</style>
