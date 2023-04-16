<template>
    <div id="ticket" style="display: block;">
            <div style="width: 170px; padding: 2px; font-size: 12px;">
                <div style="display: flex; justify-content: center;">
                    <img style="width: 130px;" src="/img/logo.svg" alt="">
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                    <label>Folio: 341323</label>
                    <label>Fecha: {{ date }}</label>
                </div>
                <table style="border-collapse: collapse; border: 1px solid; padding: 5px; width: 100%; margin-top: 10px;">
                    <tr>
                        <th style="font-size: 12px;border: 1px solid; padding: 2px; text-align: left;">Concepto</th>
                        <th style="font-size: 12px;border: 1px solid; padding: 2px; text-align: left;">Total</th>
                    </tr>
                    <template v-for="(service, index) in selectedServices">
                        <tr>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px">{{ service.subservice_id.name }} </td>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">${{ service.price?.toFixed(2) * garmentData.amount }}</td>
                        </tr>
                        <tr v-if="service.subservice_id.id == 4 && garmentData.amount <= 6">
                            <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Diseño nuevo</td>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">${{ service.price_new?.toFixed(2) }} </td>
                        </tr>
                        <tr v-if="service.subservice_id.id == 3  && garmentData.amount <= 6">
                            <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Modificar diseño </td>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">${{ service.price_update?.toFixed(2) }}</td>
                        </tr>
                    </template>
                    <tr>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>Total</strong></td>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>${{ payment.total }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
</template>

<script>

export default {
    name: "Ticket",
    props: {
        selectedServices: JSON,
        garmentData: JSON,
        payment: JSON,
    },
    components: {
    },
    data() {
        return {
            date: '',
        };
    },
    mounted() {
        this.getDate();
    },
    methods: {
        print() {
            var divContents = document.getElementById("ticket").innerHTML;
            var a = window.open('', '', 'height=500, width=600');
            a.document.write('<html>');
            a.document.write('<body >');
            a.document.write(divContents);
            a.document.write('</body></html>');
            a.document.write('<script type="text/javascript">window.onafterprint = window.close; window.print();<\/script>');
            a.document.close();
            a.print();
        },
        getDate(){
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            this.date = dd + '-' + mm + '-' + yyyy;
        }
    },
};
</script>
