<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Selecciona el diseño para el estampado</span></label>
            <TypeaheadInput :currentValue="design.name" :loadFromApiUrl="'/admin/design-print/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ error }}</div>
        </div>
        <div class="mt-6">
            <button @click="showPreview = false" v-if="showPreview" class="btn btn-info mt-3 mb-3">Ocultar Preview</button>
            <button @click="showPreview = true" v-if="!showPreview" class="btn btn-info mt-3 mb-3">Mostrar Preview</button>
        </div>
    </div>
    <div v-if="fileFormat == 'pdf'" class="flex flex-col justify-end items-end">
        <embed v-if="showPreview" :src="this.design.media" width="100%" height="700px" />
    </div>
    <div v-if="fileFormat == 'png' || fileFormat == 'jpg' || fileFormat == 'jpeg'" class="flex flex-col justify-end items-end">
        <img v-if="showPreview" :src="this.design.media" width="100%" height="700px" style="width: 700px;"/>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';

export default {
    name: "DesingPrintSelect",
    props: {
        design: JSON,
        error: String
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
    computed: {
        fileFormat() {
            return this.design.media?.split('.').pop();
        }
    },
    methods: {
        selectedData(value) {
            this.design.id = value.id;
            this.design.name = value.name;
            this.design.media = value.media;
            this.design.price = value.price;
        },
    },
};
</script>
