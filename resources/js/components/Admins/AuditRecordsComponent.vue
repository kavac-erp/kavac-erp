<template>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        Auditoría de registros
                        <a
                            href="javascript:void(0)"
                            title="haz click para ver la ayuda guiada de este elemento"
                            data-toggle="tooltip"
                            class="btn-help"
                            @click="initUIGuide(helpFile)"
                        >
                            <i class="ion ion-ios-help-outline cursor-pointer"></i>
                        </a>
                    </h6>
                    <div class="card-btns">
                        <a
                            href="#"
                            class="btn btn-sm btn-primary btn-custom"
                            @click="redirect_back(route_list)"
                            title="Ir atrás" data-toggle="tooltip"
                        >
                            <i class="fa fa-reply"></i>
                        </a>
                        <a
                            href="javascript:void(0)"
                            class="card-minimize btn btn-card-action btn-round"
                            title="Minimizar" data-toggle="tooltip"
                        >
                            <i class="now-ui-icons arrows-1_minimal-up"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Filtros</b>
                        </div>
                        <div id="helpAuditFilterFromDate" class="form-group col-md-2">
                            <label for="" class="form-label">
                                Desde la fecha
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                data-toggle="tooltip"
                                title="Desde la fecha"
                                v-model="start_date"
                                id="auditStartDate"
                                placeholder="Fecha"
                            >
                        </div>
                        <div id="helpAuditFilterToDate" class="form-group col-md-2">
                            <label for="" class="form-label">
                                Hasta la fecha
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                data-toggle="tooltip"
                                title="Hasta la fecha"
                                v-model="end_date"
                                id="auditEndDate"
                                placeholder="Fecha"
                            >
                        </div>
                        <div id="helpAuditFilterUser" class="form-group col-md-2">
                            <label for="" class="form-label">
                                Consulta por usuario
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                data-toggle="tooltip"
                                v-model="user"
                                title="Consulta por usuario"
                                placeholder="Usuario"
                            >
                        </div>
                        <div
                            id="helpAuditFilterModule"
                            class="form-group col-md-2"
                        >
                            <label for="" class="form-label">
                                Consulta por módulo
                            </label>
                            <select
                                id="restoreSearchModule"
                                class="form-control select2"
                                v-model="module_restore"
                            >
                                <option value="">Módulo</option>
                                <option
                                    :value="mod.originalName"
                                    v-for="(mod, index) in modules2"
                                    :key="index"
                                >
                                    {{ mod.name }}
                                </option>
                            </select>
                        </div>
                        <div
                            id="helpAuditFilterButton"
                            class="form-group col-md-2"
                        >
                            <button
                                type="button"
                                class="btn btn-info btn-icon btn-xs-responsive px-3"
                                data-toggle="tooltip"
                                title="Buscar registros del sistema"
                                @click="readRecords"
                            >
                                <i class="fa fa-search"></i>
                            </button>
                            <br>
                            <button
                                type="reset"
                                class="btn btn-default btn-icon btn-xs-responsive px-3"
                                aria-label="Search"
                                @click="resetFilters()"
                                title="Limpiar filtro"
                            >
                                <i class="fa fa-eraser"></i>
                            </button>
                        </div>
                    </div>
                    <hr>
                    <div id="helpAuditLeyend" class="row mg-bottom-20">
                        <div class="col-12 panel-legend" id="helpAuditLeyendNew">
                            <i
                                class="ion-android-checkbox-blank text-success"
                                title="Registros nuevos"
                                data-toggle="tooltip"
                            ></i>
                            <span>Nuevos</span>
                        </div>
                        <div class="col-12 panel-legend" id="helpAuditLeyendUpdate">
                            <i
                                class="ion-android-checkbox-blank text-warning"
                                title="Registros actualizados"
                                data-toggle="tooltip"
                            ></i>
                            <span>Actualizados</span>
                        </div>
                        <div class="col-12 panel-legend" id="helpAuditLeyendRestore">
                            <i
                                class="ion-android-checkbox-blank text-info"
                                title="Registros reestablecidos"
                                data-toggle="tooltip"
                            ></i>
                            <span>Restaurados después de eliminación</span>
                        </div>
                        <div class="col-12 panel-legend" id="helpAuditLeyendDelete">
                            <i
                                class="ion-android-checkbox-blank text-danger"
                                title="Registros eliminados"
                                data-toggle="tooltip"
                            ></i>
                            <span>Eliminados</span>
                        </div>
                    </div>
                    <div id="helpAuditTable" class="row">
                        <div class="col-12">
                            <v-server-table
                                :url="'app/audit-records'"
                                :columns="columns"
                                :options="table_options"
                                ref="tableResults"
                            >
                                <div
                                    slot="status"
                                    slot-scope="props"
                                    v-html="props.row.status"
                                    class="text-center"
                                ></div>
                                <div
                                    slot="date"
                                    slot-scope="props"
                                    v-html="props.row.date"
                                    class="text-center"
                                ></div>
                                <div
                                    slot="ip"
                                    slot-scope="props"
                                    v-html="props.row.ip"
                                    class="text-center"
                                ></div>
                                <div
                                    slot="users"
                                    slot-scope="props"
                                    v-html="props.row.users"
                                ></div>
                                <div
                                    slot="id"
                                    slot-scope="props"
                                    class="text-center"
                                >
                                    <button
                                        @click="details(props.row.id)"
                                        class="btn btn-info btn-xs btn-icon btn-action"
                                        title="Ver detalles del registro"
                                        data-toggle="tooltip"
                                        type="button"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </v-server-table>
                        </div>
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
            start_date: '',
            end_date: '',
            user: '',
            module_restore: '',
            records: [],
            columns: [
                'status',
                'date',
                'ip',
                'module',
                'users',
                'id'
            ],
            modules2: '',
        }
    },
    props: {
        modules: {
            type: Array,
            required: false,
            default: null
        }
    },
    watch: {
        start_date: function() {
            const vm = this;
            $('#auditEndDate').attr('min', vm.start_date);
        },
        end_date: function() {
            const vm = this;
            if (vm.end_date) {
                $('#auditStartDate').attr('max', vm.end_date);
            } else {
                if (!$('#auditStartDate').hasClass('no-restrict')) {
                    $('#auditStartDate').attr('max', vm.getCurrentDate());
                }
            }
        }
    },
    methods: {
        /**
         * Método para reestablecer valores iniciales del formulario de filtros.
         *
         * @method resetFilters
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
         */
        resetFilters() {
            const vm = this;
            vm.user = '';
            vm.start_date = '';
            vm.end_date = '';
        },

        /**
         * Método que obtiene los registros a mostrar
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta que obtiene todos los registros solicitados
         */
        async readRecords() {
            const vm = this;
            vm.loading = true;
            vm.$refs.tableResults.limit = vm.table_options.perPage;
            vm.$refs.tableResults.refresh();
            vm.loading = false;
        },

        /**
         * Muestra los detalles de un registro seleccionado
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param     {string}    id    Identificador del registro para el cual se desea mostrar los detalles
         */
        async details(id) {
            const vm = this;
            vm.loading = true;
            await axios.post('/app/audit-details', {
                id: id
            }).then(response => {
                if (response.data.result) {
                    let audit = response.data.audit;
                    let eventType = audit.event;
                    let eventText = '';
                    let className = '';
                    let prevRecord = 'N/A';
                    let nextRecord = 'N/A';

                    if (eventType === 'created') {
                        eventText = 'NUEVO';
                        className = 'success';
                    } else if (eventType === 'deleted') {
                        eventText = 'ELIMINADO';
                        className = 'danger';
                    } else if (eventType === 'restored') {
                        eventText = 'RESTAURADO';
                        className = 'info';
                    } else if (eventType === 'updated') {
                        eventText = 'ACTUALIZADO';
                        className = 'warning';
                    }

                    if (audit.old_values) {
                        prevRecord = '';
                        Object.keys(audit.old_values).forEach(key => {
                            prevRecord += `<b>${key}:</b> ${audit.old_values[key]}<br>`;
                        });
                    }
                    if (audit.new_values) {
                        nextRecord = '';
                        Object.keys(audit.new_values).forEach(key => {
                            nextRecord += `<b>${key}:</b> ${audit.new_values[key]}<br>`;
                        });
                    }

                    bootbox.dialog({
                        title: 'Registro',
                        message:    `<div class="row text-justify">
                                        <div class="col-12">
                                            <p>
                                                <span class="badge badge-${className} mr-1">${eventText}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <h5>Datos anteriores</h5>
                                            <div>${prevRecord}</div>
                                        </div>
                                        <div class="col-md-6 text-left">
                                            <h5>Datos nuevos</h5>
                                            <div>${nextRecord}</div>
                                        </div>
                                    </div>`,
                        size: 'large',
                        buttons: {
                            ok: {
                                label: 'Cerrar',
                                className: 'btn-primary',
                                callback: function() {
                                    //
                                }
                            }
                        }
                    });
                }
            }).catch(error => {
                console.error(error);
            });
            vm.loading = false;
        }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'status': 'Estatus',
            'date': 'Fecha - Hora',
            'ip': 'IP',
            'module': 'Módulo',
            'users': 'Usuario',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['date', 'ip', 'module', 'users'];
        vm.table_options.filterable = ['date', 'ip', 'module', 'users'];
        vm.table_options.columnsClasses = {
            'status': 'col-md-1',
            'date': 'col-md-2',
            'ip': 'col-md-1',
            'module': 'col-md-5',
            'users': 'col-md-2',
            'id': 'col-md-1'
        };
        vm.table_options.requestFunction = function(data) {
            return axios.post('/app/audit-records', {
                query: {
                    start_date: vm.start_date,
                    end_date: vm.end_date,
                    user: vm.user,
                    module_restore: vm.module_restore
                },
                limit: data.limit,
                ascending: data.ascending,
                page: data.page,
                orderBy: data.orderBy
            }).catch(error => {
                console.error(error);
            });
        };

        // Agregamos la "Aplicación base" al inicio del arreglo de los módulos.
        vm.modules.unshift({
            originalName: "App",
            alias: "App",
            name: "Aplicación base",
            installed: true,
            disabled: false,
            withSetting: false
        });

        // Actualizamos la lista de módulos del select "Módulo" de los filtros.
        vm.modules2 = vm.modules;
    },
    mounted() {
        const vm = this;
        $('#restoreSearchModule').on('change', function() {
            vm.module_restore = $(this).val();
        });
    }
};
</script>
