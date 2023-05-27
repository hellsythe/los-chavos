import { createApp } from 'vue'
import SaleComponent from "./Sale/Index.vue";
import SalePoint from "./SalePoint/Index.vue";
import showOrder from "./SaleDetails/ShowOrder.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";
import SaleEditComponent from "./SaleEdit/Edit.vue";
import PrintButton from "./Sale/PrintButton.vue";


const app = createApp({});

app.component('SaleEditComponent', SaleEditComponent)
app.component('SaleComponent', SaleComponent)
app.component('showOrderComponent', showOrder)
app.component('PaymentDetailComponent', PaymentDetail)
app.component('PrintButton', PrintButton)
app.component('SalePoint', SalePoint)


app.mount('#sale');
