<script setup>
import { onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from '@/Components/TextInput.vue';
import Tag from 'primevue/tag';

const props = defineProps({
    users: Array,
});

const toast = useToast();
const menu = ref();
const actions = ref([]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const rows = ref(5);
const rowsPerPageOptions = [5, 10, 20, 50];

const approveDialogVisible = ref(false);
const rejectDialogVisible = ref(false);
const viewDialogVisible = ref(false);
const selectedUserId = ref(null);
const selectedVerification = ref(null);
const rejectionReason = ref('');

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
                    label: 'View Details',
                    icon: 'heroicons:eye-20-solid',
                    command: () => {
                        viewVerification(id);
                    }
                },
            ],
        },
    ];

    menu.value.toggle(event);
};

const viewVerification = (id) => {
    router.visit(route('admin.verification.show', id));
};
</script>

<template>
    <section v-if="!users.length" class="p-4 bg-white shadow sm:p-6 sm:rounded-md">
        <div class="flex flex-col justify-center items-center">
            <p class="text-sm text-gray-500">No pending verification requests.</p>
        </div>
    </section>

    <section v-else>
        <DataTable v-model:filters="filters" :value="users" removableSort tableStyle="min-width: 100%"
            class="overflow-hidden" :globalFilterFields="['user.name', 'user.email']" paginator :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions" responsiveLayout="scroll">
            <template #header>
                <div class="flex gap-4 justify-between items-center mb-4">
                    <div class="relative w-96 max-w-xs">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                            </div>
                            <TextInput v-model="filters['global'].value" type="text"
                                class="pl-9 w-96 text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search users and email" />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="user.name" header="Name" class="text-sm" sortable>
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

            <Column field="user.email" header="Email" sortable>
                <template #body="{ data }">
                    <span class="text-sm text-gray-600">{{ data.user.email }}</span>
                </template>
            </Column>

            <Column field="created_at" header="Date Submitted" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600">{{ formatDate(slotProps.data.created_at) }}</span>
                </template>
            </Column>

            <Column field="school" header="School" sortable>
                <template #body="{ data }">
                    <span class="text-sm text-gray-600">{{ data.school }}</span>
                </template>
            </Column>

            <Column field="id" header="Actions" class="text-sm" style="width: 100px">
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
