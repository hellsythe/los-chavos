<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Confirmar Pedido</h1>
                <button @click="showReportInfo = true" v-if="!showReportInfo">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showReportInfo = false" v-if="showReportInfo">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="showReportInfo">
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
                                    <td>{{ service.service_id.name }} - {{ service.subservice_id.name }} - sobre {{ garmentData.data?.name }}</td>
                                    <td>${{ service.price?.toFixed(2) }}</td>
                                    <td v-if="garmentData.amount > 1">${{ service.price?.toFixed(2) * garmentData.amount }}
                                    </td>
                                </tr>
                                <tr v-if="service.subservice_id.id == 4">
                                    <td>-</td>
                                    <td> <label :class="{ 'line-through': garmentData.amount > 6 }">Costo Por Diseño
                                            nuevo</label> <strong v-if="garmentData.amount > 6">No aplica por ser mas de 6
                                            prendas</strong></td>
                                    <td :class="{ 'line-through': garmentData.amount > 6 }">${{ service.price_new?.toFixed(2) }}
                                    </td>
                                    <td v-if="garmentData.amount > 1" :class="{ 'line-through': garmentData.amount > 6 }">
                                        ${{ service.price_new?.toFixed(2) }}</td>
                                </tr>
                                <tr v-if="service.subservice_id.id == 3">
                                    <td>-</td>
                                    <td> <label :class="{ 'line-through': garmentData.amount > 6 }">Costo Por Modificar
                                            diseño</label> <strong v-if="garmentData.amount > 6">No aplica por ser mas de 6
                                            prendas</strong></td>
                                    <td :class="{ 'line-through': garmentData.amount > 6 }">
                                        ${{ service.price_update?.toFixed(2) }}</td>
                                    <td v-if="garmentData.amount > 1" :class="{ 'line-through': garmentData.amount > 6 }">
                                        ${{ service.price_update?.toFixed(2) }}</td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2"></th>
                                <th v-if="garmentData.amount > 1"></th>
                                <th class="text-end">Total Por Prenda: ${{ total.sum }}</th>
                            </tr>
                            <tr v-if="garmentData.amount > 1">
                                <th colspan="2"></th>
                                <th v-if="garmentData.amount > 1"></th>
                                <th class="text-end">Total Por {{ garmentData.amount }} Prenda: ${{ total.total }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="flex justify-end">
                    <button class="btn btn-info" @click="validate">Confirmar Pedido</button>
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


export default {
    name: "ReportSale",
    components: {
        EyeIcon,
        EyeSlashIcon
    },
    computed: {
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

            return {
                neto: sum,
                extra: extra,
                sum: sum + extra,
                total: (sum * that.garmentData.amount) + extra,
            };
        }
    },
    props: {
        selectedServices: JSON,
        garmentData: JSON,
    },
    data() {
        return {
            showReportInfo: true,
        };
    },
    mounted() {

    },
    methods: {
        validate() {

        }
    },
};
</script>
