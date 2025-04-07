<script setup>
import { onMounted, ref } from 'vue';
const model = defineModel({
    type: String,
    required: true,
});

const emit = defineEmits(['update:modelValue']);

const textareaRef = ref(null);

onMounted(() => {
    textareaRef.value.focus();
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <textarea
        ref="textareaRef"
        v-model="model"
        @input="handleInput"
        rows="5"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-primary-200 focus:border-primary-200 block w-full p-2.5"
        placeholder="Your description here"
    ></textarea>
</template>
