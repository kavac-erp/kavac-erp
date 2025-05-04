<template>
    <section id="PayrollSalaryAdjustmentsFormComponent">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Ajustes en tablas salariales</h6>
                <div class="card-btns">
                    <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                       title="Ir atrás" data-toggle="tooltip">
                        <i class="fa fa-reply"></i>
                    </a>
                    <a href="#" class="card-minimize btn btn-card-action btn-round" title="Minimizar"
                       data-toggle="tooltip">
                        <i class="now-ui-icons arrows-1_minimal-up"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- mensajes de error -->
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
                <!-- ./mensajes de error -->
                <section class="form-horizontal">
                    <div id="salaryAdjustmentsForm" v-if="panel == 'Form'">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- fecha de generación -->
                                <div class="form-group is-required">
                                    <label>Fecha de generación:</label>
                                    <input type="date" readonly
                                           data-toggle="tooltip"
                                           title="Fecha de generación del ajuste salarial"
                                           class="form-control input-sm"
                                           v-model="record.created_at">
                                </div>
                                <!-- ./fecha de generación -->
                            </div>
                            <div class="col-md-6">
                                <!-- fecha del aumento -->
                                <div class="form-group is-required">
                                    <label>Fecha del aumento:</label>
                                    <input type="date" data-toggle="tooltip"
                                           title="Fecha del aumento salarial"
                                           class="form-control input-sm"
                                           v-model="record.increase_of_date">
                                </div>
                                <!-- ./fecha del aumento -->
                            </div>
                            <div class="col-md-6">
                                <!-- tabulador salarial -->
                                <div class="form-group is-required">
                                    <label>Tabulador salarial:</label>
                                    <select2 :options="payroll_salary_tabulators"
                                             @input="showRecord()"
                                             v-model="record.payroll_salary_tabulator_id">
                                    </select2>
                                </div>
                                <!-- ./tabulador salarial -->
                            </div>
                            <div class="col-md-6">
                                <!-- tipo de aumento -->
                                <div class="form-group is-required">
                                    <label>Tipo de aumento:</label>
                                    <select2 :options="increase_of_types"
                                             v-model="record.increase_of_type">
                                    </select2>
                                </div>
                                <!-- ./tipo de aumento -->
                            </div>
                            <div class="col-md-6"
                                 v-if="record.increase_of_type == 'percentage'
                                    || record.increase_of_type == 'absolute_value'">
                                <!-- valor -->
                                <div class="form-group is-required">
                                    <label>Valor:</label>
                                    <input type="text"
                                           data-toggle="tooltip" title="Indique el valor"
                                           class="form-control input-sm"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': '2'"
                                           v-model="record.value">
                                </div>
                                <!-- ./valor -->
                            </div>
                        </div>
                    </div>
                    <div id="salaryAdjustmentsShow" v-else>
                        <div class="modal-table"
                             v-if="(payroll_salary_tabulator &&
                                (((payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0)
                                && (payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales) &&
                                (payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales.length > 0))
                                || ((payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0)
                                && (payroll_salary_tabulator.payroll_vertical_salary_scale.payroll_scales) &&
                                (payroll_salary_tabulator.payroll_vertical_salary_scale.payroll_scales.length > 0))))">

                            <table class="table table-hover table-striped table-responsive"
                                   v-if="((payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0)
                                      && (payroll_salary_tabulator.payroll_vertical_salary_scale_id == null))">
                                <thead>
                                    <th :colspan="1 + payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales.length"
                                        v-if="payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales">
                                        <strong>{{ payroll_salary_tabulator.name }}</strong>
                                    </th>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <th>Nombre:</th>
                                        <th
                                            v-for="(field_h, index) in
                                            payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales" 
                                            :key="index">
                                            {{ field_h.name }}
                                        </th>
                                    </tr>
                                    <tr class="text-center"
                                        v-if="payroll_salary_tabulator.payroll_vertical_salary_scale_id == null">
                                        <th>Incidencia:</th>
                                        <td class="td-with-border"
                                            v-for="(field_h, index) in
                                            payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales" 
                                            :key="index">
                                            <div>
                                                <input type="text" :id="'salary_scale_h_' + field_h.id"
                                                       class="form-control input-sm" data-toggle="tooltip"
                                                       :disabled="record.increase_of_type != 'different'"
                                                       onfocus="this.select()"
                                                       :value="getScaleValue(null, field_h.id)">
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="text-center"
                                        v-else
                                        v-for="(field_v, index_v) in
                                        payroll_salary_tabulator.payroll_vertical_salary_scale.payroll_scales" 
                                        :key="index_v">
                                        <th>
                                            {{field_v.name}}
                                        </th>
                                        <td class="td-with-border"
                                            v-for="(field_h, index_h) in
                                            payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales" 
                                            :key="index_h">
                                            <div>
                                                <input type="text"
                                                       :id="'salary_scale_' + field_v.id + '_' + field_h.id"
                                                       class="form-control input-sm" data-toggle="tooltip"
                                                       :disabled="record.increase_of_type != 'different'"
                                                       onfocus="this.select()"
                                                       :value="getScaleValue(field_v.id, field_h.id)">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-hover table-striped table-responsive table-assignment"
                                   v-else-if="payroll_salary_tabulator.payroll_horizontal_salary_scale_id == null
                                           && payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0">
                                <thead>
                                    <th colspan="2">
                                        <strong>{{ payroll_salary_tabulator.name }}</strong>
                                    </th>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <th>Nombre</th>
                                        <th>Incidencia</th>
                                    </tr>
                                    <tr class="text-center"
                                        v-for="(field, index) in
                                        payroll_salary_tabulator.payroll_vertical_salary_scale.payroll_scales" 
                                        :key="index">
                                        <th>
                                            {{field.name}}
                                        </th>
                                        <td>
                                            <div>
                                                <input type="text" :id="'salary_scale_v_' + field.id"
                                                       class="form-control input-sm" data-toggle="tooltip"
                                                       :disabled="record.increase_of_type != 'different'"
                                                       onfocus="this.select()"
                                                       :value="getScaleValue(field.id, null)">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-hover table-striped table-responsive table-assignment"
                                   v-else-if="payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0
                                           && payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0">
                                <thead>
                                    <th colspan="2">
                                        <strong>{{ payroll_salary_tabulator.name }}</strong>
                                    </th>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <th>Nombre:</th>
                                        <th
                                            v-for="(field_h, index) in payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales" :key="index">
                                            {{field_h.name}}
                                        </th>
                                    </tr>
                                    <tr class="text-center"
                                        v-if="payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0
                                           && payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0"
                                        v-for="(field_v, index_v) in payroll_salary_tabulator.payroll_vertical_salary_scale.payroll_scales"
                                        :key="index_v">
                                        <th>
                                            {{field_v.name}}
                                        </th>
                                        <td class="td-with-border"
                                            v-for="(field_h, index_h) in payroll_salary_tabulator.payroll_horizontal_salary_scale.payroll_scales" :key="index_h">
                                            <div>
                                                <input type="text"
                                                       :id="'salary_scale_' + field_v.id + '_' + field_h.id"
                                                       class="form-control input-sm" data-toggle="tooltip"
                                                       :disabled="record.increase_of_type != 'different'"
                                                       onfocus="this.select()"
                                                       :value="getScaleValue(field_v.id, field_h.id)">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div style="padding-bottom: 20px;">
                        <div class="pull-right"
                             v-if="panel == 'Form'">
                            <button type="button" @click="loadSalaryScales()"
                                    class="btn btn-primary btn-wd btn-sm"
                                    :disabled="isDisableNext()"
                                    data-toggle="tooltip" title="">
                                Siguiente
                            </button>
                        </div>
                        <div class="pull-left"
                             v-else>
                            <button type="button" @click="changePanel('Form')"
                                    class="btn btn-default btn-wd btn-sm"
                                    data-toggle="tooltip" title="">
                                Regresar
                            </button>
                        </div>
                    </div>
                </section>
            </div>
            <div class="card-footer text-right">
                <button type="button" @click="reset()"
                        class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                        title="Borrar datos del formulario">
                    <i class="fa fa-eraser"></i>
                </button>
                <button type="button" @click="redirect_back(route_list)"
                        class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="createRecord(route_create)"
                        class="btn btn-success btn-icon btn-round">
                    <i class="fa fa-save"></i>
                </button>
            </div>

        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id:                          '',
                    value:                       '',
                    increase_of_date:            '',
                    increase_of_type:            '',
                    payroll_salary_tabulator:    {},
                    scale_values:                [],
                    payroll_salary_tabulator_id: ''
                },

                payroll_salary_tabulator:  {},
                payroll_salary_tabulators: [],
                increase_of_types:         [
                    { id: '',               text: 'Seleccione...'},
                    { id: 'percentage',     text: 'Porcentual'},
                    { id: 'absolute_value', text: 'Valor absoluto'},
                    { id: 'different',      text: 'Diferente'}
                ],
                errors:                    [],
                records:                   [],
                panel:                     'Form'
            }
        },
        props: {
            payroll_salary_adjustment_id : Number,
        },
        created() {
            const vm = this;
            vm.reset();
            vm.getPayrollSalaryTabulators();
            if (vm.payroll_salary_adjustment_id) {
                vm.loadForm();
            }
        },
        mounted() {
            const vm = this;
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
            vm.record.scale_values = [];
        },
        methods: {
            /**
             * Método que permite borrar todos los datos del formulario
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id:                          '',
                    value:                       '',
                    increase_of_date:            '',
                    increase_of_type:            '',
                    payroll_salary_tabulator_id: ''
                };
                vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
            },
            /**
             * Reescribe el método showRecord para cambiar su comportamiento por defecto
             * Método que muestra datos de un registro seleccionado
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            showRecord() {
                const vm = this;
                let url = '';
                if (vm.record.payroll_salary_tabulator_id > 0) {
                    if (typeof(vm.route_show) !== "undefined" && vm.route_show) {
                        if (vm.route_show.indexOf("{id}") >= 0) {
                            url = vm.route_show.replace("{id}", vm.record.payroll_salary_tabulator_id);
                        } else {
                            url = vm.route_show + '/' + vm.record.payroll_salary_tabulator_id;
                        }
                        axios.get(url).then(response => {
                            if (typeof(response.data.record) !== "undefined") {
                                vm.payroll_salary_tabulator = response.data.record;
                                vm.record.payroll_salary_tabulator = vm.payroll_salary_tabulator;
                            }
                        }).catch(error => {
                            if (typeof(error.response) !== "undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                                else {
                                    vm.logs('resources/js/all.js', 343, error, 'showRecord');
                                }
                            }
                        });
                    }
                }
            },
            /**
             * Método que habilita o deshabilita el botón siguiente
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            isDisableNext() {
                const vm = this;
                if ((vm.record.increase_of_date != '') && (vm.record.increase_of_type != '') &&
                    (vm.record.payroll_salary_tabulator_id != '')) {
                    if (vm.record.increase_of_type == 'different') {
                        return false;
                    } else if (vm.record.value != '') {
                            return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }

            },
            /**
             * Método que cambia el panel de visualización
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             *
             * @param     {string}     panel    Panel seleccionado
             */
            changePanel(panel) {
                const vm    = this;
                let complete;
                if (panel == 'Show') {
                    complete = !vm.isDisableNext();
                } else {
                    complete = true;
                }
                if (complete == true) {
                    vm.panel    = panel;
                }
            },
            /**
             * Método que obtiene la información de los escalafones salariales seleccionados
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             *
             */
            loadSalaryScales() {
                const vm = this;
                vm.changePanel('Show');
            },
            /**
             * Método que obtiene el valor de la escala según sea el caso
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             *
             * @param    {integer}    vertical      Identificador único del escalafón vertical. Este campo es opcional
             * @param    {integer}    horizontal    Identificador único del escalafón horizontal. Este campo es opcional
             *
             */
            getScaleValue(vertical, horizontal) {
                const vm = this;
                let value = 0;
                $.each(vm.payroll_salary_tabulator.payroll_salary_tabulator_scales, function(index, field) {
                    if (field["payroll_vertical_scale_id"] == vertical &&
                        field["payroll_horizontal_scale_id"] == horizontal) {
                        if (vm.record.increase_of_type == 'percentage') {
                            value = JSON.parse(field.value) * JSON.parse(vm.record.value) / 100;
                        } else if (vm.record.increase_of_type == 'absolute_value') {
                            value = JSON.parse(field.value) + JSON.parse(vm.record.value);
                        } else {
                            value = JSON.parse(field.value);
                        }
                    }
                });

                return value;

            },

            /**
             * Método que permite crear o actualizar un registro
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
             * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
             *                         El valor por defecto es verdadero.
             * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
             *                         El valor por defecto es verdadero.
             */
            createRecord(url, list = true, reset = true) {
                const vm = this;
                url = vm.setUrl(url);

                if (vm.record.id) {
                    vm.updateRecord(url);
                }
                else {
                    vm.loading = true;
                    var fields = {};
                    vm.record.scale_values = [];
                    let id_scale;

                    $.each(vm.payroll_salary_tabulator.payroll_salary_tabulator_scales, function(index, field) {
                        let value = 0;
                        let vertical = field["payroll_vertical_scale_id"];
                        let horizontal = field["payroll_horizontal_scale_id"];
                        if (horizontal && vertical == null) {
                            id_scale = 'salary_scale_h_' + horizontal;
                        }
                        if (vertical && horizontal == null) {
                            id_scale = 'salary_scale_v_' + vertical;
                        }
                        if (vertical && horizontal) {
                            id_scale = 'salary_scale_' + vertical + '_' + horizontal;
                        }

                        let tabValue = document.getElementById(id_scale)
                        value = {
                            id: field["id"],
                            value: tabValue.value,
                        }
                        if (vm.record.increase_of_type == 'percentage') {
                            value = {
                                id: field["id"],
                                value: parseFloat(tabValue.value) + parseFloat(field["value"]),
                            }
                        }
                        vm.record.scale_values.push(value);
                    });

                    for (var index in vm.record) {
                        fields[index] = vm.record[index];
                    }
                    axios.post(url, fields).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
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

                        if (typeof(error.response) !="undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                ); 
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(error.response.data.errors[index][0]);
                                }
                            }
                        }

                        vm.loading = false;
                    });
                }

            },


            /**
             * Método que carga la información en el formulario para editar un registro
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */

            async loadForm() {
                let vm = this;
                await axios.get(`${window.app_url}/payroll/salary-adjustments/vue-info/${vm.payroll_salary_adjustment_id}`).then(response => {
                    let data = response.data.record;

                    vm.record = {
                        id: data.id,
                        created_at: vm.format_date(data.created_at, 'YYYY-MM-DD'),
                        value: data.value,
                        increase_of_date: data.increase_of_date,
                        increase_of_type: data.increase_of_type,
                        payroll_salary_tabulator: data.payroll_salary_tabulator,
                        payroll_salary_tabulator_id: data.payroll_salary_tabulator_id,
                        scale_values: [],
                    }
                });
            },

            /**
             * Método que permite actualizar información
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url Ruta de la acci´on que modificará los datos
             */
            updateRecord(url) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                url = vm.setUrl(url);

                vm.record.scale_values = [];
                let id_scale;

                $.each(vm.payroll_salary_tabulator.payroll_salary_tabulator_scales, function(index, field) {
                    let value = 0;
                    let vertical = field["payroll_vertical_scale_id"];
                    let horizontal = field["payroll_horizontal_scale_id"];
                    if (horizontal && vertical == null) {
                        id_scale = 'salary_scale_h_' + horizontal;
                    }
                    if (vertical && horizontal == null) {
                        id_scale = 'salary_scale_v_' + vertical;
                    }
                    if (vertical && horizontal) {
                        id_scale = 'salary_scale_' + vertical + '_' + horizontal;
                    }

                    let tabValue = document.getElementById(id_scale)
                    value = {
                        id: field["id"],
                        value: tabValue.value,
                    }
                    if (vm.record.increase_of_type == 'percentage') {
                        value = {
                            id: field["id"],
                            value: parseFloat(tabValue.value) + parseFloat(field["value"]),
                        }
                    }
                    vm.record.scale_values.push(value);
                });

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.loading = false;
                        vm.showMessage('update');
                    }

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                ); 
                            }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });
            },
        }
    };
</script>
