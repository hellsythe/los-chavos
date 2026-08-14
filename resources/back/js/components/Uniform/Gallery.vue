<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Galería de fotos</h2>

            <div class="flex flex-wrap gap-2 items-center mt-2">
                <input
                    ref="fileInput"
                    type="file"
                    multiple
                    accept="image/jpeg,image/png,image/webp"
                    class="file-input file-input-bordered w-full max-w-xs"
                    @change="onFileChange"
                />
                <button class="btn btn-primary" :disabled="!pendingFiles.length || uploading" @click="upload">
                    <span v-if="uploading" class="loading loading-spinner loading-sm"></span>
                    Subir {{ pendingFiles.length }} foto(s)
                </button>
            </div>

            <div v-if="uploadError" class="alert alert-error mt-2">
                {{ uploadError }}
            </div>

            <div class="mt-4">
                <div v-if="!photos.length && !pendingFiles.length" class="text-sm opacity-70">
                    Aún no hay fotos para este uniforme.
                </div>

                <div v-if="pendingPreviews.length" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mt-2">
                    <div v-for="(src, index) in pendingPreviews" :key="`pending-${index}`" class="relative">
                        <img :src="src" class="rounded border w-full h-32 object-cover" />
                        <span class="badge badge-warning absolute top-1 right-1">Pendiente</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mt-4">
                    <div v-for="photo in photos" :key="photo.id" class="relative group">
                        <img :src="photo.url" class="rounded border w-full h-32 object-cover" />
                        <button
                            type="button"
                            class="btn btn-error btn-sm absolute top-1 right-1 opacity-0 group-hover:opacity-100"
                            @click="removePhoto(photo)"
                            :disabled="removingId === photo.id"
                        >
                            <span v-if="removingId === photo.id" class="loading loading-spinner loading-xs"></span>
                            <span v-else>×</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import csrf from '@base/js/csrf_token';

export default {
    name: 'UniformGallery',
    props: {
        uniformId: {
            type: [Number, String],
            required: true,
        },
        initialPhotos: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            photos: [...this.initialPhotos],
            pendingFiles: [],
            pendingPreviews: [],
            uploading: false,
            removingId: null,
            uploadError: null,
        };
    },
    methods: {
        getCookie(name) {
            const match = document.cookie.split('; ').find((row) => row.startsWith(`${name}=`));
            return match ? match.split('=')[1] : '';
        },
        onFileChange(event) {
            const files = Array.from(event.target.files || []);
            this.pendingFiles = files;
            this.pendingPreviews = files.map((file) => URL.createObjectURL(file));
            this.uploadError = null;
        },
        async upload() {
            if (!this.pendingFiles.length) return;
            this.uploading = true;
            this.uploadError = null;

            const formData = new FormData();
            for (const file of this.pendingFiles) {
                formData.append('photos[]', file);
            }

            try {
                const response = await fetch(`/admin/uniform/${this.uniformId}/photos`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    this.uploadError = data.message || 'Error al subir las fotos.';
                    return;
                }

                const data = await response.json();
                const created = data.photos || [];
                this.photos = this.photos.concat(
                    created.map((p) => ({ id: p.id, url: p.photo }))
                );
                this.resetPending();
            } catch (error) {
                this.uploadError = 'Error de red al subir las fotos.';
            } finally {
                this.uploading = false;
            }
        },
        async removePhoto(photo) {
            if (!confirm('¿Eliminar esta foto?')) return;
            this.removingId = photo.id;
            try {
                const response = await fetch(`/admin/uniform-photo/${photo.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
                    },
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    this.photos = this.photos.filter((p) => p.id !== photo.id);
                } else {
                    alert('No se pudo eliminar la foto.');
                }
            } catch (error) {
                alert('Error de red al eliminar la foto.');
            } finally {
                this.removingId = null;
            }
        },
        resetPending() {
            this.pendingPreviews.forEach((src) => URL.revokeObjectURL(src));
            this.pendingFiles = [];
            this.pendingPreviews = [];
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },
    },
    beforeUnmount() {
        this.pendingPreviews.forEach((src) => URL.revokeObjectURL(src));
    },
};
</script>
