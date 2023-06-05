<template>
    <div class="lg:flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipo de Bordado</span></label>
            <select class="select select-bordered w-full" v-model="service.subservice">
                <option disabled selected>Elije uno</option>
                <option v-for="subservice in availablesubservices" :value="{id: subservice.id, name: subservice.name}" >{{ subservice.name }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.subtype }}</div>
        </div>

        <div class="form-control mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Costo por prenda</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.price }}</div>
        </div>
    </div>
    <NewComponent v-if="service.subservice.id == 4" :service="service" :errors="errors" />
    <UpdateComponent v-if="service.subservice.id == 3" :service="service" :errors="errors" />
    <CustomComponent v-if="service.subservice.id == 2" :service="service" :errors="errors" />
    <DesignComponent v-if="service.subservice.id == 1" :service="service" text="Diseño Existente" :errors="errors" />
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import NewComponent from './New.vue';
import DesignComponent from './Design.vue';
import CustomComponent from './Custom.vue';
import UpdateComponent from './Update.vue';

export default {
    name: "Embroidery",
    props: {
        service: JSON,
        availableservices: JSON,
        errors: JSON,
    },
    components: {
        NewComponent,
        UpdateComponent,
        CustomComponent,
        DesignComponent,
    },
    data() {
        return {
            availablesubservices: {}
        };
    },
    mounted() {
        this.availablesubservices = this.availableservices.find(element => element.id == 1).subservices;
    },
};
</script>
