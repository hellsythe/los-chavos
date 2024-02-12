import { createApp } from 'vue'
import Calendar from "./Calendar/Index.vue";
import SalePoint from "./SalePoint/Index.vue";
import PaymentDetail from "./SaleDetails/PaymentDetail.vue";
import CashboxComponent from "./cashbox/Index.vue";
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import ContextMenu from '@imengyu/vue3-context-menu'
import ChatDashboard from "./Chat/Dashboard.vue";
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


let chat_dashboard = document.getElementById('chatmodule')

if (chat_dashboard !== null) {
    const app = createApp({}).use(ContextMenu);
    app.component('ChatDashboard', ChatDashboard)

    app.mount('#chatmodule');
}

let chat_conversation = document.getElementById('chatconversation')

if (chat_conversation !== null) {
    const app = createApp({}).use(ContextMenu);
    app.component('ChatDashboard', ChatDashboard)

    app.mount('#chatconversation');
}
