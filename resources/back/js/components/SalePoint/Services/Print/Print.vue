<template>
    <div class="form-control mb-2 mr-2">
        <div class="form-control mb-2 mr-2">
            <label>¿Es un Diseño Nuevo?</label>
            <select v-model="service.detail.is_new_design" class="select w-full max-w-xs">
                <option :value="false">No</option>
                <option :value="true">Si</option>
            </select>
        </div>
        <div v-if="service.detail.is_new_design" class="form-control mb-2 mr-2">
            <label>¿El cliente cuenta con el diseño en este momento?</label>
            <select v-model="service.detail.design_is_here" class="select w-full max-w-xs">
                <option :value="false">No</option>
                <option :value="true">Si</option>
            </select>
        </div>

        <DesignPrint v-else :design="service.detail.design" :error="errors.detail.design.id" />

        <div v-show="service.detail.is_new_design && service.detail.design_is_here">
            <div class="form-control mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Nombre del nuevo diseño de Estampado</span></label>
                <input type="text" class="input input-bordered" v-model="service.detail.design.name" />
                <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.name }}</div>
            </div>

            <div class="form-control w-full mb-2 mr-2">
                <LoadFile :file="service.detail.design" :error="errors.detail.design.file" />
            </div>
        </div>

        <div class="form-control mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Costo por prenda</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.price }}</div>
        </div>
        <div v-if="service.detail.is_new_design && !service.detail.design_is_here"
            class="alert alert-warning shadow-lg mt-2">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Si no se cuenta con diseño este se debera subir posteriormente</span>
            </div>
        </div>
        <div v-if="service.detail.design_is_here" class="form-control mb-2 mr-2">
            <label for="¿Es un Diseño Nuevo?">¿Se debe guardar el diseño para usarse en otras ordenes?</label>
            <select v-model="service.detail.save_design" class="select w-full max-w-xs">
                <option :value="false">No</option>
                <option :value="true">Si</option>
            </select>
        </div>
    </div>
</template>

<script>

import DesignPrint from './DesignPrint.vue';
import LoadFile from './../../LoadFile.vue';

export default {
    name: "PrintComponent",
    components: {
        DesignPrint,
        LoadFile,
    },
    props: {
        service: JSON,
        errors: JSON,
    },
    mounted() {
        this.service.detail.is_new_design = true;
        this.service.detail.design_is_here = true;
        this.service.detail.save_design = false;
        this.service.subservice = {
            id: 5,
            name: 'Estampado'
        }
    },
    created(){
        if (this.service.detail.design_print) {
            this.service.detail.design = this.service.detail.design_print;
        }
    },
    methods: {
        validate() {
            if (this.service.detail.is_new_design && this.service.detail.design_is_here) {
                if (!this.service.detail.design.name) {
                    this.errors.detail.design.name = 'El nombre del diseño no puede estar vacio';
                }

                if (!this.service.detail.design.file) {
                    this.errors.detail.design.file = 'El archivo del diseño no puede estar vacio';
                }
            }

            if (!this.service.detail.is_new_design) {
                if (!this.service.detail.design.id) {
                    this.errors.detail.design.id = 'El diseño a estampar no puede estar vacio';
                }
            }

            if (!this.service.detail.design.price) {
                this.errors.detail.price = 'El costo del diseño no puede estar vacio';
            }
        }
    }
};
</script>
