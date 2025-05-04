<template>
    <div>
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
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
            <div class="row mb-3">
                <div class="col-2">
                    <div class="form-group">
                        <label class="control-label">Años de formulación</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select2
                            v-model="params.year"
                            :options="years"
                        ></select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input
                            type="radio"
                            class="custom-control-input sel_pry_acc"
                            id="project"
                            name="centralized_action"
                            v-model="isProject"
                            :value="1"
                            @change="change"
                        />
                        <label
                            class="custom-control-label"
                            for="project"
                        >
                            Proyecto
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2
                            v-model="params.project_id"
                            :options="budgetProjectsArray"
                            id="project_id"
                            :disabled="isProject !== 1"
                        ></select2>
                    </div>
                </div>
                <div class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input
                            type="radio"
                            class="custom-control-input sel_pry_acc"
                            id="centralized_action"
                            name="centralized_action"
                            v-model="isProject"
                            :value="0"
                            @change="change"
                        />
                        <label
                            class="custom-control-label"
                            for="centralized_action"
                        >
                            Acción Centralizada
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2
                            v-model="params.centralized_action_id"
                            :options="budgetCentralizedActionsArray"
                            id="centralized_action_id"
                            :disabled="isProject !== 0"
                        ></select2>
                    </div>
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
                                :options="formulations"
                                track_by="text"
                                :hide_selected="false"
                                :selected="params.formulation_id"
                                v-model="params.formulation_id"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <br />
                    <hr />
                </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Desde</label>
                        <input
                            v-model="params.start_date"
                            class="form-control input-sm"
                            type="date"
                        />
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group is-required mt-3">
                        <label class="control-label">Hasta</label>
                        <input
                            v-model="params.end_date"
                            class="form-control input-sm"
                            type="date"
                        />
                    </div>
                </div>
            </div>
        </div>
        <!-- card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                @click="getPdf"
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
        url: {
            type: String,
            required: true,
        },
        pdf: {
            type: String,
            required: true,
        },
        formulationsUrl: {
            type: String,
            required: true,
        },
        years: {
            type: Array,
            required: true,
        },
        errors: {
            type: Array,
            required: true,
        },
        budgetProjects: {
            type: String,
            default: "[]",
        },
        budgetCentralizedActions: {
            type: String,
            default: "[]",
        },
    },
    data() {
        return {
            isProject: '',
            projects: [],
            centralizedActions: [],
            formulations: [],
            all_specific_actions: false,
            params: {
                year: "",
                start_date: "",
                end_date: "",
                project_id: "",
                centralized_action_id: "",
                formulation_id: "",
            },
            budgetProjectsArray: JSON.parse(this.budgetProjects),
            budgetCentralizedActionsArray: JSON.parse(
                this.budgetCentralizedActions
            ),
            loading: false,
        };
    },
    async created() {
        await this.getFormulations();
        this.all_specific_actions = false;
    },
    watch: {
        "params.project_id": async function (newValue, _) {
            if (newValue === "") {
                this.formulations = [];
                return;
            }
            this.loading = true;
            this.formulations = await this.getFormulations();
            this.loading = false;
        },

        "params.centralized_action_id": async function (newValue, _) {
            if (newValue === "") {
                this.formulations = [];
                return;
            }
            this.loading = true;
            this.formulations = await this.getFormulations();
            this.loading = false;
        },
    },
    computed: {
        isFormulationsDisabled() {
            return this.formulations.length === 0;
        },
    },
    methods: {
        async getFormulations() {
            const config = {
                params: {
                    is_project: this.isProject,
                    id: this.isProject
                        ? this.params.project_id
                        : this.params.centralized_action_id,
                },
            };
            const { data } = await axios.get(this.formulationsUrl, config);
            return data;
        },

        change() {
            if (this.isProject) this.params.centralized_action_id = "";
            else this.params.project_id = "";
        },

        async getData() {
            if (
                this.params.formulation_id == null ||
                this.params.formulation_id === ""
            )
            return;
            const config = {
                params: {
                    start_date: this.params.start_date,
                    end_date: this.params.end_date,
                    formulation_id: this.params.formulation_id,
                },
            };
            this.loading = true;
            const { data } = await axios.get(this.url, config);
            this.loading = false;
            this.records = data.data;
        },

        reset() {
            const vm = this;
            vm.isProject = '';
            vm.all_specific_actions = false;
            vm.params = {
                year: "",
                start_date: "",
                end_date: "",
                project_id: "",
                centralized_action_id: "",
                formulation_id: "",
            };
        },

        getPdf() {
            let formulationIds = [];

            for (this.params.formulation_id of this.params.formulation_id) {
                formulationIds.push(this.params.formulation_id['id']);
            }

            window.open(
                `${this.pdf}?formulation_id[]=${formulationIds}` +
                `&start_date=${this.params.start_date}` +
                `&end_date=${this.params.end_date}` +
                `&all_specific_actions=${this.all_specific_actions}` +
                `&is_project=${this.isProject}`
            );

            this.reset();
        },
    },
};
</script>
