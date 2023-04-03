import { createApp } from 'vue'
import SaleComponent from "./Sale/Index.vue";


const app = createApp({});

app.component('SaleComponent', SaleComponent)


app.mount('#sale');
