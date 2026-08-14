<template>
    <button type="button" class="kiosk-card kiosk-uniform-card" @click="$emit('select', uniform)">
        <div class="kiosk-uniform-image">
            <img
                v-if="cover"
                :src="cover"
                :alt="`Imagen de ${uniform.name}`"
                loading="lazy"
                @error="coverError = true"
            />
            <div v-else class="kiosk-uniform-fallback">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                    <circle cx="9" cy="9" r="2"/>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                </svg>
            </div>
            <div class="kiosk-uniform-image-overlay" aria-hidden="true"></div>
        </div>
        <div class="kiosk-uniform-body">
            <p class="kiosk-uniform-meta">{{ uniform.name }}</p>
            <p class="kiosk-uniform-name line-clamp-2">{{ shortLabel }}</p>
        </div>
    </button>
</template>

<script>
export default {
    name: 'KioskUniformCard',
    props: {
        uniform: { type: Object, required: true },
    },
    emits: ['select'],
    data() {
        return { coverError: false };
    },
    watch: {
        uniform: {
            immediate: true,
            handler() {
                this.coverError = false;
            },
        },
    },
    computed: {
        cover() {
            if (this.coverError) return null;
            return this.uniform.cover
                || this.uniform.preview
                || (this.uniform.photos && this.uniform.photos[0] && this.uniform.photos[0].url)
                || null;
        },
        shortLabel() {
            // Si name = "LUNES/GALA", mostrar variante inmediatamente más legible.
            return this.uniform.name;
        },
    },
};
</script>
