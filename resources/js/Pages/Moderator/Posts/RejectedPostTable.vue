<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import Menu from 'primevue/menu';
import Dialog from 'primevue/dialog';

const props = defineProps({
    items: Object,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

const menu = ref();
const actions = ref([]);

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

const showReasonDialog = ref(false);
const selectedReason = ref('');

const showFullReason = (reason) => {
    selectedReason.value = reason;
    showReasonDialog.value = true;
};

const truncateText = (text) => {
    if (text.length > 10) {
        return text.substring(0, 10) + '...';
    }
    return text;
};

onMounted(() => {
    console.log(props.items);
});
</script>

<template>
    <section v-if="props.items.data.length === 0" class="py-4 sm:py-6 md:py-8 lg:py-10">
        <div class="max-w-screen-xl px-3 sm:px-4 mx-auto flex items-center justify-center">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <Icon icon="heroicons:x-circle" class="w-16 h-16 md:w-24 md:h-24 text-gray-300" />
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">
                    No Rejected Posts
                </h3>
                <p class="mb-6 text-gray-500">
                    There are no rejected posts available at the moment.
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
            :globalFilterFields="['item_name', 'user.name', 'category.category', 'reject_reasons[0].reason']"
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

            <Column field="created_at" header="Date Rejected" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.created_at) }}</div>
                </template>
            </Column>

            <Column field="reject_reasons[0].reason" header="Reason for Rejection" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">
                        <div class="flex items-center gap-1">
                            <div class="truncate max-w-[300px]">
                                {{ truncateText(data.reject_reasons && data.reject_reasons[0] ? data.reject_reasons[0].reason : 'No reason provided') }}
                            </div>
                            <Icon 
                                v-if="data.reject_reasons && data.reject_reasons[0] && data.reject_reasons[0].reason"
                                @click="showFullReason(data.reject_reasons[0].reason)"
                                icon="ri:information-line" 
                                class="w-5 h-5 text-gray-400 hover:text-gray-600 cursor-pointer" 
                            />
                        </div>
                    </div>
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

    <Dialog
        v-model:visible="showReasonDialog"
        modal
        header="Rejection Reason"
        :style="{ width: '30rem' }"
        :closable="true"
        class="mx-4 md:mx-0"
    >
        <div class="p-4">
            <p class="text-gray-700 whitespace-pre-wrap">{{ selectedReason }}</p>
        </div>
        <template #footer>
            <div class="flex justify-end">
                <button 
                    @click="showReasonDialog = false"
                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    Close
                </button>
            </div>
        </template>
    </Dialog>
</template>
