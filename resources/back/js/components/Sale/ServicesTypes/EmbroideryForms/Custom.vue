<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipografía</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/typography/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
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

    <div v-if="this.service.typography"  class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Texto a Bordar</span></label>
        <TipTap v-model="service.custom.text" :style="{ 'font-family': fontFamily }"></TipTap>
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';
import TipTap from '@base/js/components/Crud/Form/Fields/TipTapComponent.vue';

export default {
    name: "CustomEmbroidery",
    props: {
        service: JSON,
    },
    components: {
        TypeaheadInput,
        TipTap
    },
    data() {
        return {
            availabletypography: {},
            selectedItemIds: [],
            fontFamily: 'sans',
        };
    },
    created() {
        this.service.custom = {text:'a'}
        console.log(this.service.custom.text);
    },
    methods: {
        selectedData(value) {
            this.service.typography = {
                id: value.id,
                name: value.name,
                slutname: value.name.replace(/\s+/g, ''),
                font: value.example,
            };

            this.loadFontFromUrl();
        },
        loadFontFromUrl() {
            let newStyle = document.createElement('style');
            newStyle.appendChild(document.createTextNode(`
                @font-face {
                    font-family: '${this.service.typography.slutname}';
                    src: url('${this.service.typography.font}');
                }
          `));
          document.head.appendChild(newStyle);
          this.fontFamily = this.service.typography.slutname;
        }
    },
};
</script>
