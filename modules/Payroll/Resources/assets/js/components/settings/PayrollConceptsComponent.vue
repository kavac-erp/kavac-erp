<template>
    <section id="payrollConceptsFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="#" title="Registros de conceptos" data-toggle="tooltip"
           @click="addRecord('add_payroll_concept', 'payroll/concepts', $event)">
            <i class="icofont icofont-calculator-alt-1 ico-3x"></i>
            <span>Conceptos</span>
        </a>
        <div id="add_payroll_concept" class="modal fade text-left" role="dialog" style="overflow-y: scroll;" >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-calculator-alt-1 ico-3x"></i>
                            Concepto
                        </h6>
                    </div>
                    <div class="modal-body">
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
                        <div class="wizard-tabs with-border" v-if="budget && accounting">
                            <ul class="nav wizard-steps">
                                <li :class="panel=='conceptForm' ? 'nav-item active' : 'nav-item'">
                                    <a :href="panel != 'conceptForm' ?'#':'#w-conceptForm'"
                                       data-toggle="tab" class="nav-link text-center" id="conceptForm"
                                       @click="changePanel('conceptForm')">
                                        <span class="badge">1</span>
                                        Concepto
                                    </a>
                                </li>
                                <li :class="panel=='budgetAccountingForm' ? 'nav-item active' : 'nav-item'">
                                    <a :href="panel !='budgetAccountingForm' ?'#':'#w-budgetAccountingForm'"
                                       data-toggle="tab" class="nav-link text-center" id="budgetAccountingForm"
                                       @click="changePanel('budgetAccountingForm')">
                                        <span class="badge">2</span>
                                        Datos presupuestarios/contables
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <form class="form-horizontal">
                            <div class="tab-content">
                                <div id="w-conceptForm"
                                     :class="panel=='conceptForm' ?
                                     'tab-pane p-3 active' : 'tab-pane p-3'">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- nombre -->
                                            <div class="form-group is-required">
                                                <label>Nombre:</label>
                                                <input type="text" placeholder="Nombre del concepto"
                                                    data-toggle="tooltip"
                                                    title="Indique el nombre del concepto (requerido)"
                                                    v-is-text @input="normalizeText($event.target.value, 'name')"
                                                    class="form-control input-sm" v-model="record.name">
                                                <input type="hidden" v-model="record.id">
                                            </div>
                                            <!-- ./nombre -->
                                        </div>
                                        <!-- tipo de concepto -->
                                        <div class="col-md-6">
                                            <div class=" form-group is-required">
                                                <label>Tipo de concepto</label>
                                                <select2 :options="payroll_concept_types"
                                                        v-model="record.payroll_concept_type_id"></select2>
                                            </div>
                                        </div>
                                        <!-- ./tipo de concepto -->
                                        <!-- Moneda -->
                                        <div class="col-md-6">
                                            <div class="form-group is-required">
                                                <label>Moneda:</label>
                                                <select2 :options="currencies" v-model="record.currency_id" id="currency_id"></select2>
                                            </div>
                                        </div>
                                        <!-- ./Moneda -->
                                        <!-- Organización -->
                                        <div class="col-md-6">
                                            <div class=" form-group is-required">
                                                <label>Organización:</label>
                                                <select2 :options="institutions" v-model="record.institution_id"></select2>
                                            </div>
                                        </div>
                                        <!-- ./Organización -->
                                        <!-- activa -->
                                        <div class="col-md-2">
                                            <div class=" form-group">
                                                <label>¿Activo?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                        title="¿El concepto se encuentra activo actualmente?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="conceptActive" v-model="record.active"
                                                                :value="true">
                                                        <label class="custom-control-label" for="conceptActive"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./activa -->
                                        <!-- alimenta la arc -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>¿ARC?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                        title="¿El concepto alimenta la ARC?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="conceptArc" v-model="record.arc"
                                                                :value="true">
                                                        <label class="custom-control-label" for="conceptArc"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./alimenta la arc -->
                                        <!-- descripción -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Descripción:</label>
                                                <ckeditor :editor="ckeditor.editor" id="description"
                                                        data-toggle="tooltip"
                                                        title="Indique la descripción del concepto"
                                                        :config="ckeditor.editorConfig" class="form-control"
                                                        name="description" tag-name="textarea"
                                                        v-model="record.description"></ckeditor>
                                            </div>
                                        </div>
                                        <!-- ./descripción -->
                                    </div>
                                    <div class="row">
                                        <!-- ¿asignar a? -->
                                        <div class="col-12 form-group">
                                            <label>¿Asignar a?</label>
                                            <div class="row">
                                                <div class="col-12 col-md-7">
                                                    <div class="form-group is-required" style="z-index: unset;">
                                                        <label>Opciones:</label>
                                                        <v-multiselect data-toggle="tooltip"
                                                            title="Indique los registros a los que se les va asignar el concepto"
                                                            track_by="name"
                                                            :hide_selected="false"
                                                            :options="assign_to"
                                                            @input="updateAssignOptions"
                                                            v-model="record.assign_to">
                                                        </v-multiselect>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-5">
                                                    <div class="form-group is-required" style="z-index: unset;">
                                                        <label>Reglas:</label>
                                                        <div data-toggle="tooltip"
                                                            title="¿El filtro asignar a será estricto según las opciones seleccionadas?">
                                                            <select2 :options="assignmetRules"
                                                                    v-model="record.is_strict"></select2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./¿asignar a? -->
                                    </div>
                                    <div v-if="record.assign_to" class="row align-items-baseline">
                                         <div class="col-md-4" v-for="field in record.assign_to" :key="field['id']">
                                            <div v-if="field['type'] && assign_options[field['id']] && record.assign_options[field['id']]">

                                                <!-- registro de opciones a asignar -->
                                                <div v-if="field['type'] == 'list'" class="form-group is-required" style="z-index: unset;">
                                                    <label>{{ field['name'] }}</label>
                                                    <v-multiselect data-toggle="tooltip"
                                                        title="Indique los registros a los que se les va asignar el concepto"
                                                        track_by="text"
                                                        :hide_selected="false"
                                                        :loading="assign_options_loading"
                                                        :options="assign_options[field['id']]"
                                                        v-model="record.assign_options[field['id']]">
                                                    </v-multiselect>
                                                </div>
                                                <!-- ./registro de opciones a asignar -->

                                                <!-- registro de rangos a asignar -->
                                                <div v-if="field['type'] == 'range' && assign_options[field['id']]"
                                                    class="form-group" style="z-index: unset;">
                                                    <label>
                                                        {{ field['name'] == 'Todos los trabajadores con hijos' ?
                                                                        'Rango de edad de los hijos'
                                                                        :field['id'] == 'all_staff_according_start_date'?
                                                                            'A partir del año de servicio'
                                                                            :field['name']
                                                        }}
                                                    </label>
                                                    <div class="row" style="align-items: baseline;">
                                                        <div class="col-6" v-if="field['id'] != 'all_staff_according_start_date' ">
                                                            <div class="form-group is-required">
                                                                <label>Minimo:</label>
                                                                <input type="number" min="0" step="1"
                                                                    placeholder="Minimo" data-toggle="tooltip"
                                                                    title="Indique el minimo requerido para asignar el concepto"
                                                                    class="form-control input-sm"
                                                                    v-model="record.assign_options[field['id']]['minimum']">
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group is-required">
                                                                <label>{{ field['id'] == 'all_staff_according_start_date'?
                                                                            '': 'Máximo:'}}</label>
                                                                <input type="number" min="0" step="1"
                                                                    placeholder="Máximo" data-toggle="tooltip"
                                                                    title="Indique el máximo requerido para asignar el concepto"
                                                                    class="form-control input-sm"
                                                                    v-model="record.assign_options[field['id']]['maximum']">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- ./registro de opciones a asignar -->
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12 mb-5 mb-xl-0 col-xl-7">
                                            <!-- fórmula -->
                                            <!-- Asistente de funciones -->
                                            <section class="container">
                                                <div class="row">
                                                    <div class="col-12 pad-top-10 with-border with-radius table-responsive"
                                                         style="place-self: baseline;"
                                                         v-if="useFunction">
                                                        <h6 class="text-center">Asistente de funciones</h6>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="labelFunction">Funciones</label>
                                                                <select2 :options="functions"
                                                                         v-model="idFunction"></select2>
                                                            </div>
                                                        </div>
                                                        <div v-if="getInfoFunction['formatShow']" class="col-md-12">
                                                            <div class="row">
                                                                <span class="col-md-12">
                                                                    <strong>{{ getInfoFunction['text']}}</strong>
                                                                    {{ getInfoFunction['formatShow'] }}
                                                                </span>
                                                                <br>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <strong>Descripción:</strong>
                                                                        <div class="row" style="margin: -10px 0">
                                                                            <span class="col-md-12">
                                                                                {{ getInfoFunction['description'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div v-if="getInfoFunction['id'] == 'sum'" class="col-md-12">
                                                                    <button class="btn btn-sm btn-default btn-custom btn-mini btn-new float-right"
                                                                            type="button" @click="addParameter()">
                                                                        <i class="fa fa-plus-circle"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-danger btn-custom btn-mini btn-new float-right"
                                                                                type="button" @click="deleteParameter()">
                                                                        <i class="fa fa-minus-circle"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-md-12" v-if="getInfoFunction['currentParamenter']">
                                                                    <div class="form-group">
                                                                        <strong>
                                                                            {{ getInfoFunction['currentParamenter']['name'] }}
                                                                            ({{ (getInfoFunction['currentParamenter']['required'] == 'is-required') ? 'obligatorio' : 'opcional' }}):
                                                                        </strong>
                                                                        <div class="row" style="margin: -10px 0">
                                                                            <span class="col-md-12">
                                                                                {{ getInfoFunction['currentParamenter']['description'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-baseline">
                                                                <div :class="[(variable_option && (param['id'] == 'inputFormTest')) ? 'col-md-12' : 'col-md-6']"
                                                                    v-for="(param, index) in getInfoFunction['parameters']"
                                                                    :key="index">
                                                                    <div v-if="(variable_option && (param['id'] == 'inputFormTest'))"
                                                                        :class="['form-group', param['required']]">
                                                                        <label>{{ param['name'] }}:</label>
                                                                        <div class="input-group mb-12">
                                                                            <div class="input-group-prepend">
                                                                                <label class="input-group-text"
                                                                                    for="inputGroupSelect01"
                                                                                    style="font-size: 0.85rem;">
                                                                                    {{ updateNameVariable }}
                                                                                </label>
                                                                            </div>
                                                                            <select class="custom-select"
                                                                                style="font-size: 0.85rem;"
                                                                                :id="param['id'] + 'Operator'">
                                                                                <option selected>Operador...</option>
                                                                                <option
                                                                                    v-for="(op, index) in filteredOperators"
                                                                                    :value="op['id']" :key="index"
                                                                                >
                                                                                        {{ op['text'] }}
                                                                                    </option>
                                                                            </select>
                                                                            <select class="custom-select"
                                                                                style="font-size: 0.85rem;"
                                                                                :id="param['id'] + 'Value'"
                                                                                    v-if="((type != 'number') && (variable != 'parameter') && (variable != 'concept') && (variable != 'tabulator'))">
                                                                                <option v-if="type == 'boolean'"
                                                                                        v-for="(val, index) in boolSubOptions"
                                                                                        :key="index"
                                                                                        :value="val['id']"> {{ val['text'] }} </option>
                                                                                <option v-if="type != 'boolean'"
                                                                                        v-for="(val, index) in subOptions"
                                                                                        :key="index"
                                                                                        :value="val['id']"> {{ val['text'] }} </option>
                                                                            </select>
                                                                            <input v-if="((type == 'number') || (variable == 'parameter') || (variable == 'concept') || (variable == 'tabulator'))"
                                                                                    style="font-size: 0.85rem; border-radius: 0px;"
                                                                                    :id="param['id'] + 'Value'" type="text" placeholder="Value"
                                                                                    @input="getFormulaFunction()"
                                                                                    class="form-control" @focus="idCurrentInput = param['id']">
                                                                        </div>
                                                                    </div>
                                                                    <div :class="['form-group', param['required']]" v-else>
                                                                        <label>{{ param['name'] }}:</label>
                                                                        <div class="input-group input-sm">
                                                                            <span class="input-group-addon"
                                                                                  style="{padding: 0px 0px 10px 10px;
                                                                                         cursor: pointer;
                                                                                         background-color: #2CA8FF;
                                                                                         color: white;}">
                                                                                <strong><i>f</i><small>(x) </small> </strong>
                                                                            </span>
                                                                            <input :id="param['id']" type="text" placeholder="Value"
                                                                                   @input="getFormulaFunction"
                                                                                   class="form-control" @focus="idCurrentInput = param['id']">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="form-group row">
                                                                <label class="col-sm-4 offset-2 control-label text-sm-right pt-1">Resultado: </label>
                                                                <div class="col-sm-6">
                                                                    <input type="text" :class="[
                                                                    'form-control',
                                                                    (valueFunction == 'Error')
                                                                        ? 'is-invalid'
                                                                        : 'is-valid',
                                                                    'input-sm']"
                                                                       disabled v-model="valueFunction">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group is-required">
                                                                        <label>Fórmula</label>
                                                                        <textarea type="text" id="formulaFunction"
                                                                                  style="font-size: 1rem; font-weight: bold;"
                                                                                  class="form-control input-sm"
                                                                                  data-toggle="tooltip"
                                                                                  disabled
                                                                                  title="Fórmula a aplicar para el concepto. Utilice la siguiente calculadora para establecer los parámetros de la fórmula"
                                                                                  rows="3" v-model="formulaFunctionShow">
                                                                        </textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <button type="button" @click="openFunctionWizard()"
                                                                        class="btn btn-sm btn-primary btn-custom float-right"
                                                                        title="Aceptar"
                                                                        data-toggle="tooltip">
                                                                    Aceptar
                                                                </button>
                                                                <button type="button" @click="openFunctionWizard(true)"
                                                                        class="btn btn-sm btn-default btn-custom float-right"
                                                                        title="Cancelar"
                                                                        data-toggle="tooltip">
                                                                    Cancelar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>
                                            <!-- ./Asistente de funciones -->
                                            <div class="form-group is-required" style="z-index: 0;" v-if="!useFunction">
                                                <label>Fórmula</label>
                                                <textarea type="text" id="formulaShow"
                                                          style="font-size: 1rem; font-weight: bold;"
                                                          class="form-control input-sm"
                                                          data-toggle="tooltip"
                                                          disabled
                                                          title="Fórmula a aplicar para el concepto. Utilice la siguiente calculadora para establecer los parámetros de la fórmula"
                                                          rows="3" v-model="record.formulaShow">
                                                </textarea>
                                            </div>
                                            <!-- ./fórmula -->
                                            <div class="row" style="align-items: flex-end;">
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="worker_record">¿Expediente del trabajador?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar una variable del expediente del Trabajador"
                                                                     v-model="variable" value="worker_record">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="parameter">¿Parámetro?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar un parámetro previamente registrado"
                                                                     v-model="variable" value="parameter">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="vacation">¿Vacaciones?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar una variable asociada a la configuración de vacaciones"
                                                                     v-model="variable" value="vacation">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="benefit">¿Prestaciones sociales?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar una variable asociada a la configuración de las prestaciones sociales"
                                                                     v-model="variable" value="benefit">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="concept">¿Concepto?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar un concepto previamente registrado"
                                                                     v-model="variable" value="concept">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="tabulator">¿Tabulador?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar una variable asociada a la configuración de vacaciones"
                                                                     v-model="variable" value="tabulator">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-md-3">
                                                    <div class="form-group">
                                                        <label for="ari_register">¿Registro ARI?</label>
                                                        <div class="col-12">
                                                            <p-radio class="pretty p-switch p-fill p-bigger"
                                                                     color="success" off-color="text-gray" toggle
                                                                     data-toggle="tooltip"
                                                                     title="Indique si desea utilizar una variable asociada al registro ARI"
                                                                     v-model="variable" value="ari_register">
                                                                <label slot="off-label"></label>
                                                            </p-radio>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12"
                                                     v-if="variable && variable != 'ari_register'">
                                                    <!-- opciones -->
                                                    <div class="form-group">
                                                        <label for="register">Registro</label>
                                                        <select2 :options="variable_options"
                                                                 @input="getOptionType"
                                                                 v-model="variable_option"></select2>
                                                    </div>
                                                    <!-- ./opciones -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-xl-5">
                                            <!-- ./calculadora -->
                                            <div class="formula-calculator">
                                            <formula-calculator formulaInput='formulaShow' :withDisplay="false" ref="formulaResults"/>
                                            <div class="form-group row mb-n1">
                                                    <div class="col-12 col-md-8 col-md-6 text-center mx-auto">
                                                        <button
                                                            type="button" class="btn btn-info btn-sm btn-formula btn-function" data-toggle="tooltip"
                                                            title="presione para abir asistente de funciones"
                                                            @click="openFunctionWizard()"
                                                            :style="{opacity: useFunction ? 0.5 : 1}">
                                                            <strong><i>f</i><small>(x)</small></strong>
                                                        </button>
                                                        <button type="button" class="btn btn-info btn-sm btn-formula btn-function" data-toggle="tooltip"
                                                            title="presione para abir asistente de funciones"
                                                            @click="openFunctionWizard(false, 'sum')"
                                                            :style="{opacity: useFunction ? 0.5 : 1}">&#8721;</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" v-if="variable_option">
                                                <div class="col-12 col-md-8 text-center mx-auto">
                                                    <button
                                                        type="button" class="btn btn-info btn-sm btn-formula btn-variable"
                                                        data-toggle="tooltip"
                                                        title="Variable a usar cuando se realice el cálculo"
                                                        @click="setVariable()">
                                                        {{ updateNameVariable }}
                                                    </button>
                                                </div>
                                            </div>
                                        <!-- ./calculadora -->
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <section id="w-budgetAccountingForm"
                                     :class="panel=='budgetAccountingForm' ?
                                     'tab-pane p-3 active' : 'tab-pane p-3'"
                                     v-if="budget && accounting">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Proyecto</label>
                                                <select2 id="budget_project_id" :disabled="record.budget_centralized_action_id != ''" :options="projects"
                                                         v-model="record.budget_project_id"
                                                         @input="getSpecificActions('Project')">
                                                </select2>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Acción centralizada</label>
                                                <select2 id="budget_centralized_action_id" :disabled="record.budget_project_id != ''"
                                                         :options="centralized_actions"
                                                         v-model="record.budget_centralized_action_id"
                                                         @input="getSpecificActions('CentralizedAction')">
                                                </select2>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div :class="'form-group' + (budgetAccountingFormIsRequired()  ? ' is-required': '')">
                                                <label>Acción específica</label>
                                                <select2 id="budget_specific_action_id" disabled :options="specific_actions"
                                                         v-model="record.budget_specific_action_id"
                                                         @input="getBudgetAccounts(record.budget_specific_action_id)">
                                                </select2>
                                            </div>
                                        </div>
                                        <!-- cuenta presupuestaria -->
                                        <div class="col-md-6">
                                            <div :class="'form-group' + (budgetAccountingFormIsRequired()  ? ' is-required': '')">
                                                <label>Cuenta presupuestaria</label>
                                                <select2 :options="budget_accounts"
                                                        :disabled="!budgetAccountingFormIsRequired()"
                                                         @input="getAccountingAccounts(record.budget_account_id)"
                                                         v-model="record.budget_account_id"></select2>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div :class="'form-group' + (budgetAccountingFormIsRequired()  ? ' is-required': '')">
                                                <label>Cuenta contable</label>
                                                <select2 :options="accounting_accounts"
                                                        :disabled="!budgetAccountingFormIsRequired()"
                                                        @input="getBudgetAccounting(record.accounting_account_id)"
                                                         v-model="record.accounting_account_id"></select2>
                                            </div>
                                        </div>
                                        <!-- ./cuenta contable -->
                                    </div>
                                    <hr>
                                    <section id="w-budgetAccountingBenefitForm"
                                        v-show="record.payroll_concept_type_id && getSignConceptType === 'NA' || record.payroll_concept_type_id && getSignConceptType === '-'">
                                        <h6 class="text-left card-title">
                                            Beneficiario
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Beneficiario</label>
                                                    <v-multiselect
                                                        :options="all_receivers"
                                                        track_by="text"
                                                        :hide_selected="false"
                                                        v-model="record.receiver"
                                                        :multiple="false"
                                                        :search_change="(query) => applyFunctionDebounce(query, searchReceivers)"
                                                        :internal_search="false"
                                                        :searchable="true"
                                                        :taggable="true"
                                                        :add_tag="addTag"
                                                        :group_values="'group'"
                                                        :group_label="'label'"
                                                        style="margin-top: -25px;"
                                                    >
                                                    </v-multiselect>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Cuenta contable</label>
                                                    <select2
                                                        :options="accounting_accounts"
                                                        v-model="record.receiver_account"
                                                    ></select2>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </section>
                            </div>
                            <div class="wizard-footer" v-if="budget && accounting">
                                <div class="pull-right" v-if="panel == 'conceptForm'">
                                    <button type="button" class="btn btn-primary btn-wd btn-sm"
                                            :disabled="isDisableNextStep()" data-toggle="tooltip"
                                            title="Presione siguiente para ir a la sección de datos presupuestario/contables"
                                            @click="changePanel('budgetAccountingForm')">
                                        Siguiente
                                    </button>
                                </div>
                                <div class="pull-left" v-if="panel == 'budgetAccountingForm'">
                                    <button type="button" @click="changePanel('conceptForm', true)"
                                            class="btn btn-default btn-wd btn-sm" data-toggle="tooltip"
                                            title="">
                                        Regresar
                                    </button>
                                </div>
                            </div>
                        </form>
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
                            <button type="button" @click="createRecord('payroll/concepts'); $refs.tableResults.refresh()"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-server-table :url="route_list" :columns="columns" :options="table_options"
                                        ref="tableResults">
                            <div slot="description" slot-scope="props">
                                <span v-html="props.row.description"></span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/concepts')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-server-table>
                    </div>
                </div>
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
                    name:                        '',
                    description:                 '',
                    active:                      false,
                    arc:                         false,
                    formula:                     '',
                    formulaShow:                 '',
                    payroll_concept_type_id:     '',
                    institution_id:              '',
                    assign_to:                   '',
                    accounting_account_id:       '',
                    budget_account_id:           '',
                    currency_id:                 '',
                    assign_options:              [],
                    time:                        '',
                    is_strict:                   '',
                    budget_project_id: '',
                    budget_centralized_action_id: '',
                    budget_specific_action_id: '',
                    receiver: '',
                    receiver_account: '',
                    pay_order: true,
                },
                fiscal_years: [],
                variable:                  '',
                variable_option:           '',
                assign_options:            [],
                assign_options_loading:    false,
                type:                      '',
                value:                     '',
                valueFunction:             'Error',
                formulaFunction:           '',
                formulaFunctionShow:       '',
                idCurrentInput:            '',
                getAccount:                false,
                useFunction:               false,
                idFunction:                '',
                formulaHistory:            [],
                formulaShowHistory:        [],
                functions:                 [
                    { "id": "", "text": "Ninguno" },
                    {
                        "id": "if",
                        "text": "SI",
                        "format": "(inputFormTest;inputFormValueIf;inputFormValueElse)",
                        "formatShow": "(Prueba; Valor <<Entonces>>; Valor <<De lo contrario>>)",
                        "description": "Especfica una prueba logica que se desea efectuar.",
                        "parameters": [
                            {
                                "required": "is-required",
                                "id": "inputFormTest",
                                "name": "Prueba",
                                "description": "Cualqueir valor o expresión que pueda evaluarse como VERDADERO o FALSO.",
                                "value": "select case when"
                            },
                            {
                                "required": "",
                                "id": "inputFormValueIf",
                                "name": "Valor <<Entonces>>",
                                "description": "El resultado de la función si la prueba lógica devuelve VERDADERO.",
                                "value": "then"
                            },
                            {
                                "required": "",
                                "id": "inputFormValueElse",
                                "name": "Valor <<De lo contrario>>",
                                "description": "El resultado de la función si la prueba lógica devuelve FALSO.",
                                "value": "else"
                            }
                        ]
                    },
                    {
                        "id": "sum",
                        "text": "SUM",
                        "format": "(number1;number2)",
                        "formatShow": "(Número 1;Número 2)",
                        "description": "Devuelve la suma de los argumentos.",
                        "parameters": [
                            {
                                "required": "is-required",
                                "id": "number1",
                                "name": "Número 1",
                                "description": "Número 1, número 2,... son argumentos cuyo total se calculará.",
                                "value": "+"
                            },
                            {
                                "required": "",
                                "id": "number2",
                                "name": "Número 2",
                                "description": "Número 1, número 2,... son argumentos cuyo total se calculará.",
                                "value": "+"
                            }
                        ]
                    },
                ],
                operator:                  '',
                operators:                 [
                    {"id": "==", "text": "Igualdad (==)", "required_by": ["number", "list", "boolean"]},
                    {"id": "!=", "text": "Desigualdad (!=)", "required_by": ["number", "list", "boolean"]},
                    {"id": ">",  "text": "Mayor estricto (>)", "required_by": ["number"]},
                    {"id": "<",  "text": "Menor estricto (<)", "required_by": ["number"]},
                    {"id": ">=", "text": "Mayor o igual (>=)", "required_by": ["number"]},
                    {"id": "<=", "text": "Menor o igual (<=)", "required_by": ["number"]},
                ],
                subOptions:                [],
                boolSubOptions:            [
                    {"id": "true",  "text": "Verdadero"},
                    {"id": "false",  "text": "Falso"}
                ],

                assignmetRules:            [
                    {"id": "",  "text": "Seleccione..."},
                    {"id": "true",  "text": "Trabajadores que cumplan con todas las opciones seleccionadas"},
                    {"id": "false",  "text": "Trabajadores que cumplan con al menos una de las opciones seleccionadas"}
                ],

                errors:                    [],
                records:                   [],
                columns:                   ['name', 'description', 'id'],

                variable_options:          [],
                institutions:              [],
                payroll_concept_types:     [],
                assign_to:                 [],
                payroll_salary_tabulators: [],
                budget_accounts:           [],
                accounting_accounts:       [],
                currencies:                [],
                projects:                  [],
                centralized_actions:       [],
                specific_actions:          [],
                all_receivers:             [],
                panel: 'conceptForm',

                incidence_types: {
                    'value':          'Valor',
                    'absolute_value': 'Valor absoluto',
                    'tax_unit':       'Unidad tributaria',
                    'percent':        'Porcentaje'
                }
            }
        },
        props: {
            accounting: {
                type: [String,Number],
                required: true
            },
            budget: {
                type: String,
                required: true
            },
            concept_class: {
                type: String,
                required: false,
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'name':           'Nombre',
                'description':    'Descripción',
                'id':              'Acción'
            };
            vm.table_options.sortable       = ['code', 'name', 'description', 'incidence_type'];
            vm.table_options.filterable     = ['code', 'name', 'description', 'incidence_type'];
            vm.table_options.columnsClasses = {
                'name':           'col-xs-4',
                'description':    'col-xs-6',
                'id':             'col-xs-2'
            }
        },
        async mounted() {
            const vm = this;

            await vm.getActualFiscalYear();
            $("#add_payroll_concept").on('show.bs.modal', function() {
                vm.reset();
                vm.getPayrollConceptTypes();
                vm.getInstitutions();
                vm.getOptions('payroll/get-associated-records');
                vm.getPayrollConceptAssignTo();
                vm.getPayrollSalaryTabulators();
                vm.getCurrencies();
                vm.changePanel('conceptForm');
                if (vm.accounting) {
                    vm.getAllAccountingAccounts();
                }
                if(vm.budget) {
                    //vm.getBudgetAccounts();
                    vm.getProjects();
                    vm.getCentralizedActions();
                }

                vm.$refs.formulaResults.setFormula = function(value) {
                    let formulaDisplay = (!vm.useFunction) ? vm.record['formula'] : vm.formulaFunction;
                    let formulaDisplayShow = (!vm.useFunction) ? vm.record['formulaShow'] : vm.formulaFunctionShow;

                    let symbols = ['+', '-', '/', '*', '%'];

                    if (value === 'backspace') {
                        vm.formulaHistory.pop();
                        vm.formulaShowHistory.pop();
                        let dataF = vm.formulaHistory.pop();
                        let dataSF = vm.formulaShowHistory.pop();
                        if (!vm.useFunction) {
                            vm.record['formula'] = ("undefined" != typeof(dataF)) ? dataF : "";
                            vm.record['formulaShow'] = ("undefined" != typeof(dataSF)) ? dataSF : "";
                        } else {
                            vm.formulaFunction = formulaDisplay.substring(0, formulaDisplay.length-1);
                            vm.formulaFunctionShow = formulaDisplayShow.substring(0, formulaDisplayShow.length-1);
                        }
                        return false;
                    } else if (value === 'C') {
                        vm.variable = '';
                        vm.variable_option = '';
                        $.each(vm.functions, function(index, field) {
                            if (field['id'] == "sum") {
                                $.each(vm.functions[index]['parameters'], function(index, field) {
                                    let input = document.getElementById('number' + (index+1));
                                    if (input) input.value = '';
                                });
                            } else if (field['id'] == "if") {
                                $.each(vm.functions[index]['parameters'], function(index, field) {
                                    let input = document.getElementById(field['id']);
                                    if (input) input.value = '';
                                });

                            }
                        });
                        if (!vm.useFunction) {
                            vm.record['formula'] = '';
                            vm.record['formulaShow'] = '';
                        } else {
                            vm.formulaFunction = '';
                            vm.formulaFunctionShow = '';
                        }
                        return false;
                    }

                    if (formulaDisplay.length === 0 && symbols.includes(value)) {
                        vm.showMessage(
                            'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                            'No esta permitido indicar símbolos como primer elemento de la fórmula'
                        );
                        return false;
                    } else if (symbols.includes(formulaDisplay.slice(-1)) && symbols.includes(value)) {
                        vm.showMessage(
                            'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                            'No esta permitido indicar símbolos de forma consecutiva'
                        );
                        return false;
                    }

                    if (value === 0 && formulaDisplay.slice(-1) === '/') {
                        vm.showMessage(
                            'custom', 'Fórmula Inválida', 'warning', 'screen-warning', 'La división por cero no esta permitida'
                        );
                        return false;
                    }
                    /** Se asigna los valores al campo determinado */
                    if (!vm.useFunction) {
                        formulaDisplay += value;
                        formulaDisplayShow += value;
                        vm.record['formula'] = formulaDisplay;
                        vm.record['formulaShow'] = formulaDisplayShow;
                    } else {
                        if (vm.idFunction != "") {
                            $.each(vm.getInfoFunction["parameters"] ?? [], function(index, field) {
                                if (vm.getInfoFunction["currentParamenter"]) {
                                    if (vm.getInfoFunction["currentParamenter"]["id"] == field["id"]) {
                                        let element = document.getElementById(field["id"]);
                                        if (element) {
                                            element.value += value;
                                            vm.getFormulaFunction();
                                        }
                                    }
                                }
                            });
                        } else {
                            formulaDisplay += value;
                            formulaDisplayShow += value;
                            vm.formulaFunction = formulaDisplay;
                            vm.formulaFunctionShow = formulaDisplayShow;
                        }
                    }
                };
                vm.record.pay_order = true;
            });
        },
        watch: {
            'record.formula': {
                handler(newVal) {
                    const vm = this;
                    if (newVal && "" != newVal) {
                        vm.formulaHistory.push(newVal);
                    }
                },
                deep: true,
                immediate: false,
            },
            'record.formulaShow': {
                handler(newVal) {
                    const vm = this;
                    if (newVal && "" != newVal) {
                        vm.formulaShowHistory.push(newVal);
                    }
                },
                deep: true,
                immediate: false,
            },
            /**
             * Método que supervisa los cambios en el campo variable y actualiza el listado de opciones
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            variable: function(variable) {
                const vm = this;
                vm.operator = vm.value = '';
                if (vm.variable == 'parameter') {
                    vm.getOptions('payroll/get-parameters');
                } else if (vm.variable == 'worker_record') {
                    vm.getOptions('payroll/get-associated-records');
                } else if (vm.variable == 'vacation') {
                    vm.getOptions('payroll/get-vacation-associated-records');
                } else if (vm.variable == 'benefit') {
                    vm.getOptions('payroll/get-benefit-associated-records');
                } else if (vm.variable == 'tabulator') {
                    vm.getOptions('payroll/get-salary-tabulators');
                } else if (vm.variable == 'concept') {
                    vm.getOptions('payroll/get-concepts');
                } else if (vm.variable == 'ari_register') {
                    vm.variable_option = vm.variable;
                    vm.variable_options = [];
                } else {
                    vm.variable_options = [];
                }
            },
            formulaFunction: function(value) {
                const vm = this;
                vm.valueFunction = (value == '') ? 'Error' : 'Numeric';
            },
            /**
             * Método que supervisa los cambios en el campo type y actualiza el listado de opciones
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            type: function(type) {
                const vm = this;
                if (vm.type == 'list') {
                    axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                        vm.subOptions = response.data;
                    });
                } else if (vm.type == 'boolean') {
                    vm.value = false;
                }
            },
            record: {
                deep: true,
                handler: function() {
                    const vm = this;
                        vm.record.receiver_account = (vm.record.receiver && vm.record.receiver.accounting_account_id && '' !== vm.record.receiver.accounting_account_id)
                            ? vm.record.receiver.accounting_account_id
                            : vm.record.receiver_account;
                }
            }
        },
        computed: {
            /**
             * Método que actualiza el nombre de la variable a emplear en el cálculo
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             * @return    {string}
             */
            updateNameVariable: function() {
                const vm = this;
                var response = '';
                if (vm.variable_option != '') {
                    $.each(vm.variable_options, function(index, field) {
                        if (field['id'] == vm.variable_option) {
                            response = field['text'];
                        } else if (typeof field['children'] !== 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (field['id'] == vm.variable_option) {
                                    response = field['text'];
                                }
                            });
                        }
                    });
                }
                if (vm.variable_option == 'ari_register') {
                    response = 'Registro ARI';
                }
                return response;
            },
            /**
             * Método que actualiza los inputs de opciones a asignar
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             * @return    {void}
             */
            updateAssignOptions: function() {
                const vm = this;
                $.each(vm.record.assign_to, async(index, field) => {
                    if (field['type'] == 'list') {
                        if (typeof(vm.record.assign_options[field['id']] ) == 'undefined') {
                            vm.record.assign_options[field['id']] = [];
                            vm.assign_options[field['id']] = [];
                            vm.assign_options_loading = true;

                            axios.get(`${window.app_url}/payroll/get-concept-assign-options/${field['id']}`)
                            .then(response => {
                                vm.assign_options_loading = false;
                                vm.assign_options[field['id']] = response.data;
                            });
                        };
                    }
                    if (field['type'] == 'range') {
                        if (typeof(vm.record.assign_options[field['id']] ) == 'undefined') {
                            vm.record.assign_options[field['id']] = {
                                minimum: '',
                                maximum: ''
                            };
                        }
                        if (typeof(vm.assign_options[field['id']] ) == 'undefined') {
                            vm.assign_options[field['id']] = {
                                minimum: '',
                                maximum: ''
                            };
                        }
                    }
                });

                /** Recorrer las opciones "asignar a" para eliminar los inputs desmarcados */
                $.each(vm.record.assign_options, function(index, field) {
                    let id = index;
                    let find = false;
                    $.each(vm.record.assign_to, function(index, field) {
                        if (id == field['id']) {
                            find = true;
                        }
                    });
                    if (!find) {
                        delete vm.record.assign_options[index];
                    }
                });

                const timeOpen = setTimeout(addInstitutionId, 1000);
                function addInstitutionId () {
                    vm.record.time = vm.record.time ? vm.record.time+1 : 1;
                }
            },
            getInfoFunction() {
                const vm = this;
                let objectFunction = null;
                $.each(vm.functions, function(index, field) {
                    if (field['id'] == vm.idFunction) {
                        objectFunction = field;
                    }
                });
                if (vm.idCurrentInput != '') {
                    $.each(objectFunction['parameters'], function(index, field) {
                        if (field['id'] == vm.idCurrentInput) {
                            objectFunction['currentParamenter'] = field;
                        }
                    });
                }
                return objectFunction;
            },
            getSignConceptType: function() {
                const vm = this;
                return vm.payroll_concept_types.filter(
                    conceptType => conceptType['id'] == vm.record.payroll_concept_type_id
                )[0]['sign'];
            },
            /**
             * Metodo que devuelve los operadores disponibles
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @return  {Object}  Objeto con los operadores
             */
            filteredOperators() {
                return this.operators.filter(op => {
                    return (
                        op['required_by'].includes(this.type) ||
                        this.type === '' ||
                        this.variable === 'parameter' ||
                        this.variable === 'concept' ||
                        this.variable === 'tabulator'
                    );
                });
            }
        },
        methods: {
            initRecords(url, modal_id) {
                this.errors = [];
                if (typeof this.reset === 'function') {
                    this.reset();
                }
                if (typeof(this.$refs.tableResults) !== "undefined") {
                    this.$refs.tableResults.refresh();
                }
                if (modal_id) {
                    $(`#${modal_id}`).modal('show');
                }
            },
            /**
             * Método que permite borrar todos los datos del formulario
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            reset() {
                const vm = this;
                vm.variable = '';
                vm.variable_option = '';
                vm.errors = [];
                vm.useFunction = false;
                vm.formulaHistory = [];
                vm.formulaShowHistory = [];
                $.each(vm.functions, function(index, field) {
                    if (field['id'] == "sum") {
                        $.each(vm.functions[index]['parameters'], function(index, field) {
                            let input = document.getElementById('number' + (index+1));
                            if (input) input.value = '';
                        });
                    } else if (field['id'] == "if") {
                        $.each(vm.functions[index]['parameters'], function(index, field) {
                            let input = document.getElementById(field['id']);
                            if (input) input.value = '';
                        });

                    }
                });
                vm.record = {
                    id:                          '',
                    name:                        '',
                    description:                 '',
                    active:                      false,
                    arc:                         false,
                    formula:                     '',
                    formulaShow:                 '',
                    currency_id:                 '',
                    payroll_concept_type_id:     '',
                    institution_id:              '',
                    assign_to:                   '',
                    accounting_account_id:       '',
                    budget_account_id:           '',
                    is_strict:                   '',
                    budget_project_id:                  '',
                    budget_centralized_action_id:       '',
                    budget_specific_action_id:          '',
                    assign_options:              {},
                    receiver: '',
                    receiver_account: '',
                    pay_order: true,
                };
                vm.getCurrencies();
                //vm.getBudgetAccounts();
                vm.getAllAccountingAccounts();
                vm.changePanel('conceptForm');
            },
            getFormulaFunction(value = '') {
                const vm = this;
                let result = '';
                let resultShow = vm.getInfoFunction["format"] ?? '';
                $.each(vm.getInfoFunction["parameters"] ?? [], function(index, field) {
                    if ((field["id"] == 'inputFormTest') && (vm.variable_option != '')) {
                        let elementOp  = document.getElementById(field["id"] + 'Operator');
                        let elementVal = document.getElementById(field["id"] + 'Value');
                        if ((elementOp) && (elementVal)) {
                            resultShow = resultShow.replace(
                                            field["id"], vm.updateNameVariable + ' ' + elementOp.value + ' ' +(
                                            (typeof elementVal.options !== "undefined")
                                                ? elementVal.options[elementVal.selectedIndex].text
                                                : elementVal.value)
                                        );
                            result += field["value"] + " " + vm.variable_option + " " + elementOp.value + " " + elementVal.value + " ";
                        }
                    } else {
                        let element = document.getElementById(field["id"]);
                        if (element) {
                            resultShow = resultShow.replace(field["id"], element.value);
                            result += ((index > 0) ? (field["value"] + " ") : "") + element.value + " ";
                        }
                    }
                });
                vm.formulaFunction = result.trim();
                vm.formulaFunctionShow = vm.getInfoFunction["text"] + resultShow;
            },
            /**
             * Obtiene un listado de cuentas patrimoniales
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getAccountingAccounts(account_id) {
                const vm = this;

                if (!vm.getAccount) {
                    vm.getAccount = true;
                    if (account_id) {
                        await axios.get(`${window.app_url}/payroll/get-concept-accounting-accounts/${account_id}`).then(response => {
                            if (response.data.length > 0) {
                                vm.record.accounting_account_id = response.data[0].id;
                            } else {
                                vm.record.accounting_account_id = '';
                            }
                        }).catch(error => {
                            vm.logs('PayrollConceptsComponent', 258, error, 'getAccountingAccounts');
                        });
                    }
                    vm.getAccount = false;
                }
            },

            /**
             * Obtiene un listado de cuentas patrimoniales relacionado con cuenta contable
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            async getBudgetAccounting(account_id) {
                const vm = this;

                if (!vm.getAccount) {
                    vm.getAccount = true;
                    if (account_id) {
                        await axios.get(`${window.app_url}/payroll/get-concept-accountable/${account_id}`).then(response => {
                            if (response.data.length > 0) {
                                vm.record.budget_account_id = response.data[0].id;
                            } else {
                                vm.record.budget_account_id = '';
                            }
                        }).catch(error => {
                            vm.logs('PayrollConceptsComponent', 258, error, 'getBudgetAccounts');
                        });
                    }
                    vm.getAccount = false;
                }
            },

            /**
             * Obtiene un listado de todas las cuentas contables
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            getAllAccountingAccounts() {
                const vm = this;
                vm.accounting_accounts = [];
                axios.get(`${window.app_url}/accounting/get_accounts`).then(response => {
                    if (response.data.length > 0) {
                        vm.accounting_accounts.push({
                            id:   '',
                            text: 'Seleccione...'
                        });
                        $.each(response.data, function() {
                            vm.accounting_accounts.push({
                                id:   this.id,
                                text: `${this.code} - ${this.denomination}`,
                                disabled: `${this.code}`.split('.')[6] == '000' ? true : false
                            });
                        });
                    }
                }).catch(error => {
                });
            },

            /**
             * Obtiene un listado de cuentas presupuestarias
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getBudgetAccounts(specificActionId) {
                const vm = this;
                vm.budget_accounts = [];
                if(specificActionId == ""){
                    vm.record.accounting_account_id = "";
                    return false
                }
                await axios.get(
                         `${window.app_url}/budget/get-opened-accounts/${specificActionId}/${vm.fiscal_years}-01-01`
                     ).then(response => {
                         if (response.data.result) {
                            let ObjectResponse = response.data.records;
                            const firtElement = ObjectResponse[0];
                            ObjectResponse.shift();
                              let BudgetAccounts = ObjectResponse.map(objeto => {
                                const textwithoutAmount = objeto.text.replace(/\([^()]*\)/g, '').trim();
                                return { ...objeto, text: textwithoutAmount };
                            });
                            vm.budget_accounts = [ firtElement, ...BudgetAccounts]
                         }
                         if (response.data.records.length === 1 && response.data.records[0].id === "") {
                             vm.showMessage(
                                 'custom', 'Alerta!', 'danger', 'screen-error',
                                 `No existen cuentas aperturadas para esta acción específica o con saldo para la fecha
                                 seleccionada`
                             );
                         }

                     }).catch(error => {
                         console.error(error);
                     });
                     if(vm.record.id === ''){
                        vm.record.budget_account_id = ''
                        vm.record.accounting_account_id = ''
                     }

                     if (this.record.budget_account) {
                        this.record.budget_account_id = vm.record.budget_account.id;
                     }
            },
             /**
             * Método que realiza una consulta para obtener el año fiscal actual
             *
             * @author    Manuel Zambrano <mazambrano@cenditel.gob.ve>
             */
            async getActualFiscalYear (){
                const vm = this
                await axios.get(`${window.app_url}/get-execution-year`).then(response => {
                     vm.fiscal_years = response.data.year
                }).catch(error => {
                         console.error(error);
                });
            },

            /**
             * Método que realiza una consulta para obtener todos los receptores que coincidan
             * con el query de la búsqueda
             *
             * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            searchReceivers (query) {
                const vm = this;
                vm.all_receivers = [];

                axios.get(`${window.app_url}/all-receivers`, {params: {query:query}}).then(response => {
                    vm.all_receivers = response.data;
                });
            },

            addTag (newTag) {
                const vm = this;
                let tag = [
                    {
                        label: 'Otros',
                        group: [
                            {
                                id: '',
                                text: newTag,
                                class: null,
                                group: 'Otros'
                            },
                        ]
                    }
                ]

                vm.all_receivers.push(tag);
                vm.record.receiver = tag[0]['group'][0];
            },

            /**
             * Método que obtiene un arreglo con las opciones a listar
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getOptions(url) {
                const vm = this;
                vm.variable_options = [];
                url = vm.setUrl(url);

                axios.get(url).then(response => {
                    vm.variable_options = response.data;
                });
            },
            /**
             * Método que obtiene un arreglo con las opciones de "asignar a" de un concepto
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getPayrollConceptAssignTo() {
                const vm = this;
                vm.assign_to = [];
                axios.get(`${window.app_url}/payroll/get-concept-assign-to`).then(response => {
                    vm.assign_to = response.data;
                });
            },
            /**
             * Método que obtiene el acrónimo de la variable a emplear en el cálculo
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             * @return    {string}
             */
            setVariable() {
                const vm = this;
                let variables = ['parameter', 'tabulator', 'concept'];
                let formulaDisplay = (!vm.useFunction) ? vm.record['formula'] : vm.formulaFunction;
                let formulaDisplayShow = (!vm.useFunction) ? vm.record['formulaShow'] : vm.formulaFunctionShow;

                /** Se asigna los valores al campo determinado */
                if (!vm.useFunction) {
                    formulaDisplay += (variables.includes(vm.variable))
                        ? (vm.variable + '(' + vm.variable_option + ')' )
                        : vm.variable_option;
                    formulaDisplayShow += vm.updateNameVariable;
                    vm.record['formula'] = formulaDisplay;
                    vm.record['formulaShow'] = formulaDisplayShow;
                } else {
                    if (vm.idFunction == "") {
                        formulaDisplay += (variables.includes(vm.variable))
                            ? (vm.variable + '(' + vm.variable_option + ')' )
                            : vm.variable_option;
                        formulaDisplayShow += vm.updateNameVariable;
                        vm.formulaFunction = formulaDisplay;
                        vm.formulaFunctionShow = formulaDisplayShow;
                    } else {
                        let element = document.getElementById(vm.getInfoFunction["currentParamenter"]["id"]);
                        if (element) {
                            element.value += vm.updateNameVariable;
                            vm.getFormulaFunction(((variables.includes(vm.variable))
                                                            ? (vm.variable + '(' + vm.variable_option + ')' )
                                                            : vm.variable_option));
                        }
                    }
                }
            },
            getCodeVariable() {
                const vm = this;
                let response = '';
                let showFormula = '';
                if (vm.variable_option != '') {
                    $.each(vm.variable_options, function(index, field) {
                        if (field['id'] == vm.variable_option) {
                            if (vm.operator == '') {
                                if ((vm.value == '') && (vm.variable != 'vacation') && (vm.variable != 'benefit')) {
                                    response = vm.variable + '(' + field['id'] + ')';
                                    showFormula = field['text'];
                                } else {
                                    response = field['id'];
                                    showFormula = field['text'];
                                }
                            } else {
                                /**response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';*/
                            }
                        } else if (typeof field['children'] !== 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (typeof field['id'] !== 'undefined') {
                                    if (field['id'] == vm.variable_option) {
                                        if (vm.operator == '') {
                                            response = field['id'];
                                            showFormula = field['text'];
                                        } else {
                                            /**response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                            showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                            */
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
                if (response != '') {
                    if (vm.record.formula != '') {
                        let keys = vm.record.formula.indexOf('}');
                        if (keys > 0) {
                            let firstFormula = vm.record.formula.substr(0, keys);
                            let lastFormula = vm.record.formula.substr(keys, vm.record.formula.length);
                            vm.record.formula = firstFormula + response + lastFormula;
                        } else {
                            vm.record.formula += response;
                        }
                    } else {
                        vm.record.formula += response;
                    }
                }

                if (showFormula != '') {
                    if (vm.record.formulaShow != '') {
                        let keys = vm.record.formulaShow.indexOf('}');
                        if (keys > 0) {
                            let firstFormula = vm.record.formulaShow.substr(0, keys);
                            let lastFormula = vm.record.formulaShow.substr(keys, vm.record.formulaShow.length);
                            vm.record.formulaShow = firstFormula + showFormula + lastFormula;
                        } else {
                            vm.record.formulaShow += showFormula;
                        }
                    } else {
                        vm.record.formulaShow += showFormula;
                    }
                }
            },
            getOptionType() {
                const vm = this;
                //vm.type = '';
                if (vm.variable_option != '') {
                    $.each(vm.variable_options, function(index, field) {
                        if (field['id'] == vm.variable_option) {
                            if (vm.type == field['type']) {
                                axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                                    vm.subOptions = response.data;
                                });
                            }
                            if (typeof field['type'] !== 'undefined') {
                                vm.type = field['type'];
                                return;
                            }
                        } else if (typeof field['children'] !== 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (field['id'] == vm.variable_option) {
                                    if (vm.type == field['type']) {
                                        axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                                            vm.subOptions = response.data;
                                        });
                                    }
                                    if (typeof field['type'] !== 'undefined') {
                                        vm.type = field['type'];
                                        return;
                                    }
                                }
                            });
                        }
                    });
                }
                if (vm.type == 'list') {
                    axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                        vm.subOptions = response.data;
                    });
                } else if (vm.type == 'boolean') {
                    vm.value = false;
                }
            },
            /**
             * Reescribe el método initUpdate para cambiar su comportamiento por defecto
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Identificador del registro a ser modificado
             * @param {object} event   Objeto que gestiona los eventos
             */
            async initUpdate(id, event) {
                let vm = this;
                vm.errors = [];
                vm.loading = true;
                event.preventDefault();

                let recordEdit = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.formulaHistory = [];
                vm.formulaShowHistory = [];
                recordEdit.formulaShow = recordEdit.translate_formula ?? recordEdit.formula;
                recordEdit.is_strict = recordEdit.is_strict == true ? 'true' : 'false';
                if (recordEdit.receiver) {
                    await axios.get(`${window.app_url}/all-receivers`, {params: {query:recordEdit.receiver.description}}).then(response => {
                        vm.all_receivers = response.data;
                    });
                    recordEdit.receiver_account = recordEdit.receiver.associateable_id
                }

                vm.record = await recordEdit;

                if (vm.record.budget_project_id) {
                    await vm.getSpecificActions('Project');
                } else if (vm.record.budget_centralized_action_id) {
                    await vm.getSpecificActions('CentralizedAction');
                }

                $.each(vm.record.assign_to, async(index, field) => {
                if (field['type'] == 'range') {
                        vm.record.assign_options[field['id']] = {
                            minimum: vm.record.assign_options[field['id']]['minimum'],
                            maximum: vm.record.assign_options[field['id']]['maximum']
                        };
                        vm.assign_options[field['id']] = {
                            minimum: '',
                            maximum: ''
                        };
                    }
                });
                setTimeout(() => {
                vm.updateAssignOptionsMethod();
                vm.loading = false;
                }, 1000);
            },
            /**
             * Reescribe el método deleteRecord para cambiar su comportamiento por defecto
             * Método para la eliminación de registros
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} id    ID del Elemento seleccionado para su eliminación
             * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
             */
            deleteRecord(id, url) {
                const vm = this;
                /** @type {string} URL que atiende la petición de eliminación del registro */
                var url = vm.setUrl((url)?url:vm.route_delete);

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
                            /** @type {object} Objeto con los datos del registro a eliminar */
                            let recordDelete = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter((rec) => {
                                return rec.id === id;
                            })[0]));

                            await axios.delete(`${url}${url.endsWith('/')?'':'/'}${recordDelete.id}`).then(response => {
                                if (typeof(response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                                vm.records = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter((rec) => {
                                    return rec.id !== id;
                                })));
                                if (typeof(vm.$refs.tableResults) !== "undefined") {
                                    vm.$refs.tableResults.refresh();
                                }
                                vm.showMessage('destroy');
                            }).catch(error => {
                                if (typeof(error.response) !="undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                }
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                            vm.loading = false;
                        }
                    }
                });
            },
            openFunctionWizard(reset = false, fx = '') {
                const vm = this;
                vm.useFunction = !vm.useFunction;
                vm.idFunction = fx;

                if (!reset)  {
                    vm.record.formula += vm.formulaFunction;
                    vm.record.formulaShow += vm.formulaFunctionShow;
                    //vm.$refs.formulaResults.setFormula(vm.formulaFunctionShow);
                } else {
                    vm.variable_option = '';
                    vm.variable = '';
                }
                vm.formulaFunction = '';
                vm.formulaFunctionShow = '';
            },
            addParameter() {
                const vm = this;
                $.each(vm.functions, function(index, field) {
                    if (field['id'] == "sum") {
                        let format = '(';
                        let formatShow = '(';
                        let element = {
                            required: "",
                            id: "number" + (vm.functions[index]['parameters'].length + 1),
                            name: "Número " + (vm.functions[index]['parameters'].length + 1),
                            description: "Número 1, número 2,... son argumentos cuyo total se calculará.",
                            value: "+"
                        };
                        for (let i = 1; i <= (vm.functions[index]['parameters'].length + 1); i++) {
                            format += "number"+i+(i != (vm.functions[index]['parameters'].length + 1) ? ";" : "");
                            formatShow += "Número "+i+(i != (vm.functions[index]['parameters'].length + 1) ? ";" : "");
                        }
                        format += ")";
                        formatShow += ")";
                        vm.functions[index]['format'] = format;
                        vm.functions[index]['formatShow'] = formatShow;
                        vm.functions[index]['parameters'].push(element);
                        vm.functions[index]['currentParamenter'] = element;
                        vm.idCurrentInput = "number" + (vm.functions[index]['parameters'].length + 1);
                    }
                });
            },
            deleteParameter() {
                const vm = this;
                $.each(vm.functions, function(index, field) {
                    if (field['id'] == "sum") {
                        let format = '(';
                        let formatShow = '(';
                        for (let i = 1; i <= (vm.functions[index]['parameters'].length - 1); i++) {
                            format += "number"+i+(i != (vm.functions[index]['parameters'].length - 1) ? ";" : "");
                            formatShow += "Número "+i+(i != (vm.functions[index]['parameters'].length - 1) ? ";" : "");
                        }
                        format += ")";
                        formatShow += ")";
                        vm.functions[index]['format'] = format;
                        vm.functions[index]['formatShow'] = formatShow;
                        vm.functions[index]['currentParamenter'] = vm.functions[index]['parameters'].slice(vm.functions[index]['parameters'].length - 2, vm.functions[index]['parameters'].length - 1)[0];
                        vm.functions[index]['parameters'] = vm.functions[index]['parameters'].slice(0, vm.functions[index]['parameters'].length - 1);
                        vm.idCurrentInput = "number" + vm.functions[index]['parameters'].length - 1;
                    }
                });
            },
            /**
             * Método que marca los items seleccionados de los select assing_options a partir de los que es estén a
             * su vez seleccionados en assing_to del concepto a editar.
             *
             * @author    Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
             * @return    {array}
             */
            updateAssignOptionsMethod() {
                const vm = this;
                $.each(vm.record.assign_to, async(index, field) => {
                    if (field['type'] == 'list') {
                        vm.record.assign_options[field['id']] = [];
                        vm.assign_options[field['id']] = [];
                        vm.assign_options_loading = true;

                        axios.get(`${window.app_url}/payroll/get-concept-assign-options/${field['id']}`)
                        .then(response => {
                            vm.assign_options[field['id']] = response.data;

                            // Consulta todos assign_options asociados al registro a editar
                            $.each(vm.record.payroll_concept_assign_options, async(indexone, assign_option) => {

                                // Filtra los items se la lista assign_options que sean iguales al field id
                                if (assign_option.key == field['id']){

                                    // Para que el select lea la opción, se tiene que buscar en la lista todos sus
                                    // atributos y mandárselos en el array record.assign_options
                                    let find_options = (vm.assign_options[field['id']]?vm.assign_options[field['id']]: []).find(
                                        (element) => {
                                            return element.id == assign_option.assignable_id;
                                        }
                                    );
                                    vm.record.assign_options[field['id']].push(find_options);
                                }
                            });
                        });
                    }

                    vm.assign_options_loading = false;
                });


            },
            /**
             * Método que habilita o deshabilita el botón siguiente
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            isDisableNextStep() {
                const vm = this;
                if (vm.panel == 'conceptForm') {
                    return false;
                } else if (vm.panel == 'budgetAccountingForm') {
                    return false
                }
            },
            /**
             * Método que cambia el panel de visualización
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             *
             * @param     {string}     panel        Panel seleccionado
             * @param     {boolean}    complete     Determina si se movera al panel
             */
            changePanel(panel, complete = false) {
                const vm = this;

                // En caso de true se omite esta validacion
                if (!complete) {
                    complete = !vm.isDisableNextStep();
                }

                if (complete == true) {
                    vm.panel = panel;
                    let element = document.getElementById(panel);
                    if (element) {
                        element.click();
                    }
                }
            },
            /**
             * Obtiene un arreglo con los proyectos
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @param  {integer} id Identificador del proyecto a buscar, este parámetro es opcional
             */
            async getProjects(id) {
                const vm = this;
                var budget_project_id = typeof id !== 'undefined' ? '/' + id : '';

                const url = vm.setUrl(`budget/get-projects-assigned`);
                await axios.get(url).then(response => {
                    vm.projects = response.data;
                }).catch(error => {
                    console.error(error);
                });
            },
            /**
             * Obtiene un arreglo con las acciones centralizadas
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @param  {integer} id Identificador de la acción centralizada a buscar, este parámetro es opcional
             */
            async getCentralizedActions(id) {
                const vm = this;
                var budget_centralized_action_id = typeof id !== 'undefined' ? '/' + id : '';

                const url = vm.setUrl(`budget/get-centralized-actions-assigned`);
                await axios.get(url).then(response => {
                    vm.centralized_actions = response.data;
                });
            },
            /**
             * Obtiene las Acciones Específicas
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @param {string} type Tipo de registro
             */
            async getSpecificActions(type) {
                const vm = this;

                if (!vm.budget) {
                    return;
                }

                let id =
                    type === 'Project'
                        ? this.record.budget_project_id
                        : this.record.budget_centralized_action_id;

                this.specific_actions = [];

                if (id) {
                    await axios.get(
                        `${window.app_url}/budget/get-specific-actions/${type}/${id}/report`
                    ).then(response => {
                        this.specific_actions = response.data;
                    })
                    .catch(error => {
                        vm.logs(
                            'BudgetSubSpecificFormulationComponent.vue',
                            551,
                            error,
                            'getSpecificActions'
                        );
                    });
                    if (vm.record.id && vm.record.budget_specific_action) {
                        vm.record.budget_specific_action_id = vm.record.budget_specific_action.id;
                    }
                }
                var len = this.specific_actions.length;
                $('#budget_specific_action_id').attr('disabled', len == 0);
            },

            budgetAccountingFormIsRequired() {
                const vm = this;
                if (vm.panel == 'budgetAccountingForm') {
                    return (vm.record.budget_centralized_action_id != '' || vm.record.budget_project_id != '')
                }
                return false
            }
        }
    };
</script>
