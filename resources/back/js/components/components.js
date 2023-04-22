import { createApp } from 'vue'
import SaleComponent from "./Sale/Index.vue";
import showOrder from "./SaleDetails/ShowOrder.vue";


const app = createApp({});

app.component('SaleComponent', SaleComponent)
app.component('showOrderComponent', showOrder)


app.mount('#sale');
