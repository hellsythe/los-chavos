import { createApp } from 'vue'
import SalePoint from "./SalePoint/Index.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import ContextMenu from '@imengyu/vue3-context-menu'

let element = document.getElementById('sale')

if (element !== null) {
    const app = createApp({}).use(ContextMenu);

    app.component('PaymentDetailComponent', PaymentDetail)
    app.component('SalePoint', SalePoint)

    app.mount('#sale');

}
