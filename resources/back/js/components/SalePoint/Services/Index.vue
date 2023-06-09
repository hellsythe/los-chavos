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
                <button class="join">
                    <button :class="{ 'bg-base-100': index != currentServiceIndex, 'btn-neutral': index == currentServiceIndex }" @click="currentServiceIndex = index" v-for="(service, index) in order.services" class="btn join-item"
                        @contextmenu="onContextMenu($event, index)">
                        <a class="flex">
                            {{ service.name ?? `Servicio ${index + 1}` }} &nbsp;
                            <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
                        </a>
                    </button>
                    <button dusk="service-new-service" @click="addNewService" v-if="!extra.readonly" class="btn bg-base-100 join-item">
                        <a class="flex">Añadir Servicio &nbsp;
                            <PlusCircleIcon class="h-4 mr-1" />
                        </a>
                    </button>
                </button>
                <div v-for="(service, index) in order.services">
                    <ServiceComponent :errors="extra.errors.services[index]" :index="index" :service="service"
                        :ref="'services'" :currentServiceIndex="currentServiceIndex"
                        :availableservices="availableservices" />
                </div>
                <div class="flex justify-end" v-if="!extra.readonly">
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

        if (!this.order.services) {
            this.order.services = [];
            this.addNewService();
        }

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
                    design: {},
                    old_design: {},
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
