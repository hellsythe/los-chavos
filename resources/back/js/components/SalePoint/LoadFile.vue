<template>
    <div class="flex">
        <div class="form-control w-64 mb-2 mr-2">
            <label for="" class="label"><span class="label-text">Selecciona el nuevo archivo</span></label>
            <input type="file" class="file-input w-full max-w-xs" @change="handleFileUpload($event)" accept="image/png, image/gif, image/jpeg, application/pdf" />
            <div class="text-red-500 text-xs font-semibold mt-1">{{ error }}</div>
        </div>
        <div class="mt-6 ml-auto">
            <button @click="showPreview = false" v-if="showPreview" class="btn btn-info mt-3 mb-3">Ocultar Preview</button>
            <button @click="showPreview = true" v-if="!showPreview" class="btn btn-info mt-3 mb-3">Mostrar Preview</button>
        </div>
    </div>
    <div v-show="showPreview">
        <object v-if="previewData" :data="previewData"
                    width="100%"
                    height="600">
            </object>
    </div>
</template>

<script>
export default {
    name: "LoadFile",
    props: {
        file: {},
        error: String,
    },
    data() {
        return {
            showPreview: true,
            previewData: '',
        };
    },
    methods: {
        async handleFileUpload(e) {
            let that = this;
            let file = e.target.files[0];
            let reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function () {
                that.file.file = reader.result;
            }
            this.preview(e);
        },
        preview(event) {
            const files = event.target.files;
            const filesLength = files.length;
            if (filesLength > 0) {
                const imageSrc = URL.createObjectURL(files[0]);
                // const imagePreviewElement = document.querySelector("#preview-selected-image");
                // imagePreviewElement.data = imageSrc;
                // imagePreviewElement.style.display = "block";
                // const filename = event.target.files[0].name;
                // this.extension =filename.split(".").pop();
                this.previewData = imageSrc;
            }
        }
    },
};
</script>
