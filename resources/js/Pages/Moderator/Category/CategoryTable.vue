<script setup>
import { onMounted, ref, watch } from 'vue'
import { Icon } from '@iconify/vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    categories: Object,
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Menu actions
const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'Edit Category',
                    icon: 'heroicons:pencil-square',
                    href: '/moderator/edit-category/' + id,
                },
                {
                    label: 'Delete Category',
                    icon: 'heroicons:trash',
                    id: id,
                    command: () => deleteConfirm(id)
                },
            ],
        },
    ];
    menu.value.toggle(event);
};

// Updated delete confirmation
const deleteConfirm = (id) => {
    confirm.require({
        group: 'templating',
        message: 'This action cannot be undone. This will permanently delete the category and remove all associated data.',
        header: 'Delete Category',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Keep Category',
        rejectProps: {
            label: 'Keep Category',
            severity: 'secondary',
            outlined: true,
            class: 'px-4 py-2'
        },
        acceptProps: {
            label: 'Yes, Delete Category',
            severity: 'danger',
            class: 'px-4 py-2'
        },
        accept: () => {
            router.delete(`/moderator/category/${id}`, {
                preserveScroll: true,
                onSuccess: (page) => {
                    if (page.props.flash.error) {
                        toast.add({ 
                            severity: 'error', 
                            summary: 'Error', 
                            detail: page.props.flash.error,
                            life: 3000 
                        });
                    } else {
                        toast.add({ 
                            severity: 'success', 
                            summary: 'Success', 
                            detail: page.props.flash.success,
                            life: 3000 
                        });
                    }
                },
                onError: (errors) => {
                    toast.add({ 
                        severity: 'error', 
                        summary: 'Error', 
                        detail: 'An unexpected error occurred while deleting the category.',
                        life: 3000 
                    });
                }
            });
        },
        reject: () => {
            toast.add({ 
                severity: 'info', 
                summary: 'Action Cancelled', 
                detail: 'No changes were made.',
                life: 3000 
            });
        }
    });
};

watch(
    () => props.items,
    (newItems) => {
        items.value = newItems.data;
    },
    { deep: true }
);
</script>

<template>
    <section v-if="props.categories.data.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-md">
        <div class="flex flex-col items-center justify-center">
            <p class="text-sm text-gray-500">No categories available.</p>
        </div>
    </section>

    <section v-else class="w-full mx-auto antialiased">
        <div class="relative overflow-hidden bg-white border border-gray-200 rounded-md shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between gap-4">
                    <div class="relative flex items-center w-full max-w-[300px]">
                        <span class="absolute left-3 text-gray-400">
                            <Icon icon="ri:search-line" width="18" />
                        </span>
                        <TextInput
                            v-model="filters['global'].value"
                            type="text"
                            placeholder="Search categories..."
                            class="w-full pl-10"
                        />
                    </div>
                </div>
            </div>

            <!-- DataTable component -->
            <DataTable v-model:filters="filters" 
                :value="props.categories.data" 
                removableSort
                tableStyle="width: 100%" 
                class="text-sm"
                paginator 
                :rows="5"
                :rowsPerPageOptions="[5, 10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                responsiveLayout="stack"
                :globalFilterFields="['category', 'description', 'posts_count']">

                <Column field="category" header="Category" sortable></Column>
                <Column field="description" header="Description" sortable></Column>
                <Column field="items_count" header="Items" sortable>
                    <template #body="slotProps">
                        <Link :href="'/moderator/view-category-list/' + slotProps.data.id" class="text-blue-600 hover:text-blue-800">
                            {{ slotProps.data.items_count }} 
                            <span class="text-xs text-gray-500 hover:text-gray-600">
                                (View Items)
                            </span>
                        </Link>
                    </template>
                </Column>
                <Column header="Actions" style="width: 100px" bodyStyle="text-align:center">
                    <template #body="slotProps">
                        <div class="flex justify-start">
                            <Button type="button" 
                                @click="(event) => toggleMenu(event, slotProps.data.id)"
                                class="p-2 hover:bg-gray-100 rounded-full"
                                text>
                                <Icon icon="lucide:more-horizontal" width="20" height="20" class="text-gray-600" />
                            </Button>
                            <Menu ref="menu" :model="actions" :popup="true">
                                <template #item="{ item, props }">
                                    <Link v-if="item.href" 
                                        v-ripple 
                                        class="flex items-center px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors duration-150" 
                                        :class="item.class"
                                        v-bind="props.action" 
                                        :href="item.href">
                                        <Icon :icon="item.icon" class="mr-2.5 h-4 w-4" />
                                        <span>{{ item.label }}</span>
                                    </Link>
                                    <button v-else 
                                        v-ripple 
                                        v-bind="props.action"
                                        :disabled="isLoading"
                                        class="flex items-center w-full px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors duration-150"
                                        :class="item.class">
                                        <Icon :icon="item.icon" class="mr-2.5 h-4 w-4" />
                                        <span>{{ item.label }}</span>
                                        <Icon v-if="isLoading" icon="eos-icons:loading" class="ml-2 h-4 w-4 animate-spin" />
                                    </button>
                                </template>
                            </Menu>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </section>

    <Toast position="top-right">
        <template #message="slotProps">
            <div class="flex items-center gap-2">
                <Icon v-if="slotProps.message.severity === 'success'" 
                    icon="heroicons:check-circle" 
                    class="w-5 h-5 text-green-500" />
                <Icon v-else-if="slotProps.message.severity === 'info'" 
                    icon="heroicons:information-circle" 
                    class="w-5 h-5 text-blue-500" />
                <div>
                    <h4 class="text-sm font-medium text-gray-900">{{ slotProps.message.summary }}</h4>
                    <p class="text-sm text-gray-600">{{ slotProps.message.detail }}</p>
                </div>
            </div>
        </template>
    </Toast>
    <ConfirmDialog group="templating" class="mx-4 md:mx-0">
        <template #message="slotProps">
            <div class="flex flex-col items-center w-full gap-4 pb-4 border-b border-gray-200">
                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center">
                    <Icon icon="heroicons:exclamation-triangle" class="w-8 h-8 text-red-600" />
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ slotProps.message.header }}</h3>
                    <p class="text-sm text-gray-600 max-w-md">{{ slotProps.message.message }}</p>
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>
