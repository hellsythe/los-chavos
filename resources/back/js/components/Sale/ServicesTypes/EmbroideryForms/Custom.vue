<template>
    <div class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Tipografía</span></label>
        <TypeaheadInput :loadFromApiUrl="'/admin/typography/api?name={search}&page=1'" @selected="selectedData" :ignoredList="selectedItemIds"
            placeholder="Escribe el nombre del diseño">
        </TypeaheadInput>
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';

export default {
    name: "CustomEmbroidery",
    props: {
        service: JSON,
    },
    components: {
        TypeaheadInput
    },
    data() {
        return {
            availabletypography: {},
            selectedItemIds: [],
        };
    },
    mounted() {
    },
    methods: {
        selectedData(value) {
            this.service.typography = {
                id: value.id,
                name: value.name,
            };
        },
    },
};
</script>
