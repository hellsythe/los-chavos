<template>
    <div class="form-control w-64 mb-2 mr-2">
        <label for="" class="label"><span class="label-text">Selecciona el nuevo ponchado</span></label>
        <input type="file" class="file-input w-full max-w-xs" />
        <div class="text-red-500 text-xs font-semibold"></div>
    </div>
</template>

<script>
export default {
    name: "NewEmbroidery",
    props: {
        service: JSON,
    },
    data() {
        return {

        };
    },
    mounted() {
    },
    methods: {

    },
};
</script>
