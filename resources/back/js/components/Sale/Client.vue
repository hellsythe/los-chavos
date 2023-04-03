<template>
    <div>
        <div class="p-4 bg-white mb-5 shadow rounded-lg">
            <div class="flex justify-between">
                <h1 class="font-bold">Información del cliente</h1>
                <button @click="showClientInfo = true" v-if="!showClientInfo">
                    <EyeIcon class="h-4 mr-1" />
                </button>
                <button @click="showClientInfo = false" v-if="showClientInfo">
                    <EyeSlashIcon class="h-4 mr-1" />
                </button>
            </div>
            <div v-if="showClientInfo">
                <div class="form-control w-full mb-2">
                    <label for="" class="label"><span class="label-text">Nombre del cliente</span></label>
                    <input v-model="client.name" name="name" type="text" class="input input-bordered w-full" placeholder="John Fulanito">
                    <div class="text-red-500 text-xs font-semibold"></div>
                </div>

                <div class="form-control w-full mb-2">
                    <label for="" class="label"><span class="label-text">Teléfono del cliente</span></label>
                    <input v-model="client.phone" name="name" type="numeric" class="input input-bordered w-full" placeholder="2747430512">
                    <div class="text-red-500 text-xs font-semibold"></div>
                </div>

                <div class="form-control w-full mb-2">
                    <label for="" class="label"><span class="label-text">Correo del cliente</span></label>
                    <input v-model="client.email" name="name" type="email" class="input input-bordered w-full" placeholder="cliente@gmail.com">
                    <div class="text-red-500 text-xs font-semibold"></div>
                </div>
                <div class="flex justify-end	">
                    <button class="btn btn-info">Siguiente</button>
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
        client: JSON
    },
    data() {
        return {
            showClientInfo: true
        };
    },
    mounted() {

    },
    methods: {

    },
};
</script>
