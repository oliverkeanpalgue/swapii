<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: true,
});

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    options: {
        type: Array,
        required: true
    },
    optionLabel: {
        type: String,
        default: 'label'
    },
    optionValue: {
        type: String,
        default: 'value'
    },
    defaultOption: {
        type: Boolean,
        default: true
    }
});

const selectRef = ref(null);

onMounted(() => {
    selectRef.value.focus();
});
</script>

<template>
    <select
        ref="selectRef"
        v-model="model"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-200 focus:border-primary-200 block w-full p-2.5"
    >
        <option v-if="defaultOption" value="" disabled selected>{{ placeholder }}</option>
        <option 
            v-for="option in options" 
            :key="option[optionValue]" 
            :value="option[optionValue]"
        >
            {{ option[optionLabel] }}
        </option>
    </select>
</template>
