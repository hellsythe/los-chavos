<template>
    <div class="lg:flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipo de prenda</span></label>
            <TypeaheadInput :loadFromApiUrl="'/admin/garment/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre la prenda">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ error.data }}</div>
        </div>
        <div class="form-control mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Cantidad de prendas</span></label>
            <input v-model="garment.amount" type="number" class="input input-bordered w-full">
            <div class="text-red-500 text-xs font-semibold mt-1">{{ error.amount }}</div>
        </div>
    </div>
    <div v-show="garment.data?.preview" class="mt-3">
        <img :src="garment.data?.preview" alt="" class="absolute mt-3 rounded-md">
        <div id="container"></div>
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
        error: JSON,
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
                width: 500,
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

            if (!this.selectedServices[index].point) {
                this.selectedServices[index].point = {
                    x: 20 * (index + 1),
                    y: 20,
                }
            }

            let circle = new Konva.Circle({
                x: this.selectedServices[index].point.x,
                y: this.selectedServices[index].point.y,
                radius: 8,
                fill: color,
                stroke: 'black',
                strokeWidth: 1,
                draggable: true
            });
            circle.zIndex(99);
            this.layer.add(circle);
            circle.on('dragmove', (t) => {
                this.selectedServices[index].point = {
                    x: t.target.x(),
                    y: t.target.y(),
                }
            });

        },
    },
};
</script>
