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
                    <input dusk="order-deadline" type="date" placeholder="dd-mm-yyyy" :min="minDate"
                        v-model="order.deadline" class="input input-bordered w-full">
                    <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.deadline }}</div>
                </div>
                <div class="lg:flex">
                    <div class="form-control w-full mb-2 mr-2">
                        <label class="label"><span class="label-text">Nombre del cliente</span></label>
                        <TypeaheadInput :currentValue="order.client.phone"  :loadFromApiUrl="'/admin/client/api?name={search}&page=1'" @selected="selectedData"
                            :ignoredList="selectedItemIds" placeholder="Nombre del cliente">
                        </TypeaheadInput>
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.name }}</div>
                    </div>

                    <div class="form-control w-full mb-2 mr-2">
                        <label class="label"><span class="label-text">Teléfono del cliente</span></label>
                        <input dusk="client-phone" v-model="order.client.phone" type="number"
                            class="input input-bordered w-full" placeholder="2747430512">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.phone }}</div>
                    </div>

                    <div class="form-control w-full mb-2 mr-2">
                        <label class="label"><span class="label-text">Correo del cliente</span></label>
                        <input dusk="client-email" v-model="order.client.email" type="email"
                            class="input input-bordered w-full" placeholder="cliente@gmail.com">
                        <div class="text-red-500 text-xs font-semibold mt-1">{{ extra.errors.client.email }}</div>
                    </div>

                    <div class="form-control w-full mb-2">
                        <label for="" class="label"><span class="label-text">Enviar ticket por whatsapp</span></label>
                        <select v-model="order.client.whatsapp" class="select w-full max-w-xs">
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end" v-if="!extra.readonly">
                    <button dusk="client-next" class="btn btn-info" @click="validate">Siguiente</button>
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
import TypeaheadInput from '@base/js/components/Crud/Form/Fields/TypeaheadInput.vue';

export default {
    name: "Client",
    components: {
        EyeIcon,
        EyeSlashIcon,
        TypeaheadInput
    },
    data() {
        return {
            selectedItemIds: [],
            layer: null
        };
    },
    props: {
        order: JSON,
        extra: JSON,
    },
    computed: {
        minDate: function () {
            let date = new Date().toISOString().split("T")[0];
            return date;
        },
    },
    created() {
        this.clearErrors();
        if (!this.order.client) {
            this.order.client = {
                name: '',
                phone: '',
                email: '',
                whatsapp: 0,
            };
        }
        if (!this.order.deadline) {
            this.order.deadline = '';
        }

        if (this.order.deadlinex) {
            this.order.deadline = this.order.deadlinex;
        }
    },
    mounted() {
        let that = this;
        window.addEventListener("dispachValidations", function (event) {
            that.validateErrors();
        });
    },
    methods: {
        selectedData(value) {
            this.order.client.phone = value.phone;
            this.order.client.name = value.name;
            this.order.client.email = value.email;
        },
        validate() {
            this.clearErrors();
            this.validateErrors();

            if (Object.keys(this.extra.errors.client).length == 0) {
                this.extra.steps.client = false;
                this.extra.steps.service = true;
            }
        },
        validateErrors()
        {
            if (this.order.client.name.length === 0) {
                this.extra.errors.client.name = 'El nombre del cliente no puede estar vacio';
            }

            if (this.order.client.phone.length === 0) {
                this.extra.errors.client.phone = 'El Teléfono del cliente no puede estar vacio';
            }

            if (this.order.deadline.length === 0) {
                this.extra.errors.client.deadline = 'La fecha de entrega no puede estar vacia';
            }
        },
        clearErrors() {
            this.extra.errors.client = {};
        }
    },
};
</script>
