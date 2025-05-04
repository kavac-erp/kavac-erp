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
            <div class="row">
                <div class="col-12 mt-4">
                    <div class="form-group">
                        <label class="control-label">
                            Acciones Especificas
                        </label>
                    </div>
                    <select2
                        v-model="params.formulation_id"
                        :options="formulations"
                        id="formulation_id"
                        :disabled="isFormulationsDisabled || loading"
                    ></select2>
                </div>
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

        getPdf() {
            window.open(
                `${this.pdf}?formulation_id=${this.params.formulation_id}&start_date=${this.params.start_date}&end_date=${this.params.end_date}`
            );
        },
    },
};
</script>
