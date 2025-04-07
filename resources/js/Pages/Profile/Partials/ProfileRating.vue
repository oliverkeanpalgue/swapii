<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import DataView from 'primevue/dataview';
import Rating from 'primevue/rating';

const props = defineProps({
    ratings: {
        type: Array,
        required: true,
        default: () => [],
    },
    averageRating: {
        type: [Number, String],
        required: true,
        default: '0.00',
    },
    ratingsCount: {
        type: Number,
        required: true,
        default: 0,
    },
});

const sortOptions = ref([
    { name: 'Most Recent', value: 'recent' },
    { name: 'Highest Rated', value: 'highest' },
    { name: 'Lowest Rated', value: 'lowest' },
]);

const selectedSort = ref('recent');

const sortedRatings = computed(() => {
    return [...props.ratings].sort((a, b) => {
        if (selectedSort.value === 'recent') {
            return new Date(b.created_at) - new Date(a.created_at);
        } else if (selectedSort.value === 'highest') {
            return b.rating - a.rating;
        } else {
            return a.rating - b.rating;
        }
    });
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.charAt(0).toUpperCase();
};

const getUserDisplayName = (rating) => {
    return rating.user.name;
};
</script>

<template>
    <div class="card">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-4 mb-6">
            <h2 class="text-xl sm:text-2xl font-semibold whitespace-nowrap">Ratings & Reviews</h2>
            <div class="flex items-center gap-2">
                <span class="text-base sm:text-lg font-medium">{{ averageRating }}</span>
                <Rating :modelValue="Number(averageRating)" :readonly="true" :cancel="false" />
                <span class="text-sm sm:text-base text-gray-500 whitespace-nowrap">({{ ratingsCount }} reviews)</span>
            </div>
        </div>

        <DataView :value="sortedRatings" :paginator="true" :rows="5">
            <template #list="slotProps">
                <div class="flex flex-col">
                    <div v-for="(rating, index) in slotProps.items" :key="rating.id">
                        <div class="flex flex-col p-4" :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <div 
                                        class="flex justify-center items-center w-8 h-8 rounded-full text-white text-sm font-medium"
                                        :class="rating.is_anonymous ? 'bg-gray-500' : 'bg-primary'"
                                    >
                                        {{ getInitials(getUserDisplayName(rating)) }}
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ getUserDisplayName(rating) }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Rating v-model="rating.rating" :readonly="true" :cancel="false" />
                                    <span class="text-sm text-gray-500">
                                        {{ formatDate(rating.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-gray-600 mt-2">{{ rating.description }}</p>
                        </div>
                    </div>
                </div>
            </template>
            <template #empty>
                <div class="text-center py-8 text-gray-500">
                    No ratings yet
                </div>
            </template>
        </DataView>
    </div>
</template>

<style scoped>
:deep(.p-dataview .p-dataview-content) {
    background: transparent;
    border: none;
    padding: 0;
}

:deep(.p-dataview .p-paginator) {
    background: transparent;
    border: none;
    padding: 1rem 0;
}

:deep(.p-dataview) {
    border: none;
    background: transparent;
}
</style>