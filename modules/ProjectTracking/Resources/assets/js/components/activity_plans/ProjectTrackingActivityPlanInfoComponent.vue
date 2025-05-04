<template>
    <div id="ProjectTrackingActivityPlanInfo" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="ProjectTrackingActivityPlanInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                         Información Detallada del plan de actividades
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Código:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span>
                                                {{ record.code }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span>
                                                {{ record.institution_id ? record.institution.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombre:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.project_name && record.project">
                                                {{ record.project ? record.project.name : '' }}
                                            </span>
                                            <span v-else-if="record.product_name && record.product">
                                                {{ record.product ? record.product.name : '' }}
                                            </span>
                                            <span v-else>
                                                {{ record.sub_project && record.sub_project ? record.sub_project.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Responsable:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.project_name && record.project">
                                                {{ record.project.responsable.first_name ? record.project.responsable.first_name : record.project.responsable.name }}
                                                {{ record.project.responsable.last_name  }}
                                            </span>
                                            <span v-else-if="record.product_name && record.product">
                                                {{ record.product.responsable.first_name ? record.product.responsable.first_name : record.product.responsable.name }}
                                                {{ record.product.responsable.last_name  }}
                                            </span>
                                            <span v-else>
                                                {{ record.sub_project && record.sub_project.responsable ? record.sub_project.responsable.first_name : record.sub_project && record.sub_project.responsable ? record.sub_project.responsable.name : '' }}
                                                {{ record.sub_project ? record.sub_project.responsable.last_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Dependencia:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.project_name && record.project">
                                                {{ record.project.dependency_id ? record.project.dependency.name : '' }}
                                            </span>
                                            <span v-else-if="record.product_name && record.product">
                                                {{ record.product.dependency_id ? record.product.dependency.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de ejecución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span>
                                                {{ record.execution_year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de Inicio:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.project_name && record.project">
                                                {{ record.project ? record.project.start_date : '' }}
                                            </span>
                                            <span v-else-if="record.product_name && record.product">
                                                {{ record.product ? record.product.start_date : '' }}
                                            </span>
                                            <span v-else>
                                                {{ record.sub_project ? record.sub_project.start_date : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha Fin:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.project_name && record.project">
                                                {{ record.project ? record.project.end_date : '' }}
                                            </span>
                                            <span v-else-if="record.product_name && record.product">
                                                {{ record.product ? record.product.end_date : '' }}
                                            </span>
                                            <span v-else>
                                                {{ record.sub_project ? record.sub_project.end_date : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <h6 class="col-md-12" align="center"><br>Lista de trabajadores</h6>
                                <div class="col-md-12" v-if="record.teams && record.teams.length > 0">
                                    <v-client-table :columns="columns" :data="record.teams" :options="table_options">
                                        <div slot="employers" slot-scope="props" class="text-center">
                                            {{
                                                props.row.project_tracking_personal_register.first_name ? props.row.project_tracking_personal_register.first_name : props.row.project_tracking_personal_register.name
                                            }}
                                            {{ props.row.project_tracking_personal_register.last_name }}
                                        </div>
                                        <div slot="staff_classifications" slot-scope="props" class="text-center">
                                            {{
                                                props.row.project_tracking_staff_classification.name
                                            }}
                                        </div>
                                    </v-client-table>
                                </div>
                                <h6 class="col-md-12" align="center"><br>Lista de actividades macro</h6>
                                <div class="col-md-12" v-if="record.activities && record.activities.length > 0">
                                    <v-client-table :columns="columns2" :data="record.activities" :options="table_options">
                                        <div slot="activity" slot-scope="props" class="text-center">
                                            {{
                                                props.row.project_tracking_activities.name_activity
                                            }}
                                        </div>
                                        <div slot="responsable_activity" slot-scope="props" class="text-center">
                                            {{
                                                props.row.project_tracking_team_member.project_tracking_personal_register.first_name ? props.row.project_tracking_team_member.project_tracking_personal_register.first_name : props.row.project_tracking_team_member.project_tracking_personal_register.name
                                            }}
                                            {{ props.row.project_tracking_team_member.project_tracking_personal_register.last_name }}
                                        </div>
                                    </v-client-table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    project_name: '',
                    subproject_name: '',
                    product_name: '',
                    institution_id:'',
                    responsable: '',
                    dependency: '',
                    execution_year: '',
                    start_date: '',
                    end_date: '',
                    active:'',
                    name: '',
                    employers: '',
                    staff_classifications: '',
                    activity: '',
                    responsable_activity: '',
                    start_date_activity: '',
                    end_date_activity: '',
                },
                teams: [],
                columns: ['employers', 'staff_classifications'],
                activities: [],
                columns2: ['activity', 'responsable_activity', 'percentage'],
            }
        },
        created() {
            this.table_options.headings = {
                employers: 'Trabajador',
                staff_classifications: 'Rol',
                activity: 'Actividad',
                responsable_activity: 'Responsable de la Actividad',
                percentage: '%',
            };
            this.table_options.sortable = ['employers', 'staff_classifications', 'activity', 'responsable_activity', 'percentage'];
            this.table_options.columnsClasses = {
                employers: 'col-md-4',
                staff_classifications: 'col-md-4',
                activity: 'col-md-4',
                responsable_activity: 'col-md-4',
                percentage: 'col-md-2 text-center',
            };
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
            },
        },
    }
</script>