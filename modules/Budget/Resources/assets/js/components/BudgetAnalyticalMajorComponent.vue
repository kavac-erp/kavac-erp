<template>
    <div class="form-horizontal">
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <div
                v-if="errors.length > 0"
                class="alert alert-danger"
            >
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>¡Atención!</strong>
                    Debe verificar los siguientes errores antes de continuar:
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
            <!-- mensajes de error -->
            <div class="row">
                <div class="col-12 row">
                    <div class="col-6">
                        <div class="form-group is-required mt-2">
                            <label class="control-label">Tipo de reporte</label>
                            <select2
                                name="report_type"
                                :options="reportTypes"
                                v-model="report_type_id"
                                id="report_type_id"
                            ></select2>
                        </div>
                    </div>
                </div>
                <div class="row col-12">
                    <div class="col-6 mt-4">
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                class="custom-control-input sel_pry_acc"
                                id="sel_project"
                                value="project"
                                name="project_centralized_action"
                                v-model="selectedOption"
                                @input="enableProjectSelect"
                            >
                            <label
                                class="custom-control-label"
                                for="sel_project"
                            >
                                Proyecto
                            </label>
                        </div>
                        <div class="mt-4">
                            <select2
                                :options="budgetProjectsArray"
                                v-model="project_id"
                                id="project_id"
                                @input="getSpecificActions('Project')"
                                :disabled="isDisabled"
                            ></select2>
                        </div>
                    </div>
                    <div class="col-6 mt-4">
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                class="custom-control-input sel_pry_acc"
                                id="sel_centralized_action"
                                value="centralized_action"
                                name="project_centralized_action"
                                v-model="selectedOption"
                                @input="enableCentralizedActionSelect"
                            >
                            <label
                                class="custom-control-label"
                                for="sel_centralized_action"
                            >
                                Acción Centralizada
                            </label>
                        </div>
                        <div class="mt-4">
                            <select2
                                name="centralized_action"
                                :options="budgetCentralizedActionsArray"
                                v-model="centralized_action_id"
                                @input="getSpecificActions('CentralizedAction')"
                                id="centralized_action_id"
                                :disabled="isDisabled"
                            ></select2>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <label for="all_specific_actions">
                            Seleccionar todas las acciones especificas de este
                            Proyecto / Acción Centralizada
                        </label>
                        <div class="custom-control custom-switch">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="all_specific_actions"
                                value="true"
                                name="all_specific_actions"
                                v-model="all_specific_actions">
                            <label
                                class="custom-control-label"
                                for="all_specific_actions"
                            ></label>
                        </div>
                    </div>
                    <div
                        class="col-12"
                        id="allSpecificActions"
                        v-if="!all_specific_actions"
                    >
                        <div class="mt-4">
                            <label
                                for="specific_action_id"
                                class="control-label"
                            >
                                Acción Específica
                            </label>
                            <div
                                class="form-group is-required"
                                style="margin-top: -1.5rem"
                            >
                                <v-multiselect
                                    :options="specific_actions"
                                    track_by="text"
                                    :hide_selected="false"
                                    :selected="specific_actions_ids"
                                    v-model="specific_actions_ids"
                                >
                                </v-multiselect>
                            </div>
                        </div>
                        <br />
                        <hr />
                    </div>
                </div>
                <div class="col-12" v-if="all_specific_actions"><br /></div>
                <div class="col-4" id="budgetAvailabilityInitDate">
                    <label><strong>Desde:</strong></label>
                    <div class="form-group is-required mt-2">
                        <label class="control-label"
                            >Partida Presupuestaria</label
                        >
                        <select2
                            v-model="initialCode"
                            :options="budgetItemsArray"
                        ></select2>
                    </div>
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Desde:</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="initialDate"
                        />
                    </div>
                </div>
                <div class="col-4" id="budgetAvailabilityEndDate">
                    <label><strong>Hasta:</strong></label>
                    <div class="form-group is-required mt-2">
                        <label class="control-label"
                            >Partida Presupuestaria</label
                        >
                        <select2
                            v-model="finalCode"
                            :options="budgetItemsArray"
                        ></select2>
                    </div>
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Hasta:</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="finalDate"
                        />
                    </div>
                </div>
            </div>
        </div>
        <!-- Final card-body -->
        <!-- card-footer -->
        <div v-if="report_type_id == 1" class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                @click="generateReport(false)"
                id="budgetMajorAnayticalGenerateReport"
            >
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                @click="generateReport(true)"
                id="budgetMajorAnayticalExportReport"
            >
                <span>Exportar reporte</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
        <div v-else class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                @click="generateReport(true)"
                id="budgetMajorAnayticalExportReport"
            >
                <span>Exportar reporte</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </div>
</template>

<script>
export default {
    props: {
        budgetItems: {
            type: String,
            default: "[]",
        },
        budgetProjects: {
            type: String,
            default: "[]",
        },
        budgetCentralizedActions: {
            type: String,
            default: "[]",
        },
        url: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            initialDate: "",
            finalDate: "",
            initialCode: 0,
            finalCode: 0,
            accountsWithMovements: false,
            project_id: "",
            centralized_action_id: "",
            specific_actions_ids: [],
            all_specific_actions: false,
            reportTypes: [],
            report_type_id: "",
            selectedOption: null,
            isDisabled: true,
            budgetItemsArray: JSON.parse(this.budgetItems),
            budgetProjectsArray: JSON.parse(this.budgetProjects),
            budgetCentralizedActionsArray: JSON.parse(
                this.budgetCentralizedActions
            ),
            errors: [],
            specific_actions: [],
        };
    },
    created() {
        this.all_specific_actions = false;
    },
    mounted() {
        const vm = this;

        vm.getReportTypes();

        $(".sel_pry_acc").on("change", function (e) {
            $("#project_id").attr("disabled", e.target.id !== "sel_project");
            $("#centralized_action_id").attr(
                "disabled",
                e.target.id !== "sel_centralized_action"
            );
            if (e.target.id === "sel_project") {
                vm.centralized_action_id = "";
                vm.specific_actions_ids = [];
                $("#centralized_action_id")
                    .closest(".form-group")
                    .removeClass("is-required");
                $("#project_id").closest(".form-group").addClass("is-required");
            } else if (e.target.id === "sel_centralized_action") {
                vm.project_id = "";
                vm.specific_actions_ids = [];
                $("#centralized_action_id")
                    .closest(".form-group")
                    .addClass("is-required");
                $("#project_id")
                    .closest(".form-group")
                    .removeClass("is-required");
            }
        });

        $("#all_specific_actions").on(
            "change",
            function () {
                vm.all_specific_actions = this.checked;
                if (vm.all_specific_actions) {
                    for (
                        let index = 1;
                        index < vm.specific_actions.length;
                        index++
                    ) {
                        vm.specific_actions_ids.push(
                            vm.specific_actions[index].id
                        );
                    }
                } else {
                    vm.specific_actions_ids = [];
                }
            }
        );

        $("#consolidated").on(
            "change",
            function () {
                vm.consolidated = this.checked;
                $("#sel_project").attr(
                    "disabled",
                    vm.consolidated !== false
                );
                $("#sel_centralized_action").attr(
                    "disabled",
                    vm.consolidated !== false
                );
                vm.project_id = "";
                vm.centralized_action_id = "";
                $("#project_id").attr("disabled", vm.consolidated !== false);
                $("#centralized_action_id").attr(
                    "disabled",
                    vm.consolidated !== false
                );

                if (document.getElementById(all_specific_actions).checked) {
                    document.getElementById(all_specific_actions).checked = false;
                    vm.all_specific_actions = false;
                }
                $("#all_specific_actions").attr(
                    "disabled",
                    vm.consolidated !== false
                );
            }
        );
    },
    methods: {
        reset() {
            const vm = this;
            vm.all_specific_actions = false;
            vm.specific_actions_ids = "";
            vm.initialDate = "",
            vm.finalDate = "",
            vm.initialCode = 0,
            vm.finalCode = 0,
            vm.project_id = "",
            vm.centralized_action_id = "",
            vm.report_type_id = "";
            vm.selectedOption = null;
            vm.isDisabled = true;
            document.getElementById(all_specific_actions).checked = false;
        },

        enableProjectSelect() {
            this.isDisabled = false;
        },

        enableCentralizedActionSelect() {
            this.isDisabled = false;
        },

        getReportTypes() {
            const vm = this;

            vm.reportTypes = [
                {
                    id: '',
                    text: "Seleccione...",
                },
                {
                    id: 1,
                    text: "Detallado",
                },
                {
                    id: 2,
                    text: "Acumulado",
                },
            ];
        },

        getSpecificActions(type) {
            let id =
                type === "Project"
                    ? this.project_id
                    : this.centralized_action_id;

            this.specific_actions = [];

            if (id) {
                axios
                    .get(
                        `${window.app_url}/budget/get-specific-actions/${type}/${id}/report`
                    )
                    .then((response) => {
                        this.specific_actions = response.data;
                    })
                    .catch((error) => {
                        vm.logs(
                            "BudgetSubSpecificFormulationComponent.vue",
                            551,
                            error,
                            "getSpecificActions"
                        );
                    });
            }

            var len = this.specific_actions.length;
            $("#specific_action_id").attr("disabled", len == 0);
        },

        generateReport(exportReport) {
            this.errors = [];
            if (!this.report_type_id) {
                this.errors.push(
                    "El campo Tipo de reporte es obligatorio"
                );
            }
            if (!this.initialDate) {
                this.errors.push("El campo fecha Desde es obligatorio");
            }
            if (!this.finalDate) {
                this.errors.push("El campo fecha Hasta es obligatorio");
            }
            if (!this.initialCode) {
                this.errors.push(
                    "El campo Desde: Partida Presupuestario es obligatorio"
                );
            }
            if (!this.finalCode) {
                this.errors.push(
                    "El campo Hasta: Partida Presupuestario es obligatorio"
                );
            }
            if (!this.project_id && !this.centralized_action_id) {
                this.errors.push(
                    "El campo Proyecto o Acción Centralizada es obligatorio"
                );
            }
            if (!this.specific_actions_ids || this.specific_actions_ids == '' ) {
                this.errors.push(
                    "El campo Acción Específica es obligatorio"
                );
            } else {
                if (!this.all_specific_actions) {
                    this.specific_actions_ids =
                        this.specific_actions_ids.map(function (object) {
                            return object.id;
                        });
                }
            }
            let initialDate_ = new Date(this.initialDate);
            let finalDate_ = new Date(this.finalDate);

            if (initialDate_.getTime() >= finalDate_.getTime()) {
                this.errors.push("La fecha inicial es incorrecta");
            }

            if (this.errors.length === 0) {
                if (exportReport) {
                    window.open(
                        `${this.url}?initialDate=${this.initialDate}
                        &finalDate=${this.finalDate}
                        &initialCode=${this.initialCode}
                        &finalCode=${this.finalCode}
                        &accountsWithMovements=${this.accountsWithMovements}
                        &project_id=${this.project_id ? this.project_id : this.centralized_action_id}
                        &project_type=${this.project_id ? "project" : "centralized_action"}
                        &specific_actions_ids=${this.specific_actions_ids}
                        &report_type_id=${this.report_type_id}
                        &exportReport=${exportReport}`
                    );
                    this.reset();
                } else {
                    var fields = {
                        initialDate: this.initialDate,
                        finalDate: this.finalDate,
                        initialCode: this.initialCode,
                        finalCode: this.finalCode,
                        accountsWithMovements: this.accountsWithMovements,
                        project_id: this.project_id ? this.project_id : this.centralized_action_id,
                        project_type: this.project_id ? "project" : "centralized_action",
                        specific_actions_ids: this.specific_actions_ids,
                        exportReport: exportReport
                    };

                    axios.post(this.url, fields).then(response => {
                        if (response.data.result == false) {
                            this.showMessage('custom', 'Alerta!', 'warning', 'screen-error', 'No se pudo generar el reporte');
                        }
                        else {
                            this.showMessage('custom', '¡Éxito!', 'info', 'screen-ok', 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación');
                        }
                        this.reset();
                    }).catch(error => {
                        this.errors = [];

                        if (typeof(error.response) !="undefined") {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    this.errors.push(error.response.data.errors[index][0]);
                                }
                            }
                        }
                    });
                }
            }
        },
    },
    watch: {
        specific_actions: function () {
            $("#specific_action_id").attr(
                "disabled",
                this.specific_actions.length <= 1
            );
        },
    },
};
</script>