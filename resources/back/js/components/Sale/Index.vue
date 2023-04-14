vue
<template>
    <ClientComponent :client="client" />
    <ServicesComponent :availableservices="services" :selectedServices="selectedServices" :garmentData="garmentData" />
    <ReportComponent :selectedServices="selectedServices" :garmentData="garmentData" />
</template>

<script>
import ClientComponent from "./Client.vue";
import ServicesComponent from "./Services.vue";
import ReportComponent from "./Report.vue";


export default {
    name: "SalePoint",
    props: {
        csrf: String,
        services: JSON,
    },
    components: {
        ClientComponent,
        ServicesComponent,
        ReportComponent,
    },
    data() {
        return {
            client: {
                name: '',
                email: '',
                phone: '',
            },
            selectedServices: [{}],
            garmentData: {},
        };
    },
    mounted() {

    },
    methods: {

    },
};
</script>
