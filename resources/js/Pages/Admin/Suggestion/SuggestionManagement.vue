<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataView from 'primevue/dataview';
import Drawer from 'primevue/drawer';
import Button from 'primevue/button';
import TextInput from "@/Components/TextInput.vue";
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted } from 'vue';

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const categoryKey = ref('all');
const visibleBottom = ref(false);
const currentFeedback = ref(null);

const props = defineProps({
    feedbacks: Object
});

const openDrawer = (feedback) => {
    currentFeedback.value = feedback;
    visibleBottom.value = true;
};

const categoryOptions = ref([
    { label: 'Customer Service', value: 'Customer Service' },
    { label: 'Website Usability', value: 'Website Usability' },
    { label: 'Website Policies', value: 'Website Policies' },
    { label: 'Technical Issues', value: 'Technical Issues' },
    { label: 'Suggestions for Improvement', value: 'Suggestions for Improvement' },
    { label: 'General Inquiry', value: 'General Inquiry' },
    { label: 'Others', value: 'Others' }
]);

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const filteredFeedbacks = computed(() => {
    if (!props.feedbacks) return [];

    let filtered = props.feedbacks;
    
    if (categoryKey.value !== 'all') {
        filtered = filtered.filter(feedback => {
            return feedback.title === categoryKey.value;
        });
    }
    
    if (filters.value.global.value) {
        const searchTerm = filters.value.global.value.toLowerCase();
        filtered = filtered.filter(feedback => 
            feedback.title.toLowerCase().includes(searchTerm) ||
            feedback.feedback.toLowerCase().includes(searchTerm) ||
            feedback.user.toLowerCase().includes(searchTerm)
        );
    }
    
    return filtered;
});

onMounted(() => {
    console.log(categoryKey.value);
});
</script>

<template>

    <Head title="Suggestions Management" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="py-6 border-b border-gray-200">
                <h1 class="text-3xl font-semibold text-gray-900">Suggestions Management</h1>
                <p class="mt-2 text-base text-gray-600">Review and manage user feedback and suggestions</p>
            </div>

            <DataView 
                v-model:filters="filters"
                :value="filteredFeedbacks"
                :paginator="true" 
                :rows="5"
                :globalFilterFields="['title', 'feedback', 'user']"
                class="custom-dataview mt-4"
            >
                <template #header>
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
                        <div class="relative max-w-xs w-full md:w-96">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Icon icon="ri:search-line" class="h-4 w-4 text-gray-400" />
                                </div>
                                <TextInput 
                                    v-model="filters['global'].value"
                                    type="text"
                                    class="pl-9 w-96 text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Search feedbacks..." 
                                />
                            </div>
                        </div>

                        <div class="w-full md:w-64">
                            <select
                                v-model="categoryKey"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-200 focus:border-primary-200 block w-full p-2.5"
                            >
                                <option value="all">Sort by Category</option>
                                <option 
                                    v-for="option in categoryOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </template>

                <template #list="slotProps">
                    <div class="flex flex-col rounded-md">
                        <div v-for="(feedback, index) in slotProps.items" :key="feedback.id"
                            class="p-4 hover:bg-gray-50 transition-colors cursor-pointer border border-gray-200 m-2 rounded-md"
                            @click="openDrawer(feedback)">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-primary flex items-center justify-center rounded-full">
                                        <span class="text-sm font-medium text-white">
                                            {{ getInitials(feedback.user) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ feedback.title }}</h3>
                                            <p class="text-sm text-gray-500">
                                                By {{ feedback.user }} • {{ feedback.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-gray-600 text-sm line-clamp-2">{{ feedback.feedback }}</p>
                                        <button 
                                            v-if="feedback.feedback.length > 150"
                                            @click.stop="openDrawer(feedback)" 
                                            class="text-primary-600 text-sm font-medium hover:text-primary-700 mt-1 hover:underline"
                                        >
                                            See more
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="flex flex-col items-center justify-center p-8">
                        <Icon icon="material-symbols:feedback-outline" class="w-16 h-16 text-gray-400 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Suggestion Posted</h3>
                        <p class="text-sm text-gray-500">There are currently no suggestions available.</p>
                    </div>
                </template>
            </DataView>

            <Drawer v-model:visible="visibleBottom" :header="currentFeedback ? currentFeedback.title : ''"
                position="bottom" :modal="true" class="p-4" :style="{
                    height: '50%',
                    backgroundColor: 'white',
                    borderTopLeftRadius: '16px',
                    borderTopRightRadius: '16px',
                    boxShadow: '0 -2px 10px rgba(0,0,0,0.2)'
                }">
                <div class="p-4">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-medium text-white">
                                {{ getInitials(currentFeedback?.user) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900">
                                {{ currentFeedback?.title }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Suggested by {{ currentFeedback?.user }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ currentFeedback?.created_at }}
                            </p>
                        </div>
                    </div>

                    <div class="prose prose-sm max-w-none">
                        <p class="text-gray-700 whitespace-pre-wrap">
                            {{ currentFeedback?.feedback }}
                        </p>
                    </div>
                </div>
            </Drawer>
        </div>
    </AdminLayout>
</template>
