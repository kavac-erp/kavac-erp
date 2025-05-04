<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center" id="projecttracking_subproject">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
            title="Registros de los subproyectos" data-toggle="tooltip"
            @click="addRecord('add_projecttracking_subproject', 'projecttracking/subprojects', $event)">
            <i class="icofont icofont icofont-tasks ico-4x"></i>
            <span>Subproyectos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_projecttracking_subproject">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont icofont-tasks ico-4x"></i>
                            Registro de Subproyectos
                        </h6>
                    </div>
                    <div class="modal-body">
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
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="project_id">Proyecto:</label>
                                    <select2 :options="projects_list" id="project_id" placeholder="Proyecto"
                                        class="form-control input-sm" v-model="record.project_id" data-toggle="tooltip"
                                        title="Indique el Proyecto"
                                        @input="getProductTypesFromProject(record.project_id)">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required" name="category">
                                    <label>Tipos de Producto:</label>
                                    <v-multiselect :options="product_types_list" track_by="text" :hide_selected="false"
                                        data-toggle="tooltip" title="Indique los tipos de productos"
                                        v-model="record.product_types">
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input type="text" id="name" placeholder="Nombre" class="form-control input-sm"
                                        v-model="record.name" data-toggle="tooltip"
                                        title="Indique el nombre del subproyecto">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Descripción:</label>
                                    <input type="text" id="description" placeholder="Descripción"
                                        class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
                                        title="Indique la descripción del subproyecto">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="responsable">Responsable del Proyecto:</label>
                                    <select2 :options="payroll_staffs" id="responsable_id"
                                        placeholder="Responsable del Proyecto" v-model="record.responsable_id"
                                        data-toggle="tooltip" title="Indique el responsable del proyecto"></select2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label for="financement_amount">Monto de Financiamiento:</label>
                                    <input type="text" id="financement_amount" placeholder="Monto de Financiamiento"
                                        class="form-control input-sm numeric" v-model="record.financement_amount"
                                        data-toggle="tooltip" title="Indique el monto de financiamiento">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required" name="category">
                                    <label>Moneda:</label>
                                    <select2 :options="currencies" id="currency" data-toggle="tooltip"
                                        title="Seleccione el tipo de moneda (requerido)" v-model="record.currency_id">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="start_date">Fecha de Inicio:</label>
                                    <input type="date" id="start_date" placeholder="Fecha de Inicio"
                                        class="form-control input-sm" v-model="record.start_date" data-toggle="tooltip"
                                        title="Indique la fecha de inicio">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="end_date">Fecha Fin:</label>
                                    <input type="date" id="end_date" placeholder="Fecha Fin"
                                        class="form-control input-sm no-restrict" v-model="record.end_date"
                                        data-toggle="tooltip" title="Indique la fecha fin">
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
                            <button type="button" @click="createRecord('projecttracking/subprojects')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="responsable_name" slot-scope="props">
                                {{ props.row.responsable.first_name ? props.row.responsable.first_name :
                                    props.row.responsable.name }}
                                {{ props.row.responsable.last_name }}
                            </div>
                            <div slot="end_date" slot-scope="props">
                                {{ formatDate(props.row.end_date) }}
                            </div>

                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="show_info(props.row.id)"
                                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro"
                                    aria-label="Ver registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button @click="initUpdate(props.row.id, 'projecttracking/subprojects')"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar subproyecto"
                                    aria-label="Modificar subproyecto" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'projecttracking/subprojects')"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
                                    aria-label="Eliminar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                        <!-- Modal -->
                        <div id="show_subproject" class="modal fade" tabindex="-1" role="dialog"
                            aria-labelledby="ProjectTrackingSubProjectInfoModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg text-left" role="document"
                                style="max-width: 60rem; color: #636e7b; font-size: 13px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close closeModal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                        <h6 style="font-size: 1em">
                                            <i class="icofont icofont icofont-tasks ico-4x"></i>
                                            Información detallada del Subroyecto
                                        </h6>
                                    </div>
                                    <div class="modal-body">
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="general" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Proyecto:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="project"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Nombre:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="name_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Descripción:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="description_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Código:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="code_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Tipos de producto: </strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="product_types"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Responsable del Proyecto:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="responsable_name"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Monto de Financiamiento:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="financement_amount_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Fecha de Inicio:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="start_date_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Fecha Fin:</strong>
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <a id="end_date_id"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button"
                                                class="btn btn-default btn-sm btn-round btn-modal-close closeModal">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
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
                project_id: '',
                product_types: '',
                name: '',
                description: '',
                code: '',
                responsable_id: '',
                financement_amount: '',
                currency: '',
                currency_id: '',
                start_date: '',
                end_date: ''
            },
            dataInfo: '',
            payroll_staffs: [],
            projects_list: [],
            product_types_list: [],
            currencies: [],
            errors: [],
            records: [],
            columns: ['code', 'name', 'project_name', 'responsable_name', 'end_date', 'id'],
            payroll: ""
        }
    },
    methods: {
        async initUpdate(subproject_id, url) {
            const vm = this;
            try {
                url = vm.setUrl(`${url}/get-detail-subproject/${subproject_id}`);
                const response = await axios.get(url);
                vm.record = response.data.records;
                vm.record.product_types = response.data.selected_product_types;
            } catch (error) {
                console.error(error);
            }
        },
        async getProductTypesFromProject(project_id) {
            const vm = this;
            try {
                if (project_id) {
                    const url = `${window.app_url}/projecttracking/projects/${project_id}`;
                    const response = await axios.get(url);
                    vm.product_types_list ? vm.product_types_list = response.data.selected_product_types : [];
                }
            } catch (error) {
                console.error(error);
            }
        },
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Pedro Contreras <pdrocont@gmail.com>
         */
        reset() {
            this.record = {
                project_id: '',
                name: '',
                description: '',
                code: '',
                responsable_id: '',
                currency: '',
                currency_id: '',
                start_date: '',
                end_date: '',
                financement_amount: '',
                product_types: '',
            };
        },
        getPersonal() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-personal`).then(response => {
                vm.payroll_staffs = response.data;
            });
        },
        getProject() {
            const vm = this;
            axios.get(`${window.app_url}/projecttracking/get-projects`).then(response => {
                vm.projects_list = response.data;
            });
        },
        getCurrencies() {
            const vm = this;
            axios.get(`${window.app_url}/get-currencies/{currency_id?}`).then(response => {
                vm.currencies = response.data;
            });
        },
        formatDate(date) {
            const newdate = new Date(date);
            const yyyy = newdate.getFullYear();
            let mm = newdate.getMonth() + 1; // month is zero-based
            let dd = newdate.getDate();

            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;

            const formatted = dd + '/' + mm + '/' + yyyy;
            return formatted;
        },
        /**
         * Método que abre el modal, realiza la consulta y pasa los datos.
        */
        show_info(id) {
            // Transformar formato de fecha apropiado para la vista de la información del registro
            const formatDate = (date) => {
                const newdate = new Date(date);
                const yyyy = newdate.getFullYear();
                let mm = newdate.getMonth() + 1; // month is zero-based
                let dd = newdate.getDate();

                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;

                const formatted = dd + '/' + mm + '/' + yyyy;
                return formatted;
            };

            axios.get(`${window.app_url}/projecttracking/subprojects/get-detail-subproject/${id}`)
                .then(response => {
                    this.dataInfo = response.data.records;
                    let name = this.dataInfo.responsable.first_name ? this.dataInfo.responsable.first_name : this.dataInfo.responsable.name;
                    let formatted_start_date = this.dataInfo.start_date ? formatDate(this.dataInfo.start_date) : '';
                    let formatted_end_date = this.dataInfo.end_date ? formatDate(this.dataInfo.end_date) : '';
                    let typeProducts = [];
                    console.log(response.data.selected_product_types);

                    for (const productType of response.data.selected_product_types) {
                        typeProducts.push(productType.text);
                    }

                    $('#project').html(this.dataInfo.project.name);
                    $('#name_id').html(this.dataInfo.name);
                    $('#description_id').html(this.dataInfo.description);
                    $('#code_id').html(this.dataInfo.code);
                    $('#product_types').html(typeProducts.join(', '));
                    $('#responsable_name').html(name + ' ' + this.dataInfo.responsable.last_name);
                    $('#financement_amount_id').html((this.dataInfo.financement_amount && this.dataInfo.currency_id) ? this.dataInfo.currency.symbol + '. ' + this.dataInfo.financement_amount : 'N/A');
                    $('#start_date_id').html(formatted_start_date);
                    $('#end_date_id').html(formatted_end_date);
                });
            $('#show_subproject').modal('show');
        },
        /**
       * Inicializa los registros base del formulario
       *
       * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
       *
       * @param {string}  url       Ruta que obtiene los datos a ser mostrado en listados
       * @param {string}  modal_id  Identificador del modal a mostrar con la información solicitada
       */
        async initRecords(url, modal_id) {
            this.errors = [];
            if (typeof this.reset === 'function') {
                this.reset();
            }
            const vm = this;
            url = this.setUrl(url);

            await axios.get(url).then(response => {
                if (typeof (response.data.records) !== "undefined") {
                    vm.records = response.data.records;
                    vm.payroll = response.data.payroll;
                }
                if (modal_id) {
                    $(`#${modal_id}`).modal('show');
                }
            }).catch(error => {
                if (typeof (error.response) !== "undefined") {
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

            if (vm.payroll) {
                await vm.getPayrollStaffs();
            } else {
                await vm.getPersonal();
            }
        },
    },
    created() {
        this.table_options.headings = {
            code: 'Código',
            name: 'Nombre',
            project_name: 'Proyecto',
            responsable_name: 'Responsable',
            end_date: 'Fecha Fin',
            id: 'Acción'
        };
        this.table_options.sortable = ['code', 'name', 'project_name', 'responsable_name', 'end_date'];
        this.table_options.filterable = ['code', 'name', 'project_name', 'responsable_name', 'end_date'];
        this.table_options.columnsClasses = {
            code: 'col-md-1.5 text-center',
            name: 'col-md-2.5 text-center',
            project_name: 'col-md-2.5 text-center',
            responsable_name: 'col-md-2 text-center',
            end_date: 'col-md-2 text-center',
            id: 'col-md-1.5 text-center'
        };
    },
    mounted() {
        const vm = this;
        $("#add_projecttracking_subproject").on('show.bs.modal', function () {
            vm.reset();
            $('.closeModal').click(function () {
                $('#show_subproject').modal('hide');
            })
        });
        vm.getPersonal();
        vm.getProject();
        vm.getCurrencies();
    },
} 
</script>