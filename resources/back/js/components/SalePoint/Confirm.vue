<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Confirmar Orden</h1>
                <button @click="extra.steps.confirm = true" v-if="!extra.steps.confirm">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="extra.steps.confirm = false" v-if="extra.steps.confirm">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="extra.steps.confirm">
                <div class="lg:flex">
                    <div class="form-control mb-2 mr-2 w-64">
                        <label for="" class="label"><span class="label-text">¿Se tiene un pedido para la
                                prendas?</span></label>
                        <select v-model="order.order_number" class="select w-full max-w-xs">
                            <option value="1">Si es un pedido</option>
                            <option value="0">No es un pedido</option>
                        </select>
                        <div class="text-red-500 text-xs font-semibold mt-1"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Concepto</th>
                                <th>Costo x prenda</th>
                                <th>Prendas</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(service, index) in order.services">
                                <tr>
                                    <th>{{ index + 1 }}</th>
                                    <td>{{ service.service.name }} - {{ service.subservice.name }} - sobre {{
                                        service.garment?.name }}</td>
                                    <td>{{ formatter(service.price) }}</td>
                                    <td>{{ service.garment_amount }}</td>
                                    <td>{{ formatter(service.price * service.garment_amount) }}</td>
                                </tr>
                                <tr v-if="service.subservice.id == 4">
                                    <td>-</td>
                                    <td colspan="3">
                                        <label :class="{ 'line-through': service.garment_amount > 6 }">
                                            Costo Por Diseño nuevo
                                        </label>
                                        <strong v-if="service.garment_amount > 6">No aplica por ser mas de 6
                                            prendas
                                        </strong>
                                    </td>
                                    <td :class="{ 'line-through': service.garment_amount > 6 }">
                                        {{ formatter(service.price) }}
                                    </td>

                                </tr>
                                <tr v-if="service.subservice.id == 3">
                                    <td>-</td>
                                    <td colspan="3">
                                        <label :class="{ 'line-through': service.garment_amount > 6 }">Costo Por Modificar
                                            diseño</label>
                                        <strong v-if="service.garment_amount > 6">No aplica por ser mas de 6
                                            prendas</strong>
                                    </td>
                                    <td :class="{ 'line-through': service.garment_amount > 6 }">
                                        {{ formatter(service.detail.design.price) }}</td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4"></th>
                                <th class="text-end">Total: {{ total.total }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <PaymentComponent :order="order" :openModal="openModal" :errors="extra.errors" ref="payment">
                    <button @click="saveOrder" class="btn" :disabled="loading">Guardar e Imprimir ticket</button>
                    <!-- <label @click="goToDashboard" class="btn btn-primary">Guardado correcto ir al Dashboard</label> -->
                </PaymentComponent>
                <div class="flex justify-end">
                    <label @click="registerPayment" class="btn">Registrar Pago</label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    EyeSlashIcon,
    EyeIcon,
} from "@heroicons/vue/24/solid";
import money from './../formater';
import PaymentComponent from "./Payment.vue";
import { getAllErrorsAsArrayFromObject } from '@base/js/getErrors';
import Swal from "@node/sweetalert2";

export default {
    name: "ReportSale",
    components: {
        EyeIcon,
        EyeSlashIcon,
        PaymentComponent,
    },
    computed: {
        total: function () {
            let that = this;
            let extra = 0;
            let total = 0;

            this.order.services.forEach(function (item, index) {
                that.order.services[index].total = item.price * item.garment_amount;
                total += item.price * item.garment_amount;

                if (item.garment_amount <= 6) {
                    if (item.subservice.id == 3) {
                        extra += item.detail.design.price;
                    }

                    if (item.subservice.id == 4) {
                        extra += item.price;
                    }
                }
            });

            this.order.total = total + extra;
            return {
                extra: this.formatter(extra),
                total: this.formatter(this.order.total),
            };
        }
    },
    props: {
        order: JSON,
        extra: JSON,
    },
    data() {
        return {
            openModal: false,
            loading: false
        };
    },
    created() {
        this.extra.errors.payment = {};
    },
    methods: {
        async saveOrder() {
            this.$refs.payment.validate();
            const payment_errors = getAllErrorsAsArrayFromObject(this.extra.errors.payment);

            if (payment_errors.length == 0) {
                this.loading = true;
                this.order.missing_payment = this.order.total - this.order.payment.advance;
                await this.$emit('save-order');
            }
        },
        print() {
            this.$refs.ticket.print();
        },
        formatter(amount) {
            return money.format(amount);
        },
        goToDashboard() {
            window.location.href = '/admin';
        },
        async registerPayment() {
            window.dispatchEvent(new CustomEvent("dispachValidations"));
            setTimeout(() => {
                let errors = this.validate();

                if (errors) {
                    Swal.fire({
                        title: 'Errores en el pedido',
                        html: errors,
                        icon: "warning",
                    });
                } else {
                    document.getElementById('payment-modal').classList.add("modal-open");
                }
            }, 300);
        },
        validate() {
            let final_errors = '';
            const client_errors = getAllErrorsAsArrayFromObject(this.extra.errors.client);
            if (client_errors.length > 0) {
                final_errors += `<strong>Errores en el cliente </strong> <br>` + client_errors.join(', <br>')
            }

            this.order.services.forEach((service, index) => {
                let service_errors = getAllErrorsAsArrayFromObject(this.extra.errors.services[index]);
                if (service_errors.length > 0) {
                    final_errors += `<br><br><strong>Errores en el servicio #${index + 1}</strong> <br><br>` + service_errors.join(', <br>')
                }
            });

            return final_errors;
        },
    },
};
</script>
