<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import ModeratorLayout from '@/Layouts/ModeratorLayout.vue';

const props = defineProps({
    items: Object,
    items_count: Number,
    category: Object
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const formatDate = (value) => {
    return new Date(value).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusSeverity = (status) => {
    switch (status.toLowerCase()) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'secondary';
        case 'rejected':
            return 'warning';
        default:
            return null;
    }
};
</script>

<template>
    <ModeratorLayout>
        <Head :title="category.category" />
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Category Header -->
                <div class="mb-6 bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">{{ category.category }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ category.description }}</p>
                            </div>
                            <Link href="/moderator/category"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                Back to Categories
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <section v-if="items.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-lg">
                    <div class="flex flex-col items-center justify-center">
                        <p class="text-sm text-gray-500">No items available in this category.</p>
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
                                        placeholder="Search items..."
                                        class="w-full pl-10"
                                    />
                                </div>
                                <div class="flex items-center gap-4">
                                    <p class="text-sm text-gray-600">
                                        Total Items: {{ items_count }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <DataTable 
                            v-model:filters="filters" 
                            :value="items" 
                            removableSort
                            tableStyle="width: 100%" 
                            class="text-sm"
                            paginator 
                            :rows="5"
                            :rowsPerPageOptions="[5, 10, 25, 50]"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                            responsiveLayout="stack"
                            :globalFilterFields="['item_name', 'item_description', 'condition', 'status', 'user.name']"
                        >
                            <Column field="item_name" header="Item Name" sortable>
                                <template #body="slotProps">
                                    <div class="font-medium text-gray-900">{{ slotProps.data.item_name }}</div>
                                </template>
                            </Column>
                            <Column field="item_description" header="Description" sortable>
                                <template #body="slotProps">
                                    <div class="text-gray-600">{{ slotProps.data.item_description }}</div>
                                </template>
                            </Column>
                            <Column field="approval" header="Approval" sortable>
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.approval" :severity="getStatusSeverity(slotProps.data.approval)" 
                                        style="font-size:12px; padding: 2px 8px; text-transform: capitalize">
                                        {{ slotProps.data.approval }}
                                    </Tag>
                                </template>
                            </Column>
                            <Column field="user.name" header="Owner" sortable>
                                <template #body="slotProps">
                                    <div class="text-gray-600">{{ slotProps.data.user.name }}</div>
                                </template>
                            </Column>
                            <Column field="created_at" header="Created At" sortable>
                                <template #body="slotProps">
                                    <div class="text-gray-600">{{ formatDate(slotProps.data.created_at) }}</div>
                                </template>
                            </Column>
                            <Column header="Actions" style="width: 150px" bodyStyle="text-align:center">
                                <template #body="slotProps">
                                    <Link 
                                        :href="'/moderator/item-description/' + slotProps.data.id"
                                        class="flex items-center text-primary hover:text-primary-200 text-sm font-medium gap-2" 
                                    >
                                        View Item
                                        <Icon icon="heroicons:eye" class="h-6 w-6" />
                                    </Link>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </section>
            </div>
        </div>
    </ModeratorLayout>
</template>