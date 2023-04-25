<template>
    <div class="form-control mb-2 mr-2">
        <div class="mt-3 flex items-center">
            <input v-model="service.is_new_design" type="checkbox" checked="checked" class="checkbox mr-1" /> ¿Es un Diseño
            Nuevo?
        </div>

        <div class="mt-3 flex items-center" v-if="service.is_new_design">
            <input v-model="service.design_is_here" type="checkbox" checked="checked" class="checkbox mr-1" /> ¿Se cuenta
            con el diseño?
        </div>

        <DesignPrint v-else :service="service" text="Diseño Existente" :errors="errors" />


        <div v-if="service.is_new_design && service.design_is_here" class="form-control w-64 mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Selecciona el nuevo diseño</span></label>
            <input type="file" class="file-input w-full max-w-xs" @change="handleFileUpload($event)" />
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.new_print }}</div>
        </div>

        <div v-if="service.is_new_design && !service.design_is_here" class="alert alert-warning shadow-lg mt-2">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Si no se cuenta con diseño este se debera subir posteriormente</span>
            </div>
        </div>
        <div class="mt-3 flex items-center" v-if="service.is_new_design">
            <input v-model="service.save_design" type="checkbox" checked="checked" class="checkbox mr-1" /> ¿El diseño se
            debe guardar o solo es temporal?
        </div>
    </div>
</template>

<script>

import DesignPrint from './DesignPrint.vue';

export default {
    name: "PrintComponent",
    components: {
        DesignPrint
    },
    computed: {

    },
    props: {
        service: JSON,
        errors: JSON,
    },
    data() {
        return {

        };
    },
    mounted() {
        this.service.is_new_design = true;
        this.service.design_is_here = true;
        this.service.save_design = false;
    },
    methods: {
        async handleFileUpload(e) {
            let that = this;
            let file = e.target.files[0];
            let reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function () {
                that.service.new_print_file = reader.result;
            }
        }
    },
};
</script>
