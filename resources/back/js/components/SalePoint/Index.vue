<template>
    <ClientComponent :extra="extra" :order="order" />
    <ServicesComponent :extra="extra" :order="order" :availableservices="availableservices" />
    <ConfirmComponent :extra="extra" :order="order" @save-order="saveOrder" />
</template>

<script>
import { postToApi } from '@base/js/request/resquestToApi';
import ClientComponent from './Client.vue';
import ServicesComponent from './Services/Index.vue';
import ConfirmComponent from './Confirm.vue';
import printJS from 'print-js'

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
        async saveOrder() {
            let response = await postToApi(`/admin/sale-save`, {
                order: this.order,
            });

            this.order.id = response.order.id;

            if (this.order.id) {
                printJS({
                    printable: response.ticket,
                    onPrintDialogClose: function () {
                        window.location.href = `/admin/order/${response.order.id}`;
                    },
                });
            }
        }
    },
};
</script>
