<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import CardItem from '@/Components/CardItem.vue';
import CardItems from '@/Components/CardItems.vue';
import { onMounted } from 'vue';
const props = defineProps({
    items: Object
});

onMounted(() => {
})
</script>

<template>
    <Head title="Smart Suggestion"></Head>
    <AuthenticatedLayout>
        <h1 class="text-xl font-semibold text-gray-900">Smart Suggestion</h1>

        <section class="my-4 p-2 bg-gray-500 rounded-sm grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <!-- {{ props.items }} -->
            <div v-for="item in props.items" :key="item.id" class="bg-white rounded-sm p-2 shadow-sm">
                <CardItems :item="item" />
            </div>
        </section>
    </AuthenticatedLayout>
</template>