<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { Icon } from "@iconify/vue";

</script>

<template>
    <Link class="flex p-2 sm:p-4 gap-2 border border-gray-300 rounded-md mb-2" href="/trade">
        <div class="pt-5">
            <img class="max-w-[45px] sm:w-12 sm:h-12 rounded-full"
                src="https://darrenjameseeley.files.wordpress.com/2014/09/expendables3.jpeg"
                alt="profile image" />
        </div>
        <div class="px-2 sm:px-6">
            <p class="font-bold text-md">All Trade Notifications</p>
            <p class="text-xs text-gray-500 font-regular">
                Lorem ipsum dolor sit amet consectetur adipisicing elit.
                Tenetur neque error laudantium libero sunt quasi
                consequatur. Perferendis ab, iusto laudantium iure fugit
                nam ad distinctio quisquam ipsam ea error sequi!
            </p>
        </div>
    </Link>

</template>
