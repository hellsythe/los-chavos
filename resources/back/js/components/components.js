import { createApp } from 'vue'
import SaleComponent from "./Sale/Index.vue";
import SalePoint from "./SalePoint/Index.vue";
import showOrder from "./SaleDetails/ShowOrder.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";
import SaleEditComponent from "./SaleEdit/Edit.vue";
import PrintButton from "./Sale/PrintButton.vue";
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import ContextMenu from '@imengyu/vue3-context-menu'

const app = createApp({}).use(ContextMenu);

app.component('SaleEditComponent', SaleEditComponent)
app.component('SaleComponent', SaleComponent)
app.component('showOrderComponent', showOrder)
app.component('PaymentDetailComponent', PaymentDetail)
app.component('PrintButton', PrintButton)
app.component('SalePoint', SalePoint)


app.mount('#sale');
