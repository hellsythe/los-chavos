<template>
    <ClientComponent />
</template>

<script>

import { postToApi } from '@base/js/request/resquestToApi';
import ClientComponent from './Client.vue';

export default {
    name: "SalePoint",
    props: {
        availableservices: JSON,
        order: JSON,
    },
    components: {
        ClientComponent
    },
    data() {

    },
    mounted() {
        window.addEventListener("beforeunload", (event) => {
        // event.returnValue = true;
        });
    },
    methods: {

    },
};
</script>
