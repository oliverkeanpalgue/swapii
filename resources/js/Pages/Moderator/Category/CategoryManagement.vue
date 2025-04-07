<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from '@iconify/vue';
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import CategoryTable from "./CategoryTable.vue";
import AddCategoryForm from "./AddCategoryForm.vue";

const props = defineProps({
    categories: Object,
});

const showForm = ref(false);
</script>

<template>
    <Head title="Category Management" />

    <ModeratorLayout>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row justify-between md:items-center">
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-900">Category Management</h1>
                        <p class="mt-2 text-base text-gray-600">Manage and organize content categories</p>
                    </div>
                    
                    <div v-if="!showForm">
                        <button 
                            @click="showForm = true"
                            class="inline-flex items-center px-4 py-2 mt-4 md:mt-0 bg-primary text-white rounded-md hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 text-sm focus:ring-primary transition-colors"
                        >
                            <Icon 
                                icon="heroicons:plus-circle" 
                                class="mr-2 text-sm"
                            />
                            Add Category
                        </button>
                    </div>
                    <div v-else>
                        <button 
                            @click="showForm = false"
                            class="inline-flex items-center px-4 py-2 mt-4 md:mt-0 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 text-sm focus:ring-gray-500 transition-colors"
                        >
                            <Icon 
                                icon="heroicons:arrow-left" 
                                class="mr-2 text-sm"
                            />
                            Back to List
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="mt-6">
                <div>
                    <transition name="fade" mode="out-in">
                        <div v-if="!showForm">
                            <CategoryTable :categories="props.categories" />
                        </div>
                        <div v-else>
                            <AddCategoryForm @saved="showForm = false" />
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </ModeratorLayout>
</template>
