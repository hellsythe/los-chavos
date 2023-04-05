<template>
    <div v-show="index == currentServiceIndex">
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
            <DesignComponent :service="selectedServices[index]"></DesignComponent>
            <GarmentComponent :service="selectedServices[index]"></GarmentComponent>
        </div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import DesignComponent from './Design.vue';
import GarmentComponent from './Garment.vue';

export default {
    name: "Service",
    props: {
        selectedServices: JSON,
        availableservices: JSON,
        index: Number,
        currentServiceIndex: Number,
    },
    components: {
        GarmentComponent,
        DesignComponent
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
        async loadSubservicesFromApi() {
            let response = await resquestToApi('/admin/subservice/api?service_id=1&page=1');
            this.availablesubservices = response.data;
        }
    },
};
</script>
