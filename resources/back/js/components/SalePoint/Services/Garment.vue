<template>
    <div class="lg:flex">
        <div class="form-control w-full mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Tipo de prenda</span></label>
            <TypeaheadInput :currentValue="service.garment?.name" :dusk="`service${index}-garment`" :loadFromApiUrl="'/admin/garment/api?name={search}&page=1'" @selected="selectedData"
                :ignoredList="selectedItemIds" placeholder="Escribe el nombre la prenda">
            </TypeaheadInput>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.garment?.garment }}</div>
        </div>
        <div class="form-control mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Cantidad de prendas</span></label>
            <input :dusk="`service${index}-garment-amount`" v-model="service.garment_amount" type="number" class="input input-bordered w-full">
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.garment?.amount }}</div>
        </div>
    </div>
    <div v-show="service.garment?.preview" class="mt-3">
        <img :src="service.garment?.preview" alt="" class="absolute mt-3 rounded-md">
        <div :id="'container'+index"></div>
    </div>
</template>

<script>
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';
import Konva from 'konva';
import colors from './../../colors';

export default {
    name: "Garment",
    props: {
        service: JSON,
        errors: JSON,
        index: Number,
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
            layer: null
        };
    },
    mounted() {
        if (this.service.garment) {
            this.service.point_x = parseInt(this.service.point_x);
            this.service.point_y = parseInt(this.service.point_y);
            this.initCanva();
        }
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
            this.writeLayer();
            this.writtePoitsByEachDesign();
        },
        writeLayer() {
            var stage = new Konva.Stage({
                container: 'container'+this.index,
                width: 500,
                height: 300,
            });

            this.layer = new Konva.Layer();
            stage.add(this.layer);
        },
        writtePoitsByEachDesign() {
            this.point(this.get_colors[this.index]);
        },
        point(color) {
            if (!this.service.point_x) {
                this.service.point_x = 20;
                this.service.point_y = 20;
            }
            let circle = new Konva.Circle({
                x: this.service.point_x,
                y: this.service.point_y,
                radius: 8,
                fill: color,
                stroke: 'black',
                strokeWidth: 1,
                draggable: true
            });
            this.layer.add(circle);
            circle.on('dragmove', (t) => {
                this.service.point_x =  t.target.x();
                this.service.point_y =  t.target.y();
            });

        },
        validate() {
            this.errors.garment = {};

            if (!this.service.garment) {
                this.errors.garment.garment = 'La prenda no puede estar vacia';
            }

            if (!this.service.garment_amount) {
                this.errors.garment.amount = 'El numero de prendas no puede estar vacio';
            }
        }
    },
};
</script>
