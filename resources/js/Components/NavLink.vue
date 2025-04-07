<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

</script>

<template>
    <Link :href="href" 
          :class="{'inline-flex items-center px-1 pt-1 border-b-2 border-primary-200 text-sm font-medium leading-5 text-primary-200 focus:outline-none focus:border-primary-200 transition duration-150 ease-in-out pb-1.5': $page.url === href, 
                   'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-black hover:text-primary hover:border-primary focus:outline-none focus:text-primary focus:border-primary transition duration-150 ease-in-out pb-1.5': $page.url !== href}">
        <slot />
    </Link>
</template>
