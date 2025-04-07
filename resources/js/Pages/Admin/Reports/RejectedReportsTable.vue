<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, router } from '@inertiajs/vue3';
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
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    reports: Object
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);
const dismissReportDialog = ref(false);
const reportId = ref(null);

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

const warningDialog = ref(false);
const warningLevel = ref(null);
const adminNotes = ref('');
const selectedUser = ref(null);

const warningLevels = [
    { label: 'Level 1: Minor Violation', value: 1 },
    { label: 'Level 2: Repeated Minor Violations', value: 2 },
    { label: 'Level 3: Serious Violation', value: 3 }
];

const toggleMenu = (event, id, user) => {
    actions.value = [
        {
            label: 'View Details',
            icon: 'lucide:eye',
            href: `/admin/reports/${id}`,
        },
    ];
    menu.value.toggle(event);
};


onMounted(() => {
    console.log(props.reports);
});
</script>
<template>
    <section v-if="props.reports.data.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-md">
        <div class="flex flex-col justify-center items-center">
            <p class="text-sm text-gray-500">No reports available at the moment.</p>
        </div>
    </section>

    <section v-else>
        <DataTable v-model:filters="filters" :value="props.reports.data" removableSort tableStyle="min-width: 100%"
            class="overflow-hidden" :globalFilterFields="['user.name', 'concern', 'description']" paginator :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions" responsiveLayout="scroll">
            <template #header>
                <div class="flex gap-4 justify-between items-center mb-4">
                    <div class="relative w-96 max-w-xs">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                            </div>

                            <TextInput v-model="filters['global'].value" type="text"
                                class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search by user, type, or description" />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="user.name" header="Reported User" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex gap-3 items-center">
                        <div v-if="data.user.avatar" class="overflow-hidden w-8 h-8 rounded-full">
                            <img :src="data.user.avatar" :alt="data.user.name" class="object-cover w-full h-full">
                        </div>
                        <div v-else class="h-8 w-8 rounded-full bg-[#F5A623] flex items-center justify-center">
                            <span class="font-medium text-white">{{ data.user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="font-medium text-gray-900">{{ data.user.name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="concern" header="Report Type" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.concern }}</div>
                </template>
            </Column>

            <Column field="description" header="Description" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ data.description }}</div>
                </template>
            </Column>

            <Column field="created_at" header="Date Reported" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="text-gray-600">{{ formatDate(data.created_at) }}</div>
                </template>
            </Column>

            <Column field="proof_image" header="Proof" class="text-sm">
                <template #body="{ data }">
                    <div v-if="data.image" class="w-20 h-20">
                        <img :src="data.image.startsWith('http') ? data.image : `/storage/${data.image}`"
                            :alt="'Proof for report'" class="object-cover w-full h-full rounded">
                    </div>
                    <div v-else class="text-sm text-gray-400">No proof provided</div>
                </template>
            </Column>

            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center">
                        <button type="button"
                            @click="(event) => toggleMenu(event, slotProps.data.id, slotProps.data.user)"
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
                                    class="flex items-center px-4 py-2 w-full text-sm text-gray-700 hover:bg-gray-50">
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
<style scoped>
/* Copy all styles from UserTable.vue */
</style>
