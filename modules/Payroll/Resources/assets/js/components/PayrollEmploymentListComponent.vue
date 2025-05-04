<template>
    <div>
        <v-server-table :columns="columns" :options="table_options"
            url="/payroll/employments/show/vue-list" ref="tableResults">
            <div slot="institution_email" slot-scope="props" class="text-center">
                {{
            props.row.institution_email
                ? props.row.institution_email
                : ""
        }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="
        setDetails(
            'EmploymentInfo',
            props.row.id,
            'PayrollEmploymentInfo'
        )
            " class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip" title="Modificar registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, route_delete)"
                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span v-if="props.row.active" class="text-success font-weight-bold">
                    SI
                </span>
                <span v-else class="text-danger font-weight-bold">NO</span>
            </div>
            <div slot="department_id" slot-scope="props">
                <span>
                    {{ props.row.department.name }}
                </span>
            </div>
            <div slot="payroll_position_id" slot-scope="props">
                <span>
                    {{ props.row.payroll_positions[0].name }}
                </span>
            </div>
        </v-server-table>
        <payroll-employment-info ref="EmploymentInfo"></payroll-employment-info>
    </div>
</template>
<script>
export default {
    data() {
        return {
            record: [],
            fiscal_year: '',
            fiscal_date: '',
            columns: [
                'payroll_staff.first_name',
                'payroll_staff.last_name',
                'payroll_staff.id_number',
                'institution_email',
                'active',
                'department_id',
                'payroll_position_id',
                'id'
            ],
        }
    },

    created() {
        this.getFiscalYear();

        this.table_options.headings = {
            'payroll_staff.first_name': 'Nombres',
            'payroll_staff.last_name': 'Apellidos',
            'payroll_staff.id_number': 'Cédula de identidad',
            'institution_email': 'Correo Electrónico Institucional',
            'active': '¿Está Activo?',
            'department_id': 'Departamento',
            'payroll_position_id': 'Cargo',
            'id': 'Acción'
        };
        this.table_options.sortable = [
            'payroll_staff.first_name',
            'institution_email',
            'active'
        ];
        this.table_options.filterable = [
            'payroll_staff.first_name',
            'payroll_staff.last_name',
            'payroll_staff.id_number',
            'institution_email',
            'is_active',
            'department.name',
            'payroll_positions.0.name',
        ];
        this.table_options.columnsClasses = {
            'payroll_staff.first_name': 'col-md-2',
            'payroll_staff.last_name': 'col-md-2',
            'id': 'col-md-2'
        };
    },

    methods: {
        /**
         * Método que establece los datos del registro seleccionado para el
         * cual se desea mostrar detalles.
         *
         * @method    setDetails
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         * @param     object  var_list  Objeto con las variables y valores a
         * asignar en las variables del componente
         */
        setDetails(ref, id, modal, var_list = null) {
            const vm = this;
            if (var_list) {
                for (var i in var_list) {
                    vm.$refs[ref][i] = var_list[i];
                }
            } else {
                vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                    return r.id === id;
                })[0];
            }
            vm.$refs[ref].id = id;

            $(`#${modal}`).modal('show');

            vm.antiquity(vm.$refs[ref].record);
            vm.time_worked(vm.$refs[ref].record);
            vm.diff_dates(vm.$refs[ref].record.start_date, vm.$refs[ref].record);
            vm.time_apn(vm.$refs[ref].record);
        },

        /**
         * Método que calcula los años en otras instituciones públicas
         *
         * @method     antiquity
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        antiquity(record) {
            const vm = this;
            record.years_apn = 0;
            let data_years = 0;
            let data_months = 0;
            let data_days = 0;
            let years = 0;
            let months = 0;
            let days = 0;

            if (record.payroll_previous_job) {
                for (let job of record.payroll_previous_job) {
                    if (job.payroll_sector_type.name == 'Público') {
                        let now = job.start_date;
                        let ms = moment(job.end_date, "YYYY-MM-DD HH").diff(moment(now, "YYYY-MM-DD"));
                        let d = moment.duration(ms);

                        if (d._data.years < 0) {
                            data_years = d._data.years * -1;
                        } else {
                            data_years = d._data.years;
                        }
                        if (d._data.months < 0) {
                            data_months = d._data.months * -1;
                        } else {
                            data_months = d._data.months
                        }
                        if (d._data.days < 0) {
                            data_days = d._data.days * -1;
                        } else {
                            data_days = d._data.days
                        }

                        years += data_years;
                        months += data_months;
                        days += data_days;

                        if (months > 12) {
                            months = months % 12;
                            years = years + 1;
                        }

                        if (days > 30) {
                            days = days % 30;
                            months = months + 1;
                        }

                        record.years_apn = 'Años: ' + years + ' Meses: ' + months + ' Días: ' + days;

                    }
                }
            }
        },

        /**
         * Método que calcula los años en otras instituciones públicas
         *
         * @method     time_worked
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        time_worked(record) {
            const vm = this;
            var now = record.start_date;
            if (vm.fiscal_year) {
                vm.fiscal_date = vm.fiscal_year + "-12-31";
            }

            var ms = 0;
            if (vm.fiscal_date && !record.end_date) {
                ms = moment(now, "YYYY-MM-DD").diff(moment(vm.fiscal_date, "YYYY-MM-DD"));
            } else if (vm.fiscal_date && record.end_date) {
                ms = moment(now, "YYYY-MM-DD").diff(moment(record.end_date, "YYYY-MM-DD"));
            } else {
                ms = moment(record.end_date, "YYYY-MM-DD").diff(moment(now, "YYYY-MM-DD"));
            }
            var d = moment.duration(ms);
            let data_years = 0;
            let data_months = 0;
            let data_days = 0;
            if (d._data.years < 0) {
                data_years = d._data.years * -1;
            } else {
                data_years = d._data.years;
            }
            if (d._data.months < 0) {
                data_months = d._data.months * -1;
            } else {
                data_months = d._data.months
            }
            if (d._data.days < 0) {
                data_days = d._data.days * -1;
            } else {
                data_days = d._data.days
            }

            let time = {
                years: `Años: ${data_years}`,
                months: `Meses: ${data_months}`,
                days: `Días: ${data_days}`,
            };

            if (data_days > 0) {
                record.time_worked = time.years + ' ' + time.months + ' ' + time.days;
            } else {
                record.time_worked = 0;
            };
        },

        /**
         * Método que calcula la diferencia entre dos fechas con marca de tiempo
         *
         * @method     diff_dates
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param      {string}  dateThen    Fecha a comparar para obtener la diferencia con respecto a la fecha actual
         *
         * @return     {[type]}  Objeto con información de la diferencia obtenida entre las dos fechas
         */
        diff_dates(dateThen, record) {
            const vm = this;
            let now = moment().format("YYYY-MM-DD");
            let ms = 0;
            if (vm.fiscal_year) {
                vm.fiscal_date = vm.fiscal_year + "-12-31";
            }

            if (vm.fiscal_date && !record.end_date) {
                ms = moment(dateThen, "YYYY-MM-DD").diff(moment(vm.fiscal_date, "YYYY-MM-DD"));
            } else if (vm.fiscal_date && record.end_date) {
                ms = moment(dateThen, "YYYY-MM-DD").diff(moment(record.end_date, "YYYY-MM-DD"));
            } else {
                ms = moment(dateThen, "YYYY-MM-DD").diff(moment(now, "YYYY-MM-DD"));
            }
            let d = moment.duration(ms);
            let data_years = 0;
            let data_months = 0;
            let data_days = 0;

            if (d._data.years < 0) {
                data_years = d._data.years * -1;
            }
            if (d._data.months < 0) {
                data_months = d._data.months * -1;
            }
            if (d._data.days < 0) {
                data_days = d._data.days * -1;
            }

            let time = {
                years: `Años: ${data_years}`,
                months: `Meses: ${data_months}`,
                days: `Días: ${data_days}`,
            };

            if (data_days > 0) {
                record.institution_years = time.years + ' ' + time.months + ' ' + time.days;
            } else {
                record.institution_years = 0;
            };
        },

        /**
         * Obtiene los datos de los años fiscales registrados
         * 
         * @author Natanael Rojo <rojonatanael99@gmail.com>
         */
        getFiscalYear() {
            const vm = this;
            axios.get(`${window.app_url}/fiscal-years/opened/list`).then(response => {
                vm.fiscal_year = response.data.records[0].id;
            });
        },

        /**
         * Método que establece el total de años de servicio en la apn
         *
         * @method     time_apn
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         * @author     Fabian Palmera   <fpalmera@cenditel.gob.ve>
         *
         * @param      {string}  record    Objecto con la información del registro
         *
         * @return     {[type]}  Objeto con información de la diferencia obtenida entre las dos fechas
         */
        time_apn(record) {
            const vm = this;
            let now = moment().format("YYYY-MM-DD");
            let ms = 0;
            let time_apn = 0;

            if (vm.fiscal_year) {
                vm.fiscal_date = vm.fiscal_year + "-12-31";
            }

            //Calcular tiempo con la institución pública
            if (vm.fiscal_date && _.isNull(record.end_date)) {
                ms = moment(record.start_date, "YYYY-MM-DD").diff(moment(vm.fiscal_date, "YYYY-MM-DD"));
            } else if (vm.fiscal_date && !_.isNull(record.end_date)) {
                ms = moment(record.start_date, "YYYY-MM-DD").diff(moment(record.end_date, "YYYY-MM-DD"));
            } else {
                ms = moment(record.start_date, "YYYY-MM-DD").diff(moment(now, "YYYY-MM-DD"));
            }

            let d = moment.duration(ms);
            let data_years = 0;
            let data_months = 0;
            let data_days = 0;

            if (d._data.years < 0) {
                data_years = d._data.years * -1;
            }
            if (d._data.months < 0) {
                data_months = d._data.months * -1;
            }
            if (d._data.days < 0) {
                data_days = d._data.days * -1;
            }

            let old_data_years = 0;
            let old_data_months = 0;
            let old_data_days = 0;
            let old_years = 0;
            let old_months = 0;
            let old_days = 0;
            // Calcular tiempo con los trabajos anteriores
            if (record.payroll_previous_job) {
                for (let job of record.payroll_previous_job) {
                    if (job.payroll_sector_type.name == 'Público') {
                        let old_now = job.start_date;
                        let old_ms = moment(job.end_date, "YYYY-MM-DD HH").diff(moment(old_now, "YYYY-MM-DD"));
                        let old_d = moment.duration(old_ms);

                        if (old_d._data.years < 0) {
                            old_data_years = old_d._data.years * -1;
                        } else {
                            old_data_years = old_d._data.years;
                        }
                        if (d._data.months < 0) {
                            old_data_months = old_d._data.months * -1;
                        } else {
                            old_data_months = old_d._data.months
                        }
                        if (d._data.days < 0) {
                            old_data_days = old_d._data.days * -1;
                        } else {
                            old_data_days = old_d._data.days
                        }

                        old_years += old_data_years;
                        old_months += old_data_months;
                        old_days += old_data_days;

                        if (old_months > 12) {
                            old_months = old_months % 12;
                            old_years = old_years + 1;
                        }

                        if (old_days > 30) {
                            old_days = old_days % 30;
                            old_months = old_months + 1;
                        }

                        time_apn = old_years;

                    }
                }
            }

            // Sumar el total de años de servicio
            if (time_apn > 0) {
                record.service_years = data_years + time_apn;
            } else {
                record.service_years = data_years;
            }

        },

        /**
         * Reescribe el metodo por defecto
         * Método para la eliminación de registros
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param  {integer} id    ID del Elemento seleccionado para su eliminación
         * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
         */
         deleteRecord(id, url) {
            const vm = this;
            /** @type {string} URL que atiende la petición de eliminación del registro */
            var url = vm.setUrl((url) ? url : vm.route_delete);

            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Está seguro de eliminar este registro?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar'
                    }
                },
                callback: async function (result) {
                    if (result) {
                        vm.loading = true;
                        await axios.delete(`${url}${url.endsWith('/') ? '' : '/'}${id}`).then(response => {
                            if (response.status === 200) {
                                vm.showMessage('destroy');
                            }
                        }).catch(error => {
                            vm.logs('mixins.js', 498, error, 'deleteRecord');
                            vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', error.response.data.message);
                        });
                        vm.loading = false;
                        vm.$refs.tableResults.refresh();
                    }
                }
            });
        },
    },
};
</script>
