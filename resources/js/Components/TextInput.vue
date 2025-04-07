<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <!-- <input
        class="border-gray-300 p-2.5 focus:border-primary-200 focus:ring-primary-200 rounded-md shadow-sm"
        v-model="model"
        ref="input"
    /> -->

    <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary focus:border-primary block w-full p-2.5" 
    v-model="model"
    ref="input"
    />
</template>
