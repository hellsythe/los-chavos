<template>
    <ClientComponent :extra="extra" :order="order" />
    <ServicesComponent :extra="extra" :order="order" :availableservices="availableservices" />
    <!-- <ConfirmComponent :extra="extra" :order="order"/> -->
</template>

<script>
import { postToApi } from '@base/js/request/resquestToApi';
import ClientComponent from './Client.vue';
import ServicesComponent from './Services/Index.vue';
import ConfirmComponent from './Confirm.vue';

export default {
    name: "SalePoint",
    props: {
        availableservices: JSON,
        orderp: JSON,
        extrap: JSON,
    },
    components: {
        ClientComponent,
        ServicesComponent,
        ConfirmComponent,
    },
    data() {
        return {
            order: JSON,
            extra: JSON,
        };
    },
    beforeMount() {
        this.order = this.orderp;
        this.extra = this.extrap;
        window.addEventListener("beforeunload", (event) => {
        // event.returnValue = true;
        });
    },
    methods: {

    },
};
</script>
