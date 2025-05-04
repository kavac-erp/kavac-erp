<template>
    <section>
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="id" slot-scope="props" class="text-center">
                <button
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    type="button"
                    data-placement="bottom"
                    data-toggle="tooltip"
                    title="Ver registro"
                    @click.prevent="setDetails('SpecificActionInfo', props.row.id, 'BudgetSpecificActionsInfo')"
                >
                    <i class="fa fa-eye"></i>
                </button>
                <template v-if="(lastYear && format_date(props.row.from_date, 'YYYY') <= lastYear)">
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        v-if="!props.row.disabled"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        @click="deleteRecord(props.index, '')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>
            <div slot="specificable_type" slot-scope="props">
                <span v-if="props.row.specificable_type=='Modules\\Budget\\Models\\BudgetProject'">
                    Proyecto
                </span>
                <span v-else>Acción Centralizada</span>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span v-if="props.row.active" class="text-success font-weight-bold">SI</span>
                <span v-else class="text-danger font-weight-bold">NO</span>
            </div>
        </v-client-table>
        <budget-info-specific-actions ref="SpecificActionInfo"></budget-info-specific-actions>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                columns: [
                    'code',
                    'name',
                    'specificable_type',
                    'active',
                    'id'
                ]
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'name': 'Acción Específica',
                'specificable_type': 'Proyecto / Acc. Centralizada',
                'active': 'Activa',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'name', 'specificable_type'];
            this.table_options.filterable = ['code', 'name', 'specificable_type'];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'name': 'col-md-4',
                'specificable_type': 'col-md-3',
                'active': 'col-md-1',
                'id': 'col-md-2'
            };
        },
        async mounted() {
            const vm = this;
            vm.initRecords(vm.route_list, '');
            await vm.queryLastFiscalYear();
        },
        methods: {
            /**
             * Inicializa los datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {},

            /**
             * Método que establece los datos del registro seleccionado para el
             * cual se desea mostrar detalles.
             *
             * @method    setDetails
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param     string   ref       Identificador del componente
             * @param     integer  id        Identificador del registro seleccionado
             * @param     object  var_list  Objeto con las variables y valores
             * a asignar en las variables del componente.
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
                $(`#${modal}`).modal('show');
            },
        }
    };
</script>
