<template>
    <ul class="menu menu-vertical lg:menu-horizontal bg-base-100 rounded-box">
        <li @click="currentServiceIndex = index" v-for="(service, index) in services">
            <a :class="{ active: index == currentServiceIndex }">
                {{ service.name ?? `Servicio ${index + 1}` }}
                <div class="w-4 h-4 rounded-full" :style="'background-color:' + get_colors[index]"></div>
            </a>
        </li>
        <li>

        </li>
    </ul>
    <div v-for="(service, index) in services">
    </div>
</template>

<script>
import {
    EyeSlashIcon,
    EyeIcon,
    PlusCircleIcon,
} from "@heroicons/vue/24/solid";

import colors from './../colors';

export default {
    name: "Services",
    props: {
        // availableservices: JSON,
        // selectedServices: JSON,
        // garmentData: JSON,
        // showServicesInfo: JSON,
        // showReportInfo: JSON,
        // final_errors: JSON,
        services: JSON
    },
    components: {
        // EyeIcon,
        // EyeSlashIcon,
        PlusCircleIcon,
        // ServiceComponent,
        // GarmentComponent,
    },
    computed: {
        get_colors() { return colors }
    },
    data() {
        return {
            // currentServices: [],
            currentServiceIndex: 0,
            // garmentErrors: {}
        };
    },
    mounted() {

    },
    methods: {
        addNewService() {
            // this.selectedServices.push({});
            // this.$refs.garment.initCanva();
        },
    },
};
</script>
