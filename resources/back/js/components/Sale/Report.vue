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
                            <th>Servicio</th>
                            <th>Subservicio</th>
                            <th>Costo x prenda</th>
                            <th>Costo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(service, index) in selectedServices">
                            <th>{{index+1}}</th>
                            <td>{{service.service_id.name}}</td>
                            <td>{{service.subservice_id.name}}</td>
                            <td>${{service.price?.toFixed(2)}}</td>
                            <td>${{service.price?.toFixed(2) * garmentData.amount }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2"></th>
                            <th>Total $500.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="flex justify-end">
                <button class="btn btn-info" @click="validate">Confirmar Pedido</button>
            </div>
        </div>
    </div>
</div></template>

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
