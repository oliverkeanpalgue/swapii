<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-white">
    <div class="mx-auto w-full max-w-screen-xl p-2 py-2 lg:py-8">
        <div class="flex flex-row justify-between items-center">
            <!-- Logo -->
            <div class="mb-6 sm:mb-0">
                <Link href="/" class="flex items-center font-bold text-2xl text-primary">
                    Swapii
                </Link>
            </div>
            <!-- Copyright -->
            <div class="sm:flex sm:items-center sm:justify-between">
                <span class="text-sm text-gray-500 sm:text-center">
                    © 2024 <a href="/" class="hover:underline">Swapii™</a>. All Rights Reserved.
                </span>
            </div>
        </div>
    </div>
</footer>


</template>