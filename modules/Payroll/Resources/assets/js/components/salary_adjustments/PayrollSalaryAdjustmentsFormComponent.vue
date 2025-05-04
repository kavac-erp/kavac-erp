<template>
    <section id="PayrollSalaryAdjustmentsFormComponent">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Ajustes en tablas salariales</h6>
                <div class="card-btns">
                    <a :disabled="(record.increase_of_type != 'different') || (panel != 'Show')" onclick="$('input[name=importFile]').click()"
                    data-toggle="tooltip" type="button" title=""  class="btn btn-sm btn-primary btn-custom" data-original-title="Importar registros para el tabulador salarial">
                        <i class="fa fa-upload"></i>
                    </a>
                    <input
                        id="importFile" name="importFile"
                        type="file"
                        style="display:none"
                        @change="importSalaryTabulator()"
                    >
                    <a :disabled="(record.increase_of_type != 'different') || (panel != 'Show')" @click="exportSalaryTabulator()"
                    data-toggle="tooltip" type="button" title=""  class="btn btn-sm btn-primary btn-custom" data-original-title="Exportar registros del tabulador salarial">
                        <i class="fa fa-download"></i>
                    </a>
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
                                           class="form-control input-sm no-restrict"
                                           v-model="record.increase_of_date"
                                    >
                                </div>
                                <!-- ./fecha del aumento -->
                            </div>
                            <div class="col-md-6">
                                <!-- fecha fin del aumento -->
                                <div class="form-group">
                                    <label>Fecha de culminación del aumento:</label>
                                    <input type="date" data-toggle="tooltip"
                                           title="Fecha del aumento salarial"
                                           class="form-control input-sm no-restrict"
                                           v-model="record.end_increase_date"
                                           :min="record.increase_of_date">
                                </div>
                                <!-- ./fecha fin del aumento -->
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
                                             v-model="record.increase_of_type"
                                             @input="showRecord(true)">
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
                                                <input type="text" :id="'salary_scale_h_' + field_h.id" style="width: auto"
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
                <button type="button" @click="reset()" :disabled="(panel != 'Show')"
                        class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                        title="Borrar datos del formulario">
                    <i class="fa fa-eraser"></i>
                </button>
                <button type="button" @click="redirect_back(route_list)" :disabled="(panel != 'Show')"
                        class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="createRecord(route_create)" :disabled="(panel != 'Show')"
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
                    end_increase_date:           '',
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
                panel:                     'Form',
                edited: false
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
                vm.loadPayrollSalaryAdjustment();
            }
        },
        mounted() {
            const vm = this;
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
            vm.record.scale_values = [];
        },
        updated() {
            let vm = this;
            if(!vm.edited) {
                vm.edited = true;
            }

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
                    end_increase_date:           '',
                    increase_of_type:            '',
                    payroll_salary_tabulator_id: ''
                };
                vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
                vm.edited = false;
            },

            exportSalaryTabulator() {
                location.href = `${window.app_url}/payroll/salary-tabulators/export/${this.record.payroll_salary_tabulator_id}`;
            },

            importSalaryTabulator() {
                var vm = this;
                var url = `${window.app_url}/payroll/salary-tabulators/import`;
                var formData = new FormData();
                var importFile = document.querySelector('#importFile');
                formData.append("file", importFile.files[0]);
                axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    let dataImport = response.data;
                    let id_scale = 0;
                    let import_array = [];

                    // Transformar datos de JSON importados en array (para tabulador horizontal y mixto)
                    if (vm.record.payroll_salary_tabulator.payroll_vertical_salary_scale_id == null &&
                        vm.record.payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0) {
                        for (let key in dataImport[0]) {
                            if (key == "nombre") {
                                continue;
                            }
                            import_array.push(dataImport[0][key]);
                        }
                    } else if (vm.record.payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0 &&
                    vm.record.payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0) {
                        let j = 0;
                        for (j ; j < dataImport.length; j++) {
                            for (let key in dataImport[j]) {
                                if (key == "nombre") {
                                    continue;
                                }
                                import_array.push(dataImport[j][key]);
                            }
                        }

                    }

                    // Obtener valores en la tabla y cambiar por los importados
                    $.each(vm.payroll_salary_tabulator.payroll_salary_tabulator_scales, function(index, field) {
                        if (vm.record.payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0 &&
                            vm.record.payroll_salary_tabulator.payroll_horizontal_salary_scale_id == null) {
                            field.value = dataImport[index].incidencia;
                        } else if (vm.record.payroll_salary_tabulator.payroll_vertical_salary_scale_id == null &&
                        vm.record.payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0) {
                            field.value = import_array[index];
                        } else if (vm.record.payroll_salary_tabulator.payroll_vertical_salary_scale_id > 0 &&
                        vm.record.payroll_salary_tabulator.payroll_horizontal_salary_scale_id > 0) {
                            field.value = import_array[index];
                        }
                    });

                    var texterror = 'Registro almacenado con exito';
                    if (typeof response.data.errors !== 'undefined' && response.data.errors.length > 0) {
                        texterror = "Registros almacenados con exito, se encontraron " + response.data.errors.length + "errores, por favor revise la consola del navegador y/o correo enviado con los errores correspondientes";
                    }
                    $.gritter.add({
                        title: 'Exito!',
                        text: texterror,
                        class_name: 'growl-success',
                        image: "/images/screen-ok.png",
                        sticky: false,
                        time: 3500
                    });
                }).catch(error => {
                    console.log('failure');
                    $.gritter.add({
                        title: 'Advertencia!',
                        text: 'Error al importar el archivo',
                        class_name: 'growl-danger',
                        image: "{{ asset('images/screen-warning.png') }}",
                        sticky: false,
                        time: 2000
                    });
                    console.log(error);
                });
            },
            /**
             * Reescribe el método showRecord para cambiar su comportamiento por defecto
             * Método que muestra datos de un registro seleccionado
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            showRecord(change = null)  {
                const vm = this;
                let url = '';
                let type_form = '';
                if (change) {
                    type_form = vm.record.increase_of_type
                }

                if (vm.record.payroll_salary_tabulator_id > 0) {
                    if (typeof(vm.route_show) !== "undefined" && vm.route_show) {
                        if (vm.route_show.indexOf("{id}") >= 0) {
                            url = vm.route_show.replace("{id}", vm.record.payroll_salary_tabulator_id);
                        } else {
                            url = vm.route_show + '/' + vm.record.payroll_salary_tabulator_id;
                        }
                        axios.get(url).then(response => {
                            let history_data = 0;
                            if (typeof(response.data.record) !== "undefined") {
                                vm.payroll_salary_tabulator = response.data.record;
                                vm.record.payroll_salary_tabulator = vm.payroll_salary_tabulator;

                                if (change && type_form != 'different') {
                                    return;
                                }

                                if (vm.payroll_salary_tabulator.payroll_salary_adjustments) {

                                    if (vm.payroll_salary_tabulator.payroll_salary_adjustments[0].payroll_history_salary_adjustments) {
                                        history_data = JSON.parse(vm.payroll_salary_tabulator.payroll_salary_adjustments[0].payroll_history_salary_adjustments[0].salary_values);
                                    }

                                    if (history_data) {
                                        $.each(vm.payroll_salary_tabulator.payroll_salary_tabulator_scales, function(index, field) {
                                            field.value = history_data[index].value;
                                        });
                                    }
                                }
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
                            value = value.toFixed(2);
                        } else if (vm.record.increase_of_type == 'absolute_value') {
                            value = JSON.parse(field.value) + JSON.parse(vm.record.value);
                            value = value.toFixed(2);
                        } else {
                            value = JSON.parse(field.value);
                            value = value.toFixed(2);
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
                        increase_of_date: data.payroll_history_salary_adjustments ? data.payroll_history_salary_adjustments[0].increase_of_date : null,
                        end_increase_date: data.payroll_history_salary_adjustments ? data.payroll_history_salary_adjustments[0].end_increase_date : null,
                        increase_of_type: data.increase_of_type,
                        payroll_salary_tabulator: data.payroll_salary_tabulator,
                        payroll_salary_tabulator_id: data.payroll_salary_tabulator_id,
                        scale_values: data.payroll_history_salary_adjustments ? JSON.parse(data.payroll_history_salary_adjustments[0].salary_values) : [],
                    }
                });
            },

            async loadPayrollSalaryAdjustment() {
                let vm = this;
                await vm.loadForm();
                if (vm.record.payroll_salary_tabulator_id) {
                    vm.showRecord();
                    vm.loadSalaryScales();
                }
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
