<template>
    <section id="PayrollGuardSchemeForm">
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="m-2">
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
            <!-- ./mensajes de error -->
            <div class="row">
                <!-- Organización -->
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Organización:</label>
                        <select2 :options="institutions" v-model="record.institution_id"></select2>
                    </div>
                </div>
                <!-- ./Organización -->
                <!-- Código de grupo de supervisados -->
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Código del grupo de supervisados:</label>
                        <select2 :options="payroll_supervised_groups"
                            @input="getDatasupervisedGroup()"
                            :disabled="(record.confirmed_periods && record.confirmed_periods.length > 0) ? true : false"
                            v-model="record.payroll_supervised_group_id"></select2>
                    </div>
                </div>
                <!-- ./Código de grupo de supervisados -->
                <!-- período a planificar -->
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Desde:</label>
                        <input type="date" id="from_date" placeholder="Desde"
                                data-toggle="tooltip" title="Indique la fecha inicial del período a planificar"
                                :min="minFromDateScheme"
                                :max="(record.to_date == '') ? '' : record.to_date"
                                class="form-control input-sm no-restrict" v-model="record.from_date">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Hasta:</label>
                        <input type="date" id="to_date" placeholder="Hasta"
                                data-toggle="tooltip" title="Indique la fecha final período a planificar"
                                @input="generateData"
                                :min="record.from_date"
                                :max="add_period(add_period(record.from_date, 1, 'years', 'YYYY-MM-DD'), -1, 'days', 'YYYY-MM-DD')"
                                :disabled="(record.from_date == '')"
                                class="form-control input-sm no-restrict" v-model="record.to_date">
                    </div>
                </div>
                <!-- ./período a planificar -->
            </div>
            <div class="row">
                <div class="col-md-4" v-if="record.payroll_supervised_group">
                    <div class="form-group">
                        <strong>Supervisor:</strong>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="supervisor">
                                {{ record.payroll_supervised_group.supervisor.name }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" v-if="record.payroll_supervised_group">
                    <div class="form-group">
                        <strong>Aprobador:</strong>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="Approver">
                                {{ record.payroll_supervised_group.approver.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tabla para la planificación de esquemas de guardias -->
            <div style="border-top: 1px solid #eeeeee !important;"
                v-if="'' !== record.from_date && '' !== record.to_date">
                <h6 class="text-center" style="text-transform: uppercase; color: #0073b7; padding-top: 24px;">Registros</h6>
                <div class="row col-md-12 justify-content-between d-flex">
                    <div class="form-group form-inline">
                        <div class="VueTables__search-field">
                            <label class="">Buscar:</label>
                            <input type="text" class="form-control" placeholder="Buscar..." v-model="search">
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="VueTables__limit-field">
                            <label class="">Registros</label>
                            <select2 :options="perPageValues" v-model="perPage"></select2>
                        </div>
                    </div>
                </div>
                <div style="overflow-y: auto; overflow-x: auto;">
                    <table class="table table-hover table-striped table-bordered table-responsive" style="display: table; height: 100%;">
                        <thead style="top: -2px">
                            <tr>
                                <th class="text-capitalize" rowspan="2">N°</th>
                                <th class="text-capitalize" style="min-width: 100px" rowspan="2">Ficha</th>
                                <th class="text-capitalize" style="min-width: 100px" rowspan="2">Trabajador</th>
                                <th class="text-capitalize" style="min-width: 100px"
                                    v-for="(month, index) in months" :key="index"
                                    :colspan="daysPerMonth[month].length">
                                    <span>{{ month }}</span>
                                </th>
                            </tr>
                            <tr>
                                <th v-for="(field, index) in totalDays" :key="index"
                                    class="text-capitalize cursor-pointer"
                                    :style="(field.view)
                                        ? 'min-width: 100px; background-color: white;'
                                        : 'min-width: 100px; background-color: darkgray;'"
                                    @click="getConfirmedDays(field) ? 'javascript:void(0)' : setEditColumns(index)">
                                    <span>{{ field['day'] + ' - ' + field['day_name'] }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="staff in visibleRows" :key="staff.id">
                                <td>{{ staff.index }}</td>
                                <td>{{ staff.worksheet_code }}</td>
                                <td>{{ staff.name }}</td>
                                <td v-for="(field, fIndex) in totalDays" :key="fIndex"
                                    class="td-with-border"
                                    :style="field.view ? 'cursor: auto;' : 'cursor: not-allowed;'">
                                    <div class="custom-multiselect" style="display: grid;"
                                        v-if="!field.view &&
                                            record.data_source[staff.id + '-' + field['month'] + '-' + field['day']] &&
                                            record.data_source[staff.id + '-' + field['month'] + '-' + field['day']].length > 0">
                                        <div class="btn-group" style="background-color: white; color: darkgray; white-space: nowrap;">
                                            <button id="custom-multiselect_button" type="button" class="btn btn-secondary dropdown-toggle text-left" data-toggle="dropdown" data-display="static" aria-expanded="false"
                                                    style="background-color: white; color: darkgray;">
                                                <div class="multiselect__tags" style="display: flex; flex-wrap: wrap;">
                                                    <div class="multiselect__tags-wrap" style="inline-grid"
                                                        v-for="(selection, index) in record.data_source[staff.id + '-' + field['month'] + '-' + field['day']]" :key="index">
                                                        <span class="multiselect__tag" :style="multiselectTag">
                                                            {{ selection.acronym }}
                                                            <span class="badge badge-light" :style="'left: 1rem;'"
                                                                v-if="selection.count > 1">
                                                                {{ selection.count }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <v-custom-multiselect
                                        v-else-if="field.view"
                                        track_by="acronym"
                                        :options="payroll_time_parameters"
                                        v-model="record.data_source[staff.id + '-' + field['month'] + '-' + field['day']]">
                                        <template v-slot:customOptionLabel="{ option }">
                                            <span>{{ option.text }}</span>
                                        </template>
                                    </v-custom-multiselect>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="VuePagination-2 row col-md-12 " v-if="lastPage > 1">
                    <nav class="text-center">
                        <ul class="pagination VuePagination__pagination" style="">
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk" v-if="page != 1">
                                <a class="page-link" @click="changePage(1)">PRIMERO</a>
                            </li>
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk disabled">
                                <a class="page-link">&lt;&lt;</a>
                            </li>
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-page" v-if="page > 1">
                                <a class="page-link" @click="changePage(page - 1)">&lt;</a>
                            </li>
                            <li :class="(page == number)
                                ? 'VuePagination__pagination-item page-item active'
                                : 'VuePagination__pagination-item page-item'"
                                :key="index"
                                v-for="(number, index) in filteredPageValues">
                                <a class="page-link active" role="button" @click="changePage(number)">{{number}}</a>
                            </li>
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-next-page" v-if="page < lastPage">
                                <a class="page-link" @click="changePage(page + 1)">&gt;</a>
                            </li>
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-next-chunk disabled">
                                <a class="page-link">&gt;&gt;</a>
                            </li>
                            <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk" v-if="lastPage != page">
                                <a class="page-link" @click="changePage(lastPage)">ÚLTIMO</a>
                            </li>
                        </ul>
                        <p class="VuePagination__count text-center col-md-12" style=""> </p>
                    </nav>
                </div>
            </div>
            <!-- Final de la tabla para la planificación de esquemas de guardias -->
        </div>
        <!-- Final card-body -->

        <!-- card-footer -->
        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset()">
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createScheme()"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round">
                <i class="fa fa-save"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </section>
</template>

<script>
    import moment from 'moment';
    import 'moment/locale/es';
    export default {
        props: {
            id: {
                type: Number,
                required: false,
                default: null
            },
        },
        data() {
            return {
                record: {
					id:                          '',
                    institution_id:              '',
                    from_date:                   '',
                    to_date:                     '',
                    payroll_supervised_group_id: '',
                    payroll_supervised_group:    null,
                    data_source:                 {},
				},
                payroll_supervised_groups: [],
                institutions: [],
				errors:  [],
                payroll_time_parameters: [],
                supervised_groups: {},
                months: [],
                editColumns: {},
                daysPerMonth: [],
                totalDays: [],
                pageValues: [1,2,3,4,5,6,7,8,9,10],
                lastPage: '',
                search: '',
                page: 1,
                perPage: 10,
                perPageValues: [
                    {
                        'id': 10,
                        'text': '10'
                    },
                    {
                        'id': 25,
                        'text': '25'
                    },
                    {
                        'id': 50,
                        'text': '50'
                    }
                ],
                multiselectTag: "white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
            }
        },
        methods: {
            /**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			reset() {
				const vm = this;
				vm.errors = [];
				vm.record = {
					id:                          '',
                    institution_id:              '',
                    from_date:                   '',
                    to_date:                     '',
                    payroll_supervised_group_id: '',
                    payroll_supervised_group:    null,
                    data_source:                 {},
				};
			},
            async getDatasupervisedGroup() {
                const vm = this;
                if ('' !== vm.record.payroll_supervised_group_id) {
                    vm.record.payroll_supervised_group = vm.payroll_supervised_groups.find(function ($group) {
                        return vm.record.payroll_supervised_group_id == $group['id'];
                    });
                } else {
                    vm.record.payroll_supervised_group = null;
                }
                await vm.generateData();
            },
            async generateData() {
                const vm = this;
                vm.months = [];
                vm.daysPerMonth = [];
                vm.totalDays = [];

                if (
                    '' === vm.record.from_date &&
                    '' === vm.record.to_date &&
                    vm.record.payroll_supervised_group
                ) {
                    return false;
                };

                vm.loading = true;
                let start_date = moment(vm.record.from_date);
                let end_date = moment(vm.record.to_date);

                while (start_date.isBefore(end_date) || start_date.isSame(end_date)) {
                    let str_date = start_date.format("MMMM").charAt(0).toUpperCase() + start_date.format("MMMM").slice(1);
                    if (!vm.months.includes(str_date)) {
                        vm.months.push(str_date);
                        vm.daysPerMonth[str_date] = [];
                    }
                    vm.daysPerMonth[str_date].push(start_date.format("D"));
                    vm.totalDays.push({
                        'month': str_date,
                        'day': start_date.format("D"),
                        'day_name': start_date.format("dddd"),
                        'view': false
                    });
                    start_date.add(1, 'day');
                }
                if (vm.record.payroll_supervised_group && vm.record.payroll_supervised_group.payroll_staffs) {
                    vm.page = 1;
                    vm.lastPage = Math.ceil(vm.record.payroll_supervised_group.payroll_staffs.length / vm.perPage);
                }
                vm.loading = false;
            },
            /**
             * Obtiene los datos de los trabajadores registrados agrupados por departamento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */
            async getPayrollTimeParameters() {
                const vm = this;
                vm.payroll_time_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?group=false`).then(response => {
                    vm.payroll_time_parameters = Object.values(response.data);
                });
            },
            changePage(page) {
                const vm = this;
                vm.page = page;
                var pag = 0;
                while(1) {
                    if (pag + 10 >= vm.page) {
                        pag += 1;
                        break;
                    } else {
                        pag += 10;
                    }
                }
                vm.pageValues = [];
                for (var i = 0; i < 10; i++) {
                    vm.pageValues.push(pag + i);
                }
            },
            setEditColumns(index) {
                const vm = this;
                vm.totalDays[index].view = !vm.totalDays[index].view;
            },

            /**
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Henry Paredes <hparedes@cenditel.gob.ve>
             *
             * @param  {integer} id Identificador del registro a ser modificado
             */
            async loadForm(id) {
                let vm = this;
                vm.errors = [];
                vm.getPayrollSupervisedGroups(id, 'scheme');
                let recordEdit = await axios.get(`${window.app_url}/payroll/guard-schemes/show/${id}`).then(response => {
                    return response.data.record;
                });

                vm.record = await recordEdit;
                await vm.getDatasupervisedGroup();
            },
            createScheme() {
                const vm = this;
                if(!Object.values(vm.record.data_source).some(arr => Array.isArray(arr) && arr.length > 0)){
                    bootbox.alert("Debe agregar al menos un parámetro de tiempo a la solicitud");
					return false;
				};
                vm.createRecord('payroll/guard-schemes');
            },
            getConfirmedDays(field) {
                const vm = this;
                if (vm.record.confirmed_periods && vm.record.confirmed_periods.length > 0) {
                    return vm.record.confirmed_periods.includes(field['month']+'-'+field['day']);
                } else {
                    return false;
                }
            }
        },
        created() {
            const vm = this;
            vm.reset();
            vm.getPayrollTimeParameters();
            vm.getInstitutions();
            if (vm.id) {
                vm.loadForm(vm.id);
            } else {
                vm.getPayrollSupervisedGroups();
            }
        },
        watch: {
            perPage(res) {
                const vm = this;
                let records = (vm.record.payroll_supervised_group)
                    ? vm.record.payroll_supervised_group.payroll_staffs
                        ? vm.record.payroll_supervised_group.payroll_staffs
                        : []
                    : [];

                this.lastPage = Math.ceil(records.length / this.perPage)
            },
            page(res) {
                this.changePage(res);
            }
        },
        computed: {
            filteredPageValues() {
                return this.pageValues.filter(number => number <= this.lastPage);
            },
            visibleRows() {
                const vm = this;
                let records = (vm.record.payroll_supervised_group)
                    ? vm.record.payroll_supervised_group.payroll_staffs
                        ? vm.record.payroll_supervised_group.payroll_staffs
                        : []
                    : [];
                if (''!= vm.search) {
                    vm.page = 1;
                    records = records.filter(function (staff) {
                        return (
                            staff.name.toLowerCase().includes(vm.search.toLowerCase()) ||
                            staff.worksheet_code.toLowerCase().includes(vm.search.toLowerCase())
                        );
                    })
                }

                const startIndex = (vm.page - 1) * vm.perPage;
                const endIndex = startIndex + parseInt(vm.perPage);
                vm.lastPage = Math.ceil(records.length / vm.perPage);

                return records.slice(startIndex, endIndex).map((staff, index) => ({
                    ...staff,
                    index: startIndex + index + 1
                }));
            },
            start_operations_date() {
                const vm = this;
                if ('' !== vm.record.institution_id) {
                    return vm.institutions.find(function ($inst) {
                        return vm.record.institution_id == $inst['id'];
                    })?.start_operations_date;
                }
                return '';
            },
            minFromDateScheme() {
                const vm = this;
                if (
                    '' !== vm.record.payroll_supervised_group &&
                    vm.record.payroll_supervised_group &&
                    vm.record.payroll_supervised_group.last_date_guard_scheme
                ) {
                    return vm.add_period(vm.record.payroll_supervised_group.last_date_guard_scheme, 1, 'days', 'YYYY-MM-DD');
                } else {
                    return vm.start_operations_date;
                }
            }
        },
    };
</script>
