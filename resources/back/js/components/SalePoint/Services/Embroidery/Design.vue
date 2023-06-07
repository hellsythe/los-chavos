<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">{{ text }}</span></label>
            <TypeaheadInput :currentValue="service.detail.design.name" :loadFromApiUrl="'/admin/design/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.id }}</div>
        </div>
        <div class="mt-6">
            <button @click="showPreview = false" v-if="showPreview" class="btn btn-info mt-3 mb-3">Ocultar Preview</button>
            <button @click="showPreview = true" v-if="!showPreview" class="btn btn-info mt-3 mb-3">Mostrar Preview</button>
        </div>
    </div>
    <div v-if="getDesignFileFormat() == 'pdf'" class="flex flex-col justify-end items-end">
        <embed v-if="showPreview" :src="this.service.detail.design.media" width="100%" height="700px" />
    </div>
    <div v-if="getDesignFileFormat() == 'png' || getDesignFileFormat() == 'jpg'" class="flex flex-col justify-end items-end">
        <img v-if="showPreview" :src="this.service.detail.design.media" width="100%" height="700px" />
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';

export default {
    name: "Desing",
    props: {
        service: JSON,
        text: String,
        errors: JSON,
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
    methods: {
        selectedData(value) {
            this.service.detail.design = {
                id: value.id,
                name: value.name,
                media: value.media,

            };
            this.service.detail.price = value.price;
        },
        getDesignFileFormat() {
            return this.service.detail?.design?.media?.split('.').pop();
        }
    },
};
</script>
