<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between mb-1">
                <h1 class="font-bold">Servicios</h1>
                <button @click="showServicesInfo.value = true" v-if="!showServicesInfo.value">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showServicesInfo.value = false" v-if="showServicesInfo.value">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-show="showServicesInfo.value">
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
                        :ref="'services'" :currentServiceIndex="currentServiceIndex" />
                </div>
                <GarmentComponent ref="garment" :garment="garmentData" :selectedServices="selectedServices"
                    :error="garmentErrors">
                </GarmentComponent>
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
        garmentData: JSON,
        showServicesInfo: JSON,
        showReportInfo: JSON,
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
            currentServiceIndex: 0,
            garmentErrors: {}
        };
    },
    mounted() {

    },
    methods: {
        addNewService() {
            this.selectedServices.push({});
            this.$refs.garment.initCanva();
        },
        validate() {
            this.garmentErrors = {};
            let errors = false;
            for (let index = 0; index < this.selectedServices.length; index++) {
                errors = errors || this.$refs.services[index].validate();
            }

            if (!this.garmentData.hasOwnProperty('data')) {
                this.garmentErrors.data = 'La prenda no puede estar vacia';
                errors = true;
            };

            if (!this.garmentData.hasOwnProperty('amount') || this.garmentData.amount.length === 0 || this.garmentData.amount == 0) {
                this.garmentErrors.amount = 'La cantidad no puede estar vacia o ser igual a 0';
                errors = true;
            };

            if (!errors) {
                this.showServicesInfo.value = false;
                this.showReportInfo.value = true;
            }
        },
    },
};
</script>
