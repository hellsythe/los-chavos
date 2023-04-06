<template>
    <div>
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de prenda</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/garment/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre la prenda">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold"></div>
            <div v-show="garment.data?.preview" class="mt-3">
                <img :src="garment.data?.preview" alt="" class="absolute mt-3">
                <div id="container"></div>
            </div>
        </div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';
import Konva from 'konva';
import colors from './../colors';

export default {
    name: "Garment",
    props: {
        garment: JSON,
        selectedServices: JSON,
    },
    data() {
        return {
            layer: null
        };
    },
    components: {
        TypeaheadInput,
    },
    computed: {
        get_colors() { return colors }
    },
    data() {
        return {
            selectedItemIds: [],
            showPreview: true,
            configKonva: {
                width: 200,
                height: 200
            },
            configCircle: {
                x: 100,
                y: 100,
                radius: 70,
                fill: "red",
                stroke: "black",
                strokeWidth: 4
            }
        };
    },
    methods: {
        selectedData(value) {
            this.garment.data = {
                id: value.id,
                name: value.name,
                preview: value.preview,
            };

            this.initCanva();
        },
        initCanva() {
            this.writeLayer();
            this.writtePoitsByEachDesign();
        },
        writeLayer() {
            var stage = new Konva.Stage({
                container: 'container',
                width: window.innerWidth,
                height: 300,
            });

            this.layer = new Konva.Layer();
            stage.add(this.layer);
        },
        writtePoitsByEachDesign() {
            for (let index = 0; index < this.selectedServices.length; index++) {
                this.point(this.get_colors[index], index);
            }
        },
        point(color, index) {
            let circle = new Konva.Circle({
                x: 20 * (index+1),
                y: 20 ,
                radius: 8,
                fill: color,
                stroke: 'black',
                strokeWidth: 1,
                draggable: true
            });
            circle.zIndex(99);
            this.layer.add(circle);

        }
    },
};
</script>
