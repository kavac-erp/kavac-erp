<template>
    <section id="ProjectTrackingActivityPlanForm">
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
                <div class="col-md-1" id="project">
                    <div class="form-group">
                        <label>Proyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un proyecto">
                                <input type="radio" class="custom-control-input" @click="resetInfo()"
                                    id="active_project" name="active" v-model="record.active" value="project">
                                <label class="custom-control-label" for="active_project"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1" id="subproject">
                    <div class="form-group">
                        <label>Subproyecto:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                title="Indique si es un subproyecto">
                                <input type="radio" class="custom-control-input" @click="resetInfo()"
                                    id="active_subproject" name="active" v-model="record.active" value="subproject">
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
                                <input type="radio" class="custom-control-input" @click="resetInfo()"
                                    id="active_product" name="active" v-model="record.active" value="product">
                                <label class="custom-control-label" for="active_product"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="record.active == 'project'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Proyecto:</label>
                        <select2 :options="projects_list" id="project_name" data-toggle="tooltip"
                            @input="getProjectName()" v-model="record.project_name">
                        </select2>
                    </div>
                </div>
                <div v-else-if="record.active == 'subproject'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Proyecto:</label>
                        <select2 :options="projects_list" id="project_name" data-toggle="tooltip"
                            @input="getProjectNameAndSubProjectName()" v-model="record.project_name">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'subproject'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Subproyecto:</label>
                        <select2 :options="subprojects_list" id="subproject_name" data-toggle="tooltip"
                            @input="getSubProjectName()" v-model="record.subproject_name">
                        </select2>
                    </div>
                </div>
                <div v-if="record.active == 'product'" class="col-md-4">
                    <div class="form-group is-required">
                        <label>Producto:</label>
                        <select2 :options="products_list" id="product_name" data-toggle="tooltip"
                            @input="getProductName()" v-model="record.product_name">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="institution_id">
                    <div class="form-group is-required">
                        <label>Institución:</label>
                        <select2 :options="institutions" data-toggle="tooltip" v-model="record.institution_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Nombre:</label>
                        <input type="text" class="form-control input-sm" disabled v-model="record.name"
                            data-toggle="tooltip" title="Nombre">
                    </div>
                </div>
                <div class="col-md-4" id="responsable">
                    <div class="form-group is-required">
                        <label>Responsable:</label>
                        <input type="text" class="form-control input-sm" disabled data-toggle="tooltip"
                            v-model="record.responsable" title="Responsable" />
                    </div>
                </div>
                <div class="col-md-4" id="dependency">
                    <div class="form-group is-required">
                        <label>Dependencia:</label>
                        <input type="text" class="form-control input-sm" disabled v-model="record.dependency"
                            data-toggle="tooltip" title="Dependencia">
                    </div>
                </div>
                <div class="col-md-4" id="start_date">
                    <div class="form-group is-required">
                        <label>Fecha de inicio:</label>
                        <input type="date" class="form-control input-sm" disabled v-model="record.start_date"
                            data-toggle="tooltip" title="Fecha de inicio">
                    </div>
                </div>
                <div class="col-md-4" id="end_date">
                    <div class="form-group is-required">
                        <label>Fecha fin:</label>
                        <input type="date" class="form-control input-sm" disabled v-model="record.end_date"
                            data-toggle="tooltip" title="Fecha fin">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="control-label">Año de ejecución:</label><br>
                        <label class="control-label">
                            <h2>{{ record.execution_year }}</h2>
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <br>
                </div>
                <div class="col-12">
                    <h6 class="card-title">Asignar Equipo de Trabajo</h6>
                </div>
                <div class="row col-12">
                    <div class="col-md-2"></div>
                    <div class="col-md-4" id="employers">
                        <div class="form-group is-required">
                            <label>Trabajador:</label>
                            <select2 :options="payroll_staffs" data-toggle="tooltip" v-model="record.employers">
                            </select2>
                        </div>
                    </div>
                    <div class="col-md-4" id="staff_classifications">
                        <div class="form-group is-required">
                            <label>Rol:</label>
                            <select2 :options="staff_classifications_list" data-toggle="tooltip"
                                v-model="record.staff_classifications"></select2>
                        </div>
                    </div>
                    <div style="margin-top: 0.2em">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new mt-4"
                            @click="addTeamMember()">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body modal-table text-center">
                    <v-client-table :columns="columns" :data="records" :options="table_options">
                        <div slot="id" slot-scope="props" class="text-center">
                            <div class="d-inline-flex">
                                <project-tracking-activity-plan-team-info :modal_id="props.row.employers_id"
                                    :url="'projecttracking/get-activity-plans-team-info/' + props.row.employers_id + '/' + props.row.staff_classifications_id">
                                </project-tracking-activity-plan-team-info>
                                <button @click="editTeamMember(props.index, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                                    aria-label="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteTeamMember(props.index, $event)"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
                                    aria-label="Eliminar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </div>
                    </v-client-table>
                </div>
                <div class="col-12">
                    <hr>
                    <br>
                </div>
                <div class="col-12">
                    <h6 class="card-title">Asignar Actividad Macro</h6>
                </div>
                <div class="row col-12">
                    <div class="col-md-2">
                        <label>Porcentaje manual:</label>
                        <div class="custom-control custom-switch" data-toggle="tooltip"
                            title="Indique si agregara el porcentaje de la actividad manualmente">
                            <input type="checkbox" class="custom-control-input" @click="activePercentage()"
                                id="sel_active_percentage" name="active_percentage" value="percentage"
                                v-model="record.percentage">
                            <label class="custom-control-label" for="sel_active_percentage"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="activity">
                    <div class="form-group is-required">
                        <label>Actividad:</label>
                        <select2 :options="activities_list" data-toggle="tooltip" v-model="record.activity"></select2>
                    </div>
                </div>
                <div class="col-md-4" id="responsable_activity">
                    <div class="form-group is-required">
                        <label>Responsable de la actividad:</label>
                        <select2 :options="team_members" data-toggle="tooltip" v-model="record.responsable_activity">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="start_date_activity">
                    <div class="form-group is-required">
                        <label for="start_date_activity">Fecha de inicio:</label>
                        <input type="date" class="form-control input-sm no-restrict"
                            v-model="record.start_date_activity" data-toggle="tooltip" title="Fecha de inicio"
                            :min="record.start_date" :max="record.end_date">
                    </div>
                </div>
                <div class="row col-12">
                    <div class="col-md-4" id="end_date_activity">
                        <div class="form-group is-required">
                            <label for="end_date_activity">Fecha fin:</label>
                            <input type="date" class="form-control input-sm no-restrict"
                                v-model="record.end_date_activity" data-toggle="tooltip" title="Fecha fin"
                                :min="record.start_date" :max="record.end_date">
                        </div>
                    </div>
                    <div class="col-md-4" v-if="active_percentage == true">
                        <label class="control-label">Porcentaje</label>
                        <input type="text" class="form-control input-sm" id="porcentaje" name="porcentaje"
                            data-toggle="tooltip" v-has-tooltip placeholder="Porcentaje de la actividad" v-input-mask
                            data-inputmask="'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                            title="Porcentaje de la actividad" v-model="percentage">
                    </div>
                    <div style="margin-top: 0.2em">
                        <button class="btn btn-xs btn-icon btn-primary btn-custom btn-new mt-4" @click="addActivity()">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-12"></div>
                <div class="modal-body modal-table text-center">
                    <v-client-table :columns="columns2" :data="records2" :options="table_options">
                        <div slot="id" slot-scope="props" class="text-center">
                            <div class="d-inline-flex">
                                <project-tracking-activity-plan-activity-info :modal_id="props.row.activity_id"
                                    :url="'projecttracking/get-activity-plans-activity-info/' + props.row.activity_id"
                                    :activity_info='record.activity_plans[props.index - 1]'>
                                </project-tracking-activity-plan-activity-info>
                                <button @click="editActivity(props.index, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                                    aria-label="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteActivity(props.index, $event)"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
                                    aria-label="Eliminar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </div>
                    </v-client-table>
                </div>
            </div>
        </div>

        <div class="card-footer pull-right" id="helpParamButtons">
            <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                title="Borrar datos del formulario" aria-label="Borrar datos del formulario" @click="reset"><i
                    class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" aria-label="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="generateRecord()" data-toggle="tooltip" title="Guardar registro"
                aria-label="Guardar registro" class="btn btn-success btn-icon btn-round">
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
import axios from 'axios';

export default {
    data() {
        return {
            record: {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                institution_id: '',
                responsable: '',
                dependency: '',
                execution_year: '',
                start_date: '',
                end_date: '',
                active: '',
                name: '',
                employers: '',
                staff_classifications: '',
                activity: '',
                responsable_activity: '',
                responsable_activity_id: '',
                start_date_activity: '',
                end_date_activity: '',
                activity_plans: [],
                percentage: '',
            },
            errors: [],
            projects_list: [],
            subprojects_list: [],
            products_list: [],
            payroll_staffs: [],
            dependencies_list: [],
            personal_list: [],
            staff_classifications_list: [],
            institutions_list: [],
            activities_list: [],
            records: [],
            columns: ['employers', 'staff_classifications', 'id'],
            records2: [],
            columns2: ['activity', 'responsable_activity', 'start_date_activity', 'end_date_activity', 'percentage', 'id'],
            responsable_list: [],
            institutions: [],
            team_members: [{ 'id': '', 'text': 'Seleccione...' }],
            editIndex: null,
            editIndex2: null,
            active_percentage: false,
            percentage: '',
            activities_percentage: []
        }
    },
    props: {
        activity_planid: Number,
        payroll_employer_id: Number,
    },
    watch: {
        'record.project_name'(project_id) {
            const vm = this;
            if (project_id) {
                const project = vm.projects_list.find(project => project.id == project_id);
                const productTypeIds = project.product_type_ids.join(',');
                const url = vm.setUrl('projecttracking/get-activities-by-product-types');
                axios.get(
                    `${url}/${productTypeIds}`
                ).then(response => {
                    vm.activities_list = response.data;
                }).catch(error => {
                    console.error(error);
                });
            }
        },
        'record.subproject_name'(subproject_id) {
            const vm = this;
            if (subproject_id) {
                const subprojec = vm.subprojects_list.find(subproject => subproject.id == subproject_id);
                const productTypeIds = subprojec.product_types_ids.join(',');
                const url = vm.setUrl('projecttracking/get-activities-by-product-types');
                axios.get(
                    `${url}/${productTypeIds}`,
                ).then(response => {
                    vm.activities_list = response.data;
                }).catch(error => {
                    console.error(error);
                });
            }
        },
        'record.product_name'(product_id) {
            const vm = this;
            if (product_id) {
                const product = vm.products_list.find(product => product.id == product_id);
                const productTypeIds = product.product_type_ids.join(',');
                const url = vm.setUrl('projecttracking/get-activities-by-product-types');
                axios.get(
                    `${url}/${productTypeIds}`
                ).then(response => {
                    vm.activities_list = response.data;
                }).catch(error => {
                    console.error(error);
                });
            }
        },
    },
    methods: {
        reset() {
            this.record = {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                institution_id: '',
                responsable: '',
                dependency: '',
                execution_year: window.execution_year,
                start_date: '',
                end_date: '',
                active: '',
                name: '',
                employers: '',
                staff_classifications: '',
                activity: '',
                responsable_activity: '',
                start_date_activity: '',
                end_date_activity: '',
                activity_plans: [],
            };
            this.records = [];
            this.records2 = [];
            this.team_members = [{ 'id': '', 'text': 'Seleccione...' }];
            vm.editIndex = null;
            vm.editIndex2 = null;
        },

        getPersonal() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-personal`).then(response => {
                vm.payroll_staffs = response.data;
            });
        },
        getProjects() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-projects`).then(response => {
                vm.projects_list = response.data;
            });
        },
        getProducts() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-products`).then(response => {
                vm.products_list = response.data;
            });
        },
        getDependencies() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-dependencies`).then(response => {
                vm.dependencies_list = response.data;
            });
        },
        getSubProjectsByProject() {
            const vm = this;
            vm.subprojects_list = [],
                axios.get(`${window.app_url}/projecttracking/get-subprojects-by-project/${vm.record.project_name}`).then(response => {
                    vm.subprojects_list = response.data;
                });
        },
        getSubprojects() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-subprojects`).then(response => {
                vm.subprojects_list = response.data;
            });
        },
        getStaff_Classifications() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-staff_classifications`).then(response => {
                vm.staff_classifications_list = response.data;
            });
        },
        getActivities() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-activities`).then(response => {
                vm.activities_list = response.data;
            });
        },
        getProjectName() {
            const vm = this;
            for (let project of vm.projects_list) {
                if (vm.record.project_name == project.id && project.id) {
                    let name = project.responsable_id.first_name ? project.responsable_id.first_name : project.responsable_id.name;
                    vm.record.name = project.name;
                    vm.record.responsable = name + ' ' + project.responsable_id.last_name;
                    vm.record.dependency = project.dependency_id.name;
                    vm.record.start_date = project.start_date;
                    vm.record.end_date = project.end_date;
                }
            }
        },
        getProjectNameAndSubProjectName() {
            const vm = this;
            for (let project of vm.projects_list) {
                if (vm.record.project_name == project.id && project.id) {
                    let name = project.responsable_id.first_name ? project.responsable_id.first_name : project.responsable_id.name;
                    vm.record.name = project.name;
                    vm.record.responsable = name + ' ' + project.responsable_id.last_name;
                    vm.record.dependency = project.dependency_id.name;
                    vm.record.start_date = project.start_date;
                    vm.record.end_date = project.end_date;
                }
            }
            vm.getSubProjectsByProject();
        },
        getSubProjectName() {
            const vm = this;
            for (let sub_project of vm.subprojects_list) {
                if (vm.record.subproject_name == sub_project.id && sub_project.id) {
                    let name = sub_project.responsable_id.first_name ? sub_project.responsable_id.first_name : '';
                    vm.record.name = sub_project.name;
                    vm.record.responsable = name + ' ' + sub_project.responsable_id.last_name;
                    vm.record.start_date = sub_project.start_date ? sub_project.start_date : '';
                    vm.record.end_date = sub_project.end_date ? sub_project.end_date : '';
                }
            }
        },
        getProductName() {
            const vm = this;
            for (let product of vm.products_list) {
                if (vm.record.product_name == product.id && product.id) {
                    let name = product.responsable_id.first_name ? product.responsable_id.first_name : product.responsable_id.name;
                    vm.record.name = product.name;
                    vm.record.responsable = name + ' ' + product.responsable_id.last_name;
                    vm.record.dependency = product.dependency_id.name;
                    vm.record.start_date = product.start_date;
                    vm.record.end_date = product.end_date;
                }
            }
        },
        resetInfo() {
            const vm = this;
            vm.record.name = '';
            vm.record.responsable = '';
            vm.record.dependency = '';
            vm.record.start_date = '';
            vm.record.end_date = '';
            vm.record.project_name = '';
            vm.record.subproject_name = '';
            vm.record.product_name = '';
            this.records = [];
            this.records2 = [];
        },

        addTeamMember() {
            const vm = this;
            vm.errors = [];
            if (vm.editIndex === null) {
                if (vm.record.employers == null || vm.record.employers == '') {
                    vm.errors.push('El campo trabajador es obligatorio.');
                }
                if (vm.record.staff_classifications == null || vm.record.staff_classifications == '') {
                    vm.errors.push('El campo rol es obligatorio.');
                }
                for (let record of vm.team_members) {
                    if (vm.record.employers == record.id && record.id != '') {
                        vm.errors.push('Este trabajador ya está registrado.');
                    }
                }
                if (vm.errors.length > 0) {
                    $('html,body').animate({
                        scrollTop: $("#ProjectTrackingActivityPlanForm").offset()
                    }, 1000);

                    return;
                } else {
                    let employer_name;
                    let employer_id;

                    for (let personal of vm.payroll_staffs) {
                        ;
                        if (vm.record.employers == personal.id) {
                            employer_name = personal.text;
                            employer_id = personal.id;
                        }
                    }

                    let staff_classification_name;
                    let staff_classification_id;

                    for (let staff_classification of vm.staff_classifications_list) {
                        if (vm.record.staff_classifications == staff_classification.id) {
                            staff_classification_name = staff_classification.text;
                            staff_classification_id = staff_classification.id;
                        }
                    }
                    vm.team_members.push({
                        id: employer_id,
                        text: employer_name,
                    });

                    vm.records.push({
                        id: '',
                        employers: employer_name,
                        employers_id: employer_id,
                        staff_classifications: staff_classification_name,
                        staff_classifications_id: staff_classification_id,
                    });

                    vm.record.team_members.push({
                        id: '',
                        employers: employer_name,
                        employers_id: employer_id,
                        staff_classifications: staff_classification_name,
                        staff_classifications_id: staff_classification_id,
                    });
                }
            } else if (vm.editIndex >= 0) {
                let employer_name;
                let employer_id;

                for (let personal of vm.payroll_staffs) {
                    if (vm.record.employers == personal.id) {
                        employer_name = personal.text;
                        employer_id = personal.id;
                    }
                }

                let staff_classification_name;
                let staff_classification_id;

                for (let staff_classification of vm.staff_classifications_list) {
                    if (vm.record.staff_classifications == staff_classification.id) {
                        staff_classification_name = staff_classification.text;
                        staff_classification_id = staff_classification.id;
                    }
                }
                vm.team_members.splice(vm.editIndex + 1, 1);
                vm.records.splice(vm.editIndex, 1);
                vm.record.team_members.splice(vm.editIndex, 1);

                vm.team_members.push({
                    id: employer_id,
                    text: employer_name,
                });

                vm.records.push({
                    id: '',
                    employers: employer_name,
                    employers_id: employer_id,
                    staff_classifications: staff_classification_name,
                    staff_classifications_id: staff_classification_id,
                });

                vm.record.team_members.push({
                    id: '',
                    employers: employer_name,
                    employers_id: employer_id,
                    staff_classifications: staff_classification_name,
                    staff_classifications_id: staff_classification_id,
                });
            }
            vm.resetTeam();
        },

        resetTeam() {
            const vm = this;
            vm.record.employers = '';
            vm.record.staff_classifications = '';
            vm.editIndex = null;
        },

        addActivity() {
            const vm = this;
            vm.errors = [];
            if (vm.editIndex2 === null) {
                if (vm.record.activity == null || vm.record.activity == '') {
                    vm.errors.push('El campo actividad es obligatorio.');
                }
                if (vm.record.responsable_activity == null || vm.record.responsable_activity == '') {
                    vm.errors.push('El campo responsable de la actividad es obligatorio.');
                }
                if (vm.record.start_date_activity == null || vm.record.start_date_activity == '') {
                    vm.errors.push('El campo fecha de inicio es obligatorio.');
                }
                if (vm.record.end_date_activity == null || vm.record.end_date_activity == '') {
                    vm.errors.push('El campo fecha fin es obligatorio.');
                }
                if (vm.active_percentage == true && (vm.percentage == null || vm.percentage == '')) {
                    vm.errors.push('El campo porcentaje es obligatorio.');
                }
                for (let object of vm.records2) {
                    if (vm.record.activity == object.activity_id && object.activity_id != '') {
                        vm.errors.push('Esta actividad ya está registrada.');
                    }
                }
                if (vm.errors.length > 0) {
                    $('html,body').animate({
                        scrollTop: $("#ProjectTrackingActivityPlanForm").offset()
                    }, 1000);

                    return;
                } else {
                    let activity_name;
                    let activity_id;

                    for (let activity of vm.activities_list) {
                        if (vm.record.activity == activity.id) {
                            activity_name = activity.text;
                            activity_id = activity.id;
                        }
                    }

                    let responsable_activity_name;
                    let responsable_activity_id;

                    for (let responsable_activity of vm.team_members) {
                        if (vm.record.responsable_activity == responsable_activity.id) {
                            responsable_activity_name = responsable_activity.text;
                            responsable_activity_id = responsable_activity.id;
                        }
                    }
                    if (vm.active_percentage == false) {
                        vm.records2.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: vm.record.start_date_activity,
                            end_date_activity: vm.record.end_date_activity,
                            percentage: '',
                        });

                        vm.record.activity_plans.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: vm.record.start_date_activity,
                            end_date_activity: vm.record.end_date_activity,
                            percentage: '',
                        });

                        let activity_number = vm.records2.length;

                        vm.records2.forEach(macro => {
                            if (activity_number > 0) {
                                //macro.percentage = 100 / activity_number + '%';
                                macro.percentage = vm.numberDecimal((100 / activity_number), 2)
                            }
                        });

                        vm.record.activity_plans.forEach(macro => {
                            if (activity_number > 0) {
                                //macro.percentage = 100 / activity_number + '%';
                                macro.percentage = vm.numberDecimal((100 / activity_number), 2);
                            }
                        });
                    }
                    if (vm.active_percentage == true) {
                        vm.records2.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: vm.record.start_date_activity,
                            end_date_activity: vm.record.end_date_activity,
                            percentage: vm.numberDecimal(vm.percentage, 2),
                        });

                        vm.record.activity_plans.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: vm.record.start_date_activity,
                            end_date_activity: vm.record.end_date_activity,
                            percentage: vm.numberDecimal(vm.percentage, 2),
                        });
                        vm.activities_percentage.push({
                            percentage: vm.percentage,
                        });
                    }
                }
            } else if (vm.editIndex2 >= 0) {
                let activity_name;
                let activity_id;

                for (let activity of vm.activities_list) {
                    if (vm.record.activity == activity.id) {
                        activity_name = activity.text;
                        activity_id = activity.id;
                    }
                }

                let responsable_activity_name;
                let responsable_activity_id;

                for (let responsable_activity of vm.team_members) {
                    if (vm.record.responsable_activity == responsable_activity.id) {
                        responsable_activity_name = responsable_activity.text;
                        responsable_activity_id = responsable_activity.id;
                    }
                }
                /*Elimina el registro que se edito */
                (vm.records2).splice(vm.editIndex2, 1);
                (vm.record.activity_plans).splice(vm.editIndex2, 1);

                if (vm.active_percentage == false) {

                    vm.records2.push({
                        id: '',
                        activity: activity_name,
                        activity_id: activity_id,
                        responsable_activity: responsable_activity_name,
                        responsable_activity_id: responsable_activity_id,
                        start_date_activity: vm.record.start_date_activity,
                        end_date_activity: vm.record.end_date_activity,
                        percentage: '',
                    });

                    vm.record.activity_plans.push({
                        id: '',
                        activity: activity_name,
                        activity_id: activity_id,
                        responsable_activity: responsable_activity_name,
                        responsable_activity_id: responsable_activity_id,
                        start_date_activity: vm.record.start_date_activity,
                        end_date_activity: vm.record.end_date_activity,
                        percentage: '',
                    });

                    let activity_number = vm.records2.length;

                    vm.records2.forEach(macro => {
                        if (activity_number > 0) {
                            //macro.percentage = 100 / activity_number + '%';
                            macro.percentage = vm.numberDecimal(100 / activity_number)
                        }
                    });

                    vm.record.activity_plans.forEach(macro => {
                        if (activity_number > 0) {
                            //macro.percentage = 100 / activity_number + '%';
                            macro.percentage = vm.numberDecimal(100 / activity_number);
                        }
                    });
                }
                if (vm.active_percentage == true) {
                    vm.records2.push({
                        id: '',
                        activity: activity_name,
                        activity_id: activity_id,
                        responsable_activity: responsable_activity_name,
                        responsable_activity_id: responsable_activity_id,
                        start_date_activity: vm.record.start_date_activity,
                        end_date_activity: vm.record.end_date_activity,
                        percentage: vm.percentage,
                    });

                    vm.record.activity_plans.push({
                        id: '',
                        activity: activity_name,
                        activity_id: activity_id,
                        responsable_activity: responsable_activity_name,
                        responsable_activity_id: responsable_activity_id,
                        start_date_activity: vm.record.start_date_activity,
                        end_date_activity: vm.record.end_date_activity,
                        percentage: vm.percentage,
                    });
                    vm.activities_percentage.push({
                        percentage: vm.percentage,
                    });
                }
            }
            vm.resetActivity();
        },

        resetActivity() {
            const vm = this;
            vm.record.activity = '';
            vm.record.responsable_activity = '';
            vm.record.start_date_activity = '';
            vm.record.end_date_activity = '';
            vm.editIndex2 = null;
            vm.percentage = '';
        },

        deleteTeamMember(index, event) {
            const vm = this;
            vm.team_members.splice(index, 1);
            vm.records.splice(index - 1, 1);
            vm.record.team_members.splice(index - 1, 1);
        },

        editTeamMember(index, event) {
            const vm = this;
            vm.record.employers = '';
            vm.record.staff_classifications = '';
            vm.editIndex = index - 1;
            vm.record.employers = vm.records[index - 1].employers_id;
            vm.record.staff_classifications = vm.records[index - 1].staff_classifications_id;

            event.preventDefault();
        },

        deleteActivity(index, event) {
            const vm = this;
            vm.records2.splice(index - 1, 1);
            vm.record.activity_plans.splice(index - 1, 1);

            let activity_number = vm.records2.length;

            if (vm.active_percentage == false) {
                vm.records2.forEach(macro => {
                    if (activity_number > 0) {
                        //macro.percentage = 100 / activity_number + '%';
                        macro.percentage = vm.numberDecimal(100 / activity_number);
                    }
                });

                vm.record.activity_plans.forEach(macro => {
                    if (activity_number > 0) {
                        //macro.percentage = 100 / activity_number + '%';
                        macro.percentage = vm.numberDecimal(100 / activity_number);
                    }
                });
            }
        },

        editActivity(index, event) {
            const vm = this;
            vm.record.activity = '';
            vm.record.responsable_activity = '';
            vm.record.start_date_activity = '';
            vm.record.end_date_activity = '';
            vm.editIndex2 = index - 1;
            vm.record.activity = vm.records2[index - 1].activity_id;
            vm.record.responsable_activity = vm.records2[index - 1].responsable_activity_id;
            vm.record.start_date_activity = vm.records2[index - 1].start_date_activity;
            vm.record.end_date_activity = vm.records2[index - 1].end_date_activity;
            vm.percentage = vm.records2[index - 1].percentage;

            event.preventDefault();
        },

        generateRecord() {
            const vm = this;
            vm.errors = [];
            let percentage = 0;
            if (vm.record.name == '') {
                vm.errors.push('Debes seleccionar un proyecto, un subproyecto o un producto')
            }
            if (vm.record.institution_id == '') {
                vm.errors.push('Debes seleccionar la institución')
            }
            if (vm.record.team_members.length < 1) {
                vm.errors.push('Debes asignar un equipo de trabajo')
            }
            if (vm.record.activity_plans.length < 1) {
                vm.errors.push('Debes asignar al menos una actividad macro')
            }
            if (vm.record.activity_plans.length > 0) {
                for (let act of vm.record.activity_plans) {
                    percentage = percentage + act.percentage;
                }
                percentage = Math.ceil(percentage);
                if (percentage != 100) {
                    vm.errors.push('La suma de los porcentajes de las actividades debe ser igual a 100')
                }
            }
            if (vm.errors < 1) {
                vm.createRecord('projecttracking/activity_plans');
            }
        },
        async loadForm(id) {
            const vm = this;
            vm.loading = true;
            if (vm.payroll_employer_id) {
                await vm.getPayrollStaffs();
            } else {
                await vm.getPersonal();
            }

            await axios.get(`${window.app_url}/projecttracking/activity_plans/vue-info/${id}`).then(response => {
                if (typeof (response.data.records != "undefined")) {
                    let recordEdit = response.data.records;

                    vm.record.id = recordEdit.id;

                    if (recordEdit.project_name) {
                        vm.record.active = 'project';
                        vm.record.project_name = recordEdit.project_name;
                        vm.getProjectName();
                    } else if (recordEdit.subproject_name) {
                        vm.record.active = 'subproject';
                        vm.record.subproject_name = recordEdit.subproject_name;
                    } else {
                        vm.record.active = 'product';
                        vm.record.product_name = recordEdit.product_name;
                        vm.getProductName();
                    }
                    vm.record.institution_id = recordEdit.institution_id;

                    for (let team of recordEdit.teams) {
                        let employer_name;
                        let employer_id;

                        vm.record.employers = team.employers_id;

                        for (let personal of vm.payroll_staffs) {
                            if (vm.record.employers == personal.id) {
                                employer_name = personal.text;
                                employer_id = personal.id;
                            }
                        }

                        let staff_classification_name;
                        let staff_classification_id;

                        vm.record.staff_classifications = team.staff_classification_id;

                        for (let staff_classification of vm.staff_classifications_list) {
                            if (vm.record.staff_classifications == staff_classification.id) {
                                staff_classification_name = staff_classification.text;
                                staff_classification_id = staff_classification.id;
                            }
                        }

                        vm.team_members.push({
                            id: team.employers_id,
                            text: employer_name,
                        });

                        vm.records.push({
                            id: '',
                            employers: employer_name,
                            employers_id: employer_id,
                            staff_classifications: staff_classification_name,
                            staff_classifications_id: staff_classification_id,
                        });

                        vm.record.team_members.push({
                            id: '',
                            employers: employer_name,
                            employers_id: employer_id,
                            staff_classifications: staff_classification_name,
                            staff_classifications_id: staff_classification_id,
                        });
                    }

                    vm.resetTeam();

                    /* Proceso que verifica porcentaje manual*/
                    let activities_percentage = [];
                    for (let record of recordEdit.activities) {
                        activities_percentage.push(record.percentage);
                    }
                    let manualPercentage = vm.areAllElementsEqual(activities_percentage);

                    for (let edit_activity of recordEdit.activities) {
                        let activity_name;
                        let activity_id;

                        vm.record.activity = edit_activity.activity_id;

                        for (let activity of vm.activities_list) {
                            if (vm.record.activity == activity.id) {
                                activity_name = activity.text;
                                activity_id = activity.id;
                            }
                        }

                        vm.record.responsable_activity = edit_activity.project_tracking_team_member.employers_id;

                        let responsable_activity_name;
                        let responsable_activity_id;

                        for (let team of recordEdit.teams) {
                            if (team.id == edit_activity.responsable_activity_id) {
                                for (let member of vm.team_members) {
                                    if (team.employers_id == member.id) {
                                        responsable_activity_name = member.text;
                                        responsable_activity_id = team.employers_id;
                                    }
                                }
                            }
                        }

                        vm.records2.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: edit_activity.start_date,
                            end_date_activity: edit_activity.end_date,
                            percentage: edit_activity.percentage,
                        });

                        vm.record.activity_plans.push({
                            id: '',
                            activity: activity_name,
                            activity_id: activity_id,
                            responsable_activity: responsable_activity_name,
                            responsable_activity_id: responsable_activity_id,
                            start_date_activity: edit_activity.start_date,
                            end_date_activity: edit_activity.end_date,
                            percentage: edit_activity.percentage,
                        });

                        if (manualPercentage) {
                            let activity_number = vm.records2.length;

                            vm.records2.forEach(macro => {
                                if (activity_number > 0) {
                                    //macro.percentage = 100 / activity_number + '%';
                                    macro.percentage = vm.numberDecimal(100 / activity_number);
                                }
                            });

                            vm.record.activity_plans.forEach(macro => {
                                if (activity_number > 0) {
                                    //macro.percentage = 100 / activity_number + '%';
                                    macro.percentage = vm.numberDecimal(100 / activity_number);
                                }
                            });
                        }
                        else {
                            var checkbox = document.getElementById("sel_active_percentage");
                            if (checkbox && checkbox.checked == false) {
                                checkbox.click();
                            }
                        }

                        vm.resetActivity();
                    }
                }
            });
        },
        activePercentage() {
            const vm = this;
            if (vm.active_percentage == true) {
                vm.active_percentage = false;
            } else {
                vm.active_percentage = true;
            }
        },

        /**
         * Verifica si todos los elementos del array son iguales
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        areAllElementsEqual(arr) {
            if (arr.length > 0) {
                let firstElement = arr[0];
                for (let i = 1; i < arr.length; i++) {
                    if (arr[i] !== firstElement) {
                        return false;
                    }
                }
            }
            return true;
        },
        /**
         * Método que formatea un número a una cantidad de decimales sin redondear
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        numberDecimal(num, dec = 2) {
            var exp = Math.pow(10, dec || 2);
            return parseInt(num * exp, 10) / exp;
        }
    },

    created() {
        const vm = this;
        vm.getProjects();
        vm.getProducts();
        vm.getStaff_Classifications();
        vm.getActivities();
        vm.getInstitutions();
        if (vm.payroll_employer_id) {
            vm.getPayrollStaffs();
        } else {
            vm.getPersonal();
        }

        this.table_options.headings = {
            employers: 'Trabajador',
            staff_classifications: 'Rol',
            activity: 'Actividad',
            responsable_activity: 'Responsable de la Actividad',
            start_date_activity: 'Fecha de inicio',
            end_date_activity: 'Fecha de fin',
            percentage: '%',
            id: 'Acción'
        };
        this.table_options.sortable = ['employers', 'staff_classifications', 'activity', 'responsable_activity', 'start_date_activity', 'end_date_activity', 'percentage', 'id'];
        this.table_options.filterable = ['employers', 'staff_classifications', 'activity', 'responsable_activity', 'start_date_activity', 'end_date_activity', 'percentage', 'id'];
        this.table_options.columnsClasses = {
            employers: 'col-md-4',
            staff_classifications: 'col-md-4',
            activity: 'col-md-2',
            responsable_activity: 'col-md-4',
            start_date_activity: 'col-md-2',
            end_date_activity: 'col-md-2',
            percentage: 'col-md-1',
            id: 'col-md-2 text-center'
        };
    },

    mounted() {
        const vm = this;
        if (vm.activity_planid) {
            vm.loadForm(vm.activity_planid);
        }
        /*this.initRecords(this.route_list, '');*/
        vm.record.execution_year = window.execution_year;
        vm.record.team_members = [];
        vm.record.activity_plans = [];
    },
};
</script>
