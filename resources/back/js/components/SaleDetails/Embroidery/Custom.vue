<template>
    <div class="flex">
        <div class="form-control w-full mb-2 mr-2">
            <label class="label"><span class="label-text">Tipografía</span></label>
            <input type="text" class="input input-bordered w-full" :value="typography.model?.name" readonly>
        </div>
        <div class="form-control w-full mb-2 mr-2">
            <label class="label"><span class="label-text">Tamaño de letra</span></label>
            <input type="text" class="input input-bordered w-full"
                :value="details.size" readonly>
        </div>
    </div>

    <label class="label"><span class="label-text">Texto personalizado</span></label>
    <div class="form-control w-full mb-2">
        <div v-html="details.text" class="bg-white p-4 rounded-lg"></div>
    </div>
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';

export default {
    name: "DesignCustom",
    props: {
        details: JSON,
    },
    components: {

    },
    data() {
        return {
            typography: {}
        };
    },
    async mounted() {
        this.typography = await resquestToApi(`/admin/typography/api/${this.details.typography_id}`);
    }
};
</script>
