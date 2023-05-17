<template>
    <label class="label"><span class="label-text">Diseño</span></label>
    <input :value="design.model?.name" type="text" class="input input-bordered w-full" readonly>
    <embed id="ambed-pdf" :src="design.model?.media" width="100%" height="700px" class="mt-4" />
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
        var element = document.getElementById("ambed-pdf");
        setTimeout(() => {
            element.style.height = "700px";
        }, 1000);
    }
};
</script>
