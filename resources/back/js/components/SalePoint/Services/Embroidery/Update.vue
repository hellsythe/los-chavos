<template>
    <div class="flex">
        <div class="mr-2">
            <label for="" class="label"><span class="label-text">Costo por ponchado modificado</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.detail.design.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.price }}</div>
        </div>
        <div class="form-control mb-2">
            <label for="" class="label"><span class="label-text">Puntadas</span></label>
            <input type="number" class="input input-bordered" v-model="service.detail.design.minutes"/>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.minutes }}</div>
        </div>
    </div>
    <DesignComponent text="Diseño a mofificar" :design="service.detail.old_design" :error="errors.detail.old_design.name"></DesignComponent>
    <LoadFile :file="service.detail.design" :error="errors.detail.design.file" />

</template>

<script>
import DesignComponent from './Design.vue';
import LoadFile from './../../LoadFile.vue';

export default {
    name: "UpdateEmbroidery",
    components: {
        DesignComponent,
        LoadFile,
    },
    props: {
        service: JSON,
        errors: JSON,
    },
    data() {
        return {
            showPreview: true,
        };
    },
    created() {
        if (!this.service.detail.old_design) {
            this.service.detail.old_design = {};
        }
    },
    methods: {
        async handleFileUpload(e) {
            let that = this;
            let file = e.target.files[0];
            let reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function () {
                that.service.updateDesign = reader.result;
            }
        },
        validate() {
            if (!this.service.detail.design.price) {
                this.errors.detail.design.price = 'El costo no puede estar vacio';
            }
            if (!this.service.detail.old_design.id) {
                this.errors.detail.old_design.name = 'El diseño anterior a modificar no puede estar vacio';
            }
            if (!this.service.detail.design.file) {
                this.errors.detail.design.file = 'El archivo del nuevo diseño no puede estar vacio';
            }
            // if (!this.service.detail.text) {
            //     this.errors.detail.text = 'El texto no puede estar vacio';
            // }
            // if (!this.service.detail.typography.name) {
            //     this.errors.detail.typography = 'El tipo de letra no puede estar vacio';
            // }
        }
    },
};
</script>
