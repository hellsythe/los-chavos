<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipografía</span></label>
            <TypeaheadInput :currentValue="service.detail.typography.name" :loadFromApiUrl="'/admin/typography/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre del diseño">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.typography }}</div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tamaño</span></label>
            <select v-model="this.service.detail.size" class="select select-bordered w-full">
                <option disabled selected>Elije uno</option>
                <option>Adulto max 10 cm de ancho</option>
                <option>Niño max 7 cm de ancho</option>
            </select>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.size }}</div>
        </div>
    </div>

    <div v-if="this.service.detail.typography" class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Texto a Bordar</span></label>
        <TipTap v-model="service.detail.text" :style="{ 'font-family': fontFamily }"></TipTap>
        <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.text }}</div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';
import TipTap from '@base/js/components/Crud/Form/Fields/TipTapComponent.vue';

export default {
    name: "CustomEmbroidery",
    props: {
        service: JSON,
        errors: JSON,
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
        if (!this.service.detail.typography) {
            this.service.detail.typography = {};
        }
    },
    methods: {
        selectedData(value) {
            this.service.detail.typography = {
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
                    font-family: '${this.service.detail.typography.slutname}';
                    src: url('${this.service.detail.typography.font}');
                }
          `));
            document.head.appendChild(newStyle);
            this.fontFamily = this.service.detail.typography.slutname;
        },
        validate() {
            if (!this.service.detail.size) {
                this.errors.detail.size = 'El tamaño de la letra no puede estar vacio';
            }
            if (!this.service.detail.text) {
                this.errors.detail.text = 'El texto no puede estar vacio';
            }
            if (!this.service.detail.typography.name) {
                this.errors.detail.typography = 'El tipo de letra no puede estar vacio';
            }
        }
    },
};
</script>
