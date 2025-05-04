<template>
    <section id="AssetRequestForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpAssetRequestDate" v-if="false">
                    <div class="form-group">
                        <label>Fecha de solicitud</label>
                        <div class="input-group input-sm">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_calendar-60"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                title="Fecha de la solicitud"
                                v-model="record.created_at"
                                disabled="true"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="helpAssetRequestDeliveryDate">
                    <div class="form-group is-required">
                        <label>Fecha de entrega</label>
                        <div class="input-group input-sm">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_calendar-60"></i>
                            </span>
                            <input
                                type="date"
                                :min="
                                    new Date(
                                        new Date().getTime() -
                                            new Date().getTimezoneOffset() *
                                                60000
                                    )
                                        .toISOString()
                                        .split('T')[0]
                                "
                                class="form-control input-sm no-restrict"
                                data-toggle="tooltip"
                                title="Indique la fecha de entrega de los equipos"
                                v-model="record.delivery_date"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="helpAssetRequestType">
                    <div class="form-group is-required">
                        <label>Tipo de solicitud</label>
                        <select2 :options="types" v-model="record.type">
                        </select2>
                    </div>
                </div>
                <div class="col-md-6" id="helpAssetRequestMotive">
                    <div class="form-group is-required">
                        <label>Motivo de la solicitud</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="motive"
                            data-toggle="tooltip"
                            title="Indique el motivo de la solicitud"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="motive"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.motive"
                        >
                        </ckeditor>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Adjuntar archivos </label>
                        <input
                            id="files"
                            name="files"
                            type="file"
                            data-toggle="tooltip"
                            title="Adjuntar archivos"
                            accept=".doc, .docx, .odt, .pdf, .png, .jpg, .jpeg"
                            multiple
                        />
                    </div>
                    <div class="form-group">
                        <label><strong>Archivos Subidos</strong></label>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="archive"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="record.type > 1">
                <div class="row" style="margin: 10px 0">
                    <div class="col-md-12">
                        <b>Ubicación</b>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3" id="helpAssetCountry">
                        <div class="form-group is-required">
                            <label>Pais:</label>
                            <select2
                                :options="countries"
                                v-model="record.country_id"
                                @input="getEstates"
                            >
                            </select2>
                            <input type="hidden" v-model="record.id" />
                        </div>
                    </div>
                    <div class="col-md-3" id="helpAssetEstate">
                        <div class="form-group is-required">
                            <label>Estado:</label>
                            <select2
                                :options="estates"
                                v-model="record.estate_id"
                                @input="getMunicipalities"
                            >
                            </select2>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpAssetMunicipality">
                        <div class="form-group is-required">
                            <label>Municipio:</label>
                            <select2
                                :options="municipalities"
                                v-model="record.municipality_id"
                                @input="getParishes"
                            >
                            </select2>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpAssetParish">
                        <div class="form-group is-required">
                            <label>Parroquia:</label>
                            <select2
                                :options="parishes"
                                v-model="record.parish_id"
                            >
                            </select2>
                        </div>
                    </div>
                    <div class="col-md-6" id="helpAssetAddress">
                        <div class="form-group is-required">
                            <label>Dirección</label>
                            <ckeditor
                                :editor="ckeditor.editor"
                                data-toggle="tooltip"
                                title="Indique dirección física del bien"
                                :config="ckeditor.editorConfig"
                                class="form-control"
                                name="address"
                                tag-name="textarea"
                                rows="3"
                                v-model="record.address"
                            >
                            </ckeditor>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="record.type == 3">
                <div class="row" style="margin: 10px 0">
                    <div class="col-md-12">
                        <b>Información de contacto</b>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4" id="helpAssetRequestAgentName">
                        <div class="form-group is-required">
                            <label>Nombre del agente externo</label>
                            <input
                                type="text"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                v-input-mask
                                data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                                title="Indique el nombre del agente externo responsable del bien"
                                v-model="record.agent_name"
                            />
                        </div>
                    </div>

                    <div class="col-md-4" id="helpAssetRequestAgentTelf">
                        <div class="form-group is-required">
                            <label>Teléfono del agente externo</label>
                            <input
                                type="text"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                v-input-mask
                                data-inputmask-regex="[0-9\-]*"
                                title="Indique el teléfono del agente externo responsable del bien"
                                v-model="record.agent_telf"
                            />
                        </div>
                    </div>

                    <div class="col-md-4" id="helpAssetRequestAgentEmail">
                        <div class="form-group is-required">
                            <label>Correo del agente externo</label>
                            <input
                                type="text"
                                class="form-control input-sm"
                                data-toggle="tooltip"
                                title="Indique el correo eléctronico del agente externo responsable del bien"
                                v-model="record.agent_email"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <hr />
            <v-server-table
                :url="route_asset"
                id="helpTable"
                @row-click="toggleActive"
                :columns="columns"
                :options="table_options"
                ref="tableResults"
            >
                <div slot="h__check" class="text-center">
                    <label class="form-checkbox">
                        <input
                            type="checkbox"
                            v-model="selectAll"
                            @click="select()"
                            class="cursor-pointer"
                        />
                    </label>
                </div>

                <div slot="check" slot-scope="props" class="text-center">
                    <label class="form-checkbox">
                        <input
                            type="checkbox"
                            class="cursor-pointer"
                            :value="props.row.id"
                            :id="'checkbox_' + props.row.id"
                            v-model="selected"
                        />
                    </label>
                </div>
                <div slot="asset_details" slot-scope="props">
                    <span>
                        <div v-for="(att, index) in props.row.asset_details" :key="index">
                            <b>{{ att.label + ":" }}</b> {{ att.value }}
                        </div>
                    </span>
                </div>
            </v-server-table>
        </div>
        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button
                        type="button"
                        @click="reset()"
                        class="btn btn-default btn-icon btn-round"
                        data-toggle="tooltip"
                        title="Borrar datos del formulario"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>

                    <button
                        type="button"
                        @click="redirect_back(route_list)"
                        class="btn btn-warning btn-icon btn-round btn-modal-close"
                        data-dismiss="modal"
                        title="Cancelar y regresar"
                    >
                        <i class="fa fa-ban"></i>
                    </button>

                    <button
                        type="button"
                        @click="createRecord('asset/requests')"
                        class="btn btn-success btn-icon btn-round btn-modal-save"
                        title="Guardar registro"
                    >
                        <i class="fa fa-save"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: "",
                date: "",
                motive: "",
                type_id: "",
                delivery_date: "",
                agent_name: "",
                agent_telf: "",
                agent_email: "",

                country_id: "",
                estate_id: "",
                municipality_id: "",
                parish_id: "",

                country_id_aux: "",
                estate_id_aux: "",
                municipality_id_aux: "",
                parish_id_aux: "",

                address: "",
            },
            records: [],
            files: [],
            columns: [
                "check",
                "asset_institutional_code.name",
                "asset_specific_category.name",
                "asset_condition.name",
                "asset_status.name",
                "asset_details",
            ],
            errors: [],

            types: [
                { id: "", text: "Seleccione..." },
                { id: 1, text: "Prestamo de Equipos (Uso Interno)" },
                { id: 2, text: "Prestamo de Equipos (Uso Externo)" },
                { id: 3, text: "Prestamo de Equipos para Agentes Externos" },
            ],

            selected: [],
            selectAll: false,

            countries: [],
            estates: [],
            municipalities: [],
            parishes: [],

            table_options: {
                rowClassCallback(row) {
                    var checkbox = document.getElementById(
                        "checkbox_" + row.id
                    );
                    return checkbox && checkbox.checked
                        ? "selected-row cursor-pointer"
                        : "cursor-pointer";
                },
                headings: {
                    "asset_institutional_code.name": "Código",
                    "asset_specific_category.name": "Categoría Específica",
                    "asset_condition.name": "Condición Física",
                    "asset_status.name": "Estatus de Uso",
                    asset_details: "Detalles",
                },
                sortable: [
                    "asset_institutional_code.name",
                    "asset_specific_category.name",
                    "asset_condition.name",
                    "asset_status.name",
                ],
                filterable: [
                    "asset_institutional_code.name",
                    "asset_specific_category.name",
                    "asset_condition.name",
                    "asset_status.name",
                    "asset_details",
                ],
            },
        };
    },

    created() {
        const vm = this;
        let url = `${window.app_url}/asset/registers/vue-list`;
        url += vm.requestid != null ? "/requests/" + vm.requestid : "/requests";
        vm.table_options.requestFunction = function (data) {
            let filters = {
                query: data.query,
                limit: data.limit,
                ascending: data.ascending,
                page: data.page,
                orderBy: data.orderBy,
            };

            return axios
                .get(url, {
                    params: filters,
                })
                .catch((error) => {
                    console.error(error);
                });
        };
        vm.getCountries();
    },
    mounted() {
        if (this.requestid) {
            this.loadForm(this.requestid);
        }
    },
    props: {
        requestid: Number,
        route_asset: {
            type: String,
            required: true,
            default: "",
        },
    },
    methods: {
        toggleActive({ row }) {
            const vm = this;
            var checkbox = document.getElementById("checkbox_" + row.id);

            if (checkbox && checkbox.checked == false) {
                var index = vm.selected.indexOf(row.id);
                if (index >= 0) {
                    vm.selected.splice(index, 1);
                } else {
                    checkbox.click();
                }
            } else if (checkbox && checkbox.checked == true) {
                var index = vm.selected.indexOf(row.id);
                if (index >= 0) {
                    checkbox.click();
                } else {
                    vm.selected.push(row.id);
                }
            }
        },

        reset() {
            this.record = {
                id: "",
                created_at: "",
                motive: "",
                type_id: "",
                delivery_date: "",
                agent_name: "",
                agent_telf: "",
                agent_email: "",

                country_id: "",
                estate_id: "",
                municipality_id: "",
                parish_id: "",
                country_id_aux: "",
                estate_id_aux: "",
                municipality_id_aux: "",
                parish_id_aux: "",
                address: "",
            };

            this.selected = [];
            this.files = [];
            this.selectAll = false;
        },
        select() {
            const vm = this;
            vm.selected = [];
            $.each(vm.$refs.tableResults.data, function (index, campo) {
                var checkbox = document.getElementById("checkbox_" + campo.id);

                if (!vm.selectAll) {
                    vm.selected.push(campo.id);
                } else if (checkbox && checkbox.checked) {
                    checkbox.click();
                }
            });
        },
        createRecord(url, list = true, reset = true) {
            const vm = this;
            var inputFiles = document.querySelector("#files");
            const formData = new FormData();
            url = vm.setUrl(url);

            vm.errors = [];
            if (!vm.selected.length > 0) {
                bootbox.alert(
                    "Debe agregar al menos un elemento de la tabla a la solicitud"
                );
                return false;
            }
            if (this.record.id) {
                vm.selected = vm.selected.filter(
                    (ele, pos) => vm.selected.indexOf(ele) == pos
                );

                for (var i = 0; i < vm.selected.length; i++) {
                    formData.append(`assets[]`, vm.selected[i]);
                }
                url = vm.setUrl(url);
                vm.loading = true;
                Object.keys(vm.record).forEach((key) => {
                    if (vm.record[key] == null) {
                        vm.record[key] = "";
                    }
                    formData.append(key, vm.record[key]);
                });
                for (var i = 0; i < inputFiles.files.length; i++) {
                    let file = inputFiles.files[i];
                    formData.append(
                        `files[]`,
                        inputFiles.files[i],
                        inputFiles.files[i].name
                    );
                }

                axios({
                    method: "post",
                    url: url + "/Updatefiles/" + vm.record.id,
                    data: formData,
                })
                    .then((response) => {
                        if (typeof response.data.redirect !== "undefined") {
                            location.href = response.data.redirect;
                        } else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.$refs.tableResults.refresh();
                            }
                            vm.loading = false;
                            vm.showMessage("update");
                        }
                    })
                    .catch((error) => {
                        vm.errors = [];
                        if (typeof error.response != "undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    "custom",
                                    "Acceso Denegado",
                                    "danger",
                                    "screen-error",
                                    error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }

                        vm.loading = false;
                    });
            } else {
                vm.loading = true;
                for (var index in vm.record) {
                    formData.append(index, vm.record[index]);
                }
                for (var i = 0; i < inputFiles.files.length; i++) {
                    let file = inputFiles.files[i];

                    formData.append("files[" + i + "]", file);
                }

                formData.append(`assets`, vm.selected);

                axios
                    .post(url, formData, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    })
                    .then((response) => {
                        if (typeof response.data.redirect !== "undefined") {
                            location.href = response.data.redirect;
                        } else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.$refs.tableResults.refresh();
                            }
                            vm.loading = false;
                            vm.showMessage("store");
                        }
                    })
                    .catch((error) => {
                        vm.errors = [];
                        if (typeof error.response != "undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    "custom",
                                    "Acceso Denegado",
                                    "danger",
                                    "screen-error",
                                    error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }

                        vm.loading = false;
                    });
            }
        },

        updateRecord(url) {
            const vm = this;
            var inputFiles = document.querySelector("#files");
            var formData = new FormData();
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            formData.append(fields);

            for (var i = 0; i < inputFiles.files.length; i++) {
                let file = inputFiles.files[i];

                formData.append("files[" + i + "]", file);
            }
            formData.append("assets", vm.selected);

            axios
                .patch(
                    `${url}${url.endsWith("/") ? "" : "/"}${vm.record.id}`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                )
                .then((response) => {
                    if (typeof response.data.redirect !== "undefined") {
                        location.href = response.data.redirect;
                    } else {
                        vm.errors = [];
                        if (reset) {
                            vm.reset();
                        }
                        if (list) {
                            vm.$refs.tableResults.refresh();
                        }
                        vm.loading = false;
                        vm.showMessage("update");
                    }
                })
                .catch((error) => {
                    vm.errors = [];

                    if (typeof error.response != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }

                    vm.loading = false;
                });
        },
        loadForm(id) {
            const vm = this;
            var fields = {};
            var documents = "";

            axios
                .get(`${window.app_url}/asset/requests/vue-info/${id}`)
                .then((response) => {
                    if (typeof (response.data.records != "undefined")) {
                        vm.record = response.data.records;
                        vm.record.created_at = vm.format_date(
                            vm.record.created_at
                        );
                        vm.record.country_id_aux =
                            response.data.records.country_id;
                        vm.record.estate_id_aux =
                            response.data.records.estate_id;
                        vm.record.municipality_id_aux =
                            response.data.records.municipality_id;
                        vm.record.parish_id_aux =
                            response.data.records.parish_id;

                        fields = response.data.records.asset_request_assets;
                        $.each(fields, function (index, campo) {
                            vm.selected.push(campo.asset.id);
                        });
                    }
                });

            axios
                .get(`${window.app_url}/asset/requests/get-documents/${id}`)
                .then((response) => {
                    documents = response.data;

                    let fileText = ``;
                    documents.records.forEach(function (files) {
                        fileText += `<div class ="row">`;
                        fileText += `<a href='${window.app_url}/asset/requests/get-documents/show/${files.file}'>${files.file}</a>`;

                        fileText += "</div>";
                    });
                    document.getElementById("archive").innerHTML = fileText;
                })
                .catch((error) => {
                    if (typeof error.response !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        } else {
                            vm.logs(
                                "resources/js/all.js",
                                343,
                                error,
                                "initRecords"
                            );
                        }
                    }
                });
        },

        async getCountries() {
            const vm = this;
            await axios.get("/get-countries").then((response) => {
                vm.countries = response.data;
            });
            setTimeout(() => {
                vm.getEstates();
            }, 500);
        },
        async getEstates() {
            const vm = this;
            vm.estates = [];
            if (vm.record.country_id) {
                await axios
                    .get(
                        `${window.app_url}/get-estates/${vm.record.country_id}`
                    )
                    .then((response) => {
                        vm.estates = response.data;
                    });
            }
            if (vm.record.estate_id_aux && vm.record.id) {
                vm.record.estate_id = vm.record.estate_id_aux;
            }
        },
        async getMunicipalities() {
            const vm = this;
            vm.municipalities = [];
            if (vm.record.estate_id) {
                await axios
                    .get(
                        `${window.app_url}/get-municipalities/${vm.record.estate_id}`
                    )
                    .then((response) => {
                        vm.municipalities = response.data;
                    });
            }
            if (vm.record.municipality_id_aux && vm.record.id) {
                vm.record.municipality_id = vm.record.municipality_id_aux;
            }
        },
        async getParishes() {
            const vm = this;
            vm.parishes = [];
            if (vm.record.municipality_id) {
                await axios
                    .get(
                        `${window.app_url}/get-parishes/${vm.record.municipality_id}`
                    )
                    .then((response) => {
                        vm.parishes = response.data;
                    });
            }
            if (vm.record.parish_id_aux && vm.record.id) {
                vm.record.parish_id = vm.record.parish_id_aux;
            }
        },
    },
};
</script>
