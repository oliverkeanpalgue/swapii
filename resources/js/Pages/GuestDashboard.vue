<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    tests: Object,
    categories: Object,
})
</script>

<template>

    <Head title="Dashboard" />

    <GuestLayout>
        <div v-if="$page.props.flash.success">{{ $page.props.flash.success }}</div>
        <div v-if="$page.props.flash.error">{{ $page.props.flash.error }}</div>
        <!-- Categories section -->
        <section class="hidden py-8 antialiased sm:block md:py-10">
            <div class="max-w-screen-xl px-4 mx-auto 2xl:px-0">
                <div class="flex items-center justify-between gap-4 mb-4 md:mb-8">
                    <h1 class="text-lg font-bold text-primary">
                        Categories
                    </h1>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6">
                    <CategoryCards 
                        v-for="category in props.categories" 
                        :key="category.id" 
                        :category="category"
                        :message="category.category"
                        :imgSrc="category.category_image.startsWith('category-images/') 
                            ? `/storage/${category.category_image}` 
                            : `/assets/category-images/${category.category_image}`" 
                    />
                </div>
            </div>
        </section>

        <!-- Categories section -->
        <section v-if="Has_suggestedItem = true && $page.props.auth.user"
            class="hidden px-2 antialiased sm:block md:py-6">
            <div class="max-w-screen-xl px-4 mx-auto 2xl:px-0">
                <div class="flex items-center justify-between gap-4 mb-4 md:mb-8">
                    <h1 class="text-lg font-bold text-primary">
                        Suggested Items
                    </h1>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 md:mb-8 lg:grid-cols-3 xl:grid-cols-4">
                    <template v-for="n in suggestedNum" :key="n">
                        <SuggestedCardItems message="School Material Test Item"
                            desc="Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatibus commodi eum quos, delectus labore expedita, fuga magnam porro aliquid soluta ipsum temporibus vel distinctio consequatur harum nesciunt quidem repellendus eaque?"
                            imgSrc="https://mediaserver.goepson.com/ImConvServlet/imconv/b405fdceb71bf171658c2461424c50f032f13ded/original?use=productpictures&hybrisId=B2C&assetDescr=Projectors-Hub_2col_LS12000_1140x450" />
                    </template>
                </div>
                <div class="w-full mb-5 text-center">
                    <Button v-on:click="suggestedNum += 4" type="button"
                        class="mt-3 rounded-lg border border-gray-300 bg-white px-20 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100">
                        Load More Suggested Items
                    </Button>
                </div>
            </div>
        </section>

        <hr v-if="props.tests.data.length == 0">

        <!-- Items Section -->
        <section class="py-8 antialiased md:py-12">
            <div v-if="props.tests.data.length > 0" class="max-w-screen-xl px-4 mx-auto 2xl:px-0">
                <!-- Heading -->
                <div class="items-end justify-between mb-4 space-y-4 sm:flex sm:space-y-0 md:mb-8">
                    <h1 class="mt-3 text-lg font-bold text-primary">
                        Available Items
                    </h1>
                    <div class="relative hidden md:block">
                        <form class="max-w-md px-3 mx-auto mt-2 mb-2">
                            <div class="flex gap-0.5">
                                <input type="search"
                                    class="block text-sm text-gray-900 bg-gray-100 border border-gray-300 rounded-md w-96 ps-5 focus:ring-primary focus:border-primary"
                                    placeholder="Search Items......" required />
                                <button type="submit" class="p-2 text-white rounded-md bg-primary">
                                    <Icon icon="material-symbols:search" width="1.5rem" height="1.5rem" />
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="grid gap-4 mb-4 sm:grid-cols-2 md:mb-8 lg:grid-cols-3 xl:grid-cols-4">

                    <template v-for="item in props.tests.data" :key="item.id">
                        <CardItems :item="item" />
                    </template>
                </div>
            </div>
            <div v-else  class="flex flex-col items-center h-96">
                <p class="text-sm text-gray-500">No Items posted as of the moment.</p>
            </div>
            <div v-if="props.tests.data.length > 0" class="w-full mb-5 text-center">
                <Button v-on:click="num += 4" type="button"
                    class="mt-3 rounded-lg border border-gray-300 bg-white px-20 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100">
                    Load More
                </Button>
            </div>
        </section>
    </GuestLayout>
</template>
