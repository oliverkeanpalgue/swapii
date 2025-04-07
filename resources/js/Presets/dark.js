import { ref, onMounted, watch } from 'vue';

export function useDark() {
    const isDark = ref(localStorage.getItem('darkMode') === 'true');

    const toggleDark = () => {
        isDark.value = !isDark.value;
        updateDarkMode();
    };

    const updateDarkMode = () => {
        localStorage.setItem('darkMode', isDark.value);
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    onMounted(() => {
        updateDarkMode();
    });

    watch(isDark, () => {
        updateDarkMode();
    });

    return {
        isDark,
        toggleDark
    };
} 