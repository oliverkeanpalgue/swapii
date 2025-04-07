<script setup>
import { ref, onMounted, watch } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import { Icon } from '@iconify/vue';

const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(localStorage.getItem('adminSidebarOpen') !== 'false');

watch(isSidebarOpen, (newValue) => {
    localStorage.setItem('adminSidebarOpen', newValue);
});
</script>
<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <Sidebar :is-sidebar-open="isSidebarOpen" @toggle-sidebar="isSidebarOpen = !isSidebarOpen"/>            
            <main class="transition-all duration-300" 
                  :class="{ 
                      'sm:ml-48': isSidebarOpen, 
                      'sm:ml-16': !isSidebarOpen 
                  }">
                <div class="p-4 mt-16">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
