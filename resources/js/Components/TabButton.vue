<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

</script>

<template>
    <button class="cursor-pointer hover:text-primary border-b-2 hover:border-primary text-sm text-gray-500 text-semibold hover:text-primary" @click="page = 'all_notif'">
        <slot></slot>
    </button>
</template>
