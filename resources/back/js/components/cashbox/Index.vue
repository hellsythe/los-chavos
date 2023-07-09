<template>
    <button class="btn btn-neutral mb-4">Reportes</button>
    <div class="p-4 bg-base-100 mb-5 shadow rounded-lg mt-2">
        <div class="overflow-x-auto">
            <table class="table">
                <tbody>
                    <tr>
                        <td></td>
                        <td>Contado</td>
                        <td>Calculado</td>
                        <td>Diferencia</td>
                    </tr>
                    <tr>
                        <td><strong>Efectivo</strong></td>
                        <td><label class="input-group"><span>$</span><input type="number" class="input input-bordered w-full max-w-xs" v-model="cash"></label></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric" :value="payments.cash"></td>
                        <td> <strong :class="[(cashCalculated == 0) ? 'text-green-600' : 'text-red-600']">${{ cashCalculated}}</strong> </td>
                    </tr>
                    <tr>
                        <td><strong>Tarjeta</strong></td>
                        <td><label class="input-group"><span>$</span><input type="number" class="input input-bordered w-full max-w-xs" v-model="card"></label></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric"
                                :value="payments.card"></td>
                        <td> <strong :class="[(cardCalculated == 0) ? 'text-green-600' : 'text-red-600']">${{ cardCalculated }}</strong> </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-2 mb-2 flex">
                <div>
                    <p class="mb-2">¿Cuanto Retiras de efectivo?</p>
                    <label class="input-group"><span>$</span><input type="number" class="input input-bordered" v-model="cashout"></label>
                </div>
                <div>
                    <p class="mb-2">Dinero en caja</p>
                    <label class="input-group"><span>$</span><input type="number" class="input input-bordered" readonly></label>
                </div>
            </div>
            <button @click="saveCashBox" class="btn btn-neutral">Guardar</button>
        </div>
    </div>
</template>

<script>
import Swal from "@node/sweetalert2";

export default {
    name: "Cashbox",
    props: {
        payments: JSON,
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
    },
    methods: {
        saveCashBox() {
            Swal.fire({
                title: '¿Estás seguro de realizar el corte de caja? Esta acción no se puede deshacer',
                icon: "question",
                confirmButtonText: 'Si, Guardar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Saved!', '', 'success')
                }
            });
        }
    },
};
</script>
