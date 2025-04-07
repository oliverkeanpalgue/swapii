<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    items: Object
})

onMounted(() => {
    console.log(props.items)
})
</script>


<template>
    <template v-for="link in props.items.links" :key="link.label">

        <Link class="p-2 rounded " :class="link.active ? 'text-yellow-300' : ''" v-if="link.url" v-html="link.label"
            :href="link.url" />
        
        <span class="p-2 m-3 text-gray-300 rounded" v-else v-html="link.label"></span>
    </template>
</template>
