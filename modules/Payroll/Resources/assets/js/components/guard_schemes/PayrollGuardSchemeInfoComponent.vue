<template>
    <div
        id="PayrollGuardSchemeInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollGuardSchemeInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:80%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Información Detallada del Esquema de Guardias
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Desde:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.from_date, 'DD/MM/YYYY') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Hasta:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.to_date, 'DD/MM/YYYY') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Código del grupo de supervisados:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group
                                                    ? record.payroll_supervised_group.text
                                                        ? record.payroll_supervised_group.text
                                                        : record.payroll_supervised_group.code
                                                            ? record.payroll_supervised_group.code
                                                            : ''
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Supervisor:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group
                                                    ? record.payroll_supervised_group.supervisor.name
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Aprobador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group
                                                    ? record.payroll_supervised_group.approver.name
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Estatus:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.document_status ?
                                                    record.document_status.name :
                                                    ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tabla para la planificación de esquemas de guardias -->
                            <div v-if="'' !== record.from_date && '' !== record.to_date"
                                style="border-top: 1px solid #eeeeee !important;">
                                <h6 class="text-center"
                                    style="text-transform: uppercase;
                                    color: #0073b7;
                                    padding-top: 24px;">
                                    Registros
                                </h6>
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
                                                    style="min-width: 100px; background-color: darkgray;">
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
                                                    style="cursor: auto;">
                                                    <div class="custom-multiselect" style="display: grid;"
                                                        v-if="!field.view &&
                                                            record.data_source[staff.id + '-' + field['month'] + '-' + field['day']] &&
                                                            record.data_source[staff.id + '-' + field['month'] + '-' + field['day']].length > 0">
                                                        <div class="btn-group" style="background-color: white; color: darkgray; white-space: nowrap;">
                                                            <button id="custom-multiselect_button" type="button" class="btn btn-secondary dropdown-toggle text-left" data-toggle="dropdown" data-display="static" aria-expanded="false"
                                                                    style="background-color: white; color: darkgray;">
                                                                <div class="multiselect__tags" style="display: flex; flex-wrap: wrap; cursor: auto;">
                                                                    <div class="multiselect__tags-wrap" style="inline-grid"
                                                                        v-for="(selection, index) in record.data_source[staff.id + '-' + field['month'] + '-' + field['day']]" :key="index">
                                                                        <span class="multiselect__tag" :style="'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex;'">
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
    import moment from 'moment';
    import 'moment/locale/es';
    export default {
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
                errors: [],
                payroll_supervised_groups: [],
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
            }
        },
        methods: {
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
            async getDatasupervisedGroup() {
                const vm = this;
                if ('' !== vm.record.payroll_supervised_group_id) {
                    let payroll_supervised_group = vm.payroll_supervised_groups.find(function ($group) {
                        return vm.record.payroll_supervised_group_id == $group['id'];
                    });
                    Vue.set(vm.record.payroll_supervised_group, 'payroll_staffs', payroll_supervised_group['payroll_staffs']);
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
                if (vm.record.payroll_supervised_group) {
                    vm.page = 1;
                    vm.lastPage = Math.ceil(vm.record.payroll_supervised_group.payroll_staffs.length / vm.perPage);
                }
                vm.loading = false;
            },
        },
        mounted() {
            const vm = this;

            $("#PayrollGuardSchemeInfo").on('show.bs.modal', function() {
                vm.getPayrollSupervisedGroups(vm.record.id, 'scheme').then(() => {
                    vm.getDatasupervisedGroup();
                });
            });
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
                const endIndex = startIndex + vm.perPage;
                vm.lastPage = Math.ceil(records.length / vm.perPage);

                return records.slice(startIndex, endIndex).map((staff, index) => ({
                    ...staff,
                    index: startIndex + index + 1
                }));
            },
        },
    }
</script>
