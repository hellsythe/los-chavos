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
                    <span aria-hidden="true">←</span>
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
        >
            <span aria-hidden="true">⌂</span>
            <span>Inicio</span>
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
