<template>
    <ClientComponent :client="client" :showClientInfo="showClientInfo" :showServicesInfo="showServicesInfo" />
    <ServicesComponent :availableservices="services" :selectedServices="selectedServices" :garmentData="garmentData" :showServicesInfo="showServicesInfo" :showReportInfo="showReportInfo" />
    <ReportComponent :selectedServices="selectedServices" :garmentData="garmentData" @save-order="saveOrder" :payment="payment" :showReportInfo="showReportInfo" />
</template>

<script>
import ClientComponent from "./Client.vue";
import ServicesComponent from "./Services.vue";
import ReportComponent from "./Report.vue";
import { postToApi } from '@base/js/request/resquestToApi';

export default {
    name: "SalePoint",
    props: {
        services: JSON,
    },
    components: {
        ClientComponent,
        ServicesComponent,
        ReportComponent,
    },
    data() {
        return {
            client: {},
            selectedServices: [{}],
            garmentData: {},
            payment: {payment:0, advance:0},
            showClientInfo: {value: true},
            showServicesInfo: {value: false},
            showReportInfo: {value: false},
        };
    },
    mounted() {

    },
    methods: {
        async saveOrder(){
            let response = await postToApi(`/admin/sale-save`, {
                client: this.client,
                services: this.selectedServices,
                garment: this.garmentData,
            });
        }
    },
};
</script>
