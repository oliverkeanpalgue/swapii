<script setup>
import Footer from "@/Components/Footer.vue";
import Navbar from "@/Components/Navbar.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { onMounted } from "vue";


</script>

<template>
    <div>
        <div class="min-h-screen">
            <Navbar />
            dwadwadawd
            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <Footer />
        </div>
    </div>
</template>
