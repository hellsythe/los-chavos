<template>
    <div>
        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Tipo de prenda</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/garment/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre la prenda">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold"></div>
            <div v-show="service.garment?.preview">
                <img :src="service.garment?.preview" alt="" class="absolute">
                <div id="container"></div>
            </div>
        </div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';
import Konva from 'konva';

export default {
    name: "Garment",
    props: {
        service: JSON,
    },
    components: {
        TypeaheadInput,
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
            this.service.garment = {
                id: value.id,
                name: value.name,
                preview: value.preview,
            };

            this.initCanva();
        },
        initCanva() {
            let layer = this.writeLayer();
            this.point(layer);
        },
        writeLayer() {
            var stage = new Konva.Stage({
                container: 'container',
                width: window.innerWidth,
                height: 300,
            });

            var layer = new Konva.Layer();
            stage.add(layer);

            return layer;
        },
        point(layer) {
            var circle = new Konva.Circle({
                x: 200,
                y: 200,
                radius: 10,
                fill: 'red',
                stroke: 'black',
                strokeWidth: 3,
                draggable: true
            });
            circle.zIndex(99);
            layer.add(circle);

        }
    },
};
</script>
