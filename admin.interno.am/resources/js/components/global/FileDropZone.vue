<script setup>
import {onMounted, ref} from 'vue'
import Dropzone from 'dropzone'
import 'dropzone/dist/dropzone.css'

const emits = defineEmits([
    'update',
    'searchNotUploaded',
])
const notUploadedFiles = ref([]);
onMounted(() => {
    const dropzoneArea = document.querySelector('#demo-upload');

    if (dropzoneArea) {
        new Dropzone('#demo-upload', {
            url: '/api/erp/media/upload',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${localStorage.getItem('epodexAuthToken')}`,
                'VendorKey': localStorage.getItem('vendor_key')
            },
            maxFilesize: 500,
            paramName: 'file',
            acceptedFiles: '.jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.mp4,.avi,.mov',
            addRemoveLinks: true,
            dictDefaultMessage: 'Drop files here to upload',
            init: function() {
                this.on('success', function(file, response) {
                    emits('update')
                    this.removeFile(file);
                });
                this.on('error', function(file, errorMessage) {
                    notUploadedFiles.value.push(file.name);
                    let displayMessage = 'An error occurred during upload';
                    if (typeof errorMessage === 'object' && errorMessage !== null) {
                        if (errorMessage.message) {
                            displayMessage = errorMessage.message; // Use the `message` property if available
                        } else {
                            displayMessage = JSON.stringify(errorMessage); // Fallback to JSON string
                        }
                    } else if (typeof errorMessage === 'string') {
                        displayMessage = errorMessage; // Use the string message directly
                    }

                    // Update the error message display
                    const errorElement = file.previewElement.querySelector('[data-dz-errormessage]');
                    if (errorElement) {
                        errorElement.textContent = displayMessage;
                    }

                    console.error('Upload error:', file.name, displayMessage);

                });
                this.on('queuecomplete', function() {
                    emits('searchNotUploaded', notUploadedFiles.value);
                });
            }
        });
    }
});
</script>

<template>
    <div class="p-6.5">
        <form
            class="dropzone cursor-pointer rounded-md !border-dashed !border-bodydark1 bg-gray hover:!border-primary"
            id="demo-upload"
        >
            <div class="dz-message">
                <div class="mb-2.5 flex justify-center">
                    <div
                        class="flex h-15 w-15 items-center justify-center rounded-full bg-white text-black shadow-10"
                    >
                        <font-awesome-icon :icon="['fas', 'upload']" size="2x" />
                    </div>
                </div>
                <span class="font-medium text-black"> Drop files here to upload </span><br>
                <span class="font-medium text-black"> Maximum upload file size: 500 MB. </span>
            </div>
        </form>
    </div>
</template>
<style>
.dropzone .dz-preview .dz-error-message {
    pointer-events: none;
    z-index: 1000;
    position: absolute;
    display: block;
    display: none;
    opacity: 0;
    transition: opacity .3s ease;
    border-radius: 8px;
    font-size: 13px;
    top: 40px;
    left: -10px;
    width: 140px;
    background: #b10606;
    padding: .5em 1em;
    color: #fff;
}
</style>
