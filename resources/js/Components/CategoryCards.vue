<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    category: {
        type: Object,
        required: true,
        default: () => ({})
    },
    message: {
        type: String,
    },
    imgSrc: {
        type: String,
    }
});
</script>

<template>
    <Link v-if="props.category && props.category.id" 
          :href="'/item-category/' + props.category.id" 
          class="group relative flex flex-col items-center p-2 text-xs font-medium transition-transform duration-100 ease-in-out hover:text-primary hover:scale-105">
        <div class="relative">
            <img class="object-scale-down mb-2 border border-gray-400 rounded-full h-14 w-14 md:h-16 md:w-16 lg:h-20 lg:w-20 hover:border-primary transition-all duration-300"
                alt="Rounded Image" :src="imgSrc">
            <div class="absolute inset-0 flex flex-col items-center justify-center rounded-full opacity-0 group-hover:opacity-80 bg-black transition-opacity duration-300 h-14 w-14 md:h-16 md:w-16 lg:h-20 lg:w-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span class="text-primary text-[8px] md:text-[10px] mt-1">View Items</span>
            </div>
        </div>
        <span class="text-center text-[11px] md:text-xs mt-1">{{ category.category }}</span>
    </Link>
    <div v-else class="group relative flex flex-col items-center p-2">
        <p>Category not available</p>
    </div>
</template>
