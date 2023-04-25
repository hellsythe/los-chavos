<template>
    <label class="label"><span class="label-text">Diseño</span></label>
    <input :value="details.name" type="text" class="input input-bordered w-full" readonly>
    <embed :src="details.media"  height="300px" class="mt-4" />
</template>

<script>

export default {
    name: "DesignPrintShow",
    props: {
        details: JSON,
    },
};
</script>
