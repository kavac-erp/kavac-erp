<template>
    <div id="SaleServiceInfo" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="SaleServiceInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Información de la Solicitud Registrada
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger" v-if="errors.length > 0">
                        <ul>
                            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                        </ul>
                    </div>
                    <div class="row">
                        <ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
                            <li class="nav-item col-md-4">
                                <a class="nav-link active" data-toggle="tab" href="#general" id="info_general" role="tab">
                                    <i class="ion-android-person"></i> Datos del solicitante
                                </a>
                            </li>
                            <li class="nav-item col-md-4">
                                <a class="nav-link" data-toggle="tab" href="#service_data" role="tab">
                                    <i class="ion-android-person"></i> Datos de la solicitud de servicios
                                </a>
                            </li>
                            <li class="nav-item col-md-4">
                                <a class="nav-link" data-toggle="tab" href="#requiremnt" role="tab">
                                    <i class="ion-arrow-person"></i> Requerimientos
                                </a>
                            </li>
                            <li v-if="record.sale_technical_proposal" class="nav-item col-md-4">
                                <a class="nav-link" data-toggle="tab" href="#service_features" role="tab">
                                    <i class="ion-arrow-person"></i> Características del servicio
                                </a>
                            </li>
                            <li v-if="record.sale_technical_proposal" class="nav-item col-md-4">
                                <a class="nav-link" data-toggle="tab" href="#equipments" role="tab" @click="loadEquipment()">
                                    <i class="ion-arrow-person"></i> Equipos a utilizar
                                </a>
                            </li>
                            <li v-if="record.sale_technical_proposal" class="nav-item col-md-4" @click="loadGanttDiagram()">
                                <a class="nav-link" data-toggle="tab" href="#gantt_diagram" role="tab">
                                    <i class="ion-arrow-person"></i> Diagrama de gantt
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Cliente</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.sale_client && record.sale_client.name" class="col-md-12" id="client">
                                                {{ record.sale_client && record.sale_client.name ? record.sale_client.name : 'No definido' }}
                                            </span>
                                            <span v-if="record.sale_client && record.sale_client.business_name" class="col-md-12" id="client">
                                                {{ record.sale_client && record.sale_client.business_name ? record.sale_client.business_name : 'No definido' }}
                                            </span>
                                        </div>
                                        <input type="hidden" id="id">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Cédula de identidad / RIF</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span v-if="record.sale_client && record.sale_client.id_number" class="col-md-12" id="client">
                                                {{ record.sale_client && record.sale_client.id_number ? record.sale_client.id_number : 'No definido' }}
                                            </span>
                                            <span v-if="record.sale_client && record.sale_client.rif" class="col-md-12" id="client">
                                                {{ record.sale_client && record.sale_client.rif ? record.sale_client.rif : 'No definido' }}
                                            </span>
                                        </div>
                                        <input type="hidden" id="id">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Correo electrónico</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_client">
                                                <p v-for="(client_email, index) in record.sale_client.sale_clients_email" class="col-md-12" id="client_email" :key="index">
                                                    {{ record.sale_client && record.sale_client.sale_clients_email ? client_email.email : 'No definido' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Número telefónico</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_client && record.sale_client.sale_clients_phone">
                                                <p v-for="(client_phone, index) in record.sale_client.sale_clients_phone" class="col-md-12" id="client_phone" :key="index">
                                                    {{ record.sale_client && record.sale_client.sale_clients_phone ? client_phone.phone : 'No definido' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Organización</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="organization">
                                                {{ record.organization ? record.organization : 'No definido' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Descripción de la actividad económica</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="description">
                                                {{ record.description ? record.description : 'No definido' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="service_data" role="tabpanel">
                            <div class="row">
                                <div v-if="record.payroll_staff_id" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Encargado del servicio</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="service_manager">
                                                {{
                                                    record.payroll_staff_id
                                                        ? record.payroll_staff.first_name + ' ' + record.payroll_staff.last_name
                                                            + ' - ' + record.payroll_staff.id_number
                                                        : 'No definido'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-for="(sale_good, index) in record.sale_goods" :key="index" style="margin-left: 0.5rem;">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Servicio</strong>
                                            <div class="col-md-12" id="service">
                                                <p>
                                                    {{ sale_good ? sale_good.name : 'No definido'}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Descripción</strong>
                                            <div class="col-md-12" id="service_description">
                                                <p>
                                                    {{ sale_good ? sale_good.description : 'No definido'}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Resumen de la solicitud</strong>
                                            <div class="col-md-12" id="service_resume">
                                                <p>
                                                    {{ record.resume ? record.resume : 'No definido'}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Unidad o departamento</strong>
                                            <div class="col-md-12" id="service_department">
                                                <p>
                                                    {{ sale_good && sale_good.department ? sale_good.department.name : 'No definido'}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <table class="table table-sm">
                                            <thead class="thead-light">
                                                <th class="text-left">Nombre</th>
                                                <th class="text-left">Apellido</th>
                                                <th class="text-left">Teléfono</th>
                                                <th class="text-left">Correo electrónico</th>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(payroll_staff, idx) in sale_good.payroll_staffs" :key="idx">
                                                    <td>{{ payroll_staff ? payroll_staff.first_name : 'No definido'}}</td>
                                                    <td>{{ payroll_staff ? payroll_staff.last_name : 'No definido'}}</td>
                                                    <td v-if="payroll_staff.phones && payroll_staff.phones.length > 0">
                                                        <p v-for="(phone, idx2) in payroll_staff.phones" :key="idx2">
                                                            {{ phone.extension + '-' + phone.area_code + phone.number }}
                                                        </p>
                                                    </td>
                                                    <td v-else>
                                                        No definido
                                                    </td>
                                                    <td>{{ payroll_staff ? payroll_staff.email : 'No definido'}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="requiremnt" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Requerimiento</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_service_requirement">
                                                <p v-for="(requiremnt, index) in record.sale_service_requirement" :key="index">
                                                    <span class="col-md-12" id="service_requirement">
                                                        {{ record.sale_service_requirement ? requiremnt.name : 'No definido' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="service_features" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Subservicios</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_technical_proposal">
                                                <div v-for="(technical_proposal, index) in record.sale_technical_proposal" :key="index">
                                                    <div v-for="(subservices, idx) in technical_proposal.list_subservices" :key="idx">
                                                        <p v-for="(subservice, i) in subservices" :key="i">
                                                            <span class="col-md-12" id="service_requirement">
                                                                {{ technical_proposal.list_subservices ? subservice.name : 'No definido' }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Duración</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_technical_proposal">
                                                <div v-for="(technical_proposal, index) in record.sale_technical_proposal" :key="index">
                                                    <span class="col-md-12" id="service_requirement">
                                                        {{ technical_proposal.duration ? technical_proposal.duration : 'No definido' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Duración</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_technical_proposal">
                                                <div v-for="(technical_proposal, index) in record.sale_technical_proposal" :key="index">
                                                    <span class="col-md-12" id="service_requirement">
                                                        {{ record.sale_technical_proposal && technical_proposal.frecuency_id ? technical_proposal.frecuency.name : 'No definido' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Especificaciones técnicas</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_technical_proposal">
                                                <div v-for="(technical_proposal, index) in record.sale_technical_proposal" :key="index">
                                                    <p v-for="(specification, idx) in technical_proposal.sale_proposal_specification" :key="idx">
                                                        <span class="col-md-12" id="service_requirement">
                                                            {{ technical_proposal.sale_proposal_specification ? specification.name : 'No definido' }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Requerimientos que serán suministrados por el cliente</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <div v-if="record.sale_technical_proposal">
                                                <div v-for="(technical_proposal, index) in record.sale_technical_proposal" :key="index">
                                                    <p v-for="(requirement, idx) in technical_proposal.sale_proposal_requirement" :key="idx">
                                                        <span class="col-md-12" id="service_requirement">
                                                            {{ technical_proposal.sale_proposal_requirement ? requirement.name : 'No definido' }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="equipments" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                    <v-client-table :columns="columns_asstets" :data="records" :options="table_options"
                                    ref="tableResults">
                                        <div slot="inventory_serial" slot-scope="props" class="text-center">
                                            <p v-for="(assets, index) in props.row.asset_asignation_assets" :key="index">
                                                {{ props.row.asset_asignation_assets ? assets.asset.inventory_serial : '' }}
                                            </p>
                                        </div>
                                        <div slot="payroll_staff" slot-scope="props" class="text-center">
                                            {{ props.row.payroll_staff ? props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name + ' - ' + props.row.payroll_staff.id_number : '' }}
                                        </div>
                                        <div slot="serial" slot-scope="props" class="text-center">
                                            <p v-for="(assets, index) in props.row.asset_asignation_assets" :key="index">
                                                {{ props.row.asset_asignation_assets ? assets.asset.serial : '' }}
                                            </p>
                                        </div>
                                        <div slot="marca" slot-scope="props" class="text-center">
                                            <p v-for="(assets, index) in props.row.asset_asignation_assets" :key="index">
                                                {{ props.row.asset_asignation_assets ? assets.asset.marca : '' }}
                                            </p>
                                        </div>
                                        <div slot="model" slot-scope="props" class="text-center">
                                            <p v-for="(assets, index) in props.row.asset_asignation_assets" :key="index">
                                                {{ props.row.asset_asignation_assets ? assets.asset.model : '' }}
                                            </p>
                                        </div>
                                    </v-client-table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="gantt_diagram" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <v-client-table :columns="columns_activities" :data="activities" :options="table_option_activities">
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
                record: [],
                records: [],
                activities: [],
                stages: [],
                errors: [],
                columns_asstets: ['inventory_serial','payroll_staff','serial','marca','model'],
                columns_activities: ['stage','name','description','start_date','end_date', 'payroll_staff', 'percentage'],
            }
        },
        created() {
            this.table_options.headings = {
                'inventory_serial': 'Código',
                'payroll_staff': 'Trabajador',
                'serial': 'Serial',
                'marca': 'Marca',
                'model': 'Modelo',
            };
            this.table_options.sortable = ['inventory_serial','payroll_staff','serial','marca','model'];
            this.table_options.filterable = ['inventory_serial','payroll_staff','serial','marca','model'];
            this.table_options.orderBy = { 'column': 'id'};
            this.table_option_activities = {
                columnsDropdown: false,
                dateFormat:"DD/MM/YYYY",
                filterable: [],
                headings: {},
                orderBy: {},
                pagination: {},
                perPage: 10,
                perPageValues: ['10','20','50'],
                sortIcon: {
                    base:"fa",
                    down:"fa-sort-down cursor-pointer",
                    is:"fa-sort cursor-pointer",
                    up:"fa-sort-up cursor-pointer",
                },
                sortable: {},
                texts: {
                    count:" ",
                    filter:"Buscar:",
                    filterBy:"Buscar por {column}",
                    filterPlaceholder:"Buscar...",
                    first:"PRIMERO",
                    last:"ÚLTIMO",
                    limit:"Registros",
                    loading:"Cargando...",
                    loadingError:"Oops! No se pudo cargar la información",
                    noResults:"No existen registros",
                },
            };
            this.table_option_activities.headings = {
                'stage': 'Etapa',
                'name': 'Actividad',
                'description': 'Descripción',
                'start_date': 'Fecha de inicio',
                'end_date': 'Fecha de fin',
                'payroll_staff': 'Trabajador',
                'percentage': 'Porcentaje',
                'id': 'Acción'
            };
            this.table_option_activities.sortable = ['stage','name','description','start_date','end_date', 'payroll_staff', 'percentage'];
            this.table_option_activities.filterable = ['stage','name','description','start_date','end_date', 'payroll_staff', 'percentage'];
            this.table_option_activities.orderBy = { 'column': 'id'};
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
            },

            /**
             * Método que carga los registros en la tabla de equipos
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            loadEquipment(){
                for (let info of this.record.sale_technical_proposal) {
                    if(info.payroll_staffs.length > 0) {
                        this.records = [];
                        axios.get('/asset/asignations/vue-info/' + info.payroll_staffs).then(response => {
                            let assets = [];
                            if (info.payroll_staffs.length > 1) {
                                for (let data of response.data.records) {
                                    assets.push(data);
                                }
                                this.records = assets;
                            } else {
                                this.records.push(response.data.records);
                            }
                        });
                    } else {
                        this.records = [];
                    }
                }
            },

            /**
             * Método que carga los registros en la tabla de diagrama de gantt
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            loadGanttDiagram(){
                const vm = this;
                vm.activities = [];
                axios.get('/sale/technical-proposals/info/'+vm.record.id).then(response => {
                    if(typeof(response.data.record != "undefined")){

                        for (let gantt of response.data.record.sale_gantt_diagram){
                            let stage = '';
                            let staff = gantt.payroll_staff.first_name + ' ' + gantt.payroll_staff.first_name + ' - ' +
                                        gantt.payroll_staff.id_number;

                            for (stage of gantt.sale_gantt_diagram_stage){
                                stage = stage;

                                let gantt_stage = {
                                    id: stage.stage,
                                    stage: stage.stage,
                                    description: stage.description,
                                    text: stage.stage,
                                };

                                vm.stages.push(gantt_stage);
                            };

                            let activity = {
                                description: gantt.description,
                                end_date: gantt.end_date,
                                name: gantt.activity,
                                payroll_staff: staff,
                                payroll_staff_id: gantt.payroll_staff_id,
                                percentage: gantt.percentage,
                                stage: stage.stage,
                                stage_id: stage.stage,
                                start_date: gantt.start_date,
                            };

                            vm.activities.push(activity);
                        };
                    }
                });
            },
        },
    };
</script>
