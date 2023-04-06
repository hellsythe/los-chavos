<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipografía</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/typography/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Color</span></label>
            <select class="select select-bordered w-full ">
                <option disabled selected>Elije uno</option>
                <option>Rojo</option>
                <option>Amarillo</option>
                <option>Azul</option>
            </select>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tamaño</span></label>
            <select class="select select-bordered w-full">
                <option disabled selected>Elije uno</option>
                <option>Adulto max 10 cm de ancho</option>
                <option>Niño max 7 cm de ancho</option>
            </select>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>
    </div>
</template>

<script>
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
