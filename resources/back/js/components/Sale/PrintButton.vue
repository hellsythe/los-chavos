<template>
    <button class="btn" :disabled="loading" @click="print">Reimprimir Ticket</button>
</template>

<script>
export default {
    name: "PrintButton",
    props: {
        ticket: ''
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
            this.loading = true;
            const iframe = document.createElement('iframe');
            iframe.src = this.ticket;
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            iframe.onload = () => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
        },
    },
};
</script>
