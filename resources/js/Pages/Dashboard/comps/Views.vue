<script setup>
import { usePoll } from '@inertiajs/vue3';



usePoll(3000)
const props = defineProps({
    views: Number
})
</script>

<template>
    {{ props.views }}
</template>
