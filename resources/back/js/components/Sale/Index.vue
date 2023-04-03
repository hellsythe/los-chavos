vue
<template>
    <ClientComponent />
</template>

<script>
import ClientComponent from "./Client.vue";


export default {
    name: "SalePoint",

    props: {
        csrf: String,
        services: JSON
    },
    components: {
        ClientComponent,
    },
    data() {
        return {
            selectedServices: [],
        };
    },
    mounted() {

    },
    methods: {

    },
};
</script>
