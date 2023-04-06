<template>
    <div class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Tipo de Bordado</span></label>
        <select class="select select-bordered w-full" v-model="service.subservice_id">
            <option disabled selected>Elije uno</option>
            <option :value="service.id" v-for="service in availablesubservices">{{ service.name }}</option>
        </select>
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>

    <DesignComponent v-if="service.subservice_id == 1" :service="service"></DesignComponent>

    <CustomComponent v-if="service.subservice_id == 2" :service="service" />

    <div v-if="service.design" class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Comentarios</span></label>
        <textarea class="textarea textarea-bordered" placeholder="Comentarios para este diseño"></textarea>
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import DesignComponent from './../Design.vue';
import CustomComponent from './EmbroideryForms/Custom.vue';

export default {
    name: "Embroidery",
    props: {
        service: JSON,
        availableservices: JSON,
    },
    components: {
        DesignComponent,
        CustomComponent
    },
    data() {
        return {
            availablesubservices: {}
        };
    },
    mounted() {
        this.loadSubservicesFromApi();
    },
    methods: {
        async loadSubservicesFromApi() {
            let response = await resquestToApi('/admin/subservice/api?service_id=1&page=1');
            this.availablesubservices = response.data;
        }
    },
};
</script>
