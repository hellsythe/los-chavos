vue
<template>
    <ClientComponent :client="client" />
    <ServicesComponent :availableservices="services" :selectedServices="selectedServices"/>
    <ReportComponent :selectedServices="selectedServices"/>
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
        };
    },
    mounted() {

    },
    methods: {

    },
};
</script>
