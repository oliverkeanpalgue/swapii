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
import InputText from 'primevue/inputtext';
import TextInput from '@/Components/TextInput.vue';
const props = defineProps({
    users: Array, // The paginated items passed from Inertia
});

const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const actions = ref([]);

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

const toggleMenu = (event, id) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'Set to User',
                    icon: 'uil:user',
                    command: () => {
                        acceptDialog(id);
                    }
                },
            ],
        },
    ];

    menu.value.toggle(event);
};

const acceptDialog = (id) => {
    confirm.require({
        group: 'templating',
        message: 'Set back to User ?',
        header: 'Change role of Moderator to User',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Accept',
            severity: 'primary'
        },
        accept: () => {
            router.patch(route('admin.user.set', id));
            toast.add({ severity: 'success', summary: 'Success', detail: 'Set to user', life: 3000 });
            confirm.close();
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'Change Cancelled', life: 3000 });
            confirm.close();
        }
    });
};

onMounted(() => {
    console.log(props.users);
});
</script>

<template>
    <!-- Update empty state section to match UserTable -->
    <section v-if="props.users.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-md">
        <div class="flex flex-col items-center justify-center">
            <p class="text-sm text-gray-500">You have no Item available as of the moment.</p>
        </div>
    </section>

    <!-- Update DataTable section -->
    <section v-else>
        <DataTable v-model:filters="filters" :value="props.users" removableSort tableStyle="min-width: 100%"
            class="overflow-hidden" :globalFilterFields="['name', 'email']" paginator :rows="rows"
            :rowsPerPageOptions="rowsPerPageOptions" responsiveLayout="scroll">
            <template #header>
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div class="relative max-w-xs w-96">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Icon icon="ri:search-line" class="h-4 w-4 text-gray-400" />
                            </div>
                            <TextInput v-model="filters['global'].value" type="text"
                                class="pl-9 w-96 text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search users and email" />
                        </div>
                    </div>
                </div>
            </template>

            <Column field="name" header="Name" class="text-sm" sortable>
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <div v-if="data.avatar" class="h-8 w-8 rounded-full overflow-hidden">
                            <img :src="data.avatar" :alt="data.name" class="h-full w-full object-cover">
                        </div>
                        <div v-else class="h-8 w-8 rounded-full bg-[#F5A623] flex items-center justify-center">
                            <span class="text-white font-medium">{{ data.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="font-medium text-gray-900">{{ data.name }}</div>
                    </div>
                </template>
            </Column>

            <Column field="email" header="Email" sortable>
                <template #body="{ data }">
                    <span class="text-sm text-gray-600">{{ data.email }}</span>
                </template>
            </Column>

            <Column field="created_at" header="Date Joined" sortable>
                <template #body="slotProps">
                    <span class="text-sm text-gray-600">{{ formatDate(slotProps.data.created_at) }}</span>
                </template>
            </Column>

            <Column field="id" header="Actions" class="text-sm">
                <template #body="slotProps">
                    <div class="flex justify-start items-center">
                        <button type="button" @click="(event) => toggleMenu(event, slotProps.data.id)"
                            class="text-black text-center hover:text-gray-700" text>
                            <Icon icon="lucide:more-horizontal" width="1.25rem" height="1.25rem" />
                        </button>
                        <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                            <template #item="{ item, props }">
                                <Link v-if="item.href" v-ripple
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    v-bind="props.action" :href="item.href">
                                    <Icon :icon="item.icon" class="mr-2 h-4 w-4" />
                                    <span>{{ item.label }}</span>
                                </Link>
                                <button v-else v-ripple v-bind="props.action"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
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
    <ConfirmDialog group="templating">
        <template #message="slotProps">
            <div class="flex flex-col items-center w-full gap-4 pb-4 border-b border-gray-200">
                <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center">
                    <Icon icon="heroicons:user-circle" class="w-10 h-10 text-[#F5A623]" />
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ slotProps.message.header }}</h3>
                    <p class="text-sm text-gray-600">{{ slotProps.message.message }}</p>
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>


