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
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="service in selectedServices">
                            <th>1</th>
                            <td>Cy Ganderton</td>
                            <td>Quality Control Specialist</td>
                            <td>Littel, Schaden and Vandervort</td>
                            <td>Canada</td>
                            <td>12/16/2020</td>
                            <td>Blue</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total</th>
                            <th>$500.00</th>
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
        selectedServices: JSON
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
