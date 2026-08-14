<template>
    <section class="flex-1 flex flex-col min-h-0">
        <div class="kiosk-search">
            <span class="kiosk-search-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
            </span>
            <label class="sr-only" for="school-search">Buscar escuela</label>
            <input
                id="school-search"
                v-model="query"
                type="search"
                inputmode="search"
                placeholder="Buscar por nombre o ciudad"
                autocomplete="off"
            />
        </div>

        <div v-if="loading" class="kiosk-grid-schools">
            <div v-for="i in 4" :key="i" class="kiosk-skel-card kiosk-stagger-enter-active">
                <div class="kiosk-skel-media kiosk-shimmer"></div>
                <div class="kiosk-skel-body">
                    <div class="kiosk-shimmer h-5 w-3/4 rounded"></div>
                    <div class="kiosk-shimmer h-3 w-1/2 rounded"></div>
                </div>
            </div>
        </div>

        <div v-else-if="filtered.length" class="kiosk-grid-schools">
            <div
                v-for="(school, i) in filtered"
                :key="school.id"
                class="kiosk-stagger-enter-active"
                :style="{ animationDelay: `${Math.min(i, 8) * 35}ms` }"
            >
                <SchoolCard :school="school" @select="(s) => $emit('select', s)" />
            </div>
        </div>

        <div v-else class="kiosk-empty">
            <div class="kiosk-empty-stack">
                <div class="kiosk-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <h3 class="kiosk-empty-title">Sin coincidencias</h3>
                <p class="kiosk-empty-text">No encontramos escuelas que coincidan con tu búsqueda. Prueba con otro nombre o borra el texto.</p>
                <button type="button" class="kiosk-btn-back" @click="resetQuery">Limpiar búsqueda</button>
            </div>
        </div>
    </section>
</template>

<script>
import SchoolCard from './SchoolCard.vue';

export default {
    name: 'KioskSchoolGrid',
    components: { SchoolCard },
    props: {
        schools: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
    },
    emits: ['select'],
    data() {
        return { query: '' };
    },
    computed: {
        normalized() {
            return (this.schools || []).map((school) => ({
                ...school,
                _search: [
                    school.name,
                    school.location,
                    school.colonia,
                    school.city,
                    school.nivel_educativo_label,
                ]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase(),
            }));
        },
        filtered() {
            const term = this.query.trim().toLowerCase();
            if (!term) return this.normalized;
            return this.normalized.filter((school) => school._search.includes(term));
        },
    },
    methods: {
        resetQuery() {
            this.query = '';
        },
    },
};
</script>
