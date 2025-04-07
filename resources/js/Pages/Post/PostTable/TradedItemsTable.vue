<script setup>
import {
    onMounted,
    ref,
    watch
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
import Dropdown from 'primevue/dropdown';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';

const props = defineProps({
    items: Object, // The paginated items passed from Inertia
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const items = ref(props.items.data); //for data table component
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'View Post',
                    icon: 'icon-park-outline:preview-open',
                    href: '/item-description/' + id,
                },
            ],
        },
    ];
    menu.value.toggle(event);
};

onMounted(() => {
    console.log(props.items.data);
    console.log(items.value);
})

watch(
    () => props.items,
    (newItems) => {
        items.value = newItems.data;
    },
    { deep: true }
);

// Update rows definition to match UserTable
const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const emit = defineEmits(['itemUnlisted']); // Add emit for parent communication

</script>
<template>
    <section v-if="props.items.data.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="flex justify-center items-center px-3 mx-auto max-w-screen-xl sm:px-4">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon
                        icon="solar:box-minimalistic-broken"
                        class="w-24 h-24 text-gray-300"
                    />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Items Posted
                </h3>
                <p class="mb-6 text-gray-500">
                    You have no items available at the moment.
                </p>
            </div>
        </div>
    </section>

    <section v-else>
        <!-- Remove the extra wrapper divs to match UserTable structure -->
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
                            <TextInput
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
            <Column field="updated_at" header="Date Traded" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.updated_at) }}</div>
                </template>
            </Column>
            <Column field="trade_requests_count" header="No. of Requests" class="text-sm" sortable>
                <template #body="{ data }">
                    <Link 
                        :href="`/trade-list/${data.id}`"
                        class="flex items-center gap-2 text-gray-600 hover:text-primary-500 group"
                    >
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">{{ data.trade_requests_count }}</span>
                        </div>
                        <span class="text-xs text-gray-400 underline group-hover:text-primary-200 transition-colors">
                            (View Requests)
                        </span>
                    </Link>
                </template>
            </Column>

            <!-- Update actions column -->
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
</template>

<!-- Remove most of the scoped styles as they're no longer needed -->
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
