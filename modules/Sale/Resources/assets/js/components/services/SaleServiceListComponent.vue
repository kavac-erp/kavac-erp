<template>
    <section>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="code" slot-scope="props">
                <span>
                    {{ (props.row.code) ? props.row.code : '' }}
                </span>
            </div>
            <div slot="application_date" slot-scope="props">
                <span>
                    {{ (props.row.created_at) ? format_date(props.row.created_at) : '' }}
                </span>
            </div>
            <div slot="sale_client" slot-scope="props" v-if="props.row.sale_client">
                <span>
                    {{ (props.row.sale_client.name) ? props.row.sale_client.name : '' }}
                </span>
                <span>
                    {{ (props.row.sale_client.business_name) ? props.row.sale_client.business_name : '' }}
                </span>
            </div>
            <div slot="department" slot-scope="props">
                <span v-for="(sale_good, index) in props.row.sale_goods" :key="index">
                    <span>
                        {{ (sale_good) ? sale_good.department.name : '' }}
                    </span>
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button @click.prevent="setDetails('ServiceInfo', props.row.id, 'SaleServiceInfo')"
                            class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                            title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip
                            :disabled="props.row.status != 'Pendiente'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, 'sale/services/delete')"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro" data-toggle="tooltip" type="button" v-has-tooltip
                            :disabled="props.row.status != 'Pendiente'">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['code', 'application_date', 'sale_client', 'department', 'status', 'id']
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'application_date': 'Fecha de solicitud',
                'sale_client': 'Nombre del cliente',
                'department': 'Unidad o departamento responsable',
                'status': 'Estado de la solicitud',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'application_date', 'sale_client', 'department', 'status'];
            this.table_options.filterable = ['code', 'application_date', 'sale_client', 'department', 'status'];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'application_date': 'col-md-2',
                'sale_client': 'col-md-2',
                'department': 'col-md-2',
                'status': 'col-md-2',
                'id': 'col-md-2'
            };
        },
        mounted () {
            this.initRecords(this.route_list, '');
        },
        methods: {
            reset() {

            },

            setDetails(ref, id, modal ,var_list = null) {
                const vm = this;
                if (var_list) {
                    for(var i in var_list){
                        vm.$parent.$refs[ref][i] = var_list[i];
                    }
                }else{
                    vm.$parent.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$parent.$refs[ref].id = id;

                $(`#${modal}`).modal('show');
                document.getElementById("info_general").click();
            },
        }
    };
</script>
