import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

const Noir = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{surface.50}',
            100: '{surface.100}',
            200: '{surface.200}',
            300: '{surface.300}',
            400: '{surface.400}',
            500: '{surface.500}',
            600: '{surface.600}',
            700: '{surface.700}',
            800: '{surface.800}',
            900: '{surface.900}',
            950: '{surface.950}'
        },
        colorScheme: {
            light: {
                primary: {
                    color: '#F5A623',
                    contrastColor: '#1F1F1F',
                    hoverColor: '#FF9900',
                    activeColor: '#E69100'
                },
                highlight: {
                    background: '#F5A623',
                    focusBackground: '#FF9900',
                    color: '#1F1F1F',
                    focusColor: '#1F1F1F'
                },
            },
            dark: {
                primary: {
                    color: '#F5A623',
                    contrastColor: '#FFFFFF',
                    hoverColor: '#FF9900',
                    activeColor: '#E69100'
                },
                highlight: {
                    background: '#F5A623',
                    focusBackground: '#FF9900',
                    color: '#1F1F1F',
                    focusColor: '#1F1F1F'
                }
            }
        }
    }
});

export default Noir;
        