<template>
    <section id="ProjectTrackingTaskForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2" id="project">
                    <div class="form-group">
                        <label>Proyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un proyecto">
                                <input type="radio" class="custom-control-input" id="active_project" name="active"
                                    v-model="record.active" value="project">
                                <label class="custom-control-label" for="active_project"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" id="subproject">
                    <div class="form-group">
                        <label>Subproyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un subproyecto">
                                <input type="radio" class="custom-control-input" id="active_subproject" name="active"
                                    v-model="record.active" value="subproject">
                                <label class="custom-control-label" for="active_subproject"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" id="product">
                    <div class="form-group">
                        <label>Producto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un producto">
                                <input type="radio" class="custom-control-input" id="active_product" name="active"
                                    v-model="record.active" value="product">
                                <label class="custom-control-label" for="active_product"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="record.active == 'project'" class="col-md-5">
                    <div class="form-group is-required">
                        <label>Proyecto:</label>
                        <select2 :options="projects_list" id="project_name" data-toggle="tooltip"
                            v-model="record.project_name" @input="getActivitiesByProject()">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'subproject'" class="col-md-5">
                    <div class="form-group is-required">
                        <label>Subproyecto:</label>
                        <select2 :options="subprojects_list" id="subproject_name" data-toggle="tooltip"
                            v-model="record.subproject_name" @input="getActivitiesBySubProject()">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'product'" class="col-md-5">
                    <div class="form-group is-required">
                        <label>Producto:</label>
                        <select2 :options="products_list" id="product_name" data-toggle="tooltip"
                            v-model="record.product_name" @input="getActivitiesByProduct()">
                        </select2>
                    </div>
                </div>
                <div v-if="(activity_plans_list.length>0)" class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Actividad:</label>
                        <select2 :options="activity_plans_list" id="activity" data-toggle="tooltip"
                            title="Seleccione la Actividad (requerido)" v-model="record.activity_plan_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Nombre:</label>
                        <input type="text" id="name" placeholder="Nombre" data-toggle="tooltip"
                            title="Ingrese el nombre de la tarea (requerido)" class="form-control input-sm"
                            v-model="record.name" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descripción:</label>
                        <input type="text" id="description" placeholder="Descripción" data-toggle="tooltip"
                            title="Ingrese la descripción de la tarea" class="form-control input-sm"
                            v-model="record.description" />
                    </div>
                </div>
                <div v-if="(activity_employers_list.length>0)" class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Responsable de la tarea:</label>
                        <select2 :options="activity_employers_list" id="responsable" data-toggle="tooltip"
                            title="Seleccione la persona responsable de la tarea (requerido)"
                            v-model="record.employers_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Prioridad:</label>
                        <select2 :options="priorities_list" id="priority" data-toggle="tooltip"
                            title="Seleccione el nivel de prioridad de la tarea (requerido)"
                            v-model="record.priority_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="start_date">Fecha de inicio:</label>
                        <input type="date" id="start_date" placeholder="Fecha inicial" class="form-control input-sm"
                            data-toggle="tooltip" title="Indique el monto de financiamiento"
                            v-model="record.start_date">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="end_date">Fecha de culminación:</label>
                        <input type="date" id="end_date" placeholder="Fecha final"
                            class="form-control input-sm no-restrict" data-toggle="tooltip"
                            title="Indique el monto de financiamiento" v-model="record.end_date">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Peso de Importancia:</label>
                        <input type="number" min="0" max="100" id="weight" placeholder="1-100" data-toggle="tooltip"
                            title="Ingrese el peso de la tarea" disable class="form-control input-sm"
                            v-model="record.weight" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" name="category">
                        <label>Estatus de la Actividad:</label>
                        <select2 :options="activity_statuses_list" id="activity_status" data-toggle="tooltip"
                            title="Seleccione el Estatus de la actividad (requerido)"
                            v-model="record.activity_status_id">
                        </select2>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer pull-right" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" @click="reset"><i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="createRecord('projecttracking/tasks')" data-toggle="tooltip"
                title="Guardar registro" class="btn btn-success btn-icon btn-round">
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        task_id: {
            type: Number
        }
    },
    data() {
        return {
            record: {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                employers_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                weight: '',
                activity_status_id: '',
                active: '',
                tasks: []
            },
            errors: [],
            projects_list: [],
            subprojects_list: [],
            products_list: [],
            activity_plans_list: [],
            activity_employers_list: [],
            priorities_list: [],
            activity_statuses_list: [],
            records: [],
        }
    },

    methods: {
        reset() {
            this.record = {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                employers_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                weight: '',
                activity_status_id: '',
                active: ''
            };
        },

        getActivitiesByProject() {
            const vm = this;
            vm.activity_plans_list = [],
                axios.get(`${window.app_url}/projecttracking/get-activities-by-project/${vm.record.project_name}`).then(response => {
                    vm.activity_plans_list = response.data.activities_by_project;
                });
            vm.getPersonalByProject();
        },

        getPersonalByProject() {
            const vm = this;
            vm.activity_employers_list = [],
                axios.get(`${window.app_url}/projecttracking/get-personal-by-project/${vm.record.project_name}`).then(response => {
                    vm.activity_employers_list = response.data.personal_by_project;
                });
        },

        getActivitiesBySubProject() {
            const vm = this;
            vm.activity_plans_list = [],
                axios.get(`${window.app_url}/projecttracking/get-activities-by-subproject/${vm.record.subproject_name}`).then(response => {
                    vm.activity_plans_list = response.data.activities_by_subproject;
                });
            vm.getPersonalBySubProject();
        },

        getPersonalBySubProject() {
            const vm = this;
            vm.activity_employers_list = [],
                axios.get(`${window.app_url}/projecttracking/get-personal-by-subproject/${vm.record.subproject_name}`).then(response => {
                    vm.activity_employers_list = response.data.personal_by_subproject;
                });
        },

        getActivitiesByProduct() {
            const vm = this;
            vm.activity_plans_list = [],
                axios.get(`${window.app_url}/projecttracking/get-activities-by-product/${vm.record.product_name}`).then(response => {
                    vm.activity_plans_list = response.data.activities_by_product;
                });
            vm.getPersonalByProduct();
        },

        getPersonalByProduct() {
            const vm = this;
            vm.activity_employers_list = [],
                axios.get(`${window.app_url}/projecttracking/get-personal-by-product/${vm.record.product_name}`).then(response => {
                    vm.activity_employers_list = response.data.personal_by_product;
                });
        },

        getProjectsByActivityPlan() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-projects-by-activity-plan`).then(response => {
                vm.projects_list = response.data;
            });
        },
        getSubprojectsByActivityPlan() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-subprojects-by-activity-plan`).then(response => {
                vm.subprojects_list = response.data;
            });
        },
        getProductsByActivityPlan() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-products-by-activity-plan`).then(response => {
                vm.products_list = response.data;
            });
        },
        getPriorities() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-priorities`).then(response => {
                vm.priorities_list = response.data;
            });
        },
        getActivityStatuses() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-activity-statuses`).then(response => {
                vm.activity_statuses_list = response.data;
            });
        },
        async loadForm(id) {
            const vm = this;
            vm.loading = true;
            await axios.get(`${window.app_url}/projecttracking/task/vue-info/${id}`).then(response => {
                if (typeof (response.data.records != "undefined")) {

                    if (response.data.records.project_name) {
                        vm.record.active = 'project';
                        vm.record.project_name = response.data.records.project_name;
                        vm.getActivitiesByProject();
                    } else if (response.data.records.subproject_name) {
                        vm.record.active = 'subproject';
                        vm.record.subproject_name = response.data.records.subproject_name;
                        vm.getActivitiesBySubProject();
                    } else {
                        vm.record.active = 'product';
                        vm.record.product_name = response.data.records.product_name;
                        vm.getActivitiesByProduct();
                    }

                    vm.record.activity_plan_id = response.data.records.activity_plan_id;
                    vm.record.employers_id = response.data.records.employers_id;
                    vm.record.name = response.data.records.name;
                    vm.record.description = response.data.records.description;
                    vm.record.priority_id = response.data.records.priority_id;
                    vm.record.start_date = response.data.records.start_date;
                    vm.record.end_date = response.data.records.end_date;
                    vm.record.weight = response.data.records.weight;
                    vm.record.activity_status_id = response.data.records.activity_status_id;
                }
            });
        },
    },
    created() {
        const vm = this;
        vm.getProjectsByActivityPlan();
        vm.getSubprojectsByActivityPlan();
        vm.getProductsByActivityPlan();
        vm.getPriorities();
        vm.getActivityStatuses();
        if (vm.task_id) {
            vm.loadForm(vm.task_id);
        }
    },
    mounted() {
        const vm = this;
        vm.reset();
    }
};
</script>