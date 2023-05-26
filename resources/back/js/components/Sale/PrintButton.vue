<template>
    <button class="btn" :disabled="loading" @click="print">Reimprimir Ticket</button>
    <TicketComponent ref="ticket" :extra="order.extra" :payment="order.payment" :selectedServices="order.selectedServices"
        :garmentData="order.garmentData" :order="order.order" />
</template>

<script>
import TicketComponent from "./Ticket.vue";

export default {
    name: "PrintButton",
    components: {
        TicketComponent
    },
    props: {
        order: {}
    },
    data() {
        return {
            loading: false,
        };
    },
    mounted() {

    },
    methods: {
        print() {
            this.loading = true;
            // this.$refs.ticket.print();
        },
    },
};
</script>
