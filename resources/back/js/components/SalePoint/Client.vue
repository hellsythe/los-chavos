<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <!-- <div class="flex justify-between">
                <h1 class="font-bold">Información del cliente</h1>
                <button @click="showClientInfo.value = true" v-if="!showClientInfo.value">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showClientInfo.value = false" v-if="showClientInfo.value">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div> -->
            <!-- <div v-if="true">
                <div class="form-control mb-2 mr-2 w-64">
                    <label for="" class="label"><span class="label-text">Fecha de entrega</span></label>
                    <input type="date" placeholder="dd-mm-yyyy" :min="minDate" v-model="extra.date" class="input input-bordered w-full">
                    <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.date }}</div>
                </div>
                <div class="lg:flex">
                    <div class="form-control w-full mb-2 mr-2">
                        <label for="" class="label"><span class="label-text">Nombre del cliente</span></label>
                        <input v-model="client.name" type="text" class="input input-bordered w-full"
                            placeholder="John Fulanito">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.name }}</div>
                    </div>

                    <div class="form-control w-full mb-2 mr-2">
                        <label for="" class="label"><span class="label-text">Teléfono del cliente</span></label>
                        <input v-model="client.phone" type="number" class="input input-bordered w-full"
                            placeholder="2747430512">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.phone }}</div>
                    </div>

                    <div class="form-control w-full mb-2">
                        <label for="" class="label"><span class="label-text">Correo del cliente</span></label>
                        <input v-model="client.email" type="email" class="input input-bordered w-full"
                            placeholder="cliente@gmail.com">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.email }}</div>
                    </div>
                </div>
                <div class="flex justify-end	">
                    <button class="btn btn-info" @click="validate">Siguiente</button>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script>
import {
    EyeSlashIcon,
    EyeIcon,
} from "@heroicons/vue/24/solid";


export default {
    name: "Client",
    components: {
        EyeIcon,
        EyeSlashIcon
    },
    props: {
        order: JSON,
    },
    data() {
        return {
            errors: {},
        };
    },
    mounted() {

    },
    methods: {
        validate() {
            this.clearErrors();
            let errors = false;

            if (!this.client.hasOwnProperty('name') || this.client.name.length === 0) {
                this.errors.name = 'El nombre del cliente no puede estar vacio';
                errors = true;
            }

            if (!this.client.hasOwnProperty('phone') || this.client.phone.length === 0) {
                this.errors.phone = 'El Teléfono del cliente no puede estar vacio';
                errors = true;
            }

            if (!errors) {
                this.showClientInfo.value = false;
                this.showServicesInfo.value = true;
                this.final_errors.client = false;
            } else{
                this.final_errors.client = 'Existen errores en el Cliente';
            }
        },
        clearErrors() {
            this.errors = {};
        }
    },
};
</script>
