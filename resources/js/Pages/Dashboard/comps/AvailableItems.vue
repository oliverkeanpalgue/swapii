<script setup>
import CardItems from '@/Components/CardItems.vue';
import { Deferred } from '@inertiajs/vue3';
import Paginator from 'primevue/paginator';
import SkeletonLoader from './SkeletonLoader.vue';



const props = defineProps({
    items: Object,

})
</script>

<template>
    <Deferred data="items">
        <template #fallback>
            <SkeletonLoader />
        </template>


        <div v-if="props.items && props.items.data && props.items.data.length > 0">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <template v-for="item in props.items.data" :key="item.id">
                    <CardItems :item="item" />
                </template>
            </div>


            <div class="flex justify-center mt-4">
                <Paginator
                :totalRecords="props.items.total"
                :rows="props.items.per_page"
                :rowsPerPageOptions="[10, 20, 50]"
            />
            </div>
        </div>
        <div v-else>
            <p>No items available.</p>
        </div>
    </Deferred>


</template>
