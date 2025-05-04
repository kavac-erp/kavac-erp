<template>
<section id="SaleServicesForm">
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
            <div class="col-md-12">
                <b>Datos del solicitante</b>
            </div>
            <div class="col-md-3" id="helpClient">
                <div class="form-group is-required">
                    <label>Cliente:</label>
                    <select2 :options="sale_clients_rif"
                             v-model="record.sale_client_id" @input="getSaleClient"></select2>
                </div>
            </div>
            <div class="col-md-3" id="helpClientMail">
                <div v-show="record.sale_client_id != 0" class="form-group">
                    <label for="sale_clients_email">Correo:</label>
                    <p v-for="(email, index) in sale_client.sale_clients_email" :key="index">
                        <input type="text" class="form-control input-sm" :disabled="true"
                            data-toggle="tooltip" title="Dirección"
                            id="email" v-model="email.email" />
                    </p>
                </div>
            </div>
            <div class="col-md-3" id="helpClientPhone">
                <div v-show="record.sale_client_id != 0" class="form-group">
                    <label for="sale_clients_phone">Número telefónico:</label>
                    <p v-for="(value, index) in sale_client.sale_clients_phone" :key="index">
                        <input type="text" class="form-control input-sm" :disabled="true"
                            data-toggle="tooltip" title="Dirección fiscal"
                            id="phone" v-model="value.phone" />
                    </p>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3" id="HelpOrganization">
                <div class="form-group is-required">
                    <label for="applicant_organization">Organización:</label>
                    <input type="text" class="form-control input-sm"
                        data-toggle="tooltip" title="Dirección"
                        v-model="record.organization" id="applicant_organization" />
                </div>
            </div>
            <div class="col-md-3" id="HelpDescription">
                <div class="form-group is-required">
                    <label for="economic_activity">Descripción de la actividad económica:</label>
                    <textarea type="text" class="form-control input-sm"
                        data-toggle="tooltip" title="Dirección fiscal"
                        v-model="record.description" id="economic_activity"></textarea>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <b>Datos de la solicitud de servicios</b>
            </div>
            <div class="col-md-3" id="HelpService">
                <div class="form-group is-required">
                    <label>Servicio:</label>
                    <v-multiselect :options="services" track_by="text"
                                   :hide_selected="false" data-toggle="tooltip"
                                   title="Indique los servicios a seleccionar"
                                   v-model="sale_goods_to_be_traded">
                    </v-multiselect>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group is-required" id="HelpServiceResume">
                    <label for="economic_activity">Resumen de la solicitud:</label>
                    <textarea type="text" class="form-control input-sm"
                        data-toggle="tooltip" title="Dirección fiscal"
                        v-model="record.resume" id="economic_activity"></textarea>
                </div>
            </div>
            <div class="col-12 row" v-for="(good_to_be_traded, index) in sale_goods_to_be_traded" :key="index">
                <div class="col-md-12">
                    <hr>
                </div>
                <div class="col-md-12">
                    Servicio: <b class="text-uppercase">{{ good_to_be_traded.name }}</b>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="good_to_be_traded_description">Descripción:</label>
                        {{ good_to_be_traded.description }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="good_to_be_traded_department">Unidad o departamento:</label>
                        {{ good_to_be_traded.department }}
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
                            <tr v-for="(payroll_staff, idx) in good_to_be_traded.payroll_staffs" :key="idx">
                                <td>{{ payroll_staff.staff_name ? payroll_staff.staff_name : 'No definido'}}</td>
                                <td>{{ payroll_staff.staff_last_name ? payroll_staff.staff_last_name : 'No definido'}}</td>
                                <td>{{ payroll_staff.staff_phone ? payroll_staff.staff_phone : 'No disponible' }}</td>
                                <td>{{ payroll_staff.staff_email ? payroll_staff.staff_email : 'No definido'}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class="col-md-12" id="HelpServiceRequirement">
                <h6 class="card-title">Requerimiento del solicitante <i class="fa fa-plus-circle cursor-pointer"
                    @click="addRequirement()"></i></h6>
                <div class="row" v-for="(service_requirement, index) in record.requirements" :key="index">
                    <div class="col-md-4">
                        <div class="form-group is-required">
                            <label for="service_requirement">Requerimiento del solicitante:</label>
                            <input type="text" id="service_requirement" class="form-control input-sm" data-toggle="tooltip"
                                title="Requerimiento del solicitante" v-model="service_requirement.name">
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="form-group">
                            <button class="mt-4 btn btn-sm btn-danger btn-action" type="button" @click="removeRow(index, record.requirements)"
                                title="Eliminar este dato" data-toggle="tooltip">
                                    <i class="fa fa-minus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
        </div>
    </div>
    <div class="card-footer text-right">
        <div class="row">
            <div class="col-md-3 offset-md-9" id="HelpButtons">
    	        <button type="button" @click="reset()"
                    class="btn btn-default btn-icon btn-round btn-modal-clear"
      	            title ="Borrar datos del formulario">
    	                <i class="fa fa-eraser"></i>
                    </button>
                <button type="button" @click="redirect_back(route_list)"
    	            class="btn btn-warning btn-icon btn-round btn-modal-close"
    	            data-dismiss="modal"
    	            title="Cancelar y regresar">
    	                <i class="fa fa-ban"></i>
    	        </button>
                <button type="button"  @click="createRecord('sale/services')"
                    class="btn btn-success btn-icon btn-round btn-modal-save"
                    title="Guardar registro">
                        <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </div>
</section>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: '',
                organization: '',
                description: '',
                resume: '',
                sale_goods_to_be_traded: [],
                requirements: [],
                sale_client_id: '',
            },
            sale_goods_to_be_traded: [],
            services: [],
            records: [],
            errors: [],
            sale_client: {
                name : '',
                sale_clients_phone : '',
                sale_clients_email : '',
            },
            sale_clients_rif: [],
            sale_clients_name: [],
            sale_clients_address: [],
            sale_clients_fiscal_address: [],
        }
    },
    watch: {
        /**
         * Método que supervisa los cambios en el objeto sale_goods_to_be_traded para asignar sus valores
         * en el record
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         * @param     {object}    value    Objeto que contiene el valor de a búsqueda
         */
        sale_goods_to_be_traded() {
            const vm = this;
            vm.record.sale_goods_to_be_traded = [];

            for (let good_to_be_traded of vm.sale_goods_to_be_traded){
                let good_to_be_traded_id = good_to_be_traded.id;
                vm.record.sale_goods_to_be_traded.push(good_to_be_traded_id);
            }

        }
    },
    methods: {
        /**
         * Método que carga la información del formulario al editar
         */
        async loadForm(id){
            const vm = this;

            await axios.get('/sale/services/info/'+id).then(response => {
                if(typeof(response.data.record != "undefined")){
                    let data = response.data.record;
                    vm.record.id = data.id;
                    vm.record.organization = data.organization;
                    vm.record.description = data.description;
                    vm.record.resume = data.resume;
                    vm.record.sale_client_id = data.sale_client_id;

                    vm.sale_goods_to_be_traded = [];
                    vm.record.requirements = [];

                    for (let data of vm.services) {
                        for (let good_id of response.data.record.sale_goods_to_be_traded) {
                            if (good_id == data.id) {
                                vm.sale_goods_to_be_traded.push(data);
                            }
                        }
                    }

                    for (let requirement of response.data.record.sale_service_requirement) {
                        vm.record.requirements.push(requirement);
                    }
                }
            });
        },
        /**
         * Método que borra todos los datos del formulario
         *
         *
         */
        reset() {
            this.record = {
                id: '',
                organization: '',
                description: '',
                resume: '',
                sale_client_id: '',
                sale_goods_to_be_traded: [],
                requirements: [],
            };
            this.sale_goods_to_be_traded = [];
        },
        /**
         * Agrega una nueva columna para los requerimientos del servicio
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        addRequirement() {
            const vm = this;
            vm.record.requirements.push({
                name: '',
                sale_service_id: '',
            });
        },

        /**
         * Método que obtiene los bienes registrados en comercialización
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getSaleGoods() {
            const vm = this;
            vm.services = [];

            axios.get('/sale/get-sale-goods/').then(response => {
                vm.services = response.data.records;
            });
        },

        /**
         * Método que obtiene los clientes registrados en comercialización para los select
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getSaleClientsRif() {
            const vm = this;
            vm.sale_clients_rif = [];

            axios.get('/sale/get-sale-clients-rif/').then(response => {
                vm.sale_clients_rif = response.data.records;
            });
        },

        /**
         * Método que obtiene los clientes registrados en comercialización
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getSaleClient() {
            const vm = this;
            if (vm.record.sale_client_id > 0) {
                axios.get('/sale/get-sale-client/' + vm.record.sale_client_id).then(response => {
                    vm.sale_client.name = response.data.sale_client.name;
                    vm.sale_client.sale_clients_phone = response.data.sale_client.sale_clients_phone;
                    vm.sale_client.sale_clients_email = response.data.sale_client.sale_clients_email;
                });
            }
        },

        /**
         * Método que obtiene los clientes registrados en comercialización
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getSaleClientsAddress() {
            const vm = this;
            vm.sale_clients_address = [];

            axios.get('/sale/get-sale-clients-address/').then(response => {
                vm.sale_clients_address = response.data;
            });
        },

        /**
         * Método que obtiene los clientes registrados en comercialización
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getSaleClientsFiscalAddress() {
            const vm = this;
            vm.sale_clients_fiscal_address = [];

            axios.get('/sale/get-sale-clients-fiscal-address/').then(response => {
                vm.sale_clients_fiscal_address = response.data;
            });
        },
    },
    mounted() {
        const vm = this;

        if(this.serviceid){
            this.loadForm(this.serviceid);
        }
        else {
            vm.record.date = moment(String(new Date())).format('YYYY-MM-DD');
        }
    },
    props: {
        serviceid: {
            type: Number
        },
    },
    created() {
        this.getSaleClientsRif();
        this.getSaleClient();
        this.getSaleGoods();
        this.record.sale_goods_to_be_traded = [];
        this.record.requirements = [];
    },
};
</script>
