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

// const props = defineProps({
//     items: Object, // The paginated items passed from Inertia
// });

// const confirm = useConfirm();
// const toast = useToast();
// const menu = ref();
// const actions = ref([]);
// const items = ref(props.items.data); //for data table component

// function getStatusLabel(status) {
//     if (status === 0) return "Pending";
//     if (status === 1) return "Success";
//     if (status === 3) return "Cancelled";
//     return "Unknown";
// }

// function getStatusSeverity(status) {
//     if (status === 0) return "warn";
//     if (status === 1) return "success";
//     if (status === 3) return "danger";
//     return "warning";
// }

// const toggleMenu = (event, id) => {
//     // Update actions dynamically for each row
//     actions.value = [
//         {
//             label: 'Actions',
//             items: [
//                 {
//                     label: 'Edit',
//                     icon: 'bxs:edit',
//                     href: '/edit-post/' + id,

//                 },
//                 {
//                     label: 'Preview',
//                     icon: 'icon-park-outline:preview-open',
//                     href: '/item-description/' + id,
//                 },
//                 {
//                     label: 'View List',
//                     icon: 'icon-park-outline:box',
//                     href: '/trade-list/' + id,
//                 },
//                 {
//                     label: 'Delete',
//                     icon: 'mingcute:delete-2-fill',
//                     id: id,
//                     command: () => {
//                         deleteModal.value = true;
//                     }
//                 },
//             ],
//         },
//     ];

//     menu.value.toggle(event); // Show the menu
// };

// const deleteConfirm = (id) => {
//     confirm.require({
//         message: 'Are you sure you want to delete this item?',
//         header: 'DELETE ' ,
//         icon: 'pi pi-info-circle',
//         rejectLabel: 'Cancel',
//         rejectProps: {
//             label: 'Cancel',
//             severity: 'secondary',
//             outlined: true
//         },
//         acceptProps: {
//             label: 'Delete',
//             severity: 'danger'
//         },
//         accept: () => {
//             router.delete('/post-management/'+id)


//         },
//         reject: () => {
//             toast.add({ severity: 'warn', summary: 'Rejected', detail: 'Delete Cancelled', life: 3000 });
//         }
//     });
// };

// onMounted(() => {
//     console.log(props.items.data);
//     console.log(items.value);
// })

</script>
<template>


    <!-- if empty ang db or post -->
    <!-- <section v-if="props.items.data.length === 0" class="p-4 bg-white shadow sm:p-6 sm:rounded-md ">
        <div class="flex flex-col items-center justify-center">
            <p class="text-sm text-gray-500">You have no Item available as of the moment.</p>
        </div>
    </section> -->

    <!-- if may laman db or may post -->
    <!-- <section v-else class="px-10 antialiased"> -->
    <section class="">
        <!-- Start coding here -->
        <div class="">
            <div class="">
                <DataTable removableSort tableStyle="min-width: 50rem"
                    class="overflow-hidden text-sm text-center capitalize">
                <!-- <DataTable :value="props.items.data" removableSort tableStyle="min-width: 50rem"
                class="overflow-hidden text-sm text-center capitalize"> -->
                    <template #header>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xl font-bold">Blocklisted</span>
                        </div>
                    </template>
                    <Column field="item_name" header="Reported Users" class="text-center" sortable></Column>
                    <Column field="images[0]?.image" header="Proof Image">
                        <template #body="slotProps">
                            <img :src="`/storage/${slotProps.data.images[0]?.image}`" class="w-20 h-20 rounded"
                                v-if="slotProps.data.images[0]" />
                        </template>
                    </Column>
                    <Column field="item_description" header="Report Type" sortable></Column>
                    <Column field="category.category" header="Description" sortable></Column>

                    <!-- Action Column -->
                    <Column field="id" header="Actions">
                        <template #body="slotProps">
                            <div class="flex justify-center card">
                                <Button type="button" @click="(event) => toggleMenu(event, slotProps.data.id)"
                                    aria-haspopup="true" aria-controls="overlay_menu" text>
                                    <button class="text-black ">
                                        <Icon icon="lucide:ellipsis" width="1.5rem" height="1.5rem" />
                                    </button>
                                </Button>
                                <Menu ref="menu" id="overlay_menu" :model="actions" :popup="true">
                                    <template #item="{ item, props }">
                                        <Link v-if="item.href" v-ripple class="flex items-center" v-bind="props.action"
                                            :href="item.href">
                                        <Icon :icon="item.icon" />
                                        <span>{{ item.label }}</span>
                                        </Link>

                                        <button v-else v-ripple v-bind="props.action" @click="deleteConfirm(item.id)"
                                        class="flex items-center w-full px-4 py-2 text-red-500 hover:bg-gray-200">
                                            <Icon :icon="item.icon" />
                                            <span>{{ item.label }} </span>
                                        </button>
                                    </template>
                                </Menu>
                            </div>
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </section>

    <Toast />
    <ConfirmDialog class="mx-4 md:mx-0"></ConfirmDialog>
</template>
<style>
.dropdown-menu {
    z-index: 1000;
    /* Ensure dropdown is above other elements */
}

.absolute {
    position: absolute;
    /* Position the dropdown absolutely */
}

.shadow-lg {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    /* Add shadow for better visibility */
}
</style>
