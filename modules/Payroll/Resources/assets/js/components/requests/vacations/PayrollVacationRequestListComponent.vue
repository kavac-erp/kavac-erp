<template>
    <section id="payrollVacationRequestListComponent">
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
        >
            <div slot="date" slot-scope="props">
                <span>
                    {{ format_date(props.row.created_at, "DD/MM/YYYY") }}
                </span>
            </div>
            <div slot="payroll_staff" slot-scope="props">
                <span>
                    {{
                        props.row.payroll_staff
                            ? props.row.payroll_staff.id
                                ? props.row.payroll_staff.first_name +
                                  " " +
                                  props.row.payroll_staff.last_name
                                : "No definido"
                            : "No definido"
                    }}
                </span>
            </div>
            <div slot="status" slot-scope="props">
                <span v-if="props.row.status == 'approved'"> Aprobado </span>
                <span v-else-if="props.row.status == 'pending'">
                    Pendiente
                </span>
                <span v-else-if="props.row.status == 'suspended'">
                    Suspendido
                </span>
                <span v-else-if="props.row.status == 'rescheduled'">
                    Replanificado
                </span>
                <span v-else> Rechazado </span>
            </div>
            <div slot="reincorporation_date" slot-scope="props">
                <span>
                    {{
                        getDateOrMotive(props.row.status_parameters)
                            ? getDateOrMotive(props.row.status_parameters)[2] ==
                              "/"
                                ? getDateOrMotive(props.row.status_parameters)
                                : "N/A"
                            : "N/A"
                    }}
                </span>
            </div>
            <div slot="motive" slot-scope="props">
                <span>
                    {{
                        props.row.status != "suspended"
                            ? getDateOrMotive(props.row.status_parameters)
                                ? getDateOrMotive(
                                      props.row.status_parameters
                                  )[2] != "/"
                                    ? getDateOrMotive(
                                          props.row.status_parameters
                                      ).replace(/(<([^>]+)>)/gi, "")
                                    : "N/A"
                                : "N/A"
                            : "N/A"
                    }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <section class="d-inline-flex">
                    <payroll-vacation-request-show
                        :route_show="
                            app_url +
                            '/payroll/vacation-requests/show/' +
                            props.row.id
                        "
                        :id="props.row.id"
                    >
                    </payroll-vacation-request-show>
                    <button
                        @click.prevent="
                            suspendVacation(
                                'SuspendVacation',
                                props.row,
                                'PayrollSuspendVacation'
                            )
                        "
                        :disabled="props?.row?.status != 'approved'"
                        class="btn btn-secondary btn-xs btn-action btn-tooltip"
                        data-toggle="tooltip"
                        title="Suspender vacaciones"
                        aria-label="Suspender vacaciones"
                        v-has-tooltip
                        type="button"
                        data-placement="bottom"
                    >
                        <i
                            class="fa fa-file"
                            aria-label="Suspender vacaciones"
                        ></i>
                    </button>
                    <button
                        @click.prevent="
                            rescheduleVacation(
                                'RescheduleVacation',
                                props.row,
                                'PayrollRescheduleVacation',
                                props.row
                            )
                        "
                        :disabled="props?.row?.status != 'approved'"
                        class="btn btn-warning btn-xs btn-action btn-tooltip"
                        data-toggle="tooltip"
                        title="Replanificar vacaciones"
                        v-has-tooltip
                        type="button"
                        data-placement="bottom"
                    >
                        <i
                            class="fa fa-file"
                            aria-label="Replanificar vacaciones"
                        ></i>
                    </button>
                    <button
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        aria-label="Modificar registro"
                        v-has-tooltip
                        type="button"
                        :disabled="props.row.status != 'pending'"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        @click="deleteRecord(props.row.id, '')"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro"
                        data-toggle="tooltip"
                        aria-label="Eliminar registro"
                        v-has-tooltip
                        type="button"
                        :disabled="props.row.status != 'pending'"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <payroll-review-vacation-request-pending-form
                        :requestid="props.row.id"
                        :payroll_staff_id="props.row.payroll_staff_id"
                        :route_update="app_url + '/payroll/vacation-requests/'"
                        request_type="approved"
                        :requeststate="props.row.status"
                        :route_show="
                            app_url +
                            '/payroll/vacation-requests/show/' +
                            props.row.id
                        "
                    >
                    </payroll-review-vacation-request-pending-form>
                    <payroll-review-vacation-request-pending-form
                        :requestid="props.row.id"
                        :payroll_staff_id="props.row.payroll_staff_id"
                        :route_update="app_url + '/payroll/vacation-requests/'"
                        request_type="rejected"
                        :requeststate="props.row.status"
                        :route_show="
                            app_url +
                            '/payroll/vacation-requests/show/' +
                            props.row.id
                        "
                    >
                    </payroll-review-vacation-request-pending-form>
                </section>
            </div>
        </v-client-table>
        <payroll-reschedule-vacation ref="RescheduleVacation" :is_admin="true">
        </payroll-reschedule-vacation>
        <payroll-suspend-vacation ref="SuspendVacation">
        </payroll-suspend-vacation>
    </section>
</template>
<script>
export default {
    data() {
        return {
            record: {},
            records: [],
            columns: [
                "code",
                "date",
                "payroll_staff",
                "status",
                "reincorporation_date",
                "motive",
                "id",
            ],
        };
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            code: "Código",
            date: "Fecha de la solicitud",
            payroll_staff: "Trabajador",
            status: "Estatus de la solicitud",
            reincorporation_date: "Fecha de reincorporación",
            motive: "Motivo del rechazo",
            id: "Acción",
        };
        vm.table_options.sortable = [
            "code",
            "date",
            "payroll_staff",
            "status",
            "reincorporation_date",
            "motive",
        ];
        vm.table_options.filterable = [
            "code",
            "date",
            "payroll_staff",
            "status",
        ];
    },

    mounted() {
        const vm = this;
        vm.initRecords(vm.route_list, "");
    },
    methods: {
        suspendVacation(ref, record, modal, var_list = null) {
            const vm = this;
            vm.$refs[ref].record.payroll_vacation_request_id = record.id;
            vm.$refs[ref].record.payroll_vacation_request = record;
            vm.$refs[ref].getPendingDays();
            vm.$refs[ref].getHolidays();
            $(`#${modal}`).modal("show");
        },
        async rescheduleVacation(ref, record, modal, var_list = null) {
            const vm = this;
            vm.$refs[ref].id = record.id;
            vm.$refs[ref].record = record;
            vm.$refs[ref].payroll_staff = record.payroll_staff;
            if (vm.$refs[ref].id > 0) {
                vm.$refs[ref].showRecord(vm.$refs[ref].id);
            } else {
                vm.$refs[ref].record.created_at = vm.$refs[ref].format_date(
                    new Date(),
                    "YYYY-MM-DD"
                );
            }

            vm.$refs[ref].record.payroll_staff_id =
                vm.$refs[ref].payroll_staffs[1]?.id ??
                vm.$refs[ref].payroll_staffs[0].id;
            $(`#${modal}`).modal("show");
        },
        /**
         * Reescribe el método showRecord para cambiar su comportamiento por defecto
         * Método que muestra datos de un registro seleccionado
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param    {integer}    id    Identificador del registro a mostrar
         */
        async showRecord(id) {
            const vm = this;
            await axios
                .get(`${window.app_url}/payroll/vacation-requests/show/${id}`)
                .then((response) => {
                    vm.record = response.data.record;
                    vm.record.vacation_period_year = JSON.parse(
                        vm.record.vacation_period_year
                    );
                    vm.record.created_at = vm.format_date(
                        response.data.record.created_at,
                        "YYYY-MM-DD"
                    );
                });
        },
        /**
         * Método que carga los días feriados
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         */
        getHolidays() {
            const vm = this;
            let url = vm.setUrl("payroll/get-holidays");

            axios.get(url).then((response) => {
                if (typeof response.data !== "undefined") {
                    vm.holidays = response.data;
                }
            });
        },
        reset() {
            //
        },

        getDateOrMotive(parameter) {
            const vm = this;
            let parameter_ = JSON.parse(parameter);
            return parameter_
                ? parameter_[4] == "-"
                    ? vm.format_date(parameter_, "DD/MM/YYYY")
                    : parameter_
                : null;
        },
    },
};
</script>
