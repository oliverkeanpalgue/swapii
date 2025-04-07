import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Noir from './Presets/Noir';
import PreventBack from './Plugins/preventBack';

const appName = import.meta.env.VITE_APP_NAME || 'Swapii';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastService)
            .use(PrimeVue, {
                theme: {
                    preset: Noir,
                    options: {
                        darkModeSelector: false
                    }

                }
             })
            .use(ConfirmationService)
            .use(PreventBack)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
