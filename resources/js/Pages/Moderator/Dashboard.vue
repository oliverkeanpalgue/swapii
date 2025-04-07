<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import ModeratorLayout from "@/Layouts/ModeratorLayout.vue";
import AdminCards from "@/Components/AdminCards.vue";
import chartTrend from "./shared/chartTrend.vue";
import ChartCards from "@/Components/ChartCards.vue";
import { Icon } from '@iconify/vue';

const props = defineProps({
    categoryCount: {
        type: Number,
        required: true
    },
    postStats: {
        type: Object,
        required: true,
        default: () => ({
            totalPosts: 0,
            pendingCount: 0,
            approvedCount: 0,
            rejectedCount: 0
        })
    },
    topCategories: {
        type: Array,
        required: true
    }
});

const user_role = ref('moderator');

const getCategoryImageUrl = (image) => {
    if (image.startsWith('category-images/')) {
        return `/storage/${image}`;
    } else if (image) {
        return `/assets/category-images/${image}`;
    } else {
        return `/assets/category-images/default.jpg`;
    }
};
</script>

<template>
    <Head title="Moderator Dashboard" />

    <ModeratorLayout>
        <!-- Categories section -->
        <section class="antialiased">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-2 sm:p-4 h-max">
                <div>
                    <AdminCards 
                        entity="Posts Overview" 
                        :value="postStats.totalPosts" 
                        :widgetType="'three_column'"
                        :stats="{
                            pendingCount: postStats.pendingCount,
                            approvedCount: postStats.approvedCount,
                            rejectedCount: postStats.rejectedCount
                        }"
                        :statsConfig="[
                            {
                                icon: 'material-symbols:pending-actions',
                                iconColor: 'text-yellow-600',
                                label: 'Pending Posts',
                                key: 'pendingCount'
                            },
                            {
                                icon: 'material-symbols:check-circle',
                                iconColor: 'text-green-600',
                                label: 'Approved Posts',
                                key: 'approvedCount'
                            },
                            {
                                icon: 'material-symbols:block',
                                iconColor: 'text-red-600',
                                label: 'Rejected Posts',
                                key: 'rejectedCount'
                            }
                        ]"
                        icon='mdi:post' 
                        linkName="Manage Posts" 
                        href="/moderator/post"
                        totalLabel="total posts" 
                    />
                </div>
                <div>
                    <AdminCards 
                        entity="Reported Posts" 
                        :value="0" 
                        :widgetType="'three_column'"
                        :stats="{
                            pendingCount: 0,
                            resolvedCount: 0,
                            dismissedCount: 0
                        }"
                        :statsConfig="[
                            {
                                icon: 'material-symbols:pending-actions',
                                iconColor: 'text-yellow-600',
                                label: 'Pending Reports',
                                key: 'pendingCount'
                            },
                            {
                                icon: 'material-symbols:check-circle',
                                iconColor: 'text-green-600',
                                label: 'Resolved Reports',
                                key: 'resolvedCount'
                            },
                            {
                                icon: 'material-symbols:block',
                                iconColor: 'text-red-600',
                                label: 'Dismissed Reports',
                                key: 'dismissedCount'
                            }
                        ]"
                        icon='mdi:flag-variant' 
                        linkName="View Post Reports" 
                        href="/moderator/post-reports"
                        totalLabel="total post reports" 
                    />
                </div>
            </div>
        </section>

        <section class="antialiased mt-2 md:mt-4">
            <div class="p-2 sm:p-4">
                <div class="bg-white rounded-md shadow-sm p-4 border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Top 3 Categories</h2>
                        <Link 
                            href="/moderator/category"
                            class="flex items-center font-semibold text-sm text-gray-600 hover:text-primary-200"
                        >
                            View All Categories 
                            <Icon icon="formkit:arrowright" class="w-6 h-6 ml-1 text-primary font-bold"/>
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="(category, index) in topCategories.slice(0, 3)" :key="category.id" class="border border-primary/30 bg-gradient-to-br from-primary/5 to-primary/10 rounded-md p-2 md:p-6 transition-transform duration-300 hover:-translate-y-1 relative">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-white rounded-lg shadow-sm">
                                    <img 
                                        :src="getCategoryImageUrl(category.category_image)"
                                        :alt="category.name"
                                        class="h-12 w-12 object-cover rounded-md"
                                    />
                                </div>
                                <span class="text-xl font-bold text-primary">
                                    #{{ index + 1 }}
                                </span>
                            </div>
                            
                            <h3 class="text-sm md:text-lg font-semibold text-gray-900 mb-1">
                                {{ category.name }}
                            </h3>
                            <p class="text-[11px] md:text-sm text-gray-600 mb-4">
                                {{ category.count }} total posts
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chart section -->
        <section class="antialiased mt-2 md:mt-4">
            <div class="grid grid-cols-1 p-2 sm:p-4">
                <ChartCards name="User Posts " class="min-h-[500px]">
                    <div class="h-full">
                        <chartTrend :post-stats="postStats" />
                    </div>
                </ChartCards>
            </div>
        </section>
    </ModeratorLayout>
</template>
