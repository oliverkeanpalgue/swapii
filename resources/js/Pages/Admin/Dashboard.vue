<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import CategoryCards from "@/Components/CategoryCards.vue";
import { Icon } from '@iconify/vue';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AdminCards from "@/Components/AdminCards.vue";
import TransactionChart from "./shared/transactionChart.vue";
import ReportChart from "./shared/reportChart.vue";
import ChartCards from "@/Components/ChartCards.vue";
import FeedbackCards from "@/Components/FeedbackCards.vue";
import Drawer from 'primevue/drawer';

const props = defineProps({
    stats: Object,
    reportStats: {
        type: Object,
        default: () => ({
            labels: [],
            data: [],
            topConcerns: []
        })
    },
    feedbacks: Array,
    transactionStats: Object
});

const user_role = ref('admin');
const visibleBottom = ref(false);
const currentFeedback = ref(null);
onMounted(()=>{
    console.log(props.transactionStats);
})
const openDrawer = (feedback) => {
    currentFeedback.value = feedback;
    visibleBottom.value = true;
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :user_role="user_role">
        <!-- Categories section -->
        <section class="antialiased">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-2 sm:p-4 h-max">
                <div class="row-span-2">
                    <AdminCards 
                        entity="Users Overview" 
                        :value="stats.totalUsers" 
                        :widgetType="'two_column'"
                        :stats="{
                            moderatorCount: stats.moderatorCount,
                            userCount: stats.userCount
                        }"
                        :statsConfig="[
                            {
                                icon: 'carbon:user-profile',
                                iconColor: 'text-green-600',
                                label: 'Moderators',
                                key: 'moderatorCount'
                            },
                            {
                                icon: 'ph:users',
                                iconColor: 'text-yellow-600',
                                label: 'Users',
                                key: 'userCount'
                            }
                        ]"
                        icon='mdi:users' 
                        linkName="Manage Users" 
                        href="/admin/user-management"
                        totalLabel="total users" 
                    />
                </div>
                <div class="row-span-2">
                    <AdminCards 
                        entity="Reported Users Overview" 
                        :widgetType="'three_column'"
                        :value="stats.totalReports" 
                        :stats="{
                            pendingCount: stats.pendingReports,
                            processedCount: stats.processedReports,
                            dismissedCount: stats.dismissedReports
                        }"
                        :statsConfig="[
                            {
                                icon: 'material-symbols:pending-actions',
                                iconColor: 'text-yellow-600',
                                label: 'Pending',
                                key: 'pendingCount'
                            },
                            {
                                icon: 'material-symbols:check-circle',
                                iconColor: 'text-green-600',
                                label: 'Processed',
                                key: 'processedCount'
                            },
                            {
                                icon: 'material-symbols:block',
                                iconColor: 'text-red-600',
                                label: 'Dismissed',
                                key: 'dismissedCount'
                            }
                        ]"
                        icon='mdi:flag-variant' 
                        linkName="View Reports" 
                        href="/admin/reports"
                        totalLabel="total reports" 
                    />
                </div>
            </div>
        </section>

        <!-- Transaction Chart Section -->
        <section class="antialiased mt-2 md:mt-4">
            <div class="grid grid-cols-1 p-2 sm:p-4">
                <div class="bg-white p-4 rounded-lg shadow">
                    <ChartCards name="Transactions">
                        <TransactionChart class="h-full" :transactionData="props.transactionStats" />
                    </ChartCards>
                </div>
            </div>
        </section>

        <!-- Report Statistics Section -->
        <section class="antialiased mt-2 md:mt-4">
            <div class="grid grid-cols-1 p-2 sm:p-4">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Report Statistics</h3>
                    <ReportChart :reportStats="reportStats" />
                </div>
            </div>
        </section>

        <section v-if="feedbacks && feedbacks.length > 0" class="antialiased mt-2 md:mt-4 mb-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 sm:px-4 mb-2">
                App Suggestions
            </h2>

           <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:px-4 py-2 h-max">
                <FeedbackCards 
                    v-for="feedback in feedbacks" 
                    :key="feedback.id"
                    :title="feedback.title"
                    :feedback="feedback.feedback"
                    :user="feedback.user"
                    :created_at="feedback.created_at"
                    @click="openDrawer(feedback)"
                />
            </div>

            <Drawer v-model:visible="visibleBottom" 
                :header="currentFeedback ? currentFeedback.title : ''"
                position="bottom" 
                :modal="true" 
                class="p-4" 
                :style="{
                    height: '50%',
                    backgroundColor: 'white',
                    borderTopLeftRadius: '16px',
                    borderTopRightRadius: '16px',
                    boxShadow: '0 -2px 10px rgba(0,0,0,0.2)'
                }"
            >
                <div class="p-4">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="p-3">
                            <Icon icon="mingcute:user-4-fill" class="w-14 h-14 text-gray-600" aria-label="Profile icon" />
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
        </section>
    </AdminLayout>
</template>
