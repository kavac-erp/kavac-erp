<template>
    <div class="form-group" :class="{'is-required': isRequired}">
        <label class="control-label" :for="inputId" v-if="inputLabel">{{ inputLabel }}</label>
        <div class="custom-file">
            <input
                type="file" :name="inputId" :id="inputId" class="custom-file-input form-control-sm"
                data-toggle="tooltip" :title="inputTooltip" @change="upload" v-if="!isMultiple"
                :accept="acceptFiles"
            />
            <input
                type="file" :name="inputId" :id="inputId" class="custom-file-input"
                data-toggle="tooltip" :title="inputTooltip" @change="upload" multiple :accept="acceptFiles" v-else
            />
            <label class="custom-file-label" for="customFile">Adjuntar</label>
        </div>
        <small id="customFileHelp" class="form-text text-muted">Archivos permitidos: {{ acceptFiles }}</small>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                documentIds: [],
            }
        },
        props: {
            isRequired: {
                type: Boolean,
                required: false,
                default: false
            },
            isMultiple: {
                type: Boolean,
                required: false,
                default: false
            },
            inputLabel: {
                type: String,
                required: false,
                default: ''
            },
            inputTooltip: {
                type: String,
                required: false,
                default: 'Seleccione el(los) documento(s)'
            },
            inputId: {
                type: String,
                required: false,
                default: 'uploadDocuments'
            },
            parentRecord: {
                type: String,
                required: true
            },
            acceptFiles: {
                type: String,
                required: false,
                default: '.docx,.doc,.odt,.pdf'
            }
        },
        methods: {
            async upload() {
                const vm = this;
                vm.loading = true;
                const fileInput = document.getElementById(vm.inputId).files;
                let files = [];
                for (var i = 0; i < fileInput.length; i++) {
                    files.push(fileInput[i]);
                }

                await axios({
                    method: 'post',
                    url: `${window.app_url}/upload-document`,
                    data: {documents: files},
                    headers: { "Content-Type": "multipart/form-data" },
                }).then(response => {
                    if (response.data.result) {
                        vm.documentIds = response.data.document_ids;
                        vm.$parent.record[vm.parentRecord] = vm.documentIds.map(doc => doc.id);
                    }
                });

                const fileElement = document.getElementById(vm.inputId);
                const customFileLabel = document.querySelector('.custom-file-label');
                const fileName = files.map(file => file.name).join(', ');
                customFileLabel.innerHTML = fileName;
                fileElement.value = null;

                vm.loading = false;
            }
        }
    }
</script>