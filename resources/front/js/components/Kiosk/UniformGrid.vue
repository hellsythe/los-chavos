<template>
    <section class="flex-1 flex flex-col min-h-0">
        <div v-if="loading" class="kiosk-grid-uniforms">
            <div v-for="i in 4" :key="i" class="kiosk-skel-card kiosk-stagger-enter-active">
                <div class="kiosk-skel-media kiosk-shimmer" style="aspect-ratio: 4 / 5"></div>
                <div class="kiosk-skel-body">
                    <div class="kiosk-shimmer h-3 w-16 rounded"></div>
                    <div class="kiosk-shimmer h-5 w-3/4 rounded"></div>
                </div>
            </div>
        </div>

        <div v-else-if="uniforms.length" class="kiosk-grid-uniforms">
            <div
                v-for="(uniform, i) in uniforms"
                :key="uniform.id"
                class="kiosk-stagger-enter-active"
                :style="{ animationDelay: `${Math.min(i, 8) * 40}ms` }"
            >
                <UniformCard :uniform="uniform" @select="(u) => $emit('select', u)" />
            </div>
        </div>

        <div v-else class="kiosk-empty">
            <div class="kiosk-empty-stack">
                <div class="kiosk-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.25 15.75l8.954-8.955a1.5 1.5 0 012.122 0L21.75 9.75M4.5 18.75h15a1.5 1.5 0 001.5-1.5v-1.5a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v1.5a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                </div>
                <h3 class="kiosk-empty-title">Aún no hay uniformes</h3>
                <p class="kiosk-empty-text">No encontramos uniformes disponibles para esta escuela. Estamos trabajando en agregar más.</p>
                <button type="button" class="kiosk-btn-back" @click="$emit('back')">Volver a escuelas</button>
            </div>
        </div>
    </section>
</template>

<script>
import UniformCard from './UniformCard.vue';

export default {
    name: 'KioskUniformGrid',
    components: { UniformCard },
    props: {
        uniforms: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
    },
    emits: ['select', 'back'],
};
</script>
