<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CardItems from "@/Components/CardItems.vue";
import { ref, onMounted, computed, onUnmounted } from "vue";
import CategoryCards from "@/Components/CategoryCards.vue";
import { Icon } from '@iconify/vue';
import axios from "axios";
import SuggestedCardItems from "@/Components/SuggestedCardItems.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Carousel from 'primevue/carousel';
import { FilterMatchMode } from '@primevue/core/api';
import TextInput from "@/Components/TextInput.vue";
import MostViewedItems from "@/Pages/Dashboard/comps/MostViewedItems.vue";
import AvailableItems from "./comps/AvailableItems.vue";

const page = ref(1);
const items = ref([]);
const loading = ref(false);
const hasMoreItems = ref(true);
const suggestedNum  = ref(4);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const props = defineProps({
    tests: Object,
    mostViewedItems: Object,
    items: Object,

    suggestedItems: Object,
    categories: Object,
})

const isMobile = ref(false);

// Add responsive options for the carousel
const responsiveOptions = ref([
    {
        breakpoint: '1400px',
        numVisible: 6,
        numScroll: 1
    },
    {
        breakpoint: '1199px',
        numVisible: 4,
        numScroll: 1
    },
    {
        breakpoint: '767px',
        numVisible: 3,
        numScroll: 1
    },
    {
        breakpoint: '575px',
        numVisible: 2,
        numScroll: 1
    }
]);

const most_viewed_items = ref([]);
const interval = ref(null);



onMounted(() => {
    // getMostViewedItems();
    // interval.value = setInterval(getMostViewedItems, 10000); // Fetch every 10 seconds

    checkIfMobile();
    window.addEventListener('resize', checkIfMobile);


    // Initialize with first 4 items
    loadMoreItems();

    // Add scroll event listener
    window.addEventListener('scroll', handleScroll);
    console.log(props.suggestedItems);
});

onUnmounted(() => {
    clearInterval(interval.value); // Clear the interval on component unmount
    window.removeEventListener('scroll', handleScroll);
    console.log(props.suggestedItems);
});

const checkIfMobile = () => {
    isMobile.value = window.innerWidth < 640;
};

const loadMoreItems = async () => {
    if (loading.value || !hasMoreItems.value) return;

    loading.value = true;
    try {
        const response = await axios.get(`/post/items?page=${page.value}&count=4`);
        const newItems = response.data.data;

        if (newItems.length === 0) {
            hasMoreItems.value = false;
            return;
        }

        items.value = [...items.value, ...newItems];
        page.value++;
    } catch (error) {
        console.error('Error loading items:', error);
    } finally {
        loading.value = false;
    }
};

const handleScroll = () => {
    const bottomOfWindow = Math.ceil(window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight;

    if (bottomOfWindow) {
        loadMoreItems();
    }
};

const filteredTests = computed(() => {
    if (!items.value) return [];

    let filtered = items.value;

    if (filters.value.global.value) {
        const searchTerm = filters.value.global.value.toLowerCase();
        filtered = filtered.filter(test =>
            test.item_name.toLowerCase().includes(searchTerm) ||
            test.preferred_items.toLowerCase().includes(searchTerm) ||
            test.tags.some(tag => tag.tag.toLowerCase().includes(searchTerm))
        );
    }

    return filtered;
});

</script>

<template>

    <Head title="Home" />

    <AuthenticatedLayout>
        <div v-if="$page.props.flash.success" class="px-3 mx-auto mb-3 max-w-screen-xl sm:px-4 sm:mb-4">
            <div class="p-3 bg-green-50 rounded-md border-l-4 border-green-400 sm:p-4">
                <p class="text-sm text-green-700 sm:text-base">{{ $page.props.flash.success }}</p>
            </div>
        </div>
        <div v-if="$page.props.flash.error" class="px-4 mx-auto mb-4 max-w-screen-xl">
            <div class="p-4 bg-red-50 rounded-md border-l-4 border-red-400">
                <p class="text-red-700">{{ $page.props.flash.error }}</p>
            </div>
        </div>

        <section class="py-4 sm:py-6 md:py-8 lg:py-10">
            <div class="px-3 mx-auto max-w-screen-xl sm:px-4">
                <div class="flex justify-between items-center mb-4 md:mb-6">
                    <h2 class="text-lg font-bold text-gray-900 md:text-xl lg:text-2xl">
                        Browse Categories
                    </h2>
                </div>

                <div v-if="isMobile" class="w-full">
                    <div class="flex overflow-x-auto gap-4 px-2 pb-4 custom-scrollbar">
                        <div v-for="category in props.categories"
                             :key="category.id"
                             class="flex-shrink-0 snap-start">
                            <CategoryCards
                                :category="category"
                                :message="category.category"
                                :imgSrc="category.category_image.startsWith('category-images/')
                                    ? `/storage/${category.category_image}`
                                    : `/assets/category-images/${category.category_image}`"
                                @click="router.get(`/item-category/${category.id}`)"
                                class="cursor-pointer"
                            />
                        </div>
                    </div>
                </div>

                <div v-else>
                    <template v-if="props.categories.length <= 6">
                        <div class="grid grid-cols-4 gap-4 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 sm:gap-6">
                            <CategoryCards
                                v-for="category in props.categories"
                                :key="category.id"
                                :category="category"
                                :message="category.category"
                                :imgSrc="category.category_image.startsWith('category-images/')
                                    ? `/storage/${category.category_image}`
                                    : `/assets/category-images/${category.category_image}`"
                                @click="router.get(`/item-category/${category.id}`)"
                                class="cursor-pointer"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <Carousel
                            :value="props.categories"
                            :numVisible="6"
                            :numScroll="1"
                            :responsiveOptions="responsiveOptions"
                            circular
                        >
                            <template #item="slotProps">
                                <div class="p-2">
                                    <CategoryCards
                                        :category="slotProps.data"
                                        :message="slotProps.data.category"
                                        :imgSrc="slotProps.data.category_image.startsWith('category-images/')
                                            ? `/storage/${slotProps.data.category_image}`
                                            : `/assets/category-images/${slotProps.data.category_image}`"
                                        @click="router.get(`/item-category/${slotProps.data.id}`)"
                                        class="cursor-pointer"
                                    />
                                </div>
                            </template>
                        </Carousel>
                    </template>
                </div>
            </div>
        </section>

        <section class="py-8 bg-gray-50 sm:py-12">
            <div class="px-3 mx-auto max-w-screen-xl sm:px-4">
                <div class="flex justify-between items-center mb-4 sm:mb-8">
                    <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">
                        MOST VIEWED ITEMS
                    </h2>
                </div>
                <MostViewedItems :items="props.mostViewedItems"/>
            </div>
        </section>

        <section class="py-8 sm:py-10">
            <div class="px-3 mx-auto max-w-screen-xl sm:px-4">
                <div class="flex flex-col gap-3 justify-between mb-4 sm:flex-row sm:items-center sm:gap-4 sm:mb-8">
                    <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">
                        Available Items
                    </h2>

                    <div class="w-full sm:w-96">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <Icon icon="ri:search-line" class="w-4 h-4 text-gray-400" />
                            </div>
                            <TextInput
                                v-model="filters['global'].value"
                                type="text"
                                class="pl-9 w-full text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Search items..."
                            />
                        </div>
                    </div>
                </div>


                <AvailableItems :items="props.items"  />
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
