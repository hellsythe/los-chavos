<template>
    <div v-if="details.design_print_id">
        <label class="label"><span class="label-text">Diseño</span></label>
        <input :value="details.design_print.name" type="text" class="input input-bordered w-full" readonly>
        <embed :src="details.design_print.media" height="700px" class="mt-4" />
        <div class="mt-3 flex items-center">
            <input v-model="details.if_new_design" type="checkbox" checked="checked" class="checkbox mr-1"
                onclick="return false;" readonly /> ¿Fue un nuevo diseño?
        </div>
        <div class="mt-3 flex items-center">
            <input v-model="details.save_design" type="checkbox" checked="checked" class="checkbox mr-1"
                onclick="return false;" readonly /> ¿Este diseño se guardara despues de ser entregado?
        </div>
    </div>
    <div v-else>
        <div class="alert alert-warning shadow-lg mb-2 mt-4">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Todavía no se sube el diseño para esta orden, pero puedes subirlo ahora</span>
            </div>
        </div>
        <div class="lg:flex">
            <div class="form-control w-64 mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Nombre del nuevo diseño</span></label>
                <input type="text" class="w-full input input-bordered" />
                <div class="text-red-500 text-xs font-semibold mt-1"></div>
            </div>
            <div class="form-control mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Selecciona el archivo para el nuevo
                        diseño</span></label>
                <input type="file" class="file-input file-input-bordered w-full max-w-xs" />
            </div>
            <div class="mt-3 flex items-center">
            <input v-model="details.save_design" type="checkbox" checked="checked" class="checkbox mr-1"
                onclick="return false;" readonly /> ¿Este diseño se guardara despues de ser entregado?
        </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "DesignPrintShow",
    props: {
        details: JSON,
    },
    created() {
        this.details.if_new_design = Boolean(this.details.if_new_design);
        this.details.save_design = Boolean(this.details.save_design);
    },
};
</script>
