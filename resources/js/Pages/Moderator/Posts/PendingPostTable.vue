<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, useForm } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { FilterMatchMode } from '@primevue/core/api';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
const props = defineProps({
    items: Object, // The paginated items passed from Inertia
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const visible = ref(false);
const rejectItemId = ref(null); // New reactive variable for rejected item ID
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const imageSize = ref({});  // Change to an object to store multiple image sizes

const formatFileSize = (imagePath) => {
    if (!imagePath) return '0 KB';
    
    // If we already calculated this image's size, return it
    if (imageSize.value[imagePath]) {
        return imageSize.value[imagePath];
    }
    
    // Get the full URL of the image
    const url = `/storage/${imagePath}`;
    
    // Set initial loading state
    imageSize.value[imagePath] = 'Calculating...';
    
    // Create an Image object to get the actual image
    const img = new Image();
    img.src = url;
    
    img.onload = () => {
        // Get image dimensions
        const width = img.width;
        const height = img.height;
        
        // Estimate file size based on dimensions and assuming JPEG compression
        // This is a rough estimate as actual file size depends on image content and compression
        const estimatedSize = Math.round((width * height * 3) / 1024); // Size in KB
        
        let sizeText;
        if (estimatedSize > 1024) {
            sizeText = `${(estimatedSize / 1024).toFixed(1)} MB`;
        } else {
            sizeText = `${estimatedSize} KB`;
        }
        
        // Add dimensions to size text
        sizeText += ` (${width}x${height})`;
        
        imageSize.value[imagePath] = sizeText;
    };
    
    img.onerror = () => {
        imageSize.value[imagePath] = 'Size N/A';
    };
    
    return imageSize.value[imagePath];
};

const toggleMenu = (event, id) => {
    console.log('Toggle menu clicked for id:', id);
    actions.value = [
        {
            label: 'Actions',
            items: [
             {
                    label: 'View Post',
                    icon: 'icon-park-outline:preview-open',
                    href: '/moderator/item-description/' + id,
                },
                {
                    label: 'Accept Post',
                    icon: 'uil:check',
                    command: () => {
                        acceptDialog(id);
                    }
                },
                {
                    label: 'Reject Post',
                    icon: 'material-symbols:cancel',
                    command: () => {
                        console.log('Reject clicked, setting visible to true');
                        rejectItemId.value = id;
                        visible.value = true;
                    }
                },
            ],
        },
    ];

    menu.value.toggle(event);
};

const acceptDialogVisible = ref(false);
const acceptItemId = ref(null);

const acceptDialog = (id) => {
    acceptItemId.value = id;
    acceptDialogVisible.value = true;
};

const handleAccept = () => {
    router.patch(route('mod.post.accept', acceptItemId.value), {}, {
        onSuccess: () => {
            acceptDialogVisible.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Post accepted successfully', 
                life: 3000 
            });
        },
        onError: () => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Failed to accept post', 
                life: 3000 
            });
        }
    });
};

const rejectForm = useForm({
    reason: ''
});

const rejectPost = () => {
    rejectForm.clearErrors();
    
    rejectForm.patch(route('mod.post.reject', rejectItemId.value), {
        onSuccess: () => {
            visible.value = false;
            rejectForm.reset();
        }
    });
};

const onPage = (event) => {
    const page = event.page + 1; // PrimeVue uses 0-based index, Laravel uses 1-based index
    const perPage = event.rows;
    
    router.get(route(route().current()), {
        page: page,
        per_page: perPage
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

onMounted(() => {
    console.log(props.items);
});
</script>

<template>
    <ConfirmDialog />
    <Toast />

    <!-- Empty state - updated to match ProductTable style -->
    <section v-if="props.items.data.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="max-w-screen-xl px-3 sm:px-4 mx-auto flex items-center justify-center">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon icon="ic:baseline-pending-actions" class="w-16 h-16 md:w-24 md:h-24 text-gray-300" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Pending Posts
                </h3>
                <p class="mb-6 text-gray-500">
                    There are no pending posts available at the moment.
                </p>
            </div>
        </div>
    </section>

    <!-- Table view - remove extra section wrapper -->
    <section v-else>
        <DataTable 
            v-model:filters="filters"
            :value="props.items.data" 
            removableSort 
            tableStyle="min-width: 100%"
            class="overflow-hidden" 
            :globalFilterFields="['item_name', 'user.name', 'category.category']"
            paginator 
            :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions" 
            :totalRecords="props.items.total"
            :lazy="true"
            :first="(props.items.current_page - 1) * rows"
            @page="onPage($event)"
            responsiveLayout="scroll"
        >
            <template #header>
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div class="relative max-w-xs w-96">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon icon="ri:search-line" class="h-4 w-4 text-gray-400" />
                            </div>
                            <TextInput 
                                v-model="filters['global'].value"
                                type="text"
                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search posts" 
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="item_name" header="Post" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <div v-if="data.images[0]" class="relative">
                            <div class="h-8 w-8 rounded overflow-hidden">
                                <img :src="`/storage/${data.images[0].image}`" class="h-full w-full object-cover" 
                                    @load="formatFileSize(data.images[0].image)" />
                            </div>
                            <span class="text-xs text-gray-500 absolute -bottom-4 left-0 whitespace-nowrap">
                                {{ imageSize[data.images[0].image] || 'Calculating...' }}
                            </span>
                        </div>
                        <div v-else class="h-8 w-8 rounded bg-[#F5A623] flex items-center justify-center">
                            <span class="text-white font-medium">{{ data.item_name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="font-medium text-gray-900">{{ data.item_name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="user.name" header="Posted by" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.user.name }}</div>
                </template>
            </Column>

            <Column field="category.category" header="Category" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.category.category }}</div>
                </template>
            </Column>

            <Column field="created_at" header="Date Uploaded" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.created_at) }}</div>
                </template>
            </Column>

            <!-- Actions column - updated styling -->
            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center">
                        <button 
                            type="button" 
                            @click="(event) => toggleMenu(event, slotProps.data.id)"
                            class="text-black text-center hover:text-gray-700" 
                            text
                        >
                            <Icon icon="lucide:more-horizontal" width="1.25rem" height="1.25rem" />
                        </button>
                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                            <template #item="{ item, props }">
                                <Link 
                                    v-if="item.href" 
                                    v-ripple
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    v-bind="props.action" 
                                    :href="item.href"
                                >
                                    <Icon :icon="item.icon" class="mr-2 h-4 w-4" />
                                    <span>{{ item.label }}</span>
                                </Link>
                                <button 
                                    v-else 
                                    v-ripple 
                                    v-bind="props.action"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    <Icon :icon="item.icon" class="mr-2 h-4 w-4" />
                                    <span>{{ item.label }}</span>
                                </button>
                            </template>
                        </Menu>
                    </div>
                </template>
            </Column>
        </DataTable>
    </section>

    <!-- reject dialog -->
    <Dialog 
        v-model:visible="visible" 
        modal 
        header="Reject Post" 
        :style="{ width: '30rem' }"
        :closable="true" 
        :dismissableMask="true"
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col items-center p-4">
            <Icon 
                icon="material-symbols:warning-outline" 
                class="text-red-500 mb-4" 
                width="4rem" 
                height="4rem" 
            />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                Confirm Post Rejection
            </h3>
            <p class="text-sm text-gray-500 text-left mb-6">
                Please provide a reason for rejecting this post. This information will be sent to the user.
            </p>

            <form @submit.prevent="rejectPost" class="w-full">
                <div class="mb-3">
                    <InputLabel for="reason" value="Rejection Reason" />
                    <Textarea
                        v-model="rejectForm.reason"
                        id="reason"
                        rows="3"
                        class="block w-full mt-1"
                        :class="{ 'border-red-500': rejectForm.errors.reason }"
                        placeholder="Enter your reason for rejection here..."
                    />
                    <div v-if="rejectForm.errors.reason" class="text-sm text-red-600 mt-1">
                        {{ rejectForm.errors.reason }}
                    </div>
                </div>

                <div class="flex mt-4 gap-4">
                    <SecondaryButton 
                        @click="() => {
                            visible = false;
                            rejectForm.reset();
                        }" 
                        class="flex-1 justify-center h-10"
                    >
                        <Icon icon="material-symbols:close" width="1.2rem" height="1.2rem" class="me-2" />
                        Cancel
                    </SecondaryButton>

                    <DangerButton type="submit" class="flex-1 justify-center h-10">
                        <Icon icon="material-symbols:check" width="1.2rem" height="1.2rem" class="me-2" />
                        Reject Post
                    </DangerButton>
                </div>
            </form>
        </div>
    </Dialog>

    <!-- accept dialog -->
    <Dialog 
        v-model:visible="acceptDialogVisible" 
        modal 
        header="Accept Post" 
        :style="{ width: '30rem' }"
        :closable="true" 
        :dismissableMask="true"
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col items-center p-4">
            <Icon 
                icon="mdi:help-circle-outline" 
                class="text-primary-500 mb-4" 
                width="4rem" 
                height="4rem" 
            />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                Confirm Post Acceptance
            </h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Are you sure you want to accept this post? This action will make the post visible to all users.
            </p>
            
            <div class="flex gap-4 w-full">
                
                <SecondaryButton 
                    class="flex-1 justify-center"
                    @click="acceptDialogVisible = false"
                >
                    <Icon icon="mdi:close" class="mr-2" />
                    Cancel
                </SecondaryButton>

                <PrimaryButton 
                    class="flex-1 justify-center"
                    @click="handleAccept"
                >
                    <Icon icon="mdi:check" class="mr-2" />
                    Accept Post
                </PrimaryButton>
            </div>
        </div>
    </Dialog>
</template>
