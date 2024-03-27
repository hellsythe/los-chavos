import { createApp } from 'vue'
import MessengerComponent from "./components/Messenger/Chat/Messenger.vue";
import TemplateIndex from "./components/Messenger/Template/Index.vue";
import TemplateCreate from "./components/Messenger/Template/Create.vue";
import TemplateUpdate from "./components/Messenger/Template/Update.vue";
import TemplateDetail from "./components/Messenger/Template/Detail.vue";

let element = document.getElementById('messenger')
if (element !== null) {
    const app = createApp({});
    app.component('MessengerComponent', MessengerComponent)
    app.component('TemplateIndex', TemplateIndex)
    app.component('TemplateCreate', TemplateCreate)
    app.component('TemplateUpdate', TemplateUpdate)
    app.component('TemplateDetail', TemplateDetail)

    app.mount('#messenger');
}
