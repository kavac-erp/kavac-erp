<template>
    <form class="form-horizontal">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label"
                            >Cuenta de nivel superior</label
                        >
                        <select2
                            :options="accRecords"
                            v-model="record_select"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Código</label>
                        <div class="row inline-inputs">
                            <div class="col-6">
                                <input
                                    id="code"
                                    type="text"
                                    :class="[
                                        'form-control',
                                        hasExistCode(),
                                        'input-sm',
                                    ]"
                                    placeholder="0.0.0.00.00.00.000"
                                    data-toggle="tooltip"
                                    v-has-tooltip
                                    title="Código de la cuenta patrimonial"
                                    v-model="record.code"
                                />
                            </div>
                        </div>
                        <!-- :onkeyup="record.code=justAllow(record.code)" -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Denominación</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            id="denomination"
                            name="denomination"
                            data-toggle="tooltip"
                            v-has-tooltip
                            placeholder="Descripción de la cuenta"
                            title="Denominación o concepto de la cuenta"
                            v-model="record.denomination"
                        />
                    </div>
                </div>
                <div class="col-md-1 col-3">
                    <div class="form-group">
                        <label for="" class="control-label">Activa</label>
                        <div class="col-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tolltip"
                                title="Indica si la cuenta estará activa"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="accountingActive"
                                    v-model="record.active"
                                    :value="true"
                                />
                                <label
                                    class="custom-control-label"
                                    for="accountingActive"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-3">
                    <div class="form-group">
                        <label for="" class="control-label">Ingreso</label>
                        <div class="col-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tolltip"
                                title="Indica si es una cuenta de ingresos (Recursos)"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="accountingResource"
                                    v-model="record.ingres"
                                    :value="true"
                                />
                                <label
                                    class="custom-control-label"
                                    for="accountingResource"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-3">
                    <div class="form-group">
                        <label for="" class="control-label">Egreso</label>
                        <div class="col-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tolltip"
                                title="Indica si la cuenta es de egreso (Gastos)"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="accountingEgress"
                                    v-model="record.egress"
                                    :value="true"
                                />
                                <label
                                    class="custom-control-label"
                                    for="accountingEgress"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-3">
                    <div class="form-group">
                        <label for="" class="control-label">Original</label>
                        <div class="col-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tolltip"
                                title="Indica si la cuenta a registrar es del listado original del organo que establece las cuentas patrimoniales"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="accountingOriginal"
                                    v-model="record.original"
                                    :value="true"
                                />
                                <label
                                    class="custom-control-label"
                                    for="accountingOriginal"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="form-group">
                <button
                    type="button"
                    class="btn btn-default btn-sm btn-round btn-modal-close"
                    @click="clearFilters"
                    data-dismiss="modal"
                >
                    Cerrar
                </button>
                <button
                    type="button"
                    class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                    @click="reset()"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    @click="createRecord('accounting/accounts')"
                    class="btn btn-primary btn-sm btn-round btn-modal-save"
                >
                    Guardar
                </button>
            </div>
        </div>
    </form>
</template>
<script>
//import Inputmask from "inputmask";
export default {
    props: {
        records: {
            type: Array,
            default() {
                return [];
            },
        },
    },
    data() {
        return {
            accRecords: [],
            record_select: "",
            isEdit: false,
            record: {
                id: "",
                code: "",
                group: "",
                subgroup: "",
                item: "",
                generic: "",
                specific: "",
                subspecific: "",
                denomination: "",
                type: "",
                active: false,
                original: false,
                ingres: false,
                egress: false,
            },

            urlPrevious: `${window.app_url}/accounting/accounts`,
        };
    },
    created() {
        EventBus.$on("register:account", (data) => {
            this.isEdit = false;
            this.createRecord(data);
        });
        EventBus.$on("load:data-account-form", (data) => {
            this.isEdit = true;
            if (data == null) {
                this.reset(false);
            } else {
                this.record = {
                    id: data.id,
                    code: data.code,
                    denomination: data.denomination,
                    active: data.active,
                    original: data.original,
                    type: data.type,
                    ingres: false,
                    egress: false,
                };
                if (data.type == "resource") {
                    this.record.ingres = true;
                    this.record.egress = false;
                }
                if (data.type == "egress") {
                    this.record.egress = true;
                    this.record.ingres = false;
                }
                if (data.type == "") {
                    this.record.egress = false;
                    this.record.ingres = false;
                }
                if (data.parent) {
                    this.record_select = data.parent.id;
                }
            }
        });
    },
    mounted() {
        var selector = document.getElementById("code");
        Inputmask("9.9.9.99.99.99.999").mask(selector);

        this.reset();
    },
    methods: {
        /**
         * Limpia los valores de las variables del formulario
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset(resetRecords = true) {
            if (resetRecords) {
                this.record_select = [];
            }
            this.isEdit= false;
            this.record = {
                id: "",
                group: "",
                subgroup: "",
                item: "",
                generic: "",
                specific: "",
                subspecific: "",
                denomination: "",
                active: false,
                original: false,
                ingres: false,
                egress: false,
            };
        },
        hasExistCode() {
            const vm = this;

            const found = vm.accRecords.find(
                (element) => element["code"] == vm.record.code
            );
            return (typeof found != "undefined" && found.id == vm.record.id) ||
                typeof found == "undefined"
                ? ""
                : "is-invalid";
        },
        /**
         * Valida que los campos del código sean validos
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {boolean} retorna falso si algun campo no cumple el formato correspondiente
         */
        FormatCode() {
            var res = true;
            var errors = [];
            if (!this.record.code || this.record.code.split("_").length > 1) {
                errors.push(
                    "El campo código es obligatorio y debe ser llenado completamente"
                );
                res = false;
            }
            if (!this.record.denomination) {
                errors.push("El campo denominación es obligatorio.");
                res = false;
            }
            this.$parent.$refs.accountingAccountForm.showAlertMessages(errors);
            return res;
        },
        /**
         * Envia la información a ser almacenada de la cuenta patrimonial
         * en caso de que se este actualizando la cuenta, se envia la información a la ruta para ser actualizada
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        createRecord(url) {
            const vm = this;
            if (!vm.FormatCode()) {
                return;
            }
            var dt = vm.record;
            var auxRecord = {
                id: "",
                group: "",
                subgroup: "",
                item: "",
                generic: "",
                specific: "",
                subspecific: "",
                institutional: "",
                denomination: "",
                active: false,
                original: false,
                type: "",
            };
            /**
             * Se formatean los ultimos tres campos del codigo de ser necesario
             */
            auxRecord.id = dt.id;
            auxRecord.group = dt.code.split(".")[0];
            auxRecord.subgroup = dt.code.split(".")[1];
            auxRecord.item = dt.code.split(".")[2];
            auxRecord.generic = dt.code.split(".")[3];
            auxRecord.specific = dt.code.split(".")[4];
            auxRecord.subspecific = dt.code.split(".")[5];
            auxRecord.institutional = dt.code.split(".")[6];
            auxRecord.denomination = dt.denomination;
            auxRecord.active = dt.active;
            auxRecord.original = dt.original;
            auxRecord.type = dt.type;
            if (dt.ingres == true) {
                auxRecord.type = "resource";
            }
            if (dt.egress == true) {
                auxRecord.type = "egress";
            }
            if (dt.egress == false && dt.ingres == false) {
                auxRecord.type = "";
            }
            url = vm.setUrl(url);
            vm.loading = true;
            if (auxRecord.id) {
                axios
                    .put(url + "/" + auxRecord.id, auxRecord)
                    .then((response) => {
                        /** Se emite un evento para actualizar el listado de cuentas en el select */
                        vm.accRecords = [];
                        vm.accRecords = response.data.records;

                        /** Se emite un evento para actualizar el listado de cuentas de la tablas del componente accounting-accounts-list */
                        EventBus.$emit(
                            "reload:list-accounts",
                            response.data.records
                        );
                        vm.showMessage("update");
                        vm.loading = false;
                    })
                    .catch((error) => {
                        var errors = [];
                        if (typeof error.response != "undefined") {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                            vm.$parent.$refs.accountingAccountForm.showAlertMessages(
                                errors
                            );
                        }
                    });
            } else {
                axios
                    .post(url, auxRecord)
                    .then((response) => {
                        /** Se emite un evento para actualizar el listado de cuentas en el select */
                        vm.accRecords = [];
                        vm.accRecords = response.data.records;

                        /** Se emite un evento para actualizar el listado de cuentas de la tablas del componente accounting-accounts-list */
                        EventBus.$emit(
                            "reload:list-accounts",
                            response.data.records
                        );
                        vm.showMessage("store");

                        vm.loading = false;
                    })
                    .catch((error) => {
                        var errors = [];
                        if (typeof error.response != "undefined") {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                            vm.$parent.$refs.accountingAccountForm.showAlertMessages(
                                errors
                            );
                        }
                    });
            }

            vm.reset();
        },
    },
    watch: {
        /**
         * Obtiene el código disponible para la subcuenta y carga la información en el formulario
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        record_select(res, ant) {
            const vm = this;
            if (res != "" && res != ant) {
                /** Esta validacion es para el caso de cargar datos en el formulario
                evitando que realize la consulta axios */
                if (vm.isEdit) {
                    return;
                }
                if (typeof res == "number") {
                    return;
                }
                axios
                    .get(
                        `${window.app_url}/accounting/get-children-account/${res}`
                    )
                    .then((response) => {
                        var account = response.data.account;
                        /**
                         * Selecciona en pantalla la nueva cuentas
                         */
                        vm.record = {
                            id: "",
                            code: account.code,
                            denomination: account.denomination,
                            active: account.active,
                            exist: account.exist,
                            type: account.type,
                        };
                        if (account.type == "resource") {
                            this.record.ingres = true;
                            this.record.egress = false;
                        }
                        if (account.type == "egress") {
                            this.record.egress = true;
                            this.record.ingres = false;
                        }
                        if (account.type == "") {
                            this.record.egress = false;
                            this.record.ingres = false;
                        }
                    });
            }
        },
        "record.ingres"(newvalue, oldvalue) {
            if (newvalue == true && this.record.egress == true) {
                setTimeout(() => {
                    this.record.egress = false;
                    this.record.ingres = false;
                }, "700");
            }
        },
        "record.egress"(newvalue, oldvalue) {
            setTimeout(() => {
                if (newvalue == true && this.record.ingres == true) {
                    this.record.ingres = false;
                    this.record.egress = false;
                }
            }, "700");
        },
        records(res) {
            this.accRecords = res;
        },
    },
};
</script>
