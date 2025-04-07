<script setup>
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Dialog from 'primevue/dialog';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';
import Textarea from 'primevue/textarea';
import Rating from 'primevue/rating';
import DataView from 'primevue/dataview';
import ToggleSwitch from "primevue/toggleswitch";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import ConfirmDialog from 'primevue/confirmdialog';
import Menu from 'primevue/menu';
import Button from 'primevue/button';

const props = defineProps({
    ratings: Object,
    averageRating: {
        type: [Number, String],
        required: true,
        default: '0.00'
    },
    ratingsCount: {
        type: Number,
        required: true,
        default: 0
    }
});

const toast = useToast();
const editModal = ref(false);
const selectedRating = ref(null);

const sortOptions = ref([
    { name: 'Most Recent', value: 'recent' },
    { name: 'Highest Rated', value: 'highest' },
    { name: 'Lowest Rated', value: 'lowest' },
]);

const selectedSort = ref('recent');

const sortedRatings = computed(() => {
    return [...props.ratings].sort((a, b) => {
        if (selectedSort.value === 'recent') {
            return new Date(b.created_at) - new Date(a.created_at);
        } else if (selectedSort.value === 'highest') {
            return b.rating - a.rating;
        } else {
            return a.rating - b.rating;
        }
    });
});

const form = useForm({
    description: props.ratings.description,
    rating: 0,
    isAnonymous: false
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.charAt(0).toUpperCase();
};

const anonymizeName = (name) => {
    if (!name || name === 'Unknown User') return name;
    
    const parts = name.split(' ');
    if (parts.length < 2) return '*'.repeat(name.length);
    
    const firstName = parts[0];
    const lastName = parts[parts.length - 1];
    
    const firstAnon = firstName[0] + '*'.repeat(firstName.length - 1);
    const lastAnon = lastName[0] + '*'.repeat(lastName.length - 1);
    
    return `${firstAnon} ${lastAnon}`;
};

const getDisplayName = (rating) => {
    if (rating.is_anonymous) {
        return anonymizeName(rating.user);
    }
    return rating.user;
};

const menus = ref([]);
const actions = ref([]);

const toggleMenu = (event, rating, index) => {
    actions.value = [
        {
            label: 'Actions',
            items: [
                {
                    label: 'Edit',
                    icon: 'bxs:edit',
                    command: () => {
                        selectedRating.value = rating;
                        form.description = rating.description;
                        form.rating = rating.rating;
                        editModal.value = true;
                    }
                },
                {
                    label: 'Delete',
                    icon: 'mingcute:delete-2-fill',
                    command: () => deleteConfirm(rating.id)
                },
            ],
        },
    ];
    menus.value[index]?.toggle(event);
};

const deleteConfirm = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete this rating?',
        header: 'Delete Rating',
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-danger',
        rejectClass: 'p-button-secondary',
        accept: () => {
            router.delete(`/ratings/${id}`, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Rating deleted successfully', life: 3000 });
                }
            });
        },
        reject: () => {
            toast.add({ severity: 'warn', summary: 'Cancelled', detail: 'Delete cancelled', life: 3000 });
        }
    });
};

const submitEdit = () => {
    form.put(`/ratings/${selectedRating.value.id}`, {
        onSuccess: () => {
            editModal.value = false;
            toast.add({ severity: 'success', summary: 'Success', detail: 'Rating updated successfully', life: 3000 });
        }
    });
};

onMounted(() => {
    menus.value = Array(props.ratings.length).fill().map(() => ref(null));
});
</script>

<template>

    <Head title="Rating" />

    <AuthenticatedLayout>
        <section class="max-w-screen-xl md:px-4 2xl:px-0 mx-auto mt-6">
            <div class="card">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold">Ratings & Reviews</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-medium">{{ averageRating }}</span>
                        <Rating :modelValue="Number(averageRating)" :readonly="true" :cancel="false" />
                        <span class="text-gray-500">({{ ratingsCount }} reviews)</span>
                    </div>
                </div>

                <DataView :value="sortedRatings" :paginator="true" :rows="5">
                    <template #list="slotProps">
                        <div class="flex flex-col">
                            <div v-for="(rating, index) in slotProps.items" :key="rating.id">
                                <div class="flex flex-col p-4" 
                                    :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex justify-center items-center w-8 h-8 rounded-full text-white text-sm font-medium"
                                                :class="rating.is_anonymous ? 'bg-gray-500' : 'bg-primary'">
                                                {{ getInitials(getDisplayName(rating)) }}
                                            </div>
                                            <div class="flex items-center">
                                                <span class="font-medium">{{ getDisplayName(rating) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Rating v-model="rating.rating" :readonly="true" />
                                            <span class="text-sm text-gray-500">
                                                {{ formatDate(rating.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 mt-2">{{ rating.description }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataView>
            </div>
        </section>

        <Dialog v-model:visible="editModal" modal header="Edit Rating" :style="{ width: '450px', margin: '20px' }">
            <form @submit.prevent="submitEdit" class="flex flex-col gap-4 p-2 md:p-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Rating</label>
                    <Rating v-model="form.rating" :cancel="false" />
                    <small class="text-red-500" v-if="form.errors.rating">
                        {{ form.errors.rating }}
                    </small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Description</label>
                    <Textarea v-model="form.description" rows="3" placeholder="Update your rating description..."
                        autoResize />
                    <small class="text-red-500" v-if="form.errors.description">
                        {{ form.errors.description }}
                    </small>
                </div>

                <div class="flex items-center gap-2">
                <ToggleSwitch 
                    v-model="form.isAnonymous" 
                    class="w-16 h-8"
                    on-label="Yes"
                    off-label="No"
                />
                <span class="text-sm text-gray-600">I want to be anonymous</span>
            </div>

                <div class="flex justify-end gap-2 mt-4">
                    <SecondaryButton type="button" @click="editModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-75': form.processing }">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>

        <Toast />
        <ConfirmDialog class="m-6"/>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-dialog-header) {
    padding: 1rem 1.5rem;
}

:deep(.p-dialog-content) {
    padding: 0;
}

:deep(.p-dialog .p-dialog-header .p-dialog-title) {
    font-size: 1.25rem;
    font-weight: 600;
}

:deep(.p-rating) {
    gap: 0.5rem;
}

:deep(.p-rating .p-rating-item.p-rating-item-active .p-rating-icon) {
    color: #FFA41C;
}

:deep(.p-rating .p-rating-item .p-rating-icon) {
    font-size: 1.5rem;
}

:deep(.p-menu) {
    min-width: 200px;
}

:deep(.p-menu .p-menuitem-link) {
    padding: 0.75rem 1rem;
}

:deep(.p-menu .p-menuitem-icon) {
    margin-right: 0.5rem;
}
</style>
