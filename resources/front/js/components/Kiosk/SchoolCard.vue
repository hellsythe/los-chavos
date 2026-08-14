<template>
    <button type="button" class="kiosk-card kiosk-school-card" @click="$emit('select', school)">
        <div class="kiosk-school-media">
            <img v-if="school.logo" :src="school.logo" :alt="`Logo de ${school.name}`" />
            <span v-else class="kiosk-school-media-fallback" aria-hidden="true">{{ initials }}</span>
            <div class="kiosk-school-media-overlay" aria-hidden="true"></div>
            <span v-if="school.nivel_educativo_label" class="kiosk-school-badge">
                {{ school.nivel_educativo_label }}
            </span>
        </div>
        <div class="kiosk-school-body">
            <p class="kiosk-school-name">{{ school.name }}</p>
            <p class="kiosk-school-meta">
                <span v-if="school.location">{{ school.location }}</span>
                <span v-if="school.location && school.city"> · </span>
                <span v-if="school.city">{{ school.city }}</span>
            </p>
            <span v-if="school.uniforms_count" class="kiosk-school-count">
                {{ school.uniforms_count }} {{ school.uniforms_count === 1 ? 'uniforme' : 'uniformes' }}
            </span>
        </div>
    </button>
</template>

<script>
export default {
    name: 'KioskSchoolCard',
    props: {
        school: { type: Object, required: true },
    },
    emits: ['select'],
    computed: {
        initials() {
            const words = (this.school.name || '').trim().split(/\s+/).filter(Boolean);
            if (!words.length) return '?';
            if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
            return (words[0][0] + words[1][0]).toUpperCase();
        },
    },
};
</script>
