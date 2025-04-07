<script setup>
import { ref, watch, onMounted, computed, defineEmits } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductTable from './PostTable/ProductTable.vue';
import UnlistedPost from './PostTable/UnlistedPost.vue';
import TradedItemsTable from './PostTable/TradedItemsTable.vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { Link } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const page = usePage();

const props = defineProps({
    activeItems: Object,
    unlistedItems: Object,
    tradedItems: Object,
});

const emit = defineEmits(['redirect-to-verify']);

const activeTab = ref(0);
const currentPage = ref('table');

const isLoading = ref(false);

const activeItemsData = ref(props.activeItems);
const unlistedItemsData = ref(props.unlistedItems);

const isVerified = computed(() => {
    return page.props.auth?.user?.is_verified ?? false;
});

const navigateToVerification = () => {
    router.visit(route('profile.edit'), {
        data: { activeTab: 2 },
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.activeItems, (newValue) => {
    activeItemsData.value = newValue;
}, { deep: true });

watch(() => props.unlistedItems, (newValue) => {
    unlistedItemsData.value = newValue;
}, { deep: true });


onMounted(() => {
    const flash = page.props.flash;
    if (flash.success) {
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: flash.success,
            life: 3000
        });
    }
    if (flash.error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: flash.error,
            life: 3000
        });
    }
});
</script>

<template>
    <Toast position="top-right" class="absolute z-50" />

    <Head title="Manage Posts" />
    <AuthenticatedLayout>
        <div class="px-3 mx-auto max-w-7xl sm:px-4 lg:px-8">
            <!-- Header Section with Add New Post Button -->
            <div class="flex flex-col justify-between py-4 border-b border-gray-200 md:flex-row md:items-center md:py-6">
                <div class="">
                    <h1 class="flex gap-2 items-center text-2xl font-bold text-gray-900">
                        <Icon icon="solar:box-minimalistic-outline" class="w-6 h-6" />
                        Your Posted Items
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">View and manage all your posted items</p>
                </div>

                <Link
                    :href="route('post-item')"
                    class="inline-flex items-center px-4 py-2 h-10 text-xs font-semibold tracking-widest text-white uppercase rounded-md border border-transparent transition duration-150 ease-in-out bg-primary hover:bg-primary-200 focus:bg-primary-200 active:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary-200 focus:ring-offset-2"
                >
                    <Icon icon="heroicons:plus" class="mr-2 w-5 h-5" />
                    Add New Post
                </Link>
            </div>

            <!-- Main Content -->
            <div class="py-6">
                <div class="bg-white rounded-md shadow">
                    <div class="p-1 sm:p-2">
                        <Tabs value="0" class="custom-tabs">
                            <TabList>
                                <Tab value="0" class="w-1/3 custom-tab">
                                    <div class="flex justify-center items-center">
                                        <Icon icon="heroicons:check-circle" class="mr-2 text-xl tab-icon" />
                                        <span class="hidden sm:block sm:text-[12px] md:text-[14px] text-wrap font-medium">Active Posts</span>
                                    </div>
                                </Tab>
                                <Tab value="1" class="w-1/3 custom-tab">
                                    <div class="flex justify-center items-center">
                                        <Icon icon="mage:box-3d-check" class="mr-2 text-xl tab-icon" />
                                        <span class="hidden sm:block sm:text-[12px] md:text-[14px] text-wrap font-medium">Traded Items</span>
                                    </div>
                                </Tab>
                                <Tab value="2" class="w-1/3 custom-tab">
                                    <div class="flex justify-center items-center">
                                        <Icon icon="heroicons:archive-box" class="mr-2 text-xl tab-icon" />
                                        <span class="hidden sm:block sm:text-[12px] md:text-[14px] text-wrap font-medium">Unlisted Items</span>
                                    </div>
                                </Tab>
                            </TabList>

                            <TabPanels>
                                <!-- Active Posts -->
                                <TabPanel value="0">
                                    <div v-if="currentPage === 'table'">
                                        <ProductTable
                                            :items="props.activeItems"
                                        />
                                    </div>
                                </TabPanel>

                                 <!-- Unlisted Items -->
                                 <TabPanel value="1">
                                    <TradedItemsTable :items="props.tradedItems"/>
                                </TabPanel>

                                <!-- Unlisted Items -->
                                <TabPanel value="2">
                                    <UnlistedPost :items="props.unlistedItems"/>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Add loading overlay -->
    <div v-if="isLoading"
         class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
        <div class="w-12 h-12 rounded-full border-t-2 border-b-2 animate-spin border-primary"></div>
    </div>

    <!-- Add transition effects -->
    <TransitionGroup
        name="fade"
        mode="out-in"
        class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Your existing content -->
    </TransitionGroup>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

:deep(.p-toast) {
    z-index: 9999;
}

:deep(.p-toast-message) {
    margin: 0 0 8px 0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-radius: 6px;
}

:deep(.p-toast-message-content) {
    padding: 12px;
    border-width: 0;
}
</style>
