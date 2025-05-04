<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
            title="Clientes" data-toggle="tooltip"
            @click="addRecord('add_sale_clients', 'sale/register-clients', $event);">
            <i class="icofont icofont-business-man ico-3x"></i>
            <span>Clientes</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_sale_clients">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6><i class="icofont icofont-business-man ico-3x"></i>Clientes</h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" role="alert" v-if="errors.length > 0">
                            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    @click.prevent="errors = {}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <hr>
                            <ul>
                                <li v-for="(error, index) in errors" :key="index">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <h6 class="card-title">Datos del cliente:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="type_person">Tipo de persona:</label>
                                    <select2 :options="types_person" id='type_person_juridica' v-model="record.type_person_juridica"></select2>
                                </div>
                            </div>

                            <div v-show="record.type_person_juridica == 'Jurídica'" class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="rif">RIF</label>
                                    <input type="text" id="rif" class="form-control input-sm" data-toggle="tooltip"
                                    title="Indique el rif del cliente" v-model="record.rif">
                                </div>
                            </div>
                            <div v-show="record.type_person_juridica == 'Jurídica'" class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="business_name">Razón social:</label>
                                    <input type="text" id="name" class="form-control input-sm" data-toggle="tooltip"
                                    title="Razón social" v-model="record.business_name">
                                </div>
                            </div>
                            <div v-show="record.type_person_juridica == 'Jurídica'" class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="representative_name">Nombres y apellidos del representante legal:</label>
                                    <input type="text" id="name" class="form-control input-sm" data-toggle="tooltip"
                                        title="Nombres y Apellidos del representante legal" v-model="record.representative_name">
                                </div>
                            </div>
                            <div v-show="record.type_person_juridica == 'Natural'" class="col-md-4">
                                <label for="name">Tipo de identificación:</label>
                                <div class="d-flex">
                                    <div class="col-md-3 form-group is-required">
                                        <select2 data-toggle="tooltip" title="Tipo de identificación" 
                                            :options="id_types" v-model="record.id_type"></select2>
                                    </div>
                                    <div class="col-md-9 form-group is-required">
                                        <input type="text" id="id_type" class="form-control input-sm" data-toggle="tooltip"
                                            title="Introduza la identificación" v-model="record.id_number">
                                    </div>
                                </div>
                            </div>
                            <div v-show="record.type_person_juridica == 'Natural'" class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="name">Nombre y apellido:</label>
                                    <input type="text" id="name" class="form-control input-sm" data-toggle="tooltip"
                                        title="Nombre y apellido" v-model="record.name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="direcciondelcliente">Dirección del cliente:</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="countries">País</label>
                                    <select2 id="input_country" :options="countries" @input="getEstates()" v-model="record.country_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="estates">Estado</label>
                                    <select2  id="input_estate" :options="estates" @input="getMunicipalities()" v-model="record.estate_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="municipalities">Municipio</label>
                                    <select2 id="input_municipality" :options="municipalities" @input="getParishes()" v-model="record.municipality_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="parishes">Parroquia</label>
                                    <select2 :options="parishes" v-model="record.parish_id"></select2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group is-required">
                                    <label>Dirección Fiscal</label>
                                    <ckeditor :editor="ckeditor.editor" id="direction" data-toggle="tooltip"
                                        title="Indique dirección física del bien" :config="ckeditor.editorConfig"
                                        class="form-control" name="direction" tag-name="textarea" rows="3"
                                        v-model="record.address_tax"></ckeditor>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div v-show="record.type_person_juridica == 'Jurídica'" class="col-md-4">
                                        <div class="form-group is-required">
                                            <label for="name_client">Persona de contacto:</label>
                                            <input type="text" id="name_client" class="form-control input-sm" data-toggle="tooltip"
                                                title="Persona de contacto" v-model="record.name_client">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="card-title">Correo electrónico <i class="fa fa-plus-circle cursor-pointer" @click="addEmail"></i></h6>
                                        <div class="row" v-for="(clients_email, index) in record.sale_clients_email" 
                                             :key="index">
                                            <div class="col-md-4">
                                                <div class="form-group is-required">
                                                    <label for="email_client">Correo electrónico:</label>
                                                    <input type="text" id="email_client" class="form-control input-sm" data-toggle="tooltip"
                                                        title="Correo electrónico" v-model="clients_email.email">
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <div class="form-group">
                                                    <button class="mt-4 btn btn-sm btn-danger btn-action" type="button" @click="removeRow(index, record.sale_clients_email)"
                                                        title="Eliminar este dato" data-toggle="tooltip">
                                                            <i class="fa fa-minus-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="card-title">
                                                Números Telefónicos <i class="fa fa-plus-circle cursor-pointer" @click="addPhone"></i>
                                        </h6>
                                        <div class="row phone-row" v-for="(value, index) in record.sale_clients_phone" :key="index">
                                          <div class="col-4">
                                            <input type="text" class="form-control input-sm" placeholder="+00-000-0000000"
                                              v-model="value.phone" v-input-mask
                                              data-inputmask="'mask': '+99-999-9999999'"/>
                                          </div>
                                          <div class="col-1">
                                            <div class="form-group">
                                              <button class="btn btn-sm btn-danger btn-action" type="button"
                                                @click="removeRow(index, record.sale_clients_phone)"
                                                title="Eliminar este dato" data-toggle="tooltip">
                                                <i class="fa fa-minus-circle"></i>
                                              </button>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
									@click="clearFilters" data-dismiss="modal">
								Cerrar
							</button>
							<button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
									@click="reset()">
								Cancelar
							</button>
							<button type="button" @click="createRecord('sale/register-clients')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns_clients" :data="records" :options="table_options_clients">
                            <div slot="name_client" slot-scope="props" class="text-center">
                                <span>
                                {{ (props.row.name_client)? props.row.name_client : props.row.name }}
                                </span>
                            </div>
                            <div slot="rif" slot-scope="props" class="text-center">
                                <span>
                                {{ (props.row.rif)? props.row.rif : props.row.id_type + props.row.id_number }}
                                </span>
                            </div>
                            <div slot="sale_clients_phone" slot-scope="props">
                                <div v-if="props.row.sale_clients_phone">
                                    <ul v-for="(client_phone, index) in props.row.sale_clients_phone" :key="index">
                                        <li>{{ client_phone.phone }}</li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div slot="sale_clients_email" slot-scope="props">
                                <div v-if="props.row.sale_clients_email">
                                    <ul v-for="(client_email, index) in props.row.sale_clients_email" :key="index">
                                        <li>{{ client_email.email }}</li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    title="Modificar registro" data-toggle="tooltip" type="button">
                                        <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'sale/register-clients')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro" data-toggle="tooltip"
                                    type="button">
                                        <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
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
                    rif: '',
                    business_name: '',
                    type_person_juridica: '',
                    representative_name: '',
                    name: '',
                    country_id: '',
                    quote_clients: [],
                    estate_id: '',
                    municipality_id: '',
                    parish_id: '',
                    address_tax: '',
                    name_client: '',
                    sale_clients_email: [],
                    sale_clients_phone: [],
                    id_type: '',
                    id_number: '',
                },
                errors: [],
                quote_clients: [],
                records: [],
                countries: [],
                estates: [],
                cities: [],
                municipalities: [],
                parishes: [],
                columns_clients: ['type_person_juridica', 'rif', 'name_client', 'sale_clients_phone', 'sale_clients_email', 'id'],
                types_person:  [
                    {'id':'', 'text':"Seleccione..."},
                    {'id':'Natural', 'text':'Natural'},
                    {'id':'Jurídica', 'text':'Jurídica'}
                ],
                id_types: ['V', 'E', 'P'],
            }
        },
        methods: {
            reset() {
                this.record = {
                    rif: '',
                    type_person_juridica: '',
                    name: '',
                    country_id: '',
                    estate_id: '',
                    city_id: '',
                    municipality_id: '',
                    parish_id: '',
                    address_tax: '',
                    name_client: '',
                    sale_clients_email: [],
                    sale_clients_phone: []
                };
            },
            /**
             * Agrega una nueva columna para el registro de telefonos
             *
             * @author Jose Puentes <jpuentes@cenditel.gob.ve>
             */ 
            addPhone: function() {
                const vm = this;
                vm.record.sale_clients_phone.push({
                    id: '',
                    phone: '',
                    sale_client_id: ''
                });
            },
            /**
            * Método que agrega un nuevo campo de atributo al formulario
            *
            * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
            */
            addEmail() {      
                let field = {id: '', email: '', sale_client_id: ''};
                this.record.sale_clients_email.push(field);
            },
        },
        created() {
            this.getCountries();

            this.table_options_clients = {
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
            this.table_options_clients.headings = {
                'id': 'Acción',
                'type_person_juridica': 'Tipo de Persona',
                'rif': 'Rif/n° de identificación',
                'name_client': 'Nombre y apellido/razón social',
                'sale_clients_phone': 'Telefonos',
                'sale_clients_email': 'Correo Electrónico'
            };
            this.table_options_clients.sortable = [
                'type_person_juridica',
            ];
            this.table_options_clients.filterable = [
                'type_person_juridica',
                'rif',
                'id_number',
                'name',
                'name_client',
            ];
        },
        mounted() {
            const vm = this;
            vm.getQuoteClients();
        },
    };
</script>