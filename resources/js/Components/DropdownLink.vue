<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block px-5 py-3 my-1 w-full text-gray-600 rounded-md transition-all duration-500 ease-in-out hover:text-gray-800 hover:bg-gradient-to-t hover:from-orange-100 hover:to-white">
        <div class="flex justify-between items-center space-x-3">
            <slot />
        </div>
    </Link>
</template>
