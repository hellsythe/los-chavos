<template>
    <div class="p-4 bg-base-100 mb-5 shadow rounded-lg mt-2">
        <div class="overflow-x-auto">
            <table class="table">
                <tbody>
                    <tr>
                        <td></td>
                        <td>Contado</td>
                        <td v-if="isadmin">Calculado</td>
                        <td v-if="isadmin">Diferencia</td>
                    </tr>
                    <tr>
                        <td><strong>Efectivo</strong></td>
                        <td><label class="input-group"><span>$</span><input type="number"
                                    class="input input-bordered w-full max-w-xs" v-model="cash"></label></td>
                        <template v-if="isadmin">
                            <td>
                                <label class="input-group"><span>$</span><input type="number"
                                        class="input input-bordered w-full max-w-xs" :value="payments.cash"></label>
                            </td>
                            <td> <strong :class="[(cashCalculated == 0) ? 'text-green-600' : 'text-red-600']">${{
                                cashCalculated }}</strong> </td>
                        </template>
                    </tr>
                    <tr>
                        <td><strong>Tarjeta</strong></td>
                        <td><label class="input-group"><span>$</span><input type="number"
                                    class="input input-bordered w-full max-w-xs" v-model="card"></label></td>
                        <template v-if="isadmin">
                            <td>
                                <label class="input-group"><span>$</span><input type="number"
                                        class="input input-bordered w-full max-w-xs" :value="payments.card"></label>
                            </td>
                            <td> <strong :class="[(cardCalculated == 0) ? 'text-green-600' : 'text-red-600']">${{
                                cardCalculated }}</strong> </td>
                        </template>
                    </tr>
                </tbody>
            </table>
            <button @click="saveCashBox" class="btn btn-neutral">Guardar</button>
        </div>
    </div>
</template>

<script>
import Swal from "@node/sweetalert2";
import { postToApi } from '@base/js/request/resquestToApi';

export default {
    name: "Cashbox",
    props: {
        payments: JSON,
        isadmin: String,
    },
    data() {
        return {
            cash: 0,
            card: 0,
            cashout: 0
        };
    },
    computed: {
        cashCalculated: function () {
            return (this.cash - this.payments.cash).toFixed(2);
        },
        cardCalculated: function () {
            return (this.card - this.payments.card).toFixed(2);
        },
        cashInBox: function () {
            return (this.cash - this.cashout).toFixed(2);
        },
    },
    methods: {
        async saveCashBox() {
            const urlParams = new URLSearchParams(window.location.search);
            Swal.fire({
                title: '¿Estás seguro de realizar el corte de caja? Esta acción no se puede deshacer',
                icon: "question",
                confirmButtonText: 'Si, Guardar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await postToApi(`/admin/cashbox/save`, {
                        cash: this.cash,
                        card: this.card,
                        cardCalc: this.payments.card,
                        cashCalc: this.payments.cash,
                        cashout: this.cashout,
                        type: urlParams.get('type'),
                    });
                    // Swal.fire('Se guardo el reporte correctamente', '', 'success')
                    window.location.href = "/admin/cash-box-report";
                }
            });
        }
    },
};
</script>
