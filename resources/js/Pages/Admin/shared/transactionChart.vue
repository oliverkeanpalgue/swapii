<template>
    <div class="bg-white rounded-lg shadow-sm p-4 mb-8">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h2 class="text-lg font-semibold whitespace-nowrap">{{ getChartTitle() }}</h2>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <Dropdown
                        v-model="selectedRange"
                        :options="[
                            { label: 'Yearly', value: 'yearly' },
                            { label: 'Monthly', value: 'monthly' },
                            { label: 'Daily', value: 'daily' },
                            { label: 'Custom Range', value: 'custom' }
                        ]"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full sm:w-40"
                        inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                    />

                    <!-- Year Selection (for monthly and daily) -->
                    <Dropdown
                        v-if="selectedRange !== 'custom' && selectedRange !== 'yearly'"
                        v-model="selectedYear"
                        :options="yearOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full sm:w-32"
                        inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                    />

                    <!-- Month Selection (for daily view) -->
                    <Dropdown
                        v-if="selectedRange === 'daily'"
                        v-model="selectedMonth"
                        :options="monthOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full sm:w-40"
                        inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                    />

                    <!-- Custom Date Range -->
                    <div v-if="selectedRange === 'custom'" class="flex flex-col sm:flex-row items-center gap-2 w-full">
                        <Calendar 
                            v-model="customDateRange.startDate" 
                            :maxDate="customDateRange.endDate || new Date()"
                            :showIcon="true"
                            dateFormat="MM dd, yy"
                            placeholder="Start Date"
                            class="w-full sm:w-44"
                            inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                        />
                        <span class="hidden sm:block">to</span>
                        <Calendar 
                            v-model="customDateRange.endDate" 
                            :minDate="customDateRange.startDate"
                            :maxDate="new Date()"
                            :showIcon="true"
                            dateFormat="MM dd, yy"
                            placeholder="End Date"
                            class="w-full sm:w-44"
                            inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                        />
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="relative" style="height: 350px; max-height: 400px; margin-bottom: 1rem;">
                <canvas id="transactionChart" class="!w-full !h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter Drawer -->
    <Drawer 
        v-model:visible="drawerVisible" 
        :modal="true"
        :showCloseIcon="false"
        position="right"
        class="w-full sm:w-[400px]"
    >
        <template #header>
            <div class="flex justify-between items-center w-full px-6 py-3 ">
                <div class="flex items-center gap-2">
                    <Icon icon="heroicons:funnel" class="w-4 h-4 text-gray-600" />
                    <h3 class="text-base font-semibold text-gray-900">Filter Options</h3>
                </div>
                <button 
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    @click="drawerVisible = false"
                >
                    <Icon icon="heroicons:x-mark" class="w-4 h-4 text-gray-500" />
                </button>
            </div>
        </template>

        <div class="p-5 flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-1.5">
                    <button 
                        v-for="option in [
                            { value: 'monthly', label: 'Monthly Overview', icon: 'heroicons:chart-bar' },
                            { 
                                value: 'daily', 
                                label: 'Daily View', 
                                icon: 'heroicons:calendar',
                                desc: 'Daily breakdown for current month'
                            },
                            { value: 'custom', label: 'Custom Range', icon: 'heroicons:calendar-days' },
                            { value: 'yearly', label: 'Yearly Overview', icon: 'heroicons:calendar' }
                        ]"
                        :key="option.value"
                        class="flex items-center gap-2 p-2.5 rounded-lg transition-colors text-left w-full"
                        :class="selectedRange === option.value ? 
                            'bg-primary/10 text-primary border border-primary/20' : 
                            'hover:bg-gray-50 text-gray-700 border border-transparent'"
                        @click="selectedRange = option.value"
                    >
                        <Icon :icon="option.icon" class="w-4 h-4" />
                        <span class="text-sm font-medium">{{ option.label }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Drawer>
</template>

<script setup>
import { Chart } from 'chart.js/auto';
import { onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import axios from 'axios';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Drawer from 'primevue/drawer';
import { Icon } from '@iconify/vue';

const drawerVisible = ref(false);
const selectedRange = ref('monthly');
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const customDateRange = ref({
    startDate: null,
    endDate: null
});

const chartData = ref({
    labels: [],
    datasets: []
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
});

let chart = null;

const initChart = () => {
    const ctx = document.getElementById('transactionChart');
    if (!ctx) return;

    if (chart) {
        chart.destroy();
    }

    chart = new Chart(ctx, {
        type: 'bar',
        data: chartData.value,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        boxWidth: 6
                    }
                }
            }
        }
    });
};

const updateChart = () => {
    if (chart) {
        chart.data = chartData.value;
        chart.update('none');
    }
};

const fetchData = async () => {
    try {
        const params = {
            range: selectedRange.value
        };

        if (selectedRange.value === 'monthly') {
            params.year = selectedYear.value;
        } else if (selectedRange.value === 'daily') {
            params.year = selectedYear.value;
            params.month = selectedMonth.value;
        } else if (selectedRange.value === 'custom' && customDateRange.value.startDate && customDateRange.value.endDate) {
            params.start_date = customDateRange.value.startDate.toISOString().split('T')[0];
            params.end_date = customDateRange.value.endDate.toISOString().split('T')[0];
        }

        const response = await axios.get('/admin/monthly-transactions', { params });
        const data = response.data;

        if (!data || !Array.isArray(data)) {
            console.error('Invalid data format received:', data);
            return;
        }

        // Create a new chartData object
        const newChartData = {
            labels: data.map(item => item.period),
            datasets: [
                {
                    label: 'Total Trades',
                    backgroundColor: '#6366F1',
                    borderColor: '#6366F1',
                    data: data.map(item => item.total_trades),
                    tension: 0.4
                },
                {
                    label: 'Pending',
                    backgroundColor: '#F59E0B',
                    borderColor: '#F59E0B',
                    data: data.map(item => item.pending_trades),
                    tension: 0.4
                },
                {
                    label: 'Processing',
                    backgroundColor: '#3B82F6',
                    borderColor: '#3B82F6',
                    data: data.map(item => item.processing_trades),
                    tension: 0.4
                },
                {
                    label: 'Completed',
                    backgroundColor: '#10B981',
                    borderColor: '#10B981',
                    data: data.map(item => item.successful_trades),
                    tension: 0.4
                },
                {
                    label: 'Cancelled',
                    backgroundColor: '#EF4444',
                    borderColor: '#EF4444',
                    data: data.map(item => item.cancelled_trades),
                    tension: 0.4
                },
                {
                    label: 'Rejected',
                    backgroundColor: '#F43F5E',
                    borderColor: '#F43F5E',
                    data: data.map(item => item.rejected_trades),
                    tension: 0.4
                }
            ]
        };

        // Update chartData in nextTick to ensure reactivity
        nextTick(() => {
            chartData.value = newChartData;
            updateChart();
        });
    } catch (error) {
        console.error('Error fetching data:', error);
        if (error.response) {
            console.error('Response error:', error.response.data);
        }
    }
};

// Initialize chart when component is mounted
onMounted(() => {
    nextTick(() => {
        initChart();
        fetchData();
    });
});

// Watch for changes in the range selection
watch(selectedRange, (newValue) => {
    if (newValue === 'custom') {
        // Initialize custom date range with current month
        const today = new Date();
        customDateRange.value = {
            startDate: new Date(today.getFullYear(), today.getMonth(), 1),
            endDate: today
        };
    }
    fetchData();
});

// Watch for changes in year and month selection
watch([selectedYear, selectedMonth], () => {
    if (selectedRange.value !== 'custom') {
        fetchData();
    }
});

// Watch for changes in custom date range
watch(customDateRange, (newValue) => {
    if (selectedRange.value === 'custom' && 
        newValue.startDate && 
        newValue.endDate) {
        fetchData();
    }
}, { deep: true });

const monthLabels = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const monthOptions = [
    { label: 'January', value: 1 },
    { label: 'February', value: 2 },
    { label: 'March', value: 3 },
    { label: 'April', value: 4 },
    { label: 'May', value: 5 },
    { label: 'June', value: 6 },
    { label: 'July', value: 7 },
    { label: 'August', value: 8 },
    { label: 'September', value: 9 },
    { label: 'October', value: 10 },
    { label: 'November', value: 11 },
    { label: 'December', value: 12 }
];

// Generate year options (current year and 4 years back)
const yearOptions = ref([]);
const initYearOptions = () => {
    const currentYear = new Date().getFullYear();
    const years = Array.from({length: 5}, (_, i) => currentYear - i);
    yearOptions.value = years.map(year => ({
        label: year.toString(),
        value: year
    }));
};

onMounted(() => {
    initYearOptions();
});

const getChartTitle = () => {
    if (selectedRange.value === 'custom') {
        const start = customDateRange.value.startDate ? new Date(customDateRange.value.startDate).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }) : '';
        const end = customDateRange.value.endDate ? new Date(customDateRange.value.endDate).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }) : '';
        return `Transaction Overview (${start} - ${end})`;
    } else if (selectedRange.value === 'monthly') {
        return `Transaction Overview - ${selectedYear.value}`;
    }
    const month = monthOptions.find(m => m.value === selectedMonth.value);
    return `Transaction Overview - ${month?.label || ''} ${selectedYear.value}`;
};

const dateFormat = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<style scoped>
:deep(.p-drawer) {
    max-width: 90vw;
}

:deep(.p-drawer-content) {
    width: 100%;
}

:deep(.p-dropdown), :deep(.p-calendar) {
    width: 100%;
}

:deep(.p-dropdown-panel) {
    max-width: 90vw;
}

@media (max-width: 640px) {
    :deep(.p-drawer) {
        width: 100vw !important;
    }
}
</style>
