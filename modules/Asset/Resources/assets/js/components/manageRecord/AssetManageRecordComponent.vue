<template>
    <div>
        <a
            class="btn btn-primary btn-xs btn-icon btn-action"
            href="#"
            title="Gestionar acta"
            data-toggle="tooltip"
            :disabled="disabled(state, action)"
            type="button" v-has-tooltip
            @click="
                (!disabled(state, action)) ?
                addRecord(`manage_record_${index}_${action}`, route_list ,$event) :
                viewMessage()
            "
        >
            <i class="fa fa-file-pdf-o"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="`manage_record_${index}_${action}`"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click="reset()"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-file-text ico-2x"></i>
                            Gestión de Acta de {{action}} de bienes
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <ul>
                                <li v-for="error in errors" :key="error">{{ error }}</li>
                            </ul>
                        </div>
                        <div class="tab-content" id="info_general">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Organización:</strong>
                                            <span class="col-md-12" :id="`institution_${index}_${action}`"></span>
                                            <input type="hidden" id="id">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Ubicación Geográfica/Física: </strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Estado:</strong>
                                            <span :id="`estate_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Municipio:</strong>
                                            <span :id="`municipality_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Dirección:</strong>
                                            <span :id="`address_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Ejercicio Fiscal:</strong>
                                            <span class="col-md-12" :id="`fiscal_year_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row" v-if="action == 'Asignación' || action == 'Entrega'">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Fecha de Asignación:</strong>
                                            <span class="col-md-12" :id="`created_at_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="action == 'Entrega'">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Fecha de Entrega:</strong>
                                            <span class="col-md-12" :id="`delivered_at_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="action == 'Desincorporación'">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Fecha de Desincorporación:</strong>
                                            <span class="col-md-12" :id="`disincorporation_date_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr v-if="action == 'Asignación' || action == 'Entrega'">
                                <div class="row" v-if="action == 'Asignación' || action == 'Entrega'">
                                    <div class="col-md-12">
                                        <div class="form-group" id="staff">
                                            <strong>Responsable por uso:</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="action == 'Asignación' || action == 'Entrega'">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Apellidos:</strong>
                                            <span :id="`last_name_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombres:</strong>
                                            <span :id="`first_name_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cédula de identidad:</strong>
                                            <span :id="`id_number_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="action == 'Asignación' || action == 'Entrega'">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Departamento:</strong>
                                            <span :id="`department_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cargo:</strong>
                                            <span :id="`payroll_position_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Lugar de Ubicación:</strong>
                                            <span :id="`location_place_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr v-if="action == 'Desincorporación'">
                                <div class="row" v-if="action == 'Desincorporación'">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Motivo de la Desincorporación:</strong>
                                            <span class="col-md-12" :id="`disincorporation_motive_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr v-if="action == 'Entrega' || action == 'Desincorporación'">
                                <div class="row" v-if="action == 'Entrega' || action == 'Desincorporación'">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong>Observaciones:</strong>
                                            <span class="col-md-12" :id="`observation_${index}_${action}`"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong v-if="action=='Asignación'">Bienes Asignados:</strong>
                                            <strong v-if="action=='Entrega'">Bienes Entregados:</strong>
                                            <strong v-if="action=='Desincorporación'">Bienes Desincorporados:</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <v-client-table
                                            :columns="columns"
                                            :data="equipments"
                                            :options="table_options"
                                        >
                                            <div
                                                slot="asset.specifications"
                                                slot-scope="props" class="text-center"
                                            >
                                                <span>
                                                    {{ (props.row.asset.specifications) ? props.row.asset.specifications.replace(/(<([^>]+)>)/ig, ''):'' }}
                                                </span>
                                            </div>
                                        </v-client-table>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div
                                        class="col-md-4"
                                        id="authorized_by"
                                        v-if="
                                            action == 'Asignación' || action == 'Desincorporación'
                                        "
                                    >
                                        <div class="form-group is-required">
                                            <strong>Autorizado por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="authorized_by"></select2>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-4"
                                        id="formed_by"
                                        v-if="
                                            action == 'Asignación' || action == 'Desincorporación'
                                        "
                                    >
                                        <div class="form-group is-required">
                                            <strong>Conformado por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="formed_by"></select2>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-4"
                                        id="approved_by"
                                        v-if="action == 'Entrega'"
                                    >
                                        <div class="form-group is-required">
                                            <strong>Aprobado por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="approved_by"></select2>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-4"
                                        id="received_by"
                                        v-if="action == 'Entrega'"
                                    >
                                        <div class="form-group is-required">
                                            <strong>Recibido por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="received_by"></select2>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-4"
                                        id="delivered_by"
                                        v-if="action == 'Asignación'"
                                    >
                                        <div class="form-group is-required">
                                            <strong>Entregado por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="delivered_by"></select2>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-4"
                                        id="delivered_by"
                                        v-if="action == 'Desincorporación'"
                                    >
                                        <div class="form-group is-required">
                                            <strong>Elaborado por:</strong>
                                            <select2 :options="payroll_staffs"
                                            v-model="produced_by"></select2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            class="btn btn-default btn-sm btn-round btn-modal-close"
                            type="button"
                            data-dismiss="modal"
                            @click="reset()"
                        >
                            Cerrar
                        </button>
                        <button
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                            type="button"
                            title="Generar acta"
                            @click="createReport(index, 'create_report')"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default{
        data() {
            return {
                records: [],
                record:{
                    code: '',
                    action: '',
                    institution: '',
                    estate: '',
                    municipality: '',
                    address: '',
                    fiscal_year: '',
                    created_at: '',
                    delivered_at: '',
                    disincorporation_date: '',
                    last_name: '',
                    first_name: '',
                    id_number: '',
                    department: '',
                    payroll_position: '',
                    location_place: '',
                    disincorporation_motive: '',
                    observation: '',
                    authorized_by: '',
                    formed_by: '',
                    delivered_by: '',
                    approved_by: '',
                    received_by: '',
                    produced_by: '',
                    assets:[],
                },
                authorized_by: '',
                formed_by: '',
                delivered_by: '',
                approved_by: '',
                received_by: '',
                produced_by: '',
                errors: [],
                payroll_staffs: [],
                equipments: [],
                payroll_positions: [],
                columns: [
                    'asset.inventory_serial',
                    'asset.institution.acronym',
                    'asset.specifications',
                    'asset.asset_condition.name',
                    'asset.marca',
                    'asset.model',
                    'asset.serial',
                    'asset.color',
                    'asset.asset_institutional_code'
                ],
                table_options: {
                    headings: {
                        'asset.inventory_serial': 'Código',
                        'asset.institution.acronym': 'Organización',
                        'asset.specifications': 'Especificaciones',
                        'asset.asset_condition.name': 'Condición Física',
                        'asset.marca': 'Marca',
                        'asset.model': 'Modelo',
                        'asset.serial': 'Serial',
                        'asset.color': 'Color',
                        'asset.asset_institutional_code': 'Código de bien organizacional',
                    },
                    orderBy: { 'column': 'asset.id'},
                }
            }
        },
        props: {
            index: Number,
            action: String,
            route_list: String,
            data:Object,
            state:String
        },
        mounted(){
            const vm = this;
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
             */
            reset() {
                const vm = this;
                vm.record = [];
                vm.payroll_staffs = [];
                vm.equipments = [];
            },

            /**
             * Función que reescribe el comportamiento original de la función initRecords() para inicializar los datos base
             * del formulario.
             * @param {String} url ruta que indica desde dónde cargar los datos
             *
             * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
             */
            initRecords(url,modal_id){
                this.errors = [];
                const vm = this;
                let fields = {};
                document.getElementById("info_general").click();
                url = vm.setUrl(url);
                vm.loadEquipment(url);
                vm.getPayrollStaffs();
                axios.get(url).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        fields = response.data.records;
                        $(".modal-body #id").val( fields.id );
                        vm.record.institution = (fields.institution_id)?fields.institution.name:'N/A';
                        document.getElementById(`institution_${vm.index}_${vm.action}`).innerText = (fields.institution_id)?fields.institution.name:'N/A';
                        vm.record.estate = (fields.institution_id)?fields.institution.municipality.estate.name:'N/A';
                        document.getElementById(`estate_${vm.index}_${vm.action}`).innerText = (fields.institution_id)?fields.institution.municipality.estate.name:'N/A';
                        vm.record.municipality = (fields.institution_id)?fields.institution.municipality.name:'N/A';
                        document.getElementById(`municipality_${vm.index}_${vm.action}`).innerText = (fields.institution_id)?fields.institution.municipality.name:'N/A';
                        vm.record.address = (fields.institution_id)?fields.institution.legal_address.replace(/(<([^>]+)>)/ig, ''):'N/A';
                        document.getElementById(`address_${vm.index}_${vm.action}`).innerText = (fields.institution_id)?fields.institution.legal_address.replace(/(<([^>]+)>)/ig, ''):'N/A';
                        vm.record.fiscal_year = (fields.institution_id)?fields.institution.fiscal_years.pop().year:'N/A';
                        document.getElementById(`fiscal_year_${vm.index}_${vm.action}`).innerText = (vm.record.fiscal_year)?vm.record.fiscal_year:'N/A';
                        if(vm.action === 'Asignación' || vm.action === 'Entrega'){
                            vm.record.created_at = (fields.created_at)?vm.format_date(fields.created_at):'N/A';
                            document.getElementById(`created_at_${vm.index}_${vm.action}`).innerText = (fields.created_at)?vm.format_date(fields.created_at):'N/A';
                            vm.record.last_name = (fields.payroll_staff)?fields.payroll_staff.last_name:'N/A';
                            document.getElementById(`last_name_${vm.index}_${vm.action}`).innerText = (fields.payroll_staff)?fields.payroll_staff.last_name:'N/A';
                            vm.record.first_name = (fields.payroll_staff)?fields.payroll_staff.first_name:'N/A';
                            document.getElementById(`first_name_${vm.index}_${vm.action}`).innerText = (fields.payroll_staff)?fields.payroll_staff.first_name:'N/A';
                            vm.record.id_number = (fields.payroll_staff)?fields.payroll_staff.id_number:'N/A';
                            document.getElementById(`id_number_${vm.index}_${vm.action}`).innerText = (fields.payroll_staff)?fields.payroll_staff.id_number:'N/A';
                            vm.record.department = (fields.payroll_staff_id)?fields.payroll_staff.payroll_employment.department.name:'N/A';
                            document.getElementById(`department_${vm.index}_${vm.action}`).innerText = (fields.payroll_staff_id)?fields.payroll_staff.payroll_employment.department.name:'N/A';
                            vm.record.payroll_position = (fields.payroll_staff_id)?fields.payroll_staff.payroll_employment.payroll_position.name:'N/A';
                            document.getElementById(`payroll_position_${vm.index}_${vm.action}`).innerText = (fields.payroll_staff_id)?fields.payroll_staff.payroll_employment.payroll_position.name:'N/A';
                            vm.record.location_place = (fields.location_place)?fields.location_place:'N/A';
                            document.getElementById(`location_place_${vm.index}_${vm.action}`).innerText = (fields.location_place)?fields.location_place:'N/A';
                        }
                        if(vm.action === 'Asignación' || vm.action === 'Desincorporación'){
                            vm.record.code = vm.data.code;
                        }
                        if(vm.action === 'Entrega'){
                            vm.record.code = vm.data.asset_asignation.code;
                            vm.record.delivered_at = (vm.data.created_at)?vm.format_date(vm.data.created_at):'N/A';
                            document.getElementById(`delivered_at_${vm.index}_${vm.action}`).innerText = (vm.data.created_at)?vm.format_date(vm.data.created_at):'N/A';
                        }
                        if(vm.action === 'Entrega' || vm.action === 'Desincorporación'){
                            vm.record.observation = (vm.data.observation)?vm.data.observation.replace(/(<([^>]+)>)/ig, ''):'N/A';
                            document.getElementById(`observation_${vm.index}_${vm.action}`).innerText = (vm.data.observation)?vm.data.observation.replace(/(<([^>]+)>)/ig, ''):'N/A';
                        }
                        if(vm.action === 'Desincorporación'){
                            vm.record.disincorporation_date = (vm.data.date)?vm.format_date(vm.data.date):'N/A';
                            document.getElementById(`disincorporation_date_${vm.index}_${vm.action}`).innerText = (vm.data.date)?vm.format_date(vm.data.date):'N/A';
                            vm.record.disincorporation_motive = (vm.data.asset_disincorporation_motive)?vm.data.asset_disincorporation_motive.name:'N/A';
                            document.getElementById(`disincorporation_motive_${vm.index}_${vm.action}`).innerText = (vm.data.asset_disincorporation_motive)?vm.data.asset_disincorporation_motive.name:'N/A';
                        }
                        vm.record.action = vm.action;
                        vm.record.assets = vm.equipments;
                    }
                    if ($("#" + modal_id).length) {
                        $("#" + modal_id).modal('show');
                    }
                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        else {
                            vm.logs('resources/js/all.js', 343, error, 'initRecords');
                        }
                    }
                });
            },

            /**
             * Función que reescribe el metodo para mostrar un mensaje de aleta
             *
             * @author Argenis Osorio <aosorio at cenditel.gob.ve>
             */
            viewMessage() {
                const vm = this;
                vm.showMessage(
                    'custom', 'Alerta', 'danger', 'screen-error',
                    'La opción está en un tramite que no le permite acceder a esta funcionalidad'
                );
                return false;
            },

            /**
             * Función para cargar los datos de los bienes institucionales
             *
             * @param {String} $url ruta que indica desde dónde cargar los datos de los bienes
             *
             * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
             */
            loadEquipment(url){
                const vm = this;
                vm.equipments = [];
                let equipments_ids = [];
                url = vm.setUrl(url);
                axios.get(url).then(response => {
                    if(vm.action === 'Asignación'){
                        vm.equipments = response.data.records.asset_asignation_assets;
                    }
                    else if(vm.action === 'Entrega'){
                        equipments_ids = JSON.parse(response.data.records.ids_assets);
                        if(equipments_ids !== null){
                            vm.equipments = response.data.records.asset_asignation_assets.filter(asset => equipments_ids.delivered.includes(asset.asset.id));
                        }
                    }
                    else if(vm.action === 'Desincorporación'){
                        vm.equipments = response.data.records.asset_disincorporation_assets;
                    }

                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        else {
                            vm.logs('resources/js/all.js', 343, error, 'initRecords');
                        }
                    }
                });
            },

            /**
             * Función que desabilita el botón de gestionar acta, según sea el caso
             *
             * @param {String} state Estado de la asignación o Entrega
             * @param {String} action Ación a ejecutar (Asignación, Entrega o Desincorporación)
             *
             * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
             */
            disabled(state = '', action = ''){
                if(action === 'Asignación' && state === 'Asignado'){
                    return false;
                }
                else if(action === 'Entrega' && state === 'Aprobado'){
                    return false;
                }
                else if(action === 'Desincorporación' && state === ''){
                    return false;
                }
                else return true;
            },

            /**
             * Función que filtra los datos de un trabajador dado su identificador
             *
             * @param {Integer} payroll_id Identificador único del trabajador
             * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
             */
            filterPayrolStaffs(payroll_id){
                const vm = this;
                let text = '';
                vm.payroll_staffs.filter((el) => {
                    if(el.id == payroll_id){
                        text = el.text;
                    }
                });
                return text;
            },

            /**
             * Función que genera el acta de asigncaión, entrega o
             * desincorporación de bienes institucionales.
             *
             * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
             */
            createReport() {
                const vm = this;
                let url = '';
                // comprueba los campos obligatorios para cada una de las acciones (Asignación, Entrega o Desincorporación)
                if (vm.action === 'Asignación'){
                    url = `${window.app_url}/asset/asignations/asignations-record-pdf`;
                    if(!(vm.authorized_by && vm.formed_by && vm.delivered_by)){
                        bootbox.alert("Debe seleccionar todos los campos obligatorios (*) para completar la generación del acta.");
                        return false;
                    }
                }
                if (vm.action === 'Desincorporación'){
                    url = `${window.app_url}/asset/disincorporations/disincorporations-record-pdf`;
                    if(!(vm.authorized_by && vm.formed_by && vm.produced_by)){
                        bootbox.alert("Debe seleccionar todos los campos obligatorios (*) para completar la generación del acta.");
                        return false;
                    }
                }
                if (vm.action === 'Entrega'){
                    url = `${window.app_url}/asset/asignations/deliveries-record-pdf`;
                    if(!(vm.approved_by && vm.received_by)){
                        bootbox.alert("Debe seleccionar todos los campos obligatorios (*) para completar la generación del acta.");
                        return false;
                    }
                }
                if(vm.action === 'Asignación' || vm.action === 'Desincorporación'){
                    vm.record.authorized_by = vm.filterPayrolStaffs(vm.authorized_by);
                    vm.record.formed_by = vm.filterPayrolStaffs(vm.formed_by);
                }
                if(vm.action === 'Asignación'){
                    vm.record.delivered_by = vm.filterPayrolStaffs(vm.delivered_by);
                }
                if(vm.action === 'Desincorporación'){
                    vm.record.produced_by = vm.filterPayrolStaffs(vm.produced_by);
                }
                if(vm.action === 'Entrega'){
                    vm.record.approved_by = vm.filterPayrolStaffs(vm.approved_by);
                    vm.record.received_by = vm.filterPayrolStaffs(vm.received_by);
                }
                let formData = new FormData();
                formData.append( 'data', JSON.stringify(vm.record));
                vm.loading = true;
                axios.post(url, formData).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
                            location.href = response.data.redirect;
                            vm.showMessage(
                            'custom', 'Éxito', 'success', 'screen-ok',
                            'Acta generada exitosamente.'
                            );
                        }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) !== 'undefined') {
                        if (error.response.status == 422 || error.response.status == 500) {
                            for (var indexErrors in error.response.data.errors) {
                            var messages = error.response.data.errors[indexErrors];
                            for (var indexMsg in messages) {
                                var message = messages[indexMsg].split('. ')[1] + '. ' + messages[indexMsg].split('. ')[2];
                                vm.showMessage(
                                'custom', 'Error', 'danger', 'screen-error', message
                                );
                            }
                            }
                        }
                    }
                    vm.loading = false;
                });
            }
        }
    };
</script>
