<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipo de Bordado</span></label>
            <select class="select select-bordered w-full" v-model="service.subservice_id">
                <option disabled selected>Elije uno</option>
                <option v-for="subservice in availablesubservices" :value="{id: subservice.id, name: subservice.name}" >{{ subservice.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.subservice_id }}</div>
        </div>

        <div class="form-control mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Costo por prenda</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.price }}</div>
        </div>
        <div class="form-control mb-2" v-if="service.subservice_id.id == 3">
            <label for="" class="label"><span class="label-text">Costo por modificar ponchado</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.price_update" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.price_update }}</div>
        </div>
        <div class="form-control mb-2" v-if="service.subservice_id.id == 4">
            <label for="" class="label"><span class="label-text">Costo por ponchado nuevo</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.price_new" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.price_new }}</div>
        </div>
    </div>

    <DesignComponent v-if="service.subservice_id.id == 1" :service="service" text="Diseño Existente" :errors="errors" />

    <CustomComponent v-if="service.subservice_id.id == 2" :service="service" :errors="errors" />

    <UpdateComponent v-if="service.subservice_id.id == 3" :service="service" :errors="errors" />

    <NewComponent v-if="service.subservice_id.id == 4" :service="service" :errors="errors" />

    <div v-if="service.design" class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Comentarios</span></label>
        <textarea class="textarea textarea-bordered" placeholder="Comentarios para este diseño"
            v-model="service.comments"></textarea>
        <div class="text-red-500 text-xs font-semibold mt-1"></div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import DesignComponent from './../Design.vue';
import CustomComponent from './EmbroideryForms/Custom.vue';
import NewComponent from './EmbroideryForms/New.vue';
import UpdateComponent from './EmbroideryForms/Update.vue';

export default {
    name: "Embroidery",
    props: {
        service: JSON,
        availableservices: JSON,
        errors: JSON,
    },
    components: {
        DesignComponent,
        CustomComponent,
        NewComponent,
        UpdateComponent,
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
