<template>
    <section id="SaleReportpaymentForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <ul>
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>
            <div class="row">
                <!-- Filtrado por fechas -->
                <div class="col-xs-6 col-sm-6 col-md-2">
                    <label class="control-label">Filtrar por</label>
                    <div class="form-check col-xs-12 col-sm-12 col-md-12">
                        <input class="form-check-input" type="radio" name="flexRadioDate" id="flexRadioDateSpecific" value="specific" v-model="record.filterDate">
                        <label class="form-check-label" for="flexRadioDateSpecific">
                            Por Período
                        </label>
                    </div>
                    <div class="form-check col-xs-12 col-sm-12 col-md-12">
                        <input class="form-check-input" type="radio" name="flexRadioDate" id="flexRadioDateGeneral" value="general" v-model="record.filterDate">
                        <label class="form-check-label" for="flexRadioDateGeneral">
                            Por Mes
                        </label>
                    </div>
                </div>
                <div class="col-md-3" v-show="record.filterDate == 'specific'">
                    <div class="form-group is-required">
                        <label class="control-label">Fecha inicial</label>
                        <input type="date" class="form-control input-sm" v-model="record.dateIni">
                    </div>
                </div>
                <div class="col-md-3" v-show="record.filterDate == 'specific'">
                    <div class="form-group is-required">
                        <label class="control-label">Fecha final</label>
                        <input type="date" class="form-control input-sm"
                            v-model="record.dateEnd" :min="record.dateIni?record.dateIni:''" :disabled="record.dateIni?false:true">
                    </div>
                </div>
                <div class="col-md-3" v-show="record.filterDate == 'general'">
                    <label class="control-label">Fecha Inicial</label>
                    <div class="is-required">
                        <label>Mes</label>
                        <select2 :options="months" v-model="record.month_init"></select2>
                    </div>
                    <div class="is-required">
                        <label>Año</label>
                        <select2 :options="years" v-model="record.year_init"></select2>
                    </div>
                </div>
                <div class="col-md-3" v-show="record.filterDate == 'general'">
                    <label class="control-label">Fecha Final</label>
                    <div class="is-required">
                        <label>Mes</label>
                        <select2 :options="months" v-model="record.month_end"></select2>
                    </div>
                    <div class="is-required">
                        <label>Año</label>
                        <select2 :options="years" v-model="record.year_end"></select2>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="control-label">Estatus</label>
                    <div class="form-group is-required">
                        <select2 :options="state_list" v-model="record.state"></select2>
                    </div>
                </div>
                <div class="col-md-12">
                    <br>
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="CheckClient" v-model="record.check_client">
                        <label class="form-check-label" for="CheckClient">Clientes</label>
                    </div>
                </div>
                <div class="col-md-6" v-if="record.check_client">
                    <div class="form-group is-required" style="margin-top: -1.5rem;">
                        <v-multiselect :options="clients" track_by="text"
                            :hide_selected="false" :selected="record.clients" v-model="record.clients">
                        </v-multiselect>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right" >
            <button data-toggle="tooltip" title="Buscar" class="btn btn-info btn-sm" @click="searchRecords">
                Buscar <i class="fa fa-search"></i>
            </button>
        </div>
        <div class="col-12"><hr></div>
        <div class="col-12">
            <v-client-table :columns="columns" :data="records" :options="table_options">
                <div slot="product_name" slot-scope="props">
                    <p v-for="(product, index) in props.row.sale_payment_inventory_product" :key="index">
                        {{ (product.product_type == 'Servicio') ? product.sale_goods_to_be_traded.name : product.sale_warehouse_inventory_product.sale_setting_product.name }}
                    </p>
                </div>
                <div slot="price" slot-scope="props">
                    <p v-for="(product, index) in props.row.sale_payment_inventory_product" :key="index">
                        {{ product.value }}
                    </p>
                </div>
                <div slot="currency" slot-scope="props">
                    <p v-for="(product, index) in props.row.sale_payment_inventory_product" :key="index">
                        {{ product.currency.symbol + ' - ' + product.currency.name }}
                    </p>
                </div>
                <div slot="state" slot-scope="props">
                    <span>
                        {{ (props.row.state)?props.row.state:'N/A' }}
                    </span>
                </div>
                <div slot="id" slot-scope="props">
                    <input type="checkbox" id="CheckAddToReport" @click="addToReport(props.row.id)">
                </div>
            </v-client-table>
        </div>
        <div class="card-footer text-right" v-if="records.length > 0">
            <button class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar Reporte" :disabled="recordsToReport.length == 0"
                    v-on:click="OpenPdf(getUrlReport(), '_blank')" id="salepaymentGenerateReport">
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
    </section>
</template>

<script>
export default {
    props:{
        year_old:{
            type:String,
            default: ''
        },
        currencies:{
            type:Array,
            default:function(){
                return [];
            }
        }
    },
    data() {
        return {
            reportUrl:'sale/reports/payments/pdf',
            record: {
                filterDate  : '',
                dateIni     : '',
                dateEnd     : '',
                state      : 'Todos',
                month_init  : '',
                year_init   : new Date().getFullYear(),
                month_end   : '',
                year_end    : new Date().getFullYear(),
                check_client: false,
                clients     : [],
            },
            state_list:[
                { id:'Todos', text:'Todos' },
                { id:'Pendiente', text:'Pendiente' },
                { id:'Aprobado', text:'Aprobado' },
                { id:'Rechazado', text:'Rechazado' },
            ],
            clients: [],
            months:[
                { id:'', text:'Seleccione...' },
                { id:1,  text:'Enero' },
                { id:2,  text:'Febrero' },
                { id:3,  text:'Marzo' },
                { id:4,  text:'Abril' },
                { id:5,  text:'Mayo' },
                { id:6,  text:'Junio' },
                { id:7,  text:'Julio' },
                { id:8,  text:'Agosto' },
                { id:9,  text:'Septiembre' },
                { id:10, text:'Octubre' },
                { id:11, text:'Noviembre' },
                { id:12, text:'Diciembre' }
            ],
            years:[],
            errors: [],
            records: [],
            recordsToReport:[],
            columns: ['code', 'created_at', 'name', 'product_name', 'price', 'currency', 'state', 'id']
        }
    },
    methods: {
        /**
        * Método que borra todos los datos del formulario
        *
        * @author  Juan Rosas <mnarvaez@cenditel.gob.ve>
        */
        reset() {
            this.record = {
                filterDate  : '',
                dateIni     : '',
                dateEnd     : '',
                state       : 'Todos',
                month_init  : '',
                year_init   : new Date().getFullYear(),
                month_end   : '',
                year_end    : new Date().getFullYear(),
                check_client: false,
                clients     : [],
            };
        },

        /**
        * Crea un array con los años desde el dado hasta el actual
        *
        * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
        * @param  {integer} year_old fecha del año de inicio
        * @param  {boolean} optionExtra bandera para determinar si agregar un registro extra al pricipio del array de los años
        */
        CalculateOptionsYears:function(year_old, optionExtra = false){
            const vm = this;
            var date = new Date();
            if (optionExtra) {
                vm.years.push({
                    id:0,
                    text:'Todos'
                });
                vm.record.year_init = 0;
            }
            for (var year = date.getFullYear(); year >= year_old; year--) {
                vm.years.push({
                    id:year,
                    text:year
                });
            }
        },

        /**
        * Consulta y filtra los registros de solicitud de servicios
        *
        * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
        * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
        */
        searchRecords(){
            const vm = this;
            axios.post('/sale/reports/payments/filter-records', this.record).then(response => {
                vm.recordsToReport = [];
                vm.records = response.data.records;
            });
        },

        /**
         * Método que carga los clientes registrados para los select
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        getSaleClientsRif() {
            const vm = this;
            vm.clients = [];
            axios.get(`${window.app_url}/sale/get-sale-clients-rif/`).then(response => {
                vm.clients = response.data.records;
            });
        },

        /**
         * Método que almacena y elimina los registros que se agregaran al reporte
         * Mantiene la lista ordenada
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {integer} id Identificador del registro
         */
        addToReport(id){
            const vm = this;
            var pos = vm.recordsToReport.indexOf(id);
            if (pos != -1) {
                vm.recordsToReport.splice(pos, 1);
            } else {
                vm.recordsToReport.push(id);
            }
            vm.recordsToReport.sort();
        },

        /**
        * Abre una nueva ventana en el navegador
        *
        * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
        * @param  {string} url para la nueva ventana
        * @param  {string} type tipo de ventana que se desea abrir
        * @return {boolean} Devuelve falso si no se ha indicado alguna información requerida
        */
        OpenPdf(url, type){
            const vm = this;
            if (!url) {
                return;
            }
            window.open(url, type);
        },

        getUrlReport(){
            const vm = this;
            return ('/'+vm.reportUrl+'/['+vm.recordsToReport+']');
        },

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
    mounted(){
        const vm = this;
        vm.reset();
        this.initRecords(this.route_list, '');
        vm.getSaleClientsRif();
        vm.CalculateOptionsYears(vm.year_old);
    },
};
</script>
