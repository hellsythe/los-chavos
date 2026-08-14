<template>
    <section class="kiosk-detail">
        <div class="kiosk-detail-gallery kiosk-pop-enter-active">
            <div class="kiosk-detail-main" :class="{ 'kiosk-shimmer': imageLoading }">
                <img
                    v-if="activeImage"
                    :src="activeImage"
                    :alt="`Imagen de ${uniform.name}`"
                    @load="imageLoading = false"
                    @error="handleImageError"
                />
                <div v-else class="kiosk-uniform-fallback">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                </div>
            </div>

            <div v-if="galleryItems.length > 1" class="kiosk-detail-thumbs">
                <button
                    v-for="(photo, index) in galleryItems"
                    :key="photo.id || index"
                    type="button"
                    class="kiosk-thumb"
                    :class="{ 'is-active': activeIndex === index }"
                    @click="setActive(index)"
                >
                    <img :src="photo.url" :alt="photo.alt || `Imagen ${index + 1}`" />
                </button>
            </div>
        </div>

        <div class="kiosk-detail-info kiosk-pop-enter-active" style="animation-delay: 60ms;">
            <div class="kiosk-detail-head">
                <p class="kiosk-detail-eyebrow">Uniforme</p>
                <h2 class="kiosk-detail-title">{{ uniform.name }}</h2>
                <p v-if="uniform.school" class="kiosk-detail-school">
                    <strong>{{ uniform.school.name }}</strong>
                    <span v-if="uniform.school.location"> · {{ uniform.school.location }}</span>
                </p>
            </div>

            <div class="kiosk-detail-section" v-if="uniform.description">
                <p class="kiosk-detail-label">Descripción</p>
                <p class="kiosk-detail-text">{{ uniform.description }}</p>
            </div>

            <div class="kiosk-detail-section">
                <p class="kiosk-detail-label">Detalles</p>
                <div class="kiosk-detail-info-grid">
                    <div>
                        <p class="kiosk-detail-label" style="margin-bottom: 0.25rem">Tipo</p>
                        <p class="kiosk-detail-text">{{ uniform.name }}</p>
                    </div>
                    <div>
                        <p class="kiosk-detail-label" style="margin-bottom: 0.25rem">Fotografías</p>
                        <p class="kiosk-detail-text">{{ totalImages }} {{ totalImages === 1 ? 'imagen' : 'imágenes' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'KioskUniformDetail',
    props: {
        uniform: { type: Object, required: true },
    },
    data() {
        return {
            activeIndex: 0,
            imageLoading: false,
            currentFallback: false,
        };
    },
    watch: {
        uniform: {
            immediate: true,
            handler() {
                this.activeIndex = 0;
                this.currentFallback = false;
            },
        },
    },
    computed: {
        previewUrl() {
            return this.uniform.preview || null;
        },
        photoItems() {
            return (this.uniform.photos || []).map((p) => ({
                id: p.id,
                url: p.url,
                alt: `${this.uniform.name} imagen ${p.order || ''}`.trim(),
            }));
        },
        galleryItems() {
            const items = [];
            if (this.previewUrl) {
                items.push({
                    id: 'preview',
                    url: this.previewUrl,
                    alt: `${this.uniform.name} imagen principal`,
                });
            }
            return items.concat(this.photoItems);
        },
        totalImages() {
            return this.galleryItems.length;
        },
        activeImage() {
            if (this.currentFallback) return null;
            const item = this.galleryItems[this.activeIndex];
            return item ? item.url : null;
        },
    },
    mounted() {
        this.imageLoading = !!this.activeImage;
    },
    methods: {
        setActive(index) {
            this.activeIndex = index;
            this.imageLoading = true;
            this.currentFallback = false;
        },
        handleImageError() {
            this.currentFallback = true;
            this.imageLoading = false;
        },
    },
};
</script>
