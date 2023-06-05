<template>
    <div>
        <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Información del cliente</h1>
                <button @click="extra.steps.client = true" v-if="!extra.steps.client">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="extra.steps.client = false" v-if="extra.steps.client">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="extra.steps.client">
                <div class="form-control mb-2 mr-2 w-64">
                    <label class="label"><span class="label-text">Fecha de entrega</span></label>
                    <input type="date" placeholder="dd-mm-yyyy" :min="minDate" v-model="order.data.deadline" class="input input-bordered w-full">
                    <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.deadline }}</div>
                </div>
                <div class="lg:flex">
                    <div class="form-control w-full mb-2 mr-2">
                        <label class="label"><span class="label-text">Nombre del cliente</span></label>
                        <input v-model="order.client.name" type="text" class="input input-bordered w-full"
                            placeholder="John Fulanito">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.name }}</div>
                    </div>

                    <div class="form-control w-full mb-2 mr-2">
                        <label class="label"><span class="label-text">Teléfono del cliente</span></label>
                        <input v-model="order.client.phone" type="number" class="input input-bordered w-full"
                            placeholder="2747430512">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.phone }}</div>
                    </div>

                    <div class="form-control w-full mb-2">
                        <label class="label"><span class="label-text">Correo del cliente</span></label>
                        <input v-model="order.client.email" type="email" class="input input-bordered w-full"
                            placeholder="cliente@gmail.com">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.email }}</div>
                    </div>
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
} from "@heroicons/vue/24/solid";


export default {
    name: "Client",
    components: {
        EyeIcon,
        EyeSlashIcon
    },
    props: {
        order: JSON,
        extra: JSON,
    },
    computed: {
        minDate: function() {
            let date = new Date().toISOString().split("T")[0];
            return date;
        },
    },
    methods: {
        validate() {
            this.clearErrors();

            if (this.order.client.name.length === 0) {
                this.extra.errors.client.name = 'El nombre del cliente no puede estar vacio';
            }

            if (this.order.client.phone.length === 0) {
                this.extra.errors.client.phone = 'El Teléfono del cliente no puede estar vacio';
            }

            if (this.order.detail.deadline.length === 0) {
                this.extra.errors.client.deadline = 'La fecha de entrega no puede estar vacia';
            }

            if ( this.extra.errors.client == {}) {
                this.extra.steps.client = false;
                this.extra.steps.service = true;
            }
        },
        clearErrors() {
            this.extra.errors.client = {};
        }
    },
};
</script>
