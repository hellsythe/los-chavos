<template>
    <div class="flex justify-end">
        <input type="checkbox" id="confirmpayment" class="modal-toggle" />
        <div class="modal" id="payment-modal">
            <div class="modal-box">
                <label @click="closeModal" for="confirmpayment" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h3 class="font-bold text-lg">Confirmar Orden</h3>
                <div class="flex py-2">
                    <p>Total a pagar: </p>
                    <p class=" ml-auto">{{ formatter(order.total) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Metodo de pago: </p>

                    <p class="ml-auto">
                        <select class="select select-bordered w-full" v-model="this.order.payment.method">
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                        </select>
                    </p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Anticipo: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="order.payment.advance" class="input input-bordered"/>
                        </label>
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.payment.advance }}</div>
                    </p>
                </div>
                <div class="flex py-2">
                    <p>Resta: </p>
                    <p class="ml-auto">{{ formatter(order.total - order.payment.advance) }}</p>
                </div>
                <div class="flex py-2">
                    <p class="flex items-center">Pago: </p>
                    <p class="ml-auto">
                        <label class="input-group">
                            <span>$</span>
                            <input type="number" v-model="order.payment.payment" class="input input-bordered"/>
                        </label>
                    </p>
                </div>
                <div class="flex py-2">
                    <p>Cambio: </p>
                    <p class=" ml-auto">{{ formatter(order.payment.payment - order.payment.advance) }}</p>
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
        order: JSON,
        errors: JSON,
    },
    data() {
        return {
        };
    },
    created() {
        this.order.payment = {
            method: 'cash'
        };
        this.errors.payment = {};
    },
    methods: {
        formatter(amount){
            return  money.format(amount);
        },
        closeModal()
        {
            document.getElementById('payment-modal').classList.remove("modal-open")
        },
        validate(){
            this.errors.payment = {};

            if (!this.order.payment.advance) {
                this.errors.payment.advance = 'El anticipo no puede estar vacio';
            }

            if (this.order.payment.advance > this.order.total) {
                this.errors.payment.advance = 'El anticipo no puede ser mayor al total';
            }
        }
    },
};
</script>
