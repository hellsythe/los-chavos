<template>
    <div v-show="index==currentServiceIndex">
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
            <select class="select select-bordered w-full" v-model="selectedServices[index].service_id"
                :onchange="loadSubservicesFromApi">
                <option disabled selected>Elije uno</option>
                <option :value="service.id" v-for="service in availableservices ">{{ service.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
            <select class="select select-bordered w-full" v-model="selectedServices[index].subservice_id">
                <option disabled selected>Elije uno</option>
                <option :value="service.id" v-for="service in availablesubservices">{{ service.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="flex justify-end">
            <button class="btn btn-info">Siguiente</button>
        </div>
    </div>
</template>

<script>

export default {
    name: "Service",
    props: {
        selectedServices: JSON,
        availableservices: JSON,
        index: Number,
        currentServiceIndex: Number,
    },
    data() {
        return {
            service: {},
            availablesubservices: {}
        };
    },
    mounted() {

    },
    methods: {
        loadSubservicesFromApi() {
            const cookieValue = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                .split('=')[1];

            return fetch('/admin/subservice/api?service_id=1&page=1', {
                "headers": {
                    "Accept": "application/json",
                    'X-XSRF-TOKEN': cookieValue
                }
            })
                .then((response) => response.json())
                .then((response) => {
                    this.availablesubservices = response.data;
                });
        }
    },
};
</script>
