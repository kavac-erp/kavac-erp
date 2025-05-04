<template>
    <div class="form-horizontal">
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
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
            <!-- mensajes de error -->
            <div class="row">
                <div class="col-12 mt-4">
                    <label for="all_specific_actions">
                        Reporte de Proyectos y Acciones Centralizadas
                    </label>
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="consolidated"
                            :value="true"
                            v-model="consolidated"
                        >
                        <label
                            class="custom-control-label"
                            for="consolidated"
                        ></label>
                    </div>
                    <hr>
                </div>
                <div class="row col-12" v-show="!consolidated">
                    <div class="col-6">
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                class="custom-control-input sel_pry_acc"
                                id="sel_project"
                                value="project"
                                name="project_centralized_action"
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
                                disabled
                            ></select2>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                class="custom-control-input sel_pry_acc"
                                id="sel_centralized_action"
                                value="centralized_action"
                                name="project_centralized_action"
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
                                disabled
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
                                v-model="all_specific_actions"
                            >
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
                    </div>
                </div>
                <div class="col-12" v-if="all_specific_actions">
                    <br />
                </div>
                <div class="col-4" id="budgetAvailabilityInitDate">
                    <label>
                        <strong>Desde:</strong>
                    </label>
                    <div class="form-group is-required mt-2">
                        <label class="control-label">
                            Partida Presupuestaria
                        </label>
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
                    <label>
                        <strong>Hasta:</strong>
                    </label>
                    <div class="form-group is-required mt-2">
                        <label class="control-label">
                            Partida Presupuestaria
                        </label>
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
        <div class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                @click="generateReport"
                id="budgetAvailabilityGenerateReport"
            >
                <span>Generar reporte</span>
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
            consolidated: false,
            budgetItemsArray: JSON.parse(this.budgetItems),
            budgetProjectsArray: JSON.parse(this.budgetProjects),
            budgetCentralizedActionsArray: JSON.parse(
                this.budgetCentralizedActions
            ),
            errors: [],
            specific_actions: [],
        };
    },
    mounted() {
        const vm = this;
        $(".sel_pry_acc").on("change", function (e) {
            $("#project_id").attr("disabled", e.target.id !== "sel_project");
            $("#centralized_action_id").attr(
                "disabled",
                e.target.id !== "sel_centralized_action"
            );
            document.getElementById("all_specific_actions").checked = false;

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

                if (document.getElementById("all_specific_actions").checked) {
                    document.getElementById("all_specific_actions").checked = false;
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
            document.getElementById("all_specific_actions").checked = false;
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

        generateReport: function () {
            this.errors = [];
            if (!this.consolidated) {

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
                if (!this.specific_actions_ids) {
                    this.errors.push(
                        "El campo Acción Específica es obligatorio"
                    );
                }
                else {
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
                    window.open(
                        `${this.url}?initialDate=${this.initialDate}
                        &finalDate=${this.finalDate}
                        &initialCode=${this.initialCode}
                        &finalCode=${this.finalCode}
                        &accountsWithMovements=${this.accountsWithMovements}
                        &project_id=${this.project_id ? this.project_id : this.centralized_action_id}
                        &project_type=${this.project_id ? "project" : "centralized_action"}
                        &specific_actions_ids=${this.specific_actions_ids}`
                    );
                    this.reset();
                }
            } else {
                let initialDate_ = new Date(this.initialDate);
                let finalDate_ = new Date(this.finalDate);

                if (initialDate_.getTime() >= finalDate_.getTime()) {
                    this.errors.push("La fecha inicial es incorrecta");
                }

                if (!this.initialDate) {
                    this.errors.push("El campo desde es obligatorio");
                }
                if (!this.finalDate) {
                    this.errors.push("El campo hasta es obligatorio");
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

                let projects_ids = this.budgetProjectsArray
                    .slice(1)
                    .map((element) => {
                        return element.id;
                    });
                let centralized_actions_ids = this.budgetCentralizedActionsArray
                    .slice(1)
                    .map((element) => {
                        return element.id;
                    });

                if (this.errors.length === 0) {
                    window.open(`${window.app_url}/budget/report/consolidated-pdf?
                        initialDate=${this.initialDate}
                        &finalDate=${this.finalDate}
                        &initialCode=${this.initialCode}
                        &finalCode=${this.finalCode}
                        &accountsWithMovements=${this.accountsWithMovements}
                        &projects_ids=${projects_ids}
                        &centralized_actions_ids=${centralized_actions_ids}`);
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
