import { createApp } from 'vue'
import Calendar from "./Calendar/Index.vue";
import SalePoint from "./SalePoint/Index.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";
import CashboxComponent from "./cashbox/Index.vue";
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import ContextMenu from '@imengyu/vue3-context-menu'

let element = document.getElementById('sale')

if (element !== null) {
    const app = createApp({}).use(ContextMenu);

    app.component('PaymentDetailComponent', PaymentDetail)
    app.component('CashboxComponent', CashboxComponent)
    app.component('SalePoint', SalePoint)

    app.mount('#sale');

}

let calendar = document.getElementById('calendar')

if (calendar !== null) {
    const app = createApp({}).use(ContextMenu);
    app.component('Calendar', Calendar)

    app.mount('#calendar');
}