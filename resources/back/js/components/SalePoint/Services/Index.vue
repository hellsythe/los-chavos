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
                    <li @click="currentServiceIndex = index"  v-for="(service, index) in order.services"  @contextmenu="onContextMenu($event, index)">
                        <a :class="{ active: index == currentServiceIndex }">
                            {{ service.name ?? `Servicio ${index + 1}` }}
                            <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
                        </a>
                    </li>
                    <li @click="addNewService">
                        <a>Añadir Servicio
                            <PlusCircleIcon class="h-4 mr-1" />
                        </a>
                    </li>
                </ul>
                <div v-for="(service, index) in order.services">
                    <ServiceComponent :extra="extra" :index="index" :order="order" :service="service" :ref="'services'" :currentServiceIndex="currentServiceIndex" :availableservices="availableservices" />
                </div>
                <div class="flex justify-end	">
                    <button class="btn btn-info" @click="validate">Siguiente</button>
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
    methods: {
        addNewService() {
            this.order.services.push({
                detail: {
                    design: {}
                }
            });
        },
        validate() {
            let errors = false;
            for (let index = 0; index < this.order.services.length; index++) {
                errors = errors || this.$refs.services[index].validate();
            }
        },
        onContextMenu(e, index) {
            console.log(index);
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
