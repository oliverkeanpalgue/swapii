<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CardItems from "@/Components/CardItems.vue";
import { ref } from "vue";
import CategoryCards from "@/Components/CategoryCards.vue";
import { Icon } from '@iconify/vue';
import axios from "axios";
import { onMounted } from "vue";
import SuggestedCardItems from "@/Components/SuggestedCardItems.vue";
const num = ref(8);
const suggestedNum = ref(4);
const Has_suggestedItem = ref(false);

const props = defineProps({
    items: Object
});

onMounted(() => {
   axios.post('/create_categories')
        .then(() => {
            console.log('success')
        })
    console.log(props.items)
})
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div v-if="$page.props.flash.test" class="max-w-screen-xl mx-auto px-3 sm:px-4 mb-3 sm:mb-4">
            <div class="bg-green-50 border-l-4 border-green-400 p-3 sm:p-4 rounded-md">
                <p class="text-green-700 text-sm sm:text-base">{{ $page.props.flash.test }}</p>
            </div>
        </div>
        <section v-if="!props.items || props.items.length === 0" 
            class="py-4 sm:py-6 md:py-8 lg:py-10"
        >
            <div class="max-w-screen-xl px-3 sm:px-4 mx-auto min-h-[60vh] flex items-center justify-center">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <Icon 
                            icon="solar:box-minimalistic-broken" 
                            class="w-24 h-24 text-gray-300"
                        />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                        No Items Found
                    </h3>
                    <p class="mb-6 text-gray-500">
                        There are no items posted in this category yet.
                    </p>
                    <Link
                        href="/"
                        class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary focus:ring-4 focus:ring-primary"
                    >
                        <Icon 
                            icon="solar:home-2-broken" 
                            class="w-4 h-4 mr-2"
                        />
                        Back to Home
                    </Link>
                </div>
            </div>
        </section>
        <section v-else class="py-4 sm:py-6 md:py-8 lg:py-10">
            <div class="max-w-screen-xl mx-auto px-3 sm:px-4">
                <div class="flex flex-col gap-4 mb-4 sm:mb-8">
                    <div class="flex justify-end" v-if="!$page.props.auth.user">
                        <Link
                        href="/"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 transition-all w-fit"
                    >
                        <Icon 
                            icon="solar:home-2-broken" 
                            class="w-4 h-4 mr-2"
                        />
                            Back to Home
                        </Link>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                            Available Items for this Category
                        </h2>
                        
                        <div class="w-full sm:w-72">
                            <div class="relative">
                                <input type="search"
                                    class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-1.5 sm:py-2 rounded-lg border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm"
                                    placeholder="Search items..." />
                                <Icon 
                                    icon="material-symbols:search" 
                                    class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400"
                                    width="1rem" 
                                    height="1rem"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <template v-for="item in props.items" :key="item.id">
                        <CardItems :item="item"/>
                    </template>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.text-center {
    animation: fadeIn 0.5s ease-out;
}
</style>
