<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from "@iconify/vue";

import Drawer from 'primevue/drawer';

const props = defineProps({
    feedbacks: Object
});

const visibleBottom = ref(false);
const currentFeedback = ref(null);

const openDrawer = (feedback) => {
    currentFeedback.value = feedback; 
    visibleBottom.value = true; 
};
</script>

<template>
    <div v-if="!props.feedbacks || props.feedbacks.length === 0" 
         class="flex flex-col items-center justify-center mx-10 p-10 text-center border border-gray-200 rounded-md">
        <Icon icon="material-symbols:feedback-outline" class="w-16 h-16 text-gray-400 mb-4" />
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Suggestion Posted</h3>
        <p class="text-sm text-gray-500">There are currently no suggestion suggestions available.</p>
    </div>

    <!-- Existing feedback v-for loop -->
    <div v-else v-for="feedback in props.feedbacks" :key="feedback.id" 
         class="flex p-2 sm:p-4 gap-2 border border-gray-300 rounded-md mb-2"
         @click="openDrawer(feedback)">
        <div class="pt-5">
            <img class="max-w-[45px] sm:w-12 sm:h-12 rounded-full"
                src="https://darrenjameseeley.files.wordpress.com/2014/09/expendables3.jpeg"
                alt="profile image" />
            </div>
            <div class="px-2 sm:px-6">
                <p class="font-bold text-md">{{ feedback.title }}</p>
                <p class="text-xs text-gray-500 font-regular">Suggested By: <span class="font-bold">{{ feedback.user }}</span></p>
                <small class="text-xs text-gray-500 font-regular ">{{ feedback.created_at }}</small>
                <p class="text-xs mt-5 text-gray-500 font-regular">
                {{ feedback.feedback }}
            </p>
            <br>
            <p class="text-xs text-gray-500 font-regular">Suggested By: 
                <span class="font-bold">{{ feedback.user }}</span>
            </p>
        </div>
    </div>

    <Drawer v-model:visible="visibleBottom" 
            :header="currentFeedback ? currentFeedback.title : ''" 
            position="bottom" 
            style="height: 50%; background-color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; box-shadow: 0 -2px 10px rgba(0,0,0,0.2);"
            :modal="true">

            <div class="p-4">
                <p class="text-md font-semibold mb-2">{{ currentFeedback ? currentFeedback.feedback : '' }}</p>
                <small class="text-xs text-gray-500 font-regular">{{ currentFeedback ? currentFeedback.created_at : '' }}</small>
                <br>
                <p class="text-xs text-gray-500 font-regular mt-2">Suggested By: 
                    <span class="font-bold">{{ currentFeedback ? currentFeedback.user : '' }}</span>
                </p>
            </div>

            <div class="flex justify-end p-4 border-t border-gray-200">
                <Button label="Close" @click="visibleBottom = false" class="p-button-secondary" />
            </div>
    </Drawer>

</template>