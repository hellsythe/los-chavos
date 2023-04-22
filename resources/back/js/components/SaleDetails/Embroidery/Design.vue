<template>
    <label class="label"><span class="label-text">Diseño</span></label>
    <input :value="design.model?.name" type="text" class="input input-bordered w-full">
    <embed :src="design.model?.media" width="100%" height="300px" class="mt-4" />
</template>

<script>
import { resquestToApi } from '@base/js/request/resquestToApi';

export default {
    name: "DesignShow",
    props: {
        details: JSON,
    },
    components: {

    },
    data() {
        return {
            design: {}
        };
    },
    async mounted() {
        this.design = await resquestToApi(`/admin/design/api/${this.details.design_id}`);
    }
};
</script>
