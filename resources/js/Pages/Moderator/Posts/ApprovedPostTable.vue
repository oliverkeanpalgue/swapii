<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    items: Object,
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'View Post',
                    icon: 'icon-park-outline:preview-open',
                    href: '/moderator/item-description/' + id,
                },
            ],
        },
    ];

    menu.value.toggle(event);
};

const deleteConfirm = (id) => {
    confirm.require({
        group: 'templating',
        message: 'Do you want to delete this post?',
        header: 'Delete Post',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            toast.add({ severity: 'success', summary: 'Confirmed', detail: 'Post deleted', life: 3000 });
            confirm.close();
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'Delete Cancelled', life: 3000 });
            confirm.close();
        }
    });
};

const acceptDialog = (id) => {
    confirm.require({
        group: 'templating',
        message: 'Archive this post?',
        header: 'Archive Post',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Archive',
            severity: 'primary'
        },
        accept: () => {
            router.visit('/post-management');
            toast.add({ severity: 'success', summary: 'Success', detail: 'Post archived', life: 3000 });
            confirm.close();
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'Archive Cancelled', life: 3000 });
            confirm.close();
        }
    });
};

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

onMounted(() => {
    console.log(props.items);
});

// Update the empty state message to match style
const emptyMessage = "No approved posts available as of the moment.";
</script>

<template>
     <!-- Empty state - updated to match ProductTable style -->
     <section v-if="props.items.data.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="max-w-screen-xl px-3 sm:px-4 mx-auto flex items-center justify-center">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon icon="heroicons:check-circle" class="w-16 h-16 md:w-24 md:h-24 text-gray-300" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Approved Posts
                </h3>
                <p class="mb-6 text-gray-500">
                    There are no approved posts available at the moment.
                </p>
            </div>
        </div>
    </section>

    <!-- Table view - remove extra section wrapper to match UserTable -->
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
                                placeholder="Search by item name, posted by, or category" 
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="item_name" header="Post" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <div v-if="data.images[0]" class="h-8 w-8 rounded overflow-hidden">
                            <img :src="`/storage/${data.images[0].image}`" class="h-full w-full object-cover" />
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

            <Column field="updated_at" header="Date Approved" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.updated_at) }}</div>
                </template>
            </Column>

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

    <Toast />
    <ConfirmDialog group="templating" class="mx-4 md:mx-0">
        <template #message="slotProps">
            <div class="flex flex-col items-center w-full gap-4 pb-4 border-b border-gray-200">
                <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center">
                    <Icon icon="heroicons:document-text" class="w-10 h-10 text-[#F5A623]" />
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ slotProps.message.header }}</h3>
                    <p class="text-sm text-gray-600">{{ slotProps.message.message }}</p>
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>

