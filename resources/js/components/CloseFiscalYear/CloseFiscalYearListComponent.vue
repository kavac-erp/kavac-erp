<template>
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardSearchCloseFiscalYearForm">
                <div class="card-header">
                    <h6 class="card-title">
                        Listado de cierre de ejercicios económicos
                        <a href="javascript:void(0)" title="haz click para ver la ayuda guiada de este elemento"
                           data-toggle="tooltip" class="btn-help" @click="initUIGuide(helpFile)">
                            <i class="ion ion-ios-help-outline cursor-pointer"></i>
                        </a>
                    </h6>
                    <div class="card-btns">
                        <div class="d-inline-flex">
                            <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                               title="Ir atrás" data-toggle="tooltip">
                                <i class="fa fa-reply"></i>
                            </a>
                            <a href="javascript:void(0)" class="card-minimize btn btn-card-action btn-round"
                               title="Minimizar" data-toggle="tooltip">
                                <i class="now-ui-icons arrows-1_minimal-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableOptions">
                        <div slot="approved" slot-scope="props">
                            <span class="badge badge-success" v-show="props.row.resource_entries[close_account.id].entries.approved"><strong>Aprobado</strong></span>
                            <span class="badge badge-danger" v-show="!props.row.resource_entries[close_account.id].entries.approved"><strong>No Aprobado</strong></span>
                        </div>
                        <div slot="id" slot-scope="props" class="text-center">
                            <button @click.prevent="setDetails('CloseFiscalYear', props.row.id, 'CloseFiscalYearInfo')"
                                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                                    title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button class="btn btn-primary btn-xs btn-icon btn-action" data-toggle="tooltip"
                                    @click="OpenPdf(getUrlReport(), '_blank')" title="Generar reporte de estado de resultado del año fiscal"
                                    data-placement="bottom" type="button">
                                <i class="fa fa-file-pdf-o"></i>
                            </button>
                            <button @click="!props.row.resource_entries[close_account.id].entries.approved ? approve(props.row.id) : ''"
                                    class="btn btn-success btn-xs btn-icon btn-action" title="Aprobar Registro" data-toggle="tooltip"
                                    :disabled="props.row.resource_entries[close_account.id].entries.approved" v-has-tooltip >
                                <i class="fa fa-check"></i>
                            </button>
                            <button @click="deleteRecord(props.row.id, 'close-fiscal-year/registers')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro" data-toggle="tooltip"
                                    type="button"
                                    :disabled="props.row.resource_entries[close_account.id].entries.approved">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </div>
                    </v-client-table>
                    <close-fiscal-year-info
                        ref="CloseFiscalYear">
                    </close-fiscal-year-info>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['year', 'approved', 'id'],
                close_account: '',
            }
        },
        created() {
            this.table_options.headings = {
                'year': 'Año de ejercicio',
                'approved': 'Estatus',
                'id': 'Acción'
            };
            this.table_options.sortable = ['year', 'approved'];
            this.table_options.filterable = ['year', 'approved'];
            this.table_options.columnsClasses = {
                'year': 'col-md-4 text-center',
                'approved': 'col-md-4 text-center',
                'id': 'col-md-4 text-center'
            };
        },
        mounted() {
            this.initRecords(this.route_list, '');
        },
        props: {
            fiscal_year: {
                type: String,
            },
            currency_id: {
                type: String,
            },
        },
        methods: {
            /**
             * Inicializa los datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                //
            },

            /**
             * Inicializa los registros base del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param {string}  url       Ruta que obtiene los datos a ser mostrado en listados
             * @param {string}  modal_id  Identificador del modal a mostrar con la información solicitada
             */
            initRecords(url, modal_id) {
                this.errors = [];
                if (typeof this.reset === 'function') {
                    this.reset();
                }
                const vm = this;
                url = this.setUrl(url);

                axios.get(url).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        vm.records = response.data.records;
                        vm.close_account = response.data.close_account;
                    }
                    if (modal_id) {
                        $(`#${modal_id}`).modal('show');
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
             * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
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
                    for(let i in var_list){
                        vm.$refs[ref][i] = var_list[i];
                    }
                } else {
                    vm.$refs[ref].record = vm.$refs.tableOptions.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$refs[ref].id = id;

                $(`#${modal}`).modal('show');
            },

            /**
             * Se aprueba el cierre de ejercicio
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            approve(id) {
                let url = `${window.app_url}/close-fiscal-year/registers/approve`;
                let records = this.records;
                let confirmated = false;

                const vm = this;

                bootbox.confirm({
                    title: '¿Aprobar el cierre de ejercicio?',
                    message: 'Debe tener en cuenta que al realizar el cierre se actualizara el año fiscal y no podrá regresar.',
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },

                    callback: function(result) {
                        if (result) {
                            confirmated = true;
                            vm.loading = true;
                            axios.post(url + '/' + id).then(response => {
                                if (typeof(response.data.error) !== 'undefined') {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'danger', 'screen-error', response.data.message);
                                    return false;
                                }

                                vm.showMessage('update');
                                vm.loading = false;
                                window.location.reload();
                            }).catch(() => {
                                console.log(error);
                            });
                        }
                    }
                });
            },

            /**
             * Abre una nueva ventana en el navegador
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             * @param  {string} url para la nueva ventana
             * @param  {string} type tipo de ventana que se desea abrir
             * @return {boolean} Devuelve falso si no se ha indicado alguna información requerida
             */
            OpenPdf: function(url, type) {
                const vm = this;

                if (!url) {
                    return;
                }

                url = vm.setUrl(url).replace('/pdf', '/pdfVue');
                vm.loading = true;

                axios.get(url).then(response => {
                    if (response.data.result) {
                        url = url.split('/pdf')[0];
                        url += '/' + response.data.id;
                        window.open(url, type);
                    }
                    vm.loading = false;
                });
            },

            /**
             * Formatea la url para el reporte
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * @return {string} url para el reporte
             */
            getUrlReport: function() {
                let url = `${window.app_url}/accounting/report/StateOfResults/pdf/`;
                let year = this.records.length > 0 ? this.records[0]['year'] : this.fiscal_year;

                return (url + (year + '-' + '')) + '/' + '7' + '/' + this.currency_id + '/' + '';
            },
        }
    };
</script>
