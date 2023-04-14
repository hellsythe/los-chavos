<template>
    <div v-show="index == currentServiceIndex">
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
            <select class="select select-bordered w-full" v-model="service.service_id" :onchange="loadSubservicesFromApi">
                <option disabled selected>Elije uno</option>
                <option :value="service.id" v-for="service in availableservices ">{{ service.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.service_id }}</div>
        </div>

        <EmbroideryComponent :service="service" v-if="service.service_id == 1" :errors="errors" />
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
            availablesubservices: {},
            errors: {}
        };
    },
    created() {
        this.cleanErrors();
    },
    methods: {
        async loadSubservicesFromApi() {
            let response = await resquestToApi('/admin/subservice/api?service_id=1&page=1');
            this.availablesubservices = response.data;
        },
        validate() {
            let errors = false;

            this.cleanErrors();
            if (!this.service.hasOwnProperty('service_id') || this.service.service_id.length === 0) {
                this.errors.service_id = 'El Tipo servicio no puede estar vacio';
                errors = true;
            };

            if (!this.service.hasOwnProperty('subservice_id') || this.service.subservice_id.length === 0) {
                this.errors.subservice_id = 'El Tipo subservicio no puede estar vacio';
                errors = true;
            };

            if (!this.service.hasOwnProperty('price') || this.service.price.length === 0) {
                this.errors.price = 'El Precio por prenda no puede estar vacio';
                errors = true;
            };

            if (!this.service.hasOwnProperty('design') || this.service.design.length === 0) {
                this.errors.design = 'El Diseño no puede estar vacio';
                errors = true;
            };

        },
        cleanErrors() {
            this.errors = {};
        },
    },
};
</script>
