<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { onMounted, ref } from 'vue';
import AcquiredItemCard from '@/Components/AcquiredItemCard.vue';
const props = defineProps({
    acquiredItems: Object,
});
const num = ref(8);

const acquiredItems = ref([
    {
        id: 1,
        item_name: "Vintage Camera",
        acquired_date: "2024-03-15",
        previous_owner: "John Smith",
        images: [{ image: 'public/assets/category-images/electronics.jpg' }],
        status: "completed",
        category: "Electronics",
        tags: [
            { id: 1, tag: "Vintage" },
            { id: 2, tag: "Photography" },
            { id: 3, tag: "Analog" }
        ],
    },
]);

onMounted(() => {
    console.log(props.acquiredItems);
})
</script>

<template>
    <Head title="Acquired Items" />

    <AuthenticatedLayout>
        <section class="py-4 sm:py-6 md:py-8 lg:py-10">
            <div class="px-3 mx-auto max-w-screen-xl sm:px-4">
                <div class="flex flex-col gap-4 mb-4 sm:mb-8">
                    <div class="flex flex-col gap-3 justify-between sm:flex-row sm:items-center sm:gap-4">
                        <h2 class="flex gap-2 items-center text-xl font-bold text-gray-900 sm:text-2xl dark:text-white">
                            <Icon icon="mage:box-3d-check" class="w-8 h-8 text-black" />
                            Acquired Items
                        </h2>

                        <div class="w-full sm:w-72">
                            <div class="relative">
                                <input type="search"
                                    class="py-1.5 pr-3 pl-9 w-full text-sm rounded-lg border border-gray-200 transition-all sm:pl-10 sm:pr-4 sm:py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    placeholder="Search acquired items..." />
                                <Icon icon="material-symbols:search"
                                    class="absolute left-2.5 top-1/2 text-gray-400 -translate-y-1/2 sm:left-3"
                                    width="1rem" height="1rem" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Grid -->
                <div v-if="acquiredItems.length > 0"
                    class="grid grid-cols-1 gap-4 xs:grid-cols-2 lg:grid-cols-4 sm:gap-6">
                    <AcquiredItemCard
                        v-for="item in props.acquiredItems"
                        :key="item.id"
                        :item="item"
                    />
                </div>
                <div v-else class="min-h-[60vh] flex items-center justify-center">
                    <div class="text-center">
                        <div class="flex justify-center mb-4">
                            <Icon icon="solar:box-minimalistic-broken" class="w-24 h-24 text-gray-300" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                            No Acquired Items
                        </h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            You haven't acquired any items through trades yet.
                        </p>
                        <Link href="/"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white rounded-lg bg-primary hover:bg-primary-600 focus:ring-4 focus:ring-primary/50">
                            <Icon icon="solar:home-2-broken" class="mr-2 w-4 h-4" />
                            Back to Home
                        </Link>
                    </div>
                </div>

                <!-- Load More Button -->
                <div v-if="acquiredItems.length > 0" class="mt-8 space-y-4 text-center">
                    <div>
                        <button @click="num += 4"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-gray-700 rounded-lg border border-gray-300 transition-all dark:border-gray-600 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            Load More
                            <Icon icon="heroicons:arrow-down" class="ml-2" width="16" height="16" />
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grid {
    animation: fadeIn 0.5s ease-out;
}
</style>
