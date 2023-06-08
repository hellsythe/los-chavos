<template>
    <div class="flex">
        <div class="form-control w-64 mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Nombre del nuevo diseño</span></label>
            <input type="text" class="w-full input input-bordered" v-model="service.detail.design.name" />
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.name }}</div>
        </div>
        <div class="form-control mb-2  mr-2">
            <label for="" class="label"><span class="label-text">Costo por ponchado nuevo</span></label>
            <label class="input-group">
                <span>$</span>
                <input type="number" class="input input-bordered" v-model="service.detail.price" />
            </label>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.price }}</div>
        </div>
        <div class="form-control mb-2">
            <label for="" class="label"><span class="label-text">Puntadas</span></label>
            <input type="number" class="input input-bordered" v-model="service.detail.design.minutes"/>
            <div class="text-red-500 text-xs font-semibold mt-1">{{ errors.detail.design.minutes }}</div>
        </div>
    </div>
    <LoadFile :file="service.detail.design" :error="errors.detail.design.file" />
</template>

<script>
import LoadFile from './../../LoadFile.vue';

export default {
    name: "NewEmbroidery",
    props: {
        service: JSON,
        errors: JSON,
    },
    components: {
        LoadFile
    },
    methods: {
        validate() {
            if (!this.service.detail.design.file) {
                this.errors.detail.design.file = 'El archivo del nuevo diseño no puede estar vacio';
            }
            if (!this.service.detail.design.name) {
                this.errors.detail.design.name = 'El nombre del diseño no puede estar vacio';
            }
            if (!this.service.detail.price) {
                this.errors.detail.price = 'El costo por ponchar el nuevo diseño no puede estar vacio';
            }
        }
    }
};
</script>
