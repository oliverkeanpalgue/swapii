<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";

const props = defineProps({
    notifications: Object
});

const notificationType = computed(() => {
    return {
        trade_req: { name: 'Trade Request', color: 'blue' },
        trade_accept: { name: 'Trade Accepted', color: 'teal' },
        trade_reject: { name: 'Trade Rejected', color: 'red' },
        trade_cancel: { name: 'Trade Cancelled', color: 'orange' },
        trade_complete: { name: 'Trade Completed', color: 'green' },
        item_unlist: { name: 'Item Unlisted', color: 'purple' },
    }
})

</script>

<template>
    <div class="p-1" v-for="notification in props.notifications.data" :key="notification.id" >
        <Link class="flex gap-2 p-2 mb-2 rounded-md border border-gray-300 hover:bg-gray-50" href="/trade">
        <div class="px-2 sm:px-6">
            <h1 class="font-bold text-md" :style="{ 'color': notificationType[notification.type]?.color || 'gray' }">
                {{ notificationType[notification.type]?.name || 'Notification' }}
            </h1>
            <small>{{notification.created_at}}</small>
            <p class="text-sm text-gray-600 font-regular">
                {{notification.data}}
            </p>
        </div>
    </Link>
    </div>

</template>
