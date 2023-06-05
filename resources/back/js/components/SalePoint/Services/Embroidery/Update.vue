<template>
    <div class="flex">
        <div class="mr-2">
            <label for="" class="label"><span class="label-text">Costo por ponchado modificado</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.detail.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail?.price }}</div>
        </div>
        <div class="form-control mb-2">
            <label for="" class="label"><span class="label-text">Puntadas</span></label>
            <input type="number" class="input input-bordered" v-model="service.detail.design.minutes"/>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail?.design?.minutes }}</div>
        </div>
    </div>
    <DesignComponent text="Diseño a mofificar" :service="service" :errors="errors"></DesignComponent>
    <LoadFile :file="service.detail.design" :errors="errors" />
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
    mounted() {
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
        }
    },
};
</script>
