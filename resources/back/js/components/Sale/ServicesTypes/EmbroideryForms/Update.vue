<template>
    <DesignComponent text="Diseño a mofificar" :service="service"></DesignComponent>

    <div class="flex">
        <div class="form-control w-64 mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Selecciona nuevo ponchado</span></label>
            <input type="file" class="file-input w-full max-w-xs" />
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>
        <div class="mt-6 ml-auto">
            <button @click="showPreview = false" v-if="showPreview" class="btn btn-info mt-3 mb-3">Ocultar Preview</button>
            <button @click="showPreview = true" v-if="!showPreview" class="btn btn-info mt-3 mb-3">Mostrar Preview</button>
        </div>
    </div>
</template>

<script>
import DesignComponent from './../../Design.vue';

export default {
    name: "UpdateEmbroidery",
    components: {
        DesignComponent
    },
    props: {
        service: JSON,
    },
    data() {
        return {
            showPreview: true,
        };
    },
    mounted() {
    },
    methods: {

    },
};
</script>
