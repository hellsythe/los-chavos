<template>
    <div class="kiosk">
        <div class="kiosk-container">
            <KioskHeader
                :eyebrow="headerEyebrow"
                :title="headerTitle"
                :subtitle="headerSubtitle"
                :show-back="step !== 'schools'"
                @back="goBack"
                @home="goHome"
            />

            <div v-if="step !== 'schools'" class="kiosk-breadcrumb">
                <button type="button" @click="goHome">Escuelas</button>
                <span class="kiosk-breadcrumb-sep" aria-hidden="true">›</span>
                <span v-if="step === 'uniforms'" class="is-current">{{ school ? school.name : '...' }}</span>
                <template v-else-if="step === 'detail'">
                    <button type="button" @click="goSchool">{{ school ? school.name : '...' }}</button>
                    <span class="kiosk-breadcrumb-sep" aria-hidden="true">›</span>
                    <span class="is-current">{{ uniform ? uniform.name : '...' }}</span>
                </template>
            </div>

            <transition name="kiosk-slide" mode="out-in">
                <SchoolGrid
                    v-if="step === 'schools'"
                    key="schools"
                    :schools="schools"
                    :loading="schoolsLoading"
                    @select="openSchool"
                />
                <UniformGrid
                    v-else-if="step === 'uniforms'"
                    key="uniforms"
                    :uniforms="uniforms"
                    :loading="uniformsLoading"
                    @select="openUniform"
                    @back="goBack"
                />
                <UniformDetail
                    v-else-if="step === 'detail' && uniform"
                    key="detail"
                    :uniform="uniform"
                />
                <div v-else key="error" class="kiosk-empty">
                    <div class="kiosk-empty-stack">
                        <div class="kiosk-empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <h3 class="kiosk-empty-title">No pudimos cargar</h3>
                        <p class="kiosk-empty-text">Revisa tu conexión e intenta de nuevo.</p>
                        <button type="button" class="kiosk-btn-home" @click="goHome">Volver al inicio</button>
                    </div>
                </div>
            </transition>
        </div>

        <IdleTimeout @reset="goHome" />
    </div>
</template>

<script>
import { fetchSchools, fetchSchool, fetchUniform } from '../../services/catalog.js';
import KioskHeader from './Header.vue';
import SchoolGrid from './SchoolGrid.vue';
import UniformGrid from './UniformGrid.vue';
import UniformDetail from './UniformDetail.vue';
import IdleTimeout from './IdleTimeout.vue';

export default {
    name: 'KioskApp',
    components: {
        KioskHeader,
        SchoolGrid,
        UniformGrid,
        UniformDetail,
        IdleTimeout,
    },
    data() {
        return {
            step: 'schools',
            schools: [],
            schoolsLoading: false,
            school: null,
            uniforms: [],
            uniformsLoading: false,
            uniform: null,
            uniformLoading: false,
        };
    },
    computed: {
        headerEyebrow() {
            if (this.step === 'schools') return 'Catálogo';
            if (this.step === 'uniforms') return 'Uniformes';
            if (this.step === 'detail') return 'Detalle';
            return '';
        },
        headerTitle() {
            if (this.step === 'schools') return 'Encuentra tu uniforme';
            if (this.step === 'uniforms') return this.school ? this.school.name : '...';
            if (this.step === 'detail') return this.uniform ? this.uniform.name : '...';
            return '';
        },
        headerSubtitle() {
            if (this.step === 'schools') return 'Selecciona tu escuela para comenzar';
            if (this.step === 'uniforms' && this.school) {
                const parts = [];
                if (this.school.nivel_educativo_label) parts.push(this.school.nivel_educativo_label);
                if (this.school.location) parts.push(this.school.location);
                return parts.join(' · ');
            }
            if (this.step === 'detail') {
                return this.uniform && this.uniform.school ? this.uniform.school.name : '';
            }
            return '';
        },
    },
    mounted() {
        this.loadSchools();
        window.addEventListener('hashchange', this.handleRouteChange);
        this.handleRouteChange();
    },
    beforeUnmount() {
        window.removeEventListener('hashchange', this.handleRouteChange);
    },
    methods: {
        handleRouteChange() {
            const hash = window.location.hash.replace(/^#\/?/, '');
            const parts = hash.split('/').filter(Boolean);
            if (parts.length === 0) {
                this.goHome();
            } else if (parts[0] === 'school' && parts[1]) {
                this.openSchoolById(Number(parts[1]));
            } else if (parts[0] === 'uniform' && parts[1]) {
                this.openUniformById(Number(parts[1]));
            }
        },
        setHash(path) {
            const newHash = path ? `#/${path}` : '';
            if (window.location.hash !== newHash) {
                window.location.hash = newHash;
            }
        },
        goHome() {
            this.setHash('');
            this.step = 'schools';
            this.uniform = null;
            this.uniforms = [];
            this.school = null;
        },
        goBack() {
            if (this.step === 'uniforms') {
                this.goHome();
            } else if (this.step === 'detail') {
                if (this.school) this.openSchoolById(this.school.id, true);
                else this.goHome();
            }
        },
        goSchool() {
            if (this.school) this.openSchoolById(this.school.id, true);
        },
        async loadSchools() {
            if (this.schools.length) return;
            this.schoolsLoading = true;
            try {
                this.schools = await fetchSchools();
            } catch (err) {
                this.schools = [];
            } finally {
                this.schoolsLoading = false;
            }
        },
        async openSchool(school) {
            await this.openSchoolById(school.id);
        },
        async openSchoolById(id, skipHash = false) {
            this.uniformsLoading = true;
            try {
                const data = await fetchSchool(id);
                this.school = data;
                this.uniforms = data.uniforms || [];
                this.uniform = null;
                this.step = 'uniforms';
                if (!skipHash) this.setHash(`school/${id}`);
            } catch (err) {
                this.uniforms = [];
                this.school = null;
                this.step = 'schools';
            } finally {
                this.uniformsLoading = false;
            }
        },
        async openUniform(u) {
            await this.openUniformById(u.id);
        },
        async openUniformById(id, skipHash = false) {
            this.uniformLoading = true;
            try {
                const data = await fetchUniform(id);
                this.uniform = data;
                this.step = 'detail';
                if (!skipHash) this.setHash(`uniform/${id}`);
            } catch (err) {
                this.step = 'uniforms';
            } finally {
                this.uniformLoading = false;
            }
        },
    },
};
</script>
