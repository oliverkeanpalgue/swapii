<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AuditLogTable from "./AuditLogTable.vue";

const props = defineProps({

    logs: Object
});


onMounted(() => {
    console.log(props.logs);
});
</script>

<template>
    <Head title="Audit Logs" />

    <AdminLayout>
        <div class="px-2 mx-auto max-w-7xl sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Audit Logs Management</h1>
                <p class="mt-2 text-base text-gray-600">Monitor and track system activities and changes</p>
            </div>

            <div class="py-4">
                <div class="bg-white rounded-md border border-gray-200 shadow">
                    <div class="py-2">
                        <AuditLogTable :logs="props.logs"  />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
    padding: 0.75rem 1rem;
    font-weight: 500;
    color: #475569;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

:deep(.p-datatable .p-datatable-thead > tr > th:hover) {
    background: #e0dfdf;
    cursor: pointer;
    color: #000000;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #E2E8F0;
}

:deep(.p-datatable .p-sortable-column:not(.p-highlight):hover) {
    background: #000000;
    color: #000000;
    cursor: pointer;
}

:deep(.p-datatable .p-sortable-column.p-highlight) {
    background: #F8FAFC;
    color: #475569;
}

:deep(.p-datatable .p-sortable-column.p-highlight:hover) {
    background: #F1F5F9;
    color: #475569;
    cursor: pointer;
}

:deep(.p-datatable .p-sortable-column:focus) {
    box-shadow: inset 0 0 0 0.15rem #E2E8F0;
}

:deep(.p-link:focus) {
    box-shadow: none;
}

:deep(.p-button.p-button-text) {
    padding: 0;
}

:deep(.p-button.p-button-text:hover) {
    background: transparent;
}

:deep(.p-button.p-button-text:focus) {
    box-shadow: none;
}

:deep(.p-inputtext) {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
}
</style>
