<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between mb-1">
                <h1 class="font-bold">Servicios</h1>
                <button @click="extra.steps.service = true" v-if="!extra.steps.service">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="extra.steps.service = false" v-if="extra.steps.service">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-show="extra.steps.service">
                <ul class="menu menu-vertical lg:menu-horizontal bg-base-100 rounded-box">
                    <li @click="currentServiceIndex = index" v-for="(service, index) in order.services"
                        @contextmenu="onContextMenu($event, index)">
                        <a :class="{ active: index == currentServiceIndex }">
                            {{ service.name ?? `Servicio ${index + 1}` }}
                            <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
                        </a>
                    </li>
                    <li dusk="service-new-service" @click="addNewService">
                        <a>Añadir Servicio
                            <PlusCircleIcon class="h-4 mr-1" />
                        </a>
                    </li>
                </ul>
                <div v-for="(service, index) in order.services">
                    <ServiceComponent :errors="extra.errors.services[index]" :index="index" :service="service"
                        :ref="'services'" :currentServiceIndex="currentServiceIndex"
                        :availableservices="availableservices" />
                </div>
                <div class="flex justify-end	">
                    <button dusk="service-next" class="btn btn-info" @click="validate">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    EyeSlashIcon,
    EyeIcon,
    PlusCircleIcon,
} from "@heroicons/vue/24/solid";

import ServiceComponent from './Service.vue';
import colors from './../../colors';
import { getAllErrorsAsArray } from '@base/js/getErrors';

export default {
    name: "Services",
    props: {
        order: JSON,
        extra: JSON,
        availableservices: JSON,
    },
    components: {
        EyeIcon,
        EyeSlashIcon,
        PlusCircleIcon,
        ServiceComponent,
    },
    computed: {
        get_colors() { return colors }
    },
    data() {
        return {
            currentServiceIndex: 0,
        };
    },
    created() {
        this.extra.errors.services = [];
        this.order.services.forEach((service, index) => {
            this.extra.errors.services[index] = {};
        });
    },
    mounted() {
        let that =  this;
        window.addEventListener("dispachValidations", function (event) {
            for (let index = 0; index < that.order.services.length; index++) {
                that.$refs.services[index].validate();
            }
        });
    },
    methods: {
        addNewService() {
            this.order.services.push({
                detail: {
                    design: {}
                },
                service: {},
                subservice: {},
            });
            this.extra.errors.services.push({});
        },
        validate() {
            let errors = false;
            for (let index = 0; index < this.order.services.length; index++) {
                errors = errors || this.$refs.services[index].validate();
            }

            if (getAllErrorsAsArray(this.extra.errors.services).length == 0) {
                this.extra.steps.service = false;
                this.extra.steps.confirm = true;
            }
        },
        onContextMenu(e, index) {
            e.preventDefault();
            this.$contextmenu({
                x: e.x,
                y: e.y,
                items: [
                    {
                        label: "Eliminar el ultimo servicio",
                        onClick: () => {
                            if (index != 0) {
                                this.order.services.pop();
                            }
                        }
                    },
                ]
            });
        }
    },
};
</script>
