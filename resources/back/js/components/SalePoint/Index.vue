<template>
    <ClientComponent :extra="extra" :order="order" />
    <ServicesComponent :extra="extra" :order="order" :availableservices="availableservices" />
    <ConfirmComponent :extra="extra" :order="order" @save-order="saveOrder"/>
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
        async saveOrder(){
            let response = await postToApi(`/admin/sale-save`, {
                order: this.order,
            });

            this.order.data.id = response.id;

            if (this.order.data.id) {
                // await new Promise(r => setTimeout(r, 300));

                // JsBarcode("#barcode", this.order.id, {
                //     height: 25,
                //     fontSize: 12,
                //     displayValue: false
                // });

                // this.$refs.report.print();

                // let that = this;
                // setTimeout(function(){
                //     that.printed.value = true
                // }, 1000);
            }
        }
    },
};
</script>
