<template>
    <div v-show="index == currentServiceIndex">
        <div class="form-control w-full mb-2 mt-2">
            <label><span class="label-text">Tipo de servicio</span></label>
            <select :dusk="`service${index}-service`" class="select select-bordered w-full" v-model="service.service">
                <option disabled selected>Elije uno</option>
                <option :value="{ id: service.id, name: service.name }" v-for="(service, service_index) in availableservices " :dusk="`service${index}-service-option-${service_index}`">{{
                    service.name
                }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.service }}</div>
        </div>

        <EmbroideryComponent v-if="service.service.id == 1"  :service="service" :errors="errors" :availableservices="availableservices" :index="index"  ref="embrodery" :cost="cost"/>
        <PrintComponent v-if="service.service.id == 2" :service="service" :errors="errors" ref="print" />
        <GarmentComponent :service="service" :errors="errors" ref="garment" :index="index" />

        <div class="form-control w-full mb-2 mt-2">
            <label for="" class="label"><span class="label-text">Comentarios</span></label>
            <textarea class="textarea textarea-bordered h-40" placeholder="Comentarios"
                v-model="service.comments"></textarea>
        </div>
    </div>
</template>

<script>
import EmbroideryComponent from './Embroidery/Embroidery.vue';
import PrintComponent from './Print/Print.vue';
import GarmentComponent from './Garment.vue';

export default {
    name: "Service",
    props: {
        errors: JSON,
        service: JSON,
        availableservices: JSON,
        cost: JSON,
        index: Number,
        currentServiceIndex: Number,
    },
    components: {
        EmbroideryComponent,
        PrintComponent,
        GarmentComponent,
    },
    created() {
        if (!this.service.detail) {
            this.service.detail = {};
        }
        this.cleanErrors();
    },
    methods: {
        validate() {
            this.cleanErrors();
            this.validateCommon();

            if (this.$refs.embrodery) {
                this.$refs.embrodery.validate();
            }
            if (this.$refs.print) {
                this.$refs.print.validate();
            }

            this.$refs.garment.validate();

        },
        validateCommon() {
            if (!this.service.service.id) {
                this.errors.service = 'El Tipo servicio no puede estar vacio';
            };
        },
        cleanErrors()
        {
            this.errors.detail = {};
            this.errors.detail.design = {};
            this.errors.detail.old_design = {};
        }
    },
};
</script>
