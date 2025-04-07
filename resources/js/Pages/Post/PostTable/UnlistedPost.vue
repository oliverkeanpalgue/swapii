<script setup>
import {
    onMounted,
    ref
} from 'vue'
import {
    Icon
} from '@iconify/vue';
import {
    Link,
    router
} from '@inertiajs/vue3';
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
import InputText from 'primevue/inputtext';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    items: Object, // The paginated items passed from Inertia
    csrf_token: String  // Add this prop
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const items = ref(props.items.data);
const restoreDialogVisible = ref(false);
const restoreItemId = ref(null);
const deleteDialogVisisble = ref(false);
const deleteItemId = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'Restore',
                    icon: 'material-symbols:upload',
                    command: () => reUploadDialog(id)
                },
                {
                    label: 'Delete',
                    icon: 'mingcute:delete-2-fill',
                    command: () => {
                        deleteDialogVisisble.value = true;
                        deleteItemId.value = id
                    }
                },
            ],
        },
    ];
    menu.value.toggle(event);
};
const reUploadDialog = (id) => {
    restoreItemId.value = id;
    restoreDialogVisible.value = true;
};

const handleRestore = () => {
    router.patch(`/reupload-post/${restoreItemId.value}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            restoreDialogVisible.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Item restored successfully', 
                life: 3000 
            });
        },
        onError: (error) => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: error.message || 'Failed to restore item', 
                life: 3000 
            });
        }
    });
};

const handleDelete = () => {
    router.delete(`/delete-post/${deleteItemId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogVisisble.value = false;
            toast.add({ 
                severity: 'success', 
                summary: 'Success', 
                detail: 'Item deleted successfully', 
                life: 3000 
            });
        },
        onError: (error) => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: error.message || 'Failed to delete item', 
                life: 3000 
            });
        }
    });
};


onMounted(() => {
    console.log(props.items.data);
    console.log(items.value);
})

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

</script>
<template>


    <section v-if="props.items.data.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl sm:px-4">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon icon="solar:box-minimalistic-broken" class="w-24 h-24 text-gray-300" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Unlisted Items
                </h3>
                <p class="mb-6 text-gray-500">
                    You have no unlisted items at the moment.
                </p>
            </div>
        </div>
    </section>

    <section v-else>
        <DataTable
            v-model:filters="filters"
            :value="props.items.data"
            removableSort
            tableStyle="min-width: 100%"
            class="overflow-hidden"
            :globalFilterFields="['item_name', 'item_description', 'category.category']"
            paginator
            :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions"
            responsiveLayout="scroll">
            <template #header>
                <div class="flex gap-4 justify-between items-center mb-4">
                    <div class="relative w-96 max-w-xs">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                            </div>
                            <InputText
                                v-model="filters['global'].value"
                                type="text"
                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search items"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="item_name" header="Item Name" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <div v-if="data.images[0]?.image" class="overflow-hidden w-8 h-8 rounded-full">
                            <img :src="`/storage/${data.images[0].image}`" :alt="data.item_name" class="object-cover w-full h-full">
                        </div>
                        <div class="font-medium text-gray-900">{{ data.item_name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="category.category" header="Category" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.category.category }}</div>
                </template>
            </Column>

            <Column field="created_at" header="Date Posted" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.created_at) }}</div>
                </template>
            </Column>

            <Column field="preferred_items" header="Preferred Items" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.preferred_items }}</div>
                </template>
            </Column>

            <Column field="item_description" header="Description" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600 truncate max-w-xs" :title="data.item_description">
                        {{ data.item_description }}
                    </div>
                </template>
            </Column>

            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center">
                        <button type="button" @click="(event) => toggleMenu(event, slotProps.data.id)"
                            class="text-center text-black hover:text-gray-700" text>
                            <Icon icon="lucide:more-horizontal" width="1.25rem" height="1.25rem" />
                        </button>
                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                            <template #item="{ item, props }">
                                <Link v-if="item.href" v-ripple
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    v-bind="props.action" :href="item.href">
                                    <Icon :icon="item.icon" class="mr-2 w-4 h-4" />
                                    <span>{{ item.label }}</span>
                                </Link>
                                <button v-else v-ripple v-bind="props.action"
                                    class="flex items-center px-4 py-2 w-full text-sm text-red-500 hover:bg-gray-50">
                                    <Icon :icon="item.icon" class="mr-2 w-4 h-4" />
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

    <Dialog 
        v-model:visible="deleteDialogVisisble" 
        modal 
        header="Delete Post" 
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
                Delete Post
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Are you sure you want to delete this Post?
            </p>

            <div class="flex gap-4 w-full">
                <SecondaryButton 
                    class="flex-1 justify-center" 
                    @click="() => deleteDialogVisisble = false"
                >
                    <Icon icon="mdi:close" class="mr-2" />
                    Cancel
                </SecondaryButton>

                <PrimaryButton 
                    class="flex gap-2 justify-center bg-red-500 hover:bg-red-600 focus:bg-red-600"
                    @click="handleDelete"
                >
                    <Icon icon="mdi:check" />
                    <span class="text-xs">Delete Post</span>
                </PrimaryButton>
            </div>
        </div>
    </Dialog>

    <Dialog 
        v-model:visible="restoreDialogVisible" 
        modal 
        header="Restore Item" 
        :style="{ width: '30rem' }"
        :closable="true" 
        :dismissableMask="true" 
        class="mx-4 md:mx-0"
    >
        <div class="flex flex-col items-center p-4">
            <Icon 
                icon="material-symbols:upload" 
                class="text-primary-500 mb-4" 
                width="4rem" 
                height="4rem" 
            />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                Confirm Item Restoration
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Are you sure you want to restore this item? This will make it visible to other users again.
            </p>

            <div class="flex gap-4 w-full">
                <SecondaryButton 
                    class="flex-1 justify-center" 
                    @click="() => restoreDialogVisible = false"
                >
                    <Icon icon="mdi:close" class="mr-2" />
                    Cancel
                </SecondaryButton>

                <PrimaryButton 
                    class="flex gap-2 justify-center bg-primary-600 hover:bg-primary-500 focus:bg-primary-500"
                    @click="handleRestore"
                >
                    <Icon icon="mdi:check" />
                    <span class="text-xs">Restore Item</span>
                </PrimaryButton>
            </div>
        </div>
    </Dialog>
</template>
<style scoped>
:deep(.p-datatable) {
    border-radius: 0.5rem;
    overflow: hidden;
}

:deep(.p-dropdown) {
    min-width: 150px;
}

:deep(.p-tag) {
    font-size: 0.75rem;
}
</style>
