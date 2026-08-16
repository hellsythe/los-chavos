<template>
    <div class="form-control w-full mb-2">
        <label v-if="label" class="label">
            <span class="label-text">{{ label }}</span>
            <span v-if="tooltip" class="label-text-alt" :title="tooltip">(?)</span>
        </label>
        <div v-if="loadingInitial" class="input input-bordered w-full flex items-center text-gray-500">
            <span class="loading loading-spinner loading-xs mr-2"></span>
            Cargando...
        </div>
        <TypeaheadInput
            v-else
            ref="typeahead"
            :currentValue="displayValue"
            :loadFromApiUrl="searchUrl"
            :ignoredList="ignoredList"
            placeholder="Buscar..."
            @selected="onSelected"
        />
        <input type="hidden" :name="name" :value="selectedValue" />
        <div class="text-red-500 text-xs font-semibold">
            <p v-for="(error, index) in errors[name]" :key="index">
                {{ error }}
            </p>
        </div>
    </div>
</template>

<script>
import TypeaheadInput from './TypeaheadInput.vue';

export default {
    name: "TypeaheadFormField",
    components: {
        TypeaheadInput
    },
    props: {
        name: String,
        tooltip: String,
        extra: { type: Object, default: () => ({}) },
        label: String,
        value: String,
        errors: { type: Object, default: () => ({}) },
        submited: Boolean,
        loadOptionsFromUrl: String,
        options: { type: Array, default: () => [] },
    },
    data() {
        return {
            selectedValue: this.value || '',
            selectedLabel: '',
            ignoredList: [],
            loadingInitial: false,
        };
    },
    computed: {
        searchUrl() {
            return this.loadOptionsFromUrl || '';
        },
        displayValue() {
            return this.selectedLabel;
        }
    },
    async created() {
        if (this.selectedValue && !this.selectedLabel) {
            this.loadingInitial = true;
            await this.loadInitialLabel();
            this.loadingInitial = false;
        }
    },
    watch: {
        value(newVal) {
            if (newVal !== this.selectedValue) {
                this.selectedValue = newVal || '';
                this.selectedLabel = '';
                if (this.selectedValue) {
                    this.loadingInitial = true;
                    this.loadInitialLabel().finally(() => {
                        this.loadingInitial = false;
                    });
                }
            }
        }
    },
    methods: {
        buildUrl(extraParams = {}) {
            if (!this.loadOptionsFromUrl) return '';
            const base = this.loadOptionsFromUrl.split('?')[0];
            const url = new URL(base, window.location.origin);
            Object.entries(extraParams).forEach(([k, v]) => url.searchParams.set(k, v));
            return url.toString();
        },
        async loadInitialLabel() {
            if (!this.selectedValue || !this.loadOptionsFromUrl) return;
            try {
                const url = this.buildUrl({ id: this.selectedValue, pagination: '1' });
                const response = await fetch(url);
                const data = await response.json();
                if (data.data && data.data.length > 0) {
                    this.selectedLabel = data.data[0].name;
                }
            } catch (e) {
                console.error('loadInitialLabel error:', e);
            }
        },
        onSelected(item) {
            const valueName = this.extra?.valueName || 'id';
            this.selectedValue = item[valueName];
            this.selectedLabel = item.name;
        }
    }
};
</script>
