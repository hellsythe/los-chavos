<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Confirmar Orden</h1>
                <button @click="showReportInfo.value = true" v-if="!showReportInfo.value">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showReportInfo.value = false" v-if="showReportInfo.value">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="showReportInfo.value">
                <div class="lg:flex">
                    <div class="form-control mb-2 mr-2 w-64">
                        <label for="" class="label"><span class="label-text">¿Se tiene un pedido para la prendas?</span></label>
                        <div class="mt-3 flex items-center">
                            <input v-model="extra.isOrder" type="checkbox" checked="checked" class="checkbox mr-1" /> Habilitar
                        </div>
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
                                <th v-if="garmentData.amount > 1">Costo x {{ garmentData.amount }} prendas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(service, index) in selectedServices">
                                <tr>
                                    <th>{{ index + 1 }}</th>
                                    <td>{{ service.service_id.name }} - {{ service.subservice_id.name }} - sobre {{
                                        garmentData.data?.name }}</td>
                                    <td>{{ formatter(service.price) }}</td>
                                    <td v-if="garmentData.amount > 1">{{ formatter(service.price * garmentData.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="service.subservice_id.id == 4">
                                    <td>-</td>
                                    <td> <label :class="{ 'line-through': garmentData.amount > 6 }">Costo Por Diseño
                                            nuevo</label> <strong v-if="garmentData.amount > 6">No aplica por ser mas de 6
                                            prendas</strong></td>
                                    <td :class="{ 'line-through': garmentData.amount > 6 }">{{ formatter(service.price_new)
                                    }}
                                    </td>
                                    <td v-if="garmentData.amount > 1" :class="{ 'line-through': garmentData.amount > 6 }">
                                        {{ formatter(service.price_new) }}</td>
                                </tr>
                                <tr v-if="service.subservice_id.id == 3">
                                    <td>-</td>
                                    <td> <label :class="{ 'line-through': garmentData.amount > 6 }">Costo Por Modificar
                                            diseño</label> <strong v-if="garmentData.amount > 6">No aplica por ser mas de 6
                                            prendas</strong></td>
                                    <td :class="{ 'line-through': garmentData.amount > 6 }">
                                        {{ formatter(service.price_update) }}</td>
                                    <td v-if="garmentData.amount > 1" :class="{ 'line-through': garmentData.amount > 6 }">
                                        {{ formatter(service.price_update) }}</td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2"></th>
                                <th v-if="garmentData.amount > 1"></th>
                                <th class="text-end">Total Por Prenda: {{ total.neto }}</th>
                            </tr>
                            <tr v-if="garmentData.amount > 1">
                                <th colspan="2"></th>
                                <th v-if="garmentData.amount > 1"></th>
                                <th class="text-end">Total Por {{ garmentData.amount }} Prenda: {{ total.total }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <PaymentComponent :payment="payment" :openModal="openModal">
                    <label v-if="!printed.value" @click="saveOrder" class="btn">Guardar e Imprimir ticket</label>
                    <label v-else @click="goToDashboard" class="btn btn-primary">Guardado correcto ir al Dashboard</label>
                </PaymentComponent>
                <div class="flex justify-end">
                    <label @click="registerPayment" class="btn">Registrar Pago</label>
                </div>
            </div>
        </div>
        <TicketComponent ref="ticket" :extra="extra" :payment="payment" :selectedServices="selectedServices" :garmentData="garmentData"
            :order="order" />
    </div>
</template>

<script>
import {
    EyeSlashIcon,
    EyeIcon,
} from "@heroicons/vue/24/solid";
import money from './../formater';
import PaymentComponent from "./Payment.vue";
import TicketComponent from "./Ticket.vue";
import 'jsbarcode/dist/barcodes/JsBarcode.code128.min.js';

export default {
    name: "ReportSale",
    components: {
        EyeIcon,
        EyeSlashIcon,
        PaymentComponent,
        TicketComponent,
    },
    computed: {
        minDate: function() {
            let date = new Date().toISOString().split("T")[0];
            return date;
        },
        total: function () {
            let sum = 0;
            let extra = 0;
            let that = this;
            this.selectedServices.forEach(function (item) {
                sum += item.price;
                if (item.subservice_id.id == 4 && that.garmentData.hasOwnProperty('amount') && that.garmentData.amount <= 6) {
                    extra += item.price_new;
                }
                if (item.subservice_id.id == 3 && that.garmentData.hasOwnProperty('amount') && that.garmentData.amount <= 6) {
                    extra += item.price_update;
                }
            });

            this.payment.total = (sum * this.garmentData.amount) + extra;
            return {
                neto: this.formatter(sum),
                extra: this.formatter(extra),
                sum: this.formatter(sum + extra),
                total: this.formatter((sum * that.garmentData.amount) + extra),
            };
        }
    },
    props: {
        selectedServices: JSON,
        garmentData: JSON,
        payment: JSON,
        showServicesInfo: JSON,
        showReportInfo: JSON,
        order: JSON,
        printed: JSON,
        extra: JSON,
        final_errors: JSON,
    },
    data() {
        return {
            errors: {},
            openModal: false
        };
    },
    mounted() {

    },
    methods: {
        async saveOrder() {
            await this.$emit('save-order');
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
        registerPayment() {
            this.errors.date = '';

            if (!this.extra.hasOwnProperty('date') || this.extra.date == ''|| this.extra.date == null) {
                this.errors.date = 'La fecha de entrega no puede estar vacia';
            }

            if (this.final_errors.client == false && this.final_errors.service  == false && this.errors.date == '') {
                document.getElementById('payment-modal').classList.add("modal-open");
            }
        }
    },
};
</script>
