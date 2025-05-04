<template>
    <div id="PayrollSuspendVacation" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="PayrollEmploymentInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Suspensión de vacaciones
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger" v-if="errors.length > 0">
                        <div class="container">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                            <button type="button" class="close" data-dismiss="alert"
                                aria-label="Eliminar lista de errores" @click.prevent="errors = []">
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
                        <div class="col-md-4" id="helpPayrollSuspensionVacationDate">
                            <div class="form-group is-required" style="z-index: unset;">
                                <label for="date_request">Fecha de suspensión:</label>
                                <input type="date" id="date_request" data-toggle="tooltip" title="Fecha de suspensión"
                                    class="form-control input-sm no-restrict" v-model="record.date_request" @input="calculateDaysEnjoyed()">
                                    <!--
                                    :min="record.payroll_vacation_request?.start_date" :max="record.payroll_vacation_request?.end_date"-->
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="enjoyed_days">Días efectivamente disfrutados:</label>
                                <input id="enjoyed_days" type="text" placeholder="días efectivamente disfrutados"
                                    data-toggle="tooltip" title="Indique los días efectivamente disfrutados(requerido)"
                                    readonly class="form-control input-sm" v-model="record.enjoyed_days">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="suspension_reason">Motivo de suspensión:</label>
                                <input id="suspension_reason" type="text" placeholder="motivo de suspensión"
                                    data-toggle="tooltip" title="Indique la razón de la suspensión (requerido)"
                                    class="form-control input-sm" v-model="record.suspension_reason">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="file">Adjunte un archivo:</label>
                                <input id="file" name="file" type="file"
                                    accept=".doc, .docx, .odt, .pdf, .png, .jpg, .jpeg" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pending_days">Días totales de la solicitud del período vacacional:</label>
                                <div>{{ pending_days }} días</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" @click="reset()"
                            data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" @click="createRecord('payroll/suspension-vacation-requests')"
                            class="btn btn-primary btn-sm btn-round btn-modal-save">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            record: {
                date_request: '',
                enjoyed_days: '',
                suspension_reason: '',
                payroll_vacation_request_id: '',
                payroll_vacation_request: '',
            },
            file: '',
            pending_days: 0,
            holidays: [],
            holidaysCount: 0,
            errors: [],
            minDate: '',
            maxDate: ''
        }
    },
    methods: {
        async createRecord(url, list = true, reset = true) {
            const vm = this;
            vm.loading = true;
            var fields = {};

            for (var index in vm.record) {
                if (index == "payroll_vacation_request") {
                    continue;
                }
                fields[index] = vm.record[index];
            }

            if (fields["enjoyed_days"] > vm.pending_days) {
                vm.errors = [];
                vm.errors.push('El numero de dias efectivamente disfrutados debe ser menor o igual que los dias totales de la solicitud del período vacacional');
                vm.loading = false;
                return;
            }

            fields["missing_days"] = vm.pending_days - fields["enjoyed_days"];
            url = vm.setUrl(url);
            axios.post(url, fields).then(response => {
                if (typeof (response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
                else {
                    vm.errors = [];
                    if (reset) {
                        vm.reset();
                    }
                    if (list) {
                        vm.readRecords(url);
                    }
                    vm.loading = false;
                    vm.showMessage('store');
                }

            }).catch(error => {
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
                vm.loading = false;
            });
            vm.loading = false;
        },

        getPendingDays() {
            let vm = this;
            vm.pending_days = vm.record.payroll_vacation_request?.days_requested;

        },

        /**
         * Método que carga los días feriados
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         */
         getHolidays() {
            const vm = this;
            let url = vm.setUrl('payroll/get-holidays');

            axios.get(url).then(response => {
                if (typeof (response.data) !== "undefined") {
                    vm.holidays = response.data;
                }
            });
        },

        calculateDaysEnjoyed() {
            let vm = this;

            let start_date = new Date(document.getElementById('date_request').value.replaceAll('-', '/'));
            let ed_value = vm.record.payroll_vacation_request?.end_date;
            let end_date = new Date(ed_value.replaceAll('-', '/'));
            let diff = end_date.getTime() - start_date.getTime();
            let dias = diff / (1000 * 60 * 60 * 24);
            vm.holidaysCount = 0;

            const sumarLaborables = (f, n) => {
                for (var i = 0; i < n; i++) {
                    f.setTime(f.getTime() + (1000 * 60 * 60 * 24));

                    if (i == 0 && f.getDay() == 0) {
                        dias--;
                    } else if (i == 0 && f.getDay() == 1) {
                        dias--;
                    }

                    /** Se identifica si existen sabados o domingos en el periodo establecido */
                    if ((f.getDay() == 6) || (f.getDay() == 0)) {
                        /** Si existe un dia no laborable se hace el bucle una unidad mas larga */
                        dias--;
                    } else if (vm.holidays.length > 0) {
                        for (let holiday of vm.holidays) {
                            if (holiday.text != 'Seleccione...') {
                                let holidayDate = new Date(holiday.text);
                                holidayDate.setTime(holidayDate.getTime() + (1000 * 60 * 60 * 24));
                                if (holidayDate.getTime() >= f.getTime() && holidayDate < (f.getTime() + (1000 * 60 * 60 * 24))) {
                                    dias--;
                                    vm.holidaysCount++;
                                }
                            }
                        }
                    }
                }
            }

            sumarLaborables(start_date, dias);
            if (document.getElementById('date_request').value == '') {
                return;
            } else {
                vm.record.enjoyed_days = vm.pending_days - (dias + 1);
            }
        },

        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Natanael Rojo <rojonatanael99@gmail.com>
         */
        reset() {
            this.record = {
                date_request: '',
                enjoyed_days: '',
                suspension_reason: '',
            }
            this.file = '';
            this.pending_days = 0;
            document.querySelector("#file").value = '';
            this.errors = [];
        },

        /**
         * Método que cambia el formato de visualización de la fecha a
         * dd/mm/yyyy.
         *
         * @method convertDate
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         *
         * @param {dateString} dateString fecha ha ser fornateada
         */
        convertDate(dateString) {
            if (!dateString) {
                // Devuelve una cadena vacía si dateString es nulo o vacío.
                return "";
            }
            const dateParts = dateString.split("-");
            const year = dateParts[0];
            const month = dateParts[1];
            const day = dateParts[2];
            return `${day}/${month}/${year}`;
        },
    }
}
</script>
