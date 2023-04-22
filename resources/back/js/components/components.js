import { createApp } from 'vue'
import SaleComponent from "./Sale/Index.vue";
import showOrder from "./SaleDetails/ShowOrder.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";


const app = createApp({});

app.component('SaleComponent', SaleComponent)
app.component('showOrderComponent', showOrder)
app.component('PaymentDetailComponent', PaymentDetail)


app.mount('#sale');
