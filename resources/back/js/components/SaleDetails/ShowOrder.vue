<template>
    <ul class="menu menu-vertical lg:menu-horizontal bg-base-100 rounded-box">
        <li @click="currentServiceIndex = index" v-for="(service, index) in services">
            <a :class="{ active: index == currentServiceIndex }">
                {{ service.name ?? `Servicio ${index + 1}` }}
                <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
            </a>
        </li>
        <li></li>
    </ul>
    <div v-for="(service, index) in services">
        <div v-show="index == currentServiceIndex">
            <Design v-if="service.subservice_id == 1" :details="service.order_design"/>
            <Custom v-if="service.subservice_id == 2" :details="service.order_custom_design"/>
            <Design v-if="service.subservice_id == 3" :details="service.order_update_design"/>
            <Design v-if="service.subservice_id == 4" :details="service.order_new_design"/>
            <label class="label"><span class="label-text">Comentarios:</span></label>
            <div class="form-control w-full mb-2">
                <textarea class="textarea" readonly>{{ service.comments }}</textarea>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <img :src="garment.preview" alt="" class="absolute mt-3 rounded-md">
        <div id="container"></div>
    </div>
</template>

<script>
import colors from './../colors';
import Konva from 'konva';
import Custom from './Embroidery/Custom.vue';
import Design from './Embroidery/Design.vue';

export default {
    name: "Services",
    props: {
        services: JSON,
        garment: JSON
    },
    components: {
        Custom,
        Design,
    },
    computed: {
        get_colors() { return colors }
    },
    data() {
        return {
            currentServiceIndex: 0,
        };
    },
    mounted() {
        this.initCanva();
    },
    methods: {
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
            for (let index = 0; index < this.services.length; index++) {
                this.point(this.get_colors[index], index);
            }
        },
        point(color, index) {
            let circle = new Konva.Circle({
                x: this.services[index].point_x,
                y: this.services[index].point_y,
                radius: 8,
                fill: color,
                stroke: 'black',
                strokeWidth: 1,
                draggable: false
            });
            circle.zIndex(99);
            this.layer.add(circle);
        },
    },
};
</script>
