<template>
    <header class="kiosk-header">
        <div class="kiosk-header-text">
            <div class="flex items-center gap-3">
                <button
                    v-if="showBack"
                    type="button"
                    class="kiosk-btn-back"
                    @click="$emit('back')"
                >
                    <span class="kiosk-btn-back-icon" aria-hidden="true">
                        &lt;
                    </span>
                    <span>Regresar</span>
                </button>
                <p class="kiosk-eyebrow">{{ eyebrow }}</p>
            </div>
            <h1 class="kiosk-title" :title="title">{{ title }}</h1>
            <p v-if="subtitle" class="kiosk-subtitle">{{ subtitle }}</p>
        </div>
        <button
            type="button"
            class="kiosk-btn-home"
            @click="$emit('home')"
            aria-label="Volver al inicio"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10.5 12 3l9 7.5"/>
                <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>
            </svg>
            <span class="kiosk-btn-home-label">Volver al inicio</span>
        </button>
    </header>
</template>

<script>
export default {
    name: 'KioskHeader',
    props: {
        eyebrow: { type: String, default: '' },
        title: { type: String, required: true },
        subtitle: { type: String, default: '' },
        showBack: { type: Boolean, default: false },
    },
    emits: ['back', 'home'],
};
</script>
