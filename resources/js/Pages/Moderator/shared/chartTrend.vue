<template>
    <div class="bg-white p-4 rounded-lg shadow-sm">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h2 class="text-lg font-semibold whitespace-nowrap">{{ getChartTitle() }}</h2>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                    <!-- Range Selection -->
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

                    <!-- Year Selection (for yearly, monthly and daily) -->
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
                    <div v-if="selectedRange === 'custom'" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full sm:w-auto">
                        <Calendar 
                            v-model="customDateRange.startDate" 
                            :maxDate="customDateRange.endDate || new Date()"
                            :minDate="new Date(Date.now() - 90 * 24 * 60 * 60 * 1000)"
                            :showIcon="true"
                            dateFormat="MM dd, yy"
                            placeholder="Start Date"
                            class="w-full sm:w-44"
                            inputClass="h-8 text-sm rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                        />
                        <span class="hidden sm:inline">to</span>
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
            <div class="h-[300px] sm:h-[350px] md:h-[400px] lg:h-[500px] w-full relative">
                <canvas id="trendChart" class="!w-full !h-full"></canvas>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Chart } from 'chart.js/auto';
import { onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import axios from 'axios';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';

const selectedRange = ref('yearly');
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const customDateRange = ref({
    startDate: null,
    endDate: null
});

const labels = ref([]);
const chartData = ref({
    pending: [],
    approved: [],
    rejected: []
});

const monthLabels = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const monthOptions = monthLabels.map((label, index) => ({
    label,
    value: index + 1
}));

const yearOptions = ref([]);

let chart = null;

const initChart = () => {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    if (chart) {
        chart.destroy();
    }

    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.value,
            datasets: [
                {
                    label: 'Pending Posts',
                    data: chartData.value.pending,
                    backgroundColor: '#1e40af', // Darker blue
                    borderColor: '#1e40af',
                    borderWidth: 1
                },
                {
                    label: 'Rejected Posts',
                    data: chartData.value.rejected,
                    backgroundColor: '#991b1b', // Darker red
                    borderColor: '#991b1b',
                    borderWidth: 1
                },
                {
                    label: 'Approved Posts',
                    data: chartData.value.approved,
                    backgroundColor: '#166534', // Darker green
                    borderColor: '#166534',
                    borderWidth: 1
                }
            ]
        },
        options: {
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
        }
    });
};

const updateChart = () => {
    if (chart) {
        chart.data.labels = labels.value;
        chart.data.datasets[0].data = chartData.value.pending;
        chart.data.datasets[1].data = chartData.value.rejected;
        chart.data.datasets[2].data = chartData.value.approved;
        chart.update('none');
    }
};

const fetchData = async () => {
    try {
        const params = {
            range: selectedRange.value
        };

        if (selectedRange.value === 'yearly') {
            params.year = selectedYear.value;
        } else if (selectedRange.value === 'monthly') {
            params.year = selectedYear.value;
        } else if (selectedRange.value === 'daily') {
            params.year = selectedYear.value;
            params.month = selectedMonth.value;
        } else if (selectedRange.value === 'custom' && customDateRange.value.startDate && customDateRange.value.endDate) {
            params.start_date = customDateRange.value.startDate.toISOString().split('T')[0];
            params.end_date = customDateRange.value.endDate.toISOString().split('T')[0];
        } else {
            return; // Don't fetch if dates aren't selected for custom range
        }

        const response = await axios.get('/moderator/fetch-monthly-figures', { params });
        const chartValues = response.data.data;

        if (!chartValues || !Array.isArray(chartValues)) {
            console.error('Invalid data format received:', response.data);
            return;
        }

        if (selectedRange.value === 'custom') {
            labels.value = chartValues.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            chartData.value = {
                pending: chartValues.map(item => item.pending || 0),
                approved: chartValues.map(item => item.approved || 0),
                rejected: chartValues.map(item => item.rejected || 0)
            };
        } else if (selectedRange.value === 'daily') {
            // For daily view, show days 1-31
            const daysInMonth = new Date(selectedYear.value, selectedMonth.value, 0).getDate();
            labels.value = Array.from({ length: daysInMonth }, (_, i) => (i + 1).toString());
            
            // Initialize arrays with zeros
            const pending = new Array(daysInMonth).fill(0);
            const approved = new Array(daysInMonth).fill(0);
            const rejected = new Array(daysInMonth).fill(0);
            
            // Fill in actual data
            chartValues.forEach(item => {
                const dayIndex = parseInt(item.day) - 1;
                pending[dayIndex] = item.pending || 0;
                approved[dayIndex] = item.approved || 0;
                rejected[dayIndex] = item.rejected || 0;
            });
            
            chartData.value = { pending, approved, rejected };
        } else if (selectedRange.value === 'monthly') {
            // Monthly view
            labels.value = chartValues.map(item => monthLabels[item.month - 1]);
            chartData.value = {
                pending: chartValues.map(item => item.pending || 0),
                approved: chartValues.map(item => item.approved || 0),
                rejected: chartValues.map(item => item.rejected || 0)
            };
        } else if (selectedRange.value === 'yearly') {
            labels.value = chartValues.map(item => item.year.toString());
            chartData.value = {
                pending: chartValues.map(item => item.pending || 0),
                approved: chartValues.map(item => item.approved || 0),
                rejected: chartValues.map(item => item.rejected || 0)
            };
        }

        nextTick(() => {
            updateChart();
        });
    } catch (error) {
        console.error('Error fetching data:', error);
        if (error.response) {
            console.error('Response error:', error.response.data);
        }
    }
};

const initYearOptions = () => {
    const years = [2024, 2023, 2022, 2021, 2020];
    yearOptions.value = years.map(year => ({
        label: year.toString(),
        value: year
    }));
};

const getChartTitle = () => {
    switch (selectedRange.value) {
        case 'yearly':
            return `Posts Overview for ${selectedYear.value}`;
        case 'monthly':
            return `Monthly Posts Overview for ${selectedYear.value}`;
        case 'daily':
            return `Daily Posts Overview for ${monthLabels[selectedMonth.value - 1]} ${selectedYear.value}`;
        case 'custom':
            if (customDateRange.value.startDate && customDateRange.value.endDate) {
                return `Posts Overview from ${dateFormat(customDateRange.value.startDate)} to ${dateFormat(customDateRange.value.endDate)}`;
            }
            return 'Posts Overview';
        default:
            return 'Posts Overview';
    }
};

const dateFormat = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

watch([selectedRange, selectedYear, selectedMonth, customDateRange], () => {
    fetchData();
}, { deep: true });

onMounted(() => {
    initYearOptions();
    nextTick(() => {
        initChart();
        fetchData();
    });
});

onUnmounted(() => {
    if (chart) {
        chart.destroy();
    }
});
</script>

<style scoped>
:deep(.p-dropdown),
:deep(.p-calendar) {
    width: 100%;
}

:deep(.p-dropdown input),
:deep(.p-calendar input) {
    width: 100%;
}

:deep(.p-dropdown-panel),
:deep(.p-calendar-panel) {
    max-width: 90vw;
}

@media (max-width: 640px) {
    :deep(.p-dropdown-panel),
    :deep(.p-calendar-panel) {
        width: 100vw !important;
        max-width: 100vw;
        left: 0 !important;
    }
}
</style>
