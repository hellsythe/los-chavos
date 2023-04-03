<template>
    <div class="form-control w-full mb-2">
        <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
        <select class="select select-bordered w-full">
            <option disabled selected>Elije uno</option>
            <option v-for="service in services ">{{ service.name }}</option>
        </select>
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>
    <div class="flex justify-end	">
        <button class="btn btn-info">Siguiente</button>
    </div>
</template>

<script>

export default {
    name: "Service",
    props: {
        service: JSON,
        selectedServices: JSON,
    },
    mounted() {

    },
};
</script>
