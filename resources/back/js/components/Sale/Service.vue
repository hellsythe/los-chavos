<template>
    <div v-show="index == currentServiceIndex">
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
            <select class="select select-bordered w-full" v-model="service.service_id"
                :onchange="loadSubservicesFromApi">
                <option disabled selected>Elije uno</option>
                <option :value="service.id" v-for="service in availableservices ">{{ service.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <EmbroideryComponent :service="service" v-if="service.service_id == 1" />

    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import DesignComponent from './Design.vue';
import EmbroideryComponent from './ServicesTypes/Embroidery.vue';

export default {
    name: "Service",
    props: {
        service: JSON,
        availableservices: JSON,
        index: Number,
        currentServiceIndex: Number,
    },
    components: {
        DesignComponent,
        EmbroideryComponent,
    },
    data() {
        return {
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
