<template>
    <div class="flex justify-end">
        <input type="checkbox" id="confirmpayment" class="modal-toggle" />
        <div class="modal" id="payment-modal">
            <div class="modal-box">
                <label for="confirmpayment" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg">Realizar Abono a Orden # {{ model.id }}</h3>
                <div class="flex py-2">
                    <p>Saldo restante: </p>
                    <p class=" ml-auto">{{ formatter(model.missing_payment) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Total del nuevo abono: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="payment.deposit" class="input input-bordered" />
                        </label>
                    </p>
                </div>
                <div class="text-red-500 text-xs font-semibold mt-1 text-right">{{ errors.deposit }}</div>
                <div class="flex py-2">
                    <p>Resta: </p>
                    <p class="ml-auto">{{ formatter(model.missing_payment - payment.deposit) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Pago: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="payment.payment" class="input input-bordered" />
                        </label>
                    </p>
                </div>
                <div class="flex py-2">
                    <p>Cambio: </p>
                    <p class=" ml-auto">{{ formatter(payment.payment - payment.deposit) }}</p>
                </div>
                <div class="modal-action">
                    <button @click="confirm" class="btn btn-active">Confirmar Abono</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import money from './../formater';
import { postToApi } from '@base/js/request/resquestToApi';

export default {
    name: "PaymentDetail",
    components: {

    },
    props: {
        model: JSON,
    },
    data() {
        return {
            payment: {
                deposit: 0,
                payment: 0,
            },
            errors: {}
        };
    },
    mounted() {

    },
    methods: {
        async confirm() {
            if (!this.validate()) {
                return '';
            }

            await postToApi(`/admin/payment/api`, {
                order_id: this.model.id,
                amount: this.payment.deposit,
            });
            location.reload();
        },
        formatter(amount) {
            return money.format(amount);
        },
        validate() {
            if (this.payment.deposit) {
                if (this.payment.deposit > this.model.missing_payment) {
                    this.errors.deposit = "El abono no puede ser mayor al pago pendiente";
                    return false;
                }

                return true;
            }

            this.errors.deposit = "El abono no puede estar vacio";
            return false;
        }
    },
};
</script>
