<template>
    <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
        <div slot="product_name" slot-scope="props">
            <p v-for="(product, index) in props.row.sale_bill_inventory_product" :key="index">
                {{ (product.product_type == 'Servicio') ? product.sale_goods_to_be_traded.name : product.sale_warehouse_inventory_product.sale_setting_product.name }}
            </p>
        </div>
        <div slot="price" slot-scope="props">
            <p v-for="(product, index) in props.row.sale_bill_inventory_product" :key="index">
                {{ product.value }}
            </p>
        </div>
        <div slot="currency" slot-scope="props">
            <p v-for="(product, index) in props.row.sale_bill_inventory_product" :key="index">
                {{ product.currency.symbol + ' - ' + product.currency.name }}
            </p>
        </div>
        <div slot="state" slot-scope="props">
            <span>
                {{ (props.row.state)?props.row.state:'N/A' }}
            </span>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <button @click.prevent="setDetails('BillInfo', props.row.id, 'SaleBillInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
        </div>
    </v-client-table>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['code', 'created_at', 'name', 'product_name', 'price', 'currency', 'state', 'id']
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'created_at': 'Fecha de emisión',
                'name': 'Nombre del cliente',
                'product_name': 'Nombre del producto',
                'price': 'Monto',
                'currency': 'Moneda',
                'state': 'Estado de la factura',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'created_at', 'name', 'product_name', 'price', 'currency', 'state'];
            this.table_options.filterable = ['code', 'created_at', 'name', 'product_name', 'price', 'currency', 'state'];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'created_at': 'col-md-2',
                'name': 'col-md-2',
                'product_name': 'col-md-2',
                'price': 'col-md-1',
                'currency': 'col-md-2',
                'state': 'col-md-2',
                'id': 'col-md-1'
            };
        },
        mounted () {
            this.initRecords(this.route_list, '');
        },
        methods: {
            /**
             * Inicializa los datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
             */
            reset() {

            },
            /**
             * Método reemplaza el metodo setDetails para usar la referencia del parent por defecto
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
        },
        mounted() {
            this.initRecords(this.route_list, '');
        }
    };
</script>
