<template>
    <div class="flex justify-end">
        <input type="checkbox" id="confirmpayment" class="modal-toggle" />
        <div class="modal" id="payment-modal">
            <div class="modal-box">
                <label @click="closeModal" for="confirmpayment" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg">Confirmar Pedido</h3>
                <div class="flex py-2">
                    <p>Total a pagar: </p>
                    <p class=" ml-auto">{{ formatter(payment.total) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Anticipo: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="payment.advance" class="input input-bordered"/>
                        </label>
                    </p>
                </div>
                <div class="flex py-2">
                    <p>Resta: </p>
                    <p class="ml-auto">{{ formatter(payment.total - payment.advance) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Pago: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="payment.payment" class="input input-bordered"/>
                        </label>
                    </p>
                </div>
                <div class="flex py-2">
                    <p>Cambio: </p>
                    <p class=" ml-auto">{{ formatter(payment.payment - payment.advance) }}</p>
                </div>
                <div class="modal-action">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import money from './../formater';

export default {
    name: "Payment",
    components: {

    },
    props: {
        payment: JSON,
    },
    data() {
        return {
        };
    },
    mounted() {

    },
    methods: {
        // saveOrder() {
        //     this.$emit('save-order')
        // },
        formatter(amount){
            return  money.format(amount);
        },
        closeModal()
        {
            document.getElementById('payment-modal').classList.remove("modal-open")
        }
    },
};
</script>
