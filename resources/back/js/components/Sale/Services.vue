<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Servicios</h1>
                <button @click="showServicesInfo = true" v-if="!showServicesInfo">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showServicesInfo = false" v-if="showServicesInfo">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="showServicesInfo">
                <ul class="menu menu-vertical lg:menu-horizontal bg-base-100 rounded-box">
                    <li @click="currentServiceIndex = index" v-for="(seledtedService, index) in selectedServices">
                        <a :class="{ active: index == currentServiceIndex }">
                            {{ seledtedService.name ?? `Servicio ${index + 1}` }}
                            <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
                        </a>
                    </li>
                    <li @click="addNewService">
                        <a>Añadir Servicio
                            <PlusCircleIcon class="h-4 mr-1" />
                        </a>
                    </li>
                </ul>
                <div v-for="(seledtedService, index) in selectedServices">
                    <ServiceComponent :availableservices="availableservices" :index="index" :service="seledtedService"
                        :currentServiceIndex="currentServiceIndex" />
                </div>
                <GarmentComponent ref="garment" :garment="garmentData" :selectedServices="selectedServices"></GarmentComponent>
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
import GarmentComponent from './Garment.vue';
import colors from './../colors';

export default {
    name: "Services",
    props: {
        availableservices: JSON,
        selectedServices: JSON,
    },
    components: {
        EyeIcon,
        EyeSlashIcon,
        PlusCircleIcon,
        ServiceComponent,
        GarmentComponent,
    },
    computed: {
        get_colors() { return colors }
    },
    data() {
        return {
            currentServices: [],
            showServicesInfo: true,
            currentServiceIndex: 0,
            garmentData: {}
        };
    },
    mounted() {

    },
    methods: {
        addNewService() {
            this.selectedServices.push({});
            this.$refs.garment.initCanva();
        },
        validate()
        {

        }
    },
};
</script>
