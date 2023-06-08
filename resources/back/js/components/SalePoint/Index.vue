<template>
    <ClientComponent :extra="extra" :order="order" />
    <ServicesComponent :extra="extra" :order="order" :availableservices="availableservices" />
    <ConfirmComponent v-if="!extra.readonly" :extra="extra" :order="order" @save-order="saveOrder" />
    <PaymentDetailComponent :model="orderp" v-if="extra.readonly" />
</template>

<script>
import { postToApi } from '@base/js/request/resquestToApi';
import ClientComponent from './Client.vue';
import ServicesComponent from './Services/Index.vue';
import ConfirmComponent from './Confirm.vue';
import PaymentDetailComponent from './../SaleDetails/PaymentDetail.vue';
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
        PaymentDetailComponent,
    },
    data() {
        return {
            order: JSON,
            extra: JSON,
        };
    },
    created() {
        this.order = this.orderp;
        this.extra = this.extrap;
        window.addEventListener("beforeunload", (event) => {
            event.returnValue = true;
        });

        if(!this.extra.hasOwnProperty('readonly')){
            this.extra.readonly = false;
        }
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
