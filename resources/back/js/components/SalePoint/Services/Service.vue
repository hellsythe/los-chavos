<template>
    <div v-show="index == currentServiceIndex">
        <div class="form-control w-full mb-2 mt-2">
            <label><span class="label-text">Tipo de servicio</span></label>
            <select class="select select-bordered w-full" v-model="service.service">
                <option disabled selected>Elije uno</option>
                <option :value="{ id: service.id, name: service.name }" v-for="service in availableservices ">{{
                    service.name
                }}</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.services[index]?.service }}</div>
        </div>

        <EmbroideryComponent :service="service" v-if="service.service.id == 1" :errors="extra.errors.services[index]"
            :availableservices="availableservices" />
        <!-- <PrintComponent :service="service" v-if="service.service.id == 2" :errors="extra.errors.services[index]" :availableservices="availableservices"/> -->

        <GarmentComponent :service="service" :errors="extra.errors.services[index]" :index="index" />
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Comentarios</span></label>
            <textarea class="textarea textarea-bordered" placeholder="Comentarios"
                v-model="service.comments"></textarea>
        </div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
// import DesignComponent from './Design.vue';
import EmbroideryComponent from './Embroidery/Embroidery.vue';
// import PrintComponent from './Print.vue';
import GarmentComponent from './Garment.vue';

export default {
    name: "Service",
    props: {
        extra: JSON,
        service: JSON,
        availableservices: JSON,
        index: Number,
        currentServiceIndex: Number,
    },
    components: {
        // DesignComponent,
        EmbroideryComponent,
        // PrintComponent,
        GarmentComponent,
    },
    created() {
        this.extra.errors.services[this.index] = {};
        this.cleanErrors();
        this.service.service = {};
        this.service.subservice = {};
    },
    methods: {
        validate() {
            this.cleanErrors();
            this.validateExistingDesign();
            this.validateCommon();
            this.validateNewDesign();
            this.validateCustomDesign();
            this.validateUpdatedDesign();
            if (Object.keys(this.errors).length !== 0) {
                return true;
            }
        },
        cleanErrors() {
            this.errors = {};
        },

        validateCommon() {
            if (!this.service.service_id.id) {
                this.errors.service_id = 'El Tipo servicio no puede estar vacio';
            };

            if (!this.service.subservice_id.id) {
                this.errors.subservice_id = 'El Tipo subservicio no puede estar vacio';
            };

            if (!this.service.hasOwnProperty('price') || this.service.price.length === 0) {
                this.errors.price = 'El Precio por prenda no puede estar vacio';
            };
        },
        validateExistingDesign() {
            if (this.service.subservice_id.id === 1) {
                if (!this.service.hasOwnProperty('design')) {
                    this.errors.design = 'El Diseño no puede estar vacio';
                }
            }
        },
        validateNewDesign() {
            if (this.service.subservice_id.id === 4) {
                if (!this.service.hasOwnProperty('new_design_name') || this.service.new_design_name === '') {
                    this.errors.new_design_name = 'El Nombre del nuevo diseño no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('newDesign') || this.service.new_design_name === '') {
                    this.errors.newDesign = 'El Archivo del nuevo Diseño no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('puntadas') || this.service.puntadas === '') {
                    this.errors.puntadas = 'Las puntadas del nuevo Diseño no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('price_new') || this.service.price_new === '') {
                    this.errors.price_new = 'El precio del nuevo Diseño no puede estar vacio';
                }
            }
        },
        validateUpdatedDesign() {
            if (this.service.subservice_id.id === 3) {
                if (!this.service.hasOwnProperty('updateDesign') || this.service.new_design_name === '') {
                    this.errors.updateDesign = 'El Archivo de Diseño Modificado no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('puntadas') || this.service.puntadas === '') {
                    this.errors.puntadas = 'Las puntadas del Diseño Modificado no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('price_update') || this.service.price_update === '') {
                    this.errors.price_update = 'El precio por actualizar Diseño no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('design')) {
                    this.errors.design = 'El Diseño anterior no puede estar vacio';
                }
            }
        },
        validateCustomDesign() {
            if (this.service.subservice_id.id === 2) {
                if (!this.service.hasOwnProperty('textsize') || this.service.textsize === '') {
                    this.errors.textsize = 'El Tamaño del la letra no puede estar vacio';
                }

                if (!this.service.hasOwnProperty('typography') || this.service.typography === '') {
                    this.errors.typography = 'La tipografía no puede estar vacio';
                }
                if (!this.service.hasOwnProperty('custom') || this.service.custom.text === '' || this.service.custom.text === '<p></p>') {
                    this.errors.custom = 'El texto personalizado no puede estar vacio';
                }
            };
        }
    },
};
</script>
