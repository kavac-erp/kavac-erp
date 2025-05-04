<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="name" slot-scope="props" class="text-center">
                <div v-if="props.row.project_name && props.row.project">
                    {{ props.row.project ? props.row.project.name : '' }}
                </div>
                <div v-else-if="props.row.product_name && props.row.product">
                    {{ props.row.product ? props.row.product.name : '' }}
                </div>
                <div v-else>
                    {{ props.row.sub_project ? props.row.sub_project.name : '' }}
                </div>
            </div>
            <div slot="responsable_name" slot-scope="props" class="text-center">
                <div v-if="props.row.project_name && props.row.project">
                    {{ props.row.project.responsable.first_name ? props.row.project.responsable.first_name : props.row.project.responsable.name }}
                    {{ props.row.project.responsable.last_name  }}
                </div>
                <div v-else-if="props.row.product_name && props.row.product">
                    {{ props.row.product.responsable.first_name ? props.row.product.responsable.first_name : props.row.product.responsable.name }}
                    {{ props.row.product.responsable.last_name  }}
                </div>
                <div v-else>
                    {{ props.row.sub_project.responsable.first_name ? props.row.sub_project.responsable.first_name : props.row.sub_project.responsable.name }}
                    {{ props.row.sub_project.responsable.last_name  }}
                </div>
            </div>
            <div slot="end_date" slot-scope="props" class="text-center">
                <div v-if="props.row.project_name && props.row.project">
                    {{ props.row.project ? props.row.project.end_date : '' }}
                </div>
                <div v-else-if="props.row.product_name && props.row.product">
                    {{ props.row.product ? props.row.product.end_date : '' }}
                </div>
                <div v-else>
                    {{ props.row.sub_project ? props.row.sub_project.end_date : '' }}
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('ActivityPlanInfo', props.row.id, 'ProjectTrackingActivityPlanInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                        title="Modificar registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, props.row.index)"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro" data-toggle="tooltip" data-placement="bottom"
                        type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-client-table>
        <project-tracking-activity-plan-info
            ref="ActivityPlanInfo">
        </project-tracking-activity-plan-info>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['code', 'name', 'responsable_name','execution_year','end_date', 'id'],
            }
        },

        created() {
            this.table_options.headings = {
                'code': 'Código',
                'name': 'Nombre',
                'responsable_name': 'Responsable',
                'execution_year': 'Año de ejecución',
                'end_date': 'Fecha de entrega',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name', 'responsable_name', 'execution_year', 'end_date'];
            this.table_options.filterable = ['code', 'project.name', 'sub_project.name', 'product.name', 'project.responsable.name',
            'sub_project.responsable.name', 'product.responsable.name','execution_year','project.end_date','sub_project.end_date','product.end_date'];
            this.table_options.columnsClasses = {
                'code': 'col-md-1 text-center',
                'name': 'col-md-3 text-center',
                'responsable_name': 'col-md-3 text-center',
                'execution_year': 'col-md-1 text-center',
                'end_date': 'col-md-1 text-center',
                'id':'col-md-1 text-center'
            };
        },

        mounted() {
            this.initRecords(this.route_list, '');
        },
        methods: {
            reset() {
            },

            /**
             * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
             *
             * @method    setDetails
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param     string   ref       Identificador del componente
             * @param     integer  id        Identificador del registro seleccionado
             * @param     object  var_list  Objeto con las variables y valores a asignar en las variables del componente
             */
            setDetails(ref, id, modal ,var_list = null) {
                const vm = this;
                if (var_list) {
                    for(var i in var_list){
                        vm.$refs[ref][i] = var_list[i];
                    }
                }else{
                    vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$refs[ref].id = id;

                let activity_number = vm.$refs[ref].record.activities.length;

                vm.$refs[ref].record.activities.forEach(macro=>{
                    if (activity_number > 0) {
                        macro.percentage = 100 / activity_number + '%';
                    }
                });

                $(`#${modal}`).modal('show');
            },

            /**
             * Método que borra un registro de la tabla
             *
             * @author  Pedro Contreras <pdrocont@gmail.com>
             */
            deleteRecord(id, index) {
                const vm = this;

                bootbox.confirm({
                    title: "¿Eliminar registro?",
                    message: "¿Está seguro de eliminar este registro?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: async function (result) {
                        if (result) {
                            vm.loading = true;

                            await axios.delete(`${window.app_url}/projecttracking/activity_plans/delete/${id}`).then(response => {
                                vm.records.splice(index, 1);
                                vm.showMessage('destroy');
                                vm.loading = false;
                            });
                        }
                    }
                });

            }
        }
    };
</script>