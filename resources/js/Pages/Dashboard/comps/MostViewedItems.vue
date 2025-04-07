<script setup>
import { onMounted } from 'vue';
import SuggestedCardItems from '@/Components/SuggestedCardItems.vue';
import Views from './Views.vue';
import CardItems from '@/Components/CardItems.vue';

const props = defineProps({
    items: Object
})



</script>

<template>



    <!-- {{ props.most_viewed_items.value }} -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6">
            <template v-if="props.items && props.items.length > 0">
                <template v-for="item in props.items" :key="item.id">
                    <CardItems
                        :item="item"
                    />

                </template>
            </template>
            <p v-else class="col-span-full text-center text-gray-500">
                most viewed items unavailable
            </p>
        </div>

</template>
