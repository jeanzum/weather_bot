import './bootstrap';
import { createApp } from 'vue';
import AppLayout from './components/Layout/AppLayout.vue';

const app = createApp(AppLayout);

app.mount('#app');