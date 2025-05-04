<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="generation_date" slot-scope="props" class="text-center">
                <span>
                    {{ format_date(props.row.created_at, 'DD/MM/YYYY') }}
                </span>
            </div>
            <div slot="increase_of_date" slot-scope="props" class="text-center">
                <span>
                    {{ format_date(props.row.increase_of_date, 'DD/MM/YYYY') }}
                </span>
            </div>
            <div slot="increase_of_type" slot-scope="props" class="text-center">
                <span v-for="type in increase_of_types">
                    {{ type.id == props.row.increase_of_type ? type.text : '' }}
                </span>
            </div>
            <div slot="value" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.value ? props.row.value : 'N/A' }}
                </span>
            </div>
            <div slot="salary_tabulator" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.payroll_salary_tabulator_id ? props.row.payroll_salary_tabulator.name : '' }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                        title="Modificar registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, '')"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro" data-toggle="tooltip" data-placement="bottom"
                        type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-client-table>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['generation_date', 'increase_of_date', 'increase_of_type', 'value', 'salary_tabulator', 'id'],
                increase_of_types:         [
                    { id: '',               text: 'Seleccione...'},
                    { id: 'percentage',     text: 'Porcentual'},
                    { id: 'absolute_value', text: 'Valor absoluto'},
                    { id: 'different',      text: 'Diferente'}
                ],
            }
        },

        created() {
            this.table_options.headings = {
                'generation_date': 'Fecha de generación',
                'increase_of_date': 'Fecha del aumento',
                'increase_of_type': 'Tipo de aumento',
                'value': 'Valor',
                'salary_tabulator': 'Tabulador salarial',
                'id': 'Acción'
            };
            this.table_options.sortable = ['generation_date', 'increase_of_date', 'increase_of_type', 'value', 'salary_tabulator'];
            this.table_options.filterable = ['generation_date', 'increase_of_date', 'increase_of_type', 'value', 'salary_tabulator'];
        },

        mounted() {
            this.initRecords(this.route_list, '');
        },

        methods: {
            /**
             * Método que borra todos los datos del formulario
             * 
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
            },
        }
    };
</script>
