<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Chart } from 'chart.js/auto';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const props = defineProps({
    reportStats: {
        type: Object,
        required: true,
        default: () => ({
            labels: [],
            datasets: []
        })
    }
});

const selectedRange = ref('monthly');
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const customDateRange = ref({
    startDate: null,
    endDate: null
});
const topConcerns = ref([]);

const yearOptions = ref([]);
const monthOptions = ref([
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
]);

let chart = null;

const initChart = (data) => {
    const ctx = document.getElementById('reportChart');
    if (!ctx) return;

    if (chart) {
        chart.destroy();
    }

    chart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 1
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
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });
};

const fetchData = async () => {
    try {
        const params = {
            range: selectedRange.value,
            year: selectedYear.value,
            month: selectedMonth.value
        };

        if (selectedRange.value === 'custom' && customDateRange.value.startDate && customDateRange.value.endDate) {
            params.start_date = formatDateForAPI(customDateRange.value.startDate);
            params.end_date = formatDateForAPI(customDateRange.value.endDate);
        }

        const response = await axios.get('/api/report-statistics', { params });
        initChart(response.data.chartData);
        topConcerns.value = response.data.topConcerns;
    } catch (error) {
        console.error('Error fetching report statistics:', error);
    }
};

const formatDateForAPI = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};

const initYearOptions = () => {
    const currentYear = new Date().getFullYear();
    const startYear = currentYear - 5;
    yearOptions.value = [];
    for (let year = currentYear; year >= startYear; year--) {
        yearOptions.value.push({
            label: year.toString(),
            value: year
        });
    }
};

const getChartTitle = () => {
    switch (selectedRange.value) {
        case 'yearly':
            return 'Yearly Report Statistics (Last 5 Years)';
        case 'monthly':
            return `Monthly Report Statistics (${selectedYear.value})`;
        case 'daily':
            const monthName = monthOptions.value.find(m => m.value === selectedMonth.value)?.label;
            return `Daily Report Statistics (${monthName} ${selectedYear.value})`;
        case 'custom':
            if (customDateRange.value.startDate && customDateRange.value.endDate) {
                return `Report Statistics (${dateFormat(customDateRange.value.startDate)} - ${dateFormat(customDateRange.value.endDate)})`;
            }
            return 'Custom Range Report Statistics';
        default:
            return 'Report Statistics';
    }
};

const dateFormat = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

watch([selectedRange, selectedYear, selectedMonth], () => {
    fetchData();
});

watch(() => customDateRange.value, () => {
    if (selectedRange.value === 'custom' && 
        customDateRange.value.startDate && 
        customDateRange.value.endDate) {
        fetchData();
    }
}, { deep: true });

onMounted(() => {
    initYearOptions();
    nextTick(() => {
        if (props.reportStats) {
            initChart(props.reportStats);
        }
        fetchData();
    });
});

onUnmounted(() => {
    if (chart) {
        chart.destroy();
    }
});
</script>

<template>
    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ getChartTitle() }}</h2>
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

                    <!-- Year Selection -->
                    <Dropdown
                        v-if="selectedRange !== 'custom' && selectedRange !== 'yearly'"
                        v-model="selectedYear"
                        :options="yearOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full sm:w-32"
                        inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                    />

                    <!-- Month Selection -->
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
                    <div v-if="selectedRange === 'custom'" class="flex flex-col sm:flex-row gap-4">
                        <Calendar
                            v-model="customDateRange.startDate"
                            :maxDate="customDateRange.endDate || new Date()"
                            :showIcon="true"
                            dateFormat="mm/dd/yy"
                            placeholder="Start Date"
                            class="w-full sm:w-40"
                            inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                        />
                        <Calendar
                            v-model="customDateRange.endDate"
                            :minDate="customDateRange.startDate"
                            :maxDate="new Date()"
                            :showIcon="true"
                            dateFormat="mm/dd/yy"
                            placeholder="End Date"
                            class="w-full sm:w-40"
                            inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart and Top Concerns Section -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Chart Section -->
            <div class="flex-1 bg-white rounded-lg shadow-sm p-4">
                <div class="min-h-[400px]">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>

            <!-- Top Concerns Section -->
            <div class="lg:w-80 bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Concerns</h3>
                <div class="space-y-4">
                    <div v-if="topConcerns.length === 0" class="text-gray-500 text-center py-4">
                        No concerns reported for this period
                    </div>
                    <div v-for="(concern, index) in topConcerns" :key="index" 
                        class="flex items-center p-3 rounded-lg"
                        :class="[
                            index === 0 ? 'bg-amber-50' : '',
                            index === 1 ? 'bg-gray-50' : '',
                            index === 2 ? 'bg-orange-50' : ''
                        ]"
                    >
                        <div class="flex items-center justify-center w-8 h-8 rounded-full mr-3"
                            :class="[
                                index === 0 ? 'bg-amber-100 text-amber-700' : '',
                                index === 1 ? 'bg-gray-100 text-gray-700' : '',
                                index === 2 ? 'bg-orange-100 text-orange-700' : ''
                            ]"
                        >
                            <span class="font-semibold">#{{ index + 1 }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ concern.concern }}</h4>
                            <p class="text-sm text-gray-600">{{ concern.count }} reports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.p-dropdown) {
    width: 100%;
}

:deep(.p-calendar) {
    width: 100%;
}

:deep(.p-calendar .p-inputtext) {
    width: 100%;
}
</style>
