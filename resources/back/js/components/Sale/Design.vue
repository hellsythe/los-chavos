<template>
    <div>
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Diseño</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/design/api?name={search}&page=1'" @selected="selectedData" :ignoredList="selectedItemIds"
                placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>
        <div v-if="getDesignFileFormat() == 'pdf'"  class="flex flex-col justify-end items-end">
            <button @click="showPreview = false" v-if="showPreview" class="btn btn-info mt-3 mb-3">Ocultar Preview</button>
            <button @click="showPreview = true" v-if="!showPreview" class="btn btn-info mt-3 mb-3">Mostrar Preview</button>
            <embed v-if="showPreview" :src="this.service.design.media" width="90%" height="500px" />
        </div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';

export default {
    name: "Desing",
    props: {
        service: JSON,
    },
    components: {
        TypeaheadInput
    },
    data() {
        return {
            selectedItemIds: [],
            showPreview: true,
        };
    },
    mounted() {

    },
    methods: {
        selectedData(value) {
            this.service.design = {
                id: value.id,
                name: value.name,
                media: value.media,
            };
        },
        getDesignFileFormat(){
            return this.service.design?.media.split('.').pop();
        }
    },
};
</script>
