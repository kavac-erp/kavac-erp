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
                                    @click="reset" v-model="record.active" value="project">
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
                                    @click="reset" v-model="record.active" value="subproject">
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
                                    @click="reset" v-model="record.active" value="product">
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
                <div v-if="activity_plans_list.length > 0" class="col-md-4">
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
                    <div class="form-group is-required">
                        <label>Descripción:</label>
                        <input type="text" id="description" placeholder="Descripción" data-toggle="tooltip"
                            title="Ingrese la descripción de la tarea" class="form-control input-sm"
                            v-model="record.description" />
                    </div>
                </div>
                <div v-if="(activity_employers_list.length > 0)" class="col-md-4">
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
                    <div class="d-flex justify-content-end mt-2" v-if="!isEditMode && !isUpdateMode">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new" style="width: 10%;"
                            data-toggle="tooltip" title="Agregar tarea" aria-label="Agregar tarea"
                            @click.prevent="addTask">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-end mt-2" v-else-if="isEditMode && !isUpdateMode">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new" style="width: 10%;"
                            data-toggle="tooltip" title="Guardar tarea" aria-label="Guardar tarea"
                            @click.prevent="saveTask(record.id)">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body modal-table text-center">
                    <div v-if="tasks.length > 0">
                        <v-client-table :columns="columns" :data="tasks" :options="table_options"
                            v-if="tasks.length > 0">
                            <div slot="number" slot-scope="props" class="text-center">
                                {{ getTaskNumber(props.row.id) }}
                            </div>
                            <div slot="associate_to" slot-scope="props" class="text-center">
                                <div v-if="props.row.project_name">
                                    {{ 'Proyecto: ' + getEntityName(props.row.project_name, projects_list) }}
                                </div>
                                <div v-else-if="props.row.product_name">
                                    {{ 'Producto: ' + getEntityName(props.row.product_name, products_list) }}
                                </div>
                                <div v-else>
                                    {{ 'Subproyecto: ' + getEntityName(props.row.subproject_name, subprojects_list) }}
                                </div>
                            </div>
                            <!-- <div slot="employers_name" slot-scope="props" class="text-center">
                            {{ getEntityName(props.row.employers_id, activity_employers_list) }}
                        </div> -->
                            <div slot="activity_status" slot-scope="props" class="text-center">
                                {{ getEntityName(props.row.activity_status_id, activity_statuses_list) }}
                            </div>
                            <div slot="priority" slot-scope="props" class="text-center">
                                {{ getEntityName(props.row.priority_id, priorities_list) }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="editTask(props.row.id)"
                                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                                        title="Modificar registro" aria-label="Modificar registro" data-toggle="tooltip"
                                        data-placement="bottom" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteTask(props.row.id)"
                                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                                        title="Eliminar registro" aria-label="Eliminar registro" data-toggle="tooltip"
                                        data-placement="bottom" type="button">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </v-client-table>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-footer pull-right" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" @click="reset">
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="saveRecords()" data-toggle="tooltip" title="Guardar registro"
                class="btn btn-success btn-icon btn-round">
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
                tasks: [],
            },
            isEditMode: false,
            isUpdateMode: false,
            tasks_added: 0,
            tasks: [],
            errors: [],
            projects_list: [],
            subprojects_list: [],
            products_list: [],
            activity_plans_list: [],
            activity_employers_list: [],
            priorities_list: [],
            activity_statuses_list: [],
            records: [],
            columns: [
                'number',
                'name',
                'associate_to',
                'employer_name',
                'end_date',
                'activity_status',
                'priority',
                'weight',
                'id'
            ],
        }
    },
    methods: {
        saveRecords() {
            const vm = this;
            if (vm.isEditMode) {
                vm.errors.push('Debes culminar la ediciòn del registro para guardar.');
                return;
            }
            vm.createRecord('projecttracking/tasks');
        },
        validateForm() {
            const vm = this;
            vm.errors = [];
            let isValid = true;
            if (vm.isEditMode) {
                isValid = false;
                vm.push('Debe guardar loscambios de la tarea actual antes de guardar.')
            }
            if (!vm.record.active) {
                isValid = false;
                vm.errors.push('Debe elegir una opción entre proyecto, subproyecto o producto.');;
            }
            if (!vm.record.project_name && vm.record.active == 'project') {
                isValid = false;
                vm.errors.push('El campo Proyecto es obligatorio.');
            }
            if (!vm.record.subproject_name && vm.record.active == 'subproject') {
                isValid = false;
                vm.errors.push('El campo Subproyecto es obligatorio.');
            }
            if (!vm.record.product_name && vm.record.active == 'product') {
                isValid = false;
                vm.errors.push('El campo Producto es obligatorio.');
            }
            if (!vm.record.employers_id) {
                isValid = false;
                vm.errors.push('El campo responsable de la tarea es obligatorio.');
            }
            if (!vm.record.name) {
                isValid = false;
                vm.errors.push('El campo Nombre de la tarea es obligatorio.');
            }
            if (!vm.record.description) {
                isValid = false;
                vm.errors.push('El campo Descripción de la tarea es obligatorio.');
            }
            if (!vm.record.priority_id) {
                isValid = false;
                vm.errors.push('El campo Prioridad es obligatorio.');
            }
            if (!vm.record.start_date) {
                isValid = false;
                vm.errors.push('El campo Fecha de inicio es obligatorio.');
            }
            if (!vm.record.end_date) {
                isValid = false;
                vm.errors.push('El campo Fecha de finalización es obligatorio.');
            }
            if (!vm.record.activity_status_id) {
                isValid = false;
                vm.errors.push('El campo estatus de la actividad es obligatorio.');
            }
            if (!vm.record.weight) {
                isValid = false;
                vm.errors.push('El campo Peso es obligatorio.');
            }
            if (vm.record.weight && vm.record.weight < 1) {
                isValid = false;
                vm.errors.push('El campo Peso de importancia debe ser mayor a cero.');
            }
            return isValid;
        },
        getEntityName(entityId, entitiesList) {
            const entity = entitiesList.find(entity => entity.id == entityId);
            return entity ? entity.text : '';
        },
        saveTask(taskId) {
            const vm = this;
            const editedTask = {
                id: taskId,
                project_name: vm.record.project_name,
                subproject_name: vm.record.subproject_name,
                product_name: vm.record.product_name,
                activity_plan_id: vm.record.activity_plan_id,
                name: vm.record.name,
                description: vm.record.description,
                employers_id: vm.record.employers_id,
                employer_name: vm.getEntityName(vm.record.employers_id, vm.activity_employers_list),
                priority_id: vm.record.priority_id,
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                activity_status_id: vm.record.activity_status_id,
                weight: vm.record.weight
            };
            vm.tasks = vm.tasks.map(task => task.id == taskId ? editedTask : task);
            vm.reset();
            vm.record.tasks = vm.tasks;
            vm.isEditMode = false;
        },
        editTask(taskId) {
            const vm = this;
            vm.isEditMode = true;
            const task = vm.tasks.find(task => task.id == taskId);
            vm.record = {
                id: task.id,
                project_name: task.project_name,
                subproject_name: task.subproject_name,
                product_name: task.product_name,
                activity_plan_id: task.activity_plan_id,
                name: task.name,
                description: task.description,
                employers_id: task.employers_id,
                priority_id: task.priority_id,
                start_date: task.start_date,
                end_date: task.end_date,
                active: task.selector,
                activity_status_id: task.activity_status_id,
                weight: task.weight,
                tasks: vm.tasks
            };
            if (task.project_name) {
                vm.getActivitiesByProject();
            } else if (task.subproject_name) {
                vm.getActivitiesBySubProject();
            } else {
                vm.getActivitiesByProduct();
            }
        },
        deleteTask(taskId) {
            const vm = this;
            vm.tasks = vm.tasks.filter(task => task.id != taskId);
            vm.record.tasks = vm.tasks;
        },
        addTask() {
            const vm = this;
            if (!vm.validateForm()) {
                return;
            }
            vm.tasks.push({
                id: vm.tasks_added,
                selector: vm.record.active,
                project_name: vm.record.project_name,
                subproject_name: vm.record.subproject_name,
                product_name: vm.record.product_name,
                activity_plan_id: vm.record.activity_plan_id,
                name: vm.record.name,
                description: vm.record.description,
                employers_id: vm.record.employers_id,
                employer_name: vm.getEntityName(vm.record.employers_id, vm.activity_employers_list),
                priority_id: vm.record.priority_id,
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                activity_status_id: vm.record.activity_status_id,
                weight: vm.record.weight,
            });
            vm.tasks_added++;
            vm.reset();
            vm.record.tasks = vm.tasks;
        },
        getTaskNumber(param) {
            const vm = this
            let index = vm.tasks.findIndex(task => task.id == param);
            return index + 1
        },
        reset() {
            const vm = this;
            vm.record = {
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
                active: '',
                weight: '',
                activity_status_id: '',
            };
            vm.isEditMode = false;
            vm.errors = [];
            vm.activity_plans_list = [];
            vm.activity_employers_list = [];
            vm.records = [];
        },
        resetForm() {
            const vm = this;
            vm.record = {
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
                active: '',
                weight: '',
                activity_status_id: '',
            };
            vm.isEditMode = false;
            vm.errors = [];
            vm.activity_plans_list = [];
            vm.activity_employers_list = [];
            vm.records = [];
        },
        getActivitiesByProject() {
            const vm = this;
            if (!vm.record.project_name) {
                return;
            }
            vm.activity_plans_list = [];
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
            if (!vm.record.subproject_name) {
                return;
            }
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
            if (!vm.record.product_name) {
                return;
            }
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
            vm.isUpdateMode = true;
            vm.record.id = id;
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
        vm.table_options.headings = {
            'number': 'N°',
            'name': 'Nombre de la Tarea',
            'associate_to': 'Asociada a',
            'employer_name': 'Responsable de la Tarea',
            'end_date': 'Fecha de Entrega',
            'activity_status': 'Estatus',
            'priority': 'Prioridad',
            'weight': 'Peso',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['number', 'name', 'associate_to', 'employers_name', 'end_date', 'activity_status', 'priority', 'weight'];
        vm.table_options.filterable = ['number', 'name', 'associate_to', 'employers_name', 'end_date', 'activity_status', 'priority', 'weight'];
        vm.table_options.columnsClasses = {
            'number': 'text-center',
            'name': 'text-center',
            'associate_to': 'text-center',
            'employers_name': 'text-center',
            'end_date': 'text-center',
            'activity_status': 'text-center',
            'priority': 'text-center',
            'weight': 'text-center',
            'id': 'text-center'
        };
    },
    mounted() {
        const vm = this;
        if (!vm.isUpdateMode) {
            vm.reset();
        }
    }
};
</script>