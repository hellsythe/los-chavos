vue
<template>
    <ClientComponent :client="client" />
    <ServicesComponent :availableservices="services" :selectedServices="selectedServices"/>
</template>

<script>
import ClientComponent from "./Client.vue";
import ServicesComponent from "./Services.vue";


export default {
    name: "SalePoint",
    props: {
        csrf: String,
        services: JSON,
    },
    components: {
        ClientComponent,
        ServicesComponent,
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
