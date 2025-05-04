<template>
    <section id="payrollVacationPoliciesFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de políticas vacacionales" data-toggle="tooltip"
           @click="addRecord('add_payroll_vacation_policy', 'payroll/vacation-policies', $event)"
        >
            <i class="icofont icofont-ui-flight ico-3x"></i>
            <span>Políticas<br>Vacacionales</span>
        </a>
        <div id="add_payroll_vacation_policy" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-flight ico-3x"></i>
                            Política vacacional
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- mensajes de error -->
                        <div v-if="errors.length > 0" class="alert alert-danger">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>¡Atención!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click.prevent="errors = []">
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
                        <div class="wizard-tabs with-border">
                            <ul class="nav wizard-steps">
                                <li :class="panel=='vacationPolicyForm' ? 'nav-item active' : 'nav-item'">
                                    <a id="vacationPolicyForm" class="nav-link text-center" data-toggle="tab"
                                       :href="panel != 'vacationPolicyForm' ?'#':'#w-vacationPolicyForm'"
                                       @click="changePanel('vacationPolicyForm')">
                                        <span class="badge">1</span>
                                        Política vacacional
                                    </a>
                                </li>
                                <li :class="panel=='vacationRequestForm' ? 'nav-item active' : 'nav-item'">
                                    <a id="vacationRequestForm" class="nav-link text-center" data-toggle="tab"
                                       :href="panel !='vacationRequestForm' ?'#':'#w-vacationRequestForm'"
                                        @click="changePanel('vacationRequestForm')"
                                    >
                                        <span class="badge">2</span>
                                        Solicitud de vacaciones
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <form class="form-horizontal">
                            <div class="tab-content">
                                <div id="w-vacationPolicyForm" :class="panel=='vacationPolicyForm' ? 'tab-pane p-3 active' : 'tab-pane p-3'">
                                    <div class="row">
                                        <!-- nombre -->
                                        <div class="col-md-6">
                                            <div class="form-group is-required">
                                                <label>Nombre:</label>
                                                <input type="text" class="form-control input-sm" placeholder="Nombre de política vacacional" data-toggle="tooltip" title="Indique el nombre del tabulador (requerido)" v-model="record.name"
                                                :disabled="record.hasAssociated">
                                                <input type="hidden" v-model="record.id">
                                            </div>
                                        </div>
                                        <!-- ./nombre -->
                                        <!-- fecha de aplicación -->
                                        <div class="col-md-3">
                                            <div class="form-group is-required">
                                                <label>Desde:</label>
                                                <input type="date"
                                                    id="start_date"
                                                    placeholder="Desde"
                                                    data-toggle="tooltip"
                                                    title="Indique la fecha de aplicación asociada a la política vacacional"
                                                    class="form-control input-sm" :min="start_operations_date"
                                                    :max="(record.end_date == '') ? '' : record.end_date" v-model="record.start_date"
                                                    :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hasta:</label>
                                                <input type="date" id="end_date" placeholder="Hasta" data-toggle="tooltip" title="Indique la fecha de aplicación asociada a la política vacacional" :min="record.start_date" :disabled="(record.start_date == '')" class="form-control input-sm" v-model="record.end_date">
                                            </div>
                                        </div>
                                        <!-- ./fecha de aplicación -->
                                        <!-- activa -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>¿Activo?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                         title="¿La política vacacional se encuentra activa actualmente?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="vacationalPolicieActive" v-model="record.active"
                                                                :value="true"
                                                                >
                                                        <label class="custom-control-label" for="vacationalPolicieActive"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./activa -->
                                        <!-- organización -->
                                        <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label>Organización:</label>
                                                <select2 :options="institutions" v-model="record.institution_id"
                                                :disabled="record.hasAssociated"></select2>
                                            </div>
                                        </div>
                                        <!-- ./organización -->
                                        <!-- tipo de vacaciones -->
                                        <div class="col-md-6">
                                            <div class="form-group is-required">
                                                <label>Tipo de vacaciones:</label>
                                                <select2 :options="vacation_types" @input="switchTypeVacation()" v-model="record.vacation_type"
                                                :disabled="record.hasAssociated"></select2>
                                            </div>
                                        </div>
                                        <!-- ./tipo de vacaciones -->
                                    </div>
                                    <h6 class="card-title" v-if="record.vacation_type == 'collective_vacations'">
                                        Salidas colectivas <i class="fa fa-plus-circle cursor-pointer" title="Nueva fecha de salida" data-toggle="tooltip" @click="addVacationPeriod()"></i>
                                    </h6>
                                    <div class="row" v-for="(vacation_period, index) in record.vacation_periods" :key="index">
                                        <!-- fecha de inicio del período de vacaciones colectivas -->
                                        <div class="col-md-5">
                                            <div class="form-group is-required">
                                                <label>Fecha de inicio:</label>
                                                <input @input=getCalculateTime(index) type="date" :id="'start_date_vacation_' + index" placeholder="Fecha de inicio" data-toggle="tooltip" title="Indique la fecha del inicio del salidas individuales" :min="record.start_date" :max="(vacation_period.end_date == '') ? record.end_date : vacation_period.end_date" class="form-control input-sm" v-model="vacation_period.start_date">
                                            </div>
                                        </div>
                                        <!-- ./fecha de inicio del período de vacaciones colectivas -->
                                        <!-- fecha de finalización del período de vacaciones colectivas -->
                                        <div class="col-md-5">
                                            <div class="form-group is-required">
                                                <label>Fecha de Finalización:</label>
                                                <input @input=getCalculateTime(index) type="date" :id="'end_date_vacation_' + index" placeholder="Fecha de Finalización" data-toggle="tooltip" title="Indique la fecha de Finalización del salidas individuales" :min="vacation_period.start_date" :max="record.end_date" :disabled="(vacation_period.start_date == '')" class="form-control input-sm" v-model="vacation_period.end_date">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label> Días a otorgar para el disfrute de vacaciones:</label>
                                                <input type="text" data-toggle="tooltip" :id="'vacations_days_' + index" title="Días a otorgar para el disfrute de vacaciones" class="form-control input-sm" disabled>
                                            </div>
                                        </div>
                                        <!-- activa -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>¿Son días hábiles?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                         title="¿La fecha incluye días hábiles?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="vacationalPolicieBusinessDays"
                                                                v-model="vacation_period.business_days"
                                                                :value="true" @change="getCalculateTime(index)">
                                                        <label class="custom-control-label" for="vacationalPolicieBusinessDays"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./activa -->
                                        <!-- ./fecha de finalización del período de vacaciones colectivas -->
                                        <div class="col-1" style="align-self: flex-end;">
                                            <div class="form-group">
                                                <button class="btn btn-sm btn-danger btn-action" type="button" @click="removeRow(index, record.vacation_periods)" title="Eliminar registro" data-toggle="tooltip">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tipo de vacaciones individuales -->
                                    <div class="row" style="align-items: baseline;" v-if="record.vacation_type == 'vacation_period'">
                                        <!-- Días a otorgar para el disfrute de vacaciones -->
                                        <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label> Días a otorgar para el disfrute de vacaciones:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique la cantidad de días a otorgar para el disfrute de vacaciones" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.vacation_days"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./Días a otorgar para el disfrute de vacaciones -->

                                        <!-- Períodos vacacionales permitidos por año -->
                                        <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label> Períodos vacacionales permitidos por año:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique la cantidad de períodos vacacionales permitidos por año" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.vacation_period_per_year"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./Períodos vacacionales permitidos por año -->

                                        <!-- Días de disfrute adicionales a otorgar según años de servicios -->
                                        <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label>Días de disfrute adicionales a otorgar según años de servicios:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique la cantidad de días de disfrute adicionales a otorgar según años de servicios" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.additional_days_per_year"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./Días de disfrute adicionales a otorgar según años de servicios -->

                                         <!-- A partir de que año de servicio se otorgan los días de disfrute adicionales-->
                                         <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label>A partir de que año de servicio se otorgan días adicionales:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique el año a partir del cual se comienzan a aumentar los días de disfrute" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.from_year"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./A partir de que año de servicio se otorgan los días de disfrute adicionales -->

                                         <!-- Intervalo en años de servicios para el aumento de días de disfrute-->
                                         <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label> Intervalo en años de servicios para el aumento de días de disfrute:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique el intervalo en años de servicios para el aumento de días de disfrute" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'min': 1,
                                                            'digits': 0" v-model="record.years_for_additional_days"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./Intervalo en años de servicios para el aumento de días de disfrute -->

                                        <!-- Días de disfrute de vacaciones máximos por años de servicio -->
                                        <div class="col-md-4">
                                            <div class="form-group is-required">
                                                <label>Días de disfrute de vacaciones máximo por año de servicio:</label>
                                                <input type="text" data-toggle="tooltip" title="Indique la cantidad de días de disfrute de vacaciones máximos por años de servicio" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.maximum_additional_days_per_year"
                                                            :disabled="record.hasAssociated">
                                            </div>
                                        </div>
                                        <!-- ./Días de disfrute de vacaciones máximos por años de servicio -->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>¿Son días hábiles?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                         title="¿La fecha incluye días hábiles?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="vacationalPolicieBusinessDays"
                                                                v-model="record.business_days"
                                                                :value="true"
                                                                :disabled="record.hasAssociated">
                                                        <label class="custom-control-label" for="vacationalPolicieBusinessDays"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Años de servicios en otras instituciones públicas -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>¿Toma en cuenta los años de servicios en otras instituciones públicas?</label>
                                                <div class="col-12">
                                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                                         title="¿Toma en cuenta los años de servicios en otras instituciones públicas?">
                                                        <input type="checkbox" class="custom-control-input"
                                                                id="vacationalPolicieOldJobs"
                                                                v-model="record.old_jobs"
                                                                :value="true"
                                                                :disabled="record.hasAssociated">
                                                        <label class="custom-control-label" for="vacationalPolicieOldJobs"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- ./Años de servicios en otras instituciones públicas -->
                                        <div class="col-12 row" v-if="record.days_on_scale && record.days_group_by">
                                            <div class="col-7 pad-top-10 with-border with-radius table-responsive" style="place-self: baseline;">
                                                <h6 class="text-center">Escalas o niveles del escalafón</h6>
                                                <div class="row" v-if="record.payroll_days_scales.length == 0">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-info" role="alert">
                                                            <div class="container">
                                                                <div class="alert-icon">
                                                                    <i class="now-ui-icons travel_info"></i>
                                                                </div>
                                                                <strong>No se encontraron registros</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-else>
                                                    <table class="table table-hover table-striped table-responsive  table-scale">
                                                        <thead>
                                                            <th :colspan="1 + record.payroll_days_scales.length">
                                                                <strong>{{ record.name }}</strong>
                                                            </th>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="selected-row text-center">
                                                                <th>{{ getGroupBy }}</th>
                                                                <th v-for="(field,index) in record.payroll_days_scales" :key="index">
                                                                    <span v-if="type == 'list'
                                                                             && options.length > 0">
                                                                        {{ getValueScale(field.value) }}
                                                                    </span>
                                                                    <span v-else-if="type == 'range'">
                                                                        {{ field.value['from'] + ' - ' + field.value['to'] }}
                                                                    </span>
                                                                    <span v-else-if="type == 'boolean'">
                                                                        {{ field.value?'SI':'NO' }}
                                                                    </span>
                                                                    <span v-else>
                                                                        {{ field.value }}
                                                                    </span>
                                                                </th>
                                                            </tr>
                                                            <tr class="selected-row text-center">
                                                                <th>Nombre</th>
                                                                <td v-for="(field,index) in record.payroll_days_scales" :key="index">
                                                                    {{ field.name}}
                                                                </td>
                                                            </tr>
                                                            <tr class="config-row text-center">
                                                                <th>Acción:</th>
                                                                <td v-for="(field,index) in record.payroll_days_scales" :key="index">
                                                                    <div class="d-inline-flex">
                                                                        <button @click="editDaysScale(index,$event)" class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro" data-toggle="tooltip" type="button">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>
                                                                        <button @click="removeDaysScale(index,$event)" class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro" data-toggle="tooltip" type="button">
                                                                            <i class="fa fa-trash-o"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-4 offset-1 pad-top-10 with-border with-radius table-responsive">
                                                <h6 class="text-center">Nueva escala</h6>
                                                <div class="row" style="align-items: flex-end;" v-if="days_type != '' && days_type != 'boolean'
                                                        && days_type != 'list'">
                                                    <strong class="col-md-12">
                                                        Expresado en
                                                    </strong>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Valor puntual</label>
                                                            <div class="col-12">
                                                                <p-radio class="pretty p-switch p-fill p-bigger" color="success" off-color="text-gray" toggle data-toggle="tooltip" title="Indique si el valor está expresado puntualmente" @change="resetDaysScales()" v-model="days_type" value="value">
                                                                    <label slot="off-label"></label>
                                                                </p-radio>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 col-md-4">
                                                        <div class="form-group">
                                                            <label>Rango</label>
                                                            <div class="col-12">
                                                                <p-radio class="pretty p-switch p-fill p-bigger" color="success" off-color="text-gray" toggle data-toggle="tooltip" title="Indique si el valor está expresado en rangos" @change="resetDysScales()" v-model="days_type" value="range">
                                                                    <label slot="off-label"></label>
                                                                </p-radio>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group is-required">
                                                            <label>Nombre</label>
                                                            <input type="text" placeholder="Nombre" data-toggle="tooltip" title="Indique un nombre para identificar la agrupación" class="form-control input-sm" v-model="days_scale.name">
                                                            <input type="hidden" v-model="days_scale.id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12" v-if="days_type == 'list'">
                                                        <div class="form-group">
                                                            <label>Valor:</label>
                                                            <select2 :options="options" v-model="days_scale.value"></select2>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12" v-if="days_type == 'value'">
                                                        <div class="form-group is-required">
                                                            <label>Valor</label>
                                                            <input type="number" placeholder="Valor" class="form-control input-sm" data-toggle="tooltip" title="Indique la cantidad (requerido)" v-model="days_scale.value" min="0" onfocus="this.select()">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12" v-if="days_type == 'range'">
                                                        <div class="form-group is-required">
                                                            <label>Desde</label>
                                                            <input id="days-scale-value-from" days_type="number" placeholder="Valor" class="form-control input-sm" data-toggle="tooltip" title="Indique la cantidad (requerido)" min="0" step=".01" onfocus="this.select()">
                                                        </div>
                                                        <div class="form-group is-required">
                                                            <label>Hasta</label>
                                                            <input id="days-scale-value-to" days_type="number" placeholder="Valor" class="form-control input-sm" data-toggle="tooltip" title="Indique la cantidad (requerido)" min="0" step=".01" onfocus="this.select()">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12" v-if="days_type == 'boolean'">
                                                        <div class="form-group">
                                                            <label>Valor</label>
                                                            <div class="col-12">
                                                                <div class="pretty p-switch p-fill p-bigger p-toggle">
                                                                    <input type="checkbox" data-toggle="tooltip" title="Indique si el campo está activo" v-model="days_scale.value">
                                                                    <div class="state p-off">
                                                                        <label></label>
                                                                    </div>
                                                                    <div class="state p-on p-success">
                                                                        <label></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-sm btn-primary btn-custom float-right"
                                                                type="button" data-toggle="tooltip" title="Agregar escala"
                                                                @click="addDaysScale($event)"
                                                        >
                                                            <i class="fa fa-plus-circle"></i>
                                                            Agregar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- ./Tipo de vacaciones individuales -->

                                    <div class="row">
                                        <!-- ¿asignar a? -->
                                        <div class="col-md-12">
                                            <div class=" form-group is-required">
                                                <label>¿Asignar a?</label>
                                                <v-multiselect data-toggle="tooltip"
                                                            title="Indique los registros a los que se les va asignar el concepto"
                                                            track_by="name"
                                                            :hide_selected="false"
                                                            :options="assign_to"
                                                            @input="updateAssignOptions"
                                                            v-model="record.assign_to"
                                                            :disabled="record.hasAssociated">
                                                </v-multiselect>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- ./¿asignar a? -->

                                    <div class="row">
                                        <div v-if="record.assign_to" class="col-12 d-flex flex-wrap align-items-end">
                                            <div v-for="field in record.assign_to" :key="field['id']" class="col-md-4">
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
                                                                <div class="form-group is-required" style="z-index: unset;">
                                                                    <label>Minimo:</label>
                                                                    <input type="number" min="0" step="1"
                                                                        placeholder="Minimo" data-toggle="tooltip"
                                                                        title="Indique el minimo requerido para asignar el concepto"
                                                                        class="form-control input-sm"
                                                                        v-model="record.assign_options[field['id']]['minimum']">
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group is-required" style="z-index: unset;">
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
                                    </div>
                                </div>
                                <div id="w-vacationRequestForm" :class="panel=='vacationRequestForm' ? 'tab-pane p-3 active' : 'tab-pane p-3'">
                                    <div class="row">
                                        <!-- nombre -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Días de anticipación para la realizar la solicitud de vacaciones:</label><br>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group is-required">
                                                <label>Días de anticipación (mínimo)</label>
                                                <input type="text" data-toggle="tooltip" title="Días de anticipación (mínimo)" class="form-control input-sm" v-input-mask data-inputmask="
                                                            'alias': 'numeric',
                                                            'allowMinus': 'false',
                                                            'digits': 0" v-model="record.min_days_advance">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-footer">
                                <div class="pull-right" v-if="panel == 'vacationPolicyForm'">
                                    <button type="button" class="btn btn-primary btn-wd btn-sm" :disabled="isDisableNextStep()" data-toggle="tooltip" title="" @click="changePanel('vacationRequestForm')">
                                        Siguiente
                                    </button>
                                </div>
                                <div class="pull-left" v-if="panel == 'vacationRequestForm'">
                                    <button type="button" @click="changePanel('vacationPolicyForm', true)" class="btn btn-default btn-wd btn-sm" data-toggle="tooltip" title="">
                                        Regresar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button class="btn btn-default btn-sm btn-round btn-modal-close" type="button"
                                    data-dismiss="modal" @click="clearFilters"
                            >
                                Cerrar
                            </button>
                            <button class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" type="button"
                                    @click="reset()"
                            >
                                Cancelar
                            </button>
                            <button class="btn btn-primary btn-sm btn-round btn-modal-save" type="button"
                                    @click="createRecord('payroll/vacation-policies')"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="application_date" slot-scope="props" class="text-center">
                                <span v-if="props.row.end_date">
                                    {{ props.row.start_date + ' - ' + props.row.end_date }}
                                </span>
                                <span v-else> {{ props.row.start_date + ' - No definido' }} </span>
                            </div>
                            <div slot="active" slot-scope="props" class="text-center">
                                <span v-if="props.row.active" class="text-success font-weight-bold">SI</span>
                                <span v-else class="text-danger font-weight-bold">NO</span>
                            </div>
                            <div slot="vacation_type" slot-scope="props">
                                <span v-if="props.row.vacation_type == 'collective_vacations'">
                                    Vacaciones colectivas
                                </span>
                                <span v-else-if="props.row.vacation_type == 'vacation_period'">
                                    Salidas individuales
                                </span>
                            </div>
                            <div class="text-center" slot="id" slot-scope="props">
                                <button class="btn btn-warning btn-xs btn-icon btn-action" type="button"
                                        data-toggle="tooltip" title="Modificar registro"
                                        @click="initUpdate(props.row.id, $event)"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-xs btn-icon btn-action" type="button"
                                        data-toggle="tooltip" title="Eliminar registro"
                                        @click="deleteRecord(props.row.id, 'payroll/vacation-policies')"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { async } from 'q';

export default {
    data() {
        return {
            record: {
                id: '',
                name: '',
                start_date: '',
                end_date: '',
                vacation_type: '',
                vacation_days: '',
                vacation_periods_accumulated_per_year: '',
                vacation_days: '',
                vacation_period_per_year: '',
                years_for_additional_days: '',
                additional_days_per_year: '',
                minimum_additional_days_per_year: '',
                maximum_additional_days_per_year: '',
                from_year: '',
                salary_type: '',
                institution_id: '',
                payroll_payment_type_id: '',
                vacation_periods: [],
                active: false,
                vacation_advance: false,
                vacation_postpone: false,
                staff_antiquity: false,
                on_scale: false,
                group_by: '',
                type:'',
                payroll_scales: [],
                assign_to: '',
                assign_options: {},
                worker_arises: false,
                generate_worker_arises: 0,
                min_days_advance: '',

                days_on_scale: false,
                days_group_by: '',
                payroll_days_scales: [],
                days_type: '',
                business_days: false,
                old_jobs: false,
            },
            type:'',
            days_type:'',
            scale: {
                id: '',
                name: '',
                value: ''
            },
            days_scale: {
                id: '',
                name: '',
                value: ''
            },
            resetScale: true,
            editIndex: null,
            options: [],
            assign_to: [],
            assign_options: {},
            assign_options_loading: false,
            assign_options_lists: [],
            payroll_salary_tabulators_groups: [],
            errors: [],
            records: [],
            holidays: [],
            columns: ['name', 'application_date', 'vacation_type', 'active', 'id'],
            institutions: [],
            payroll_payment_types: [],
            vacation_types: [
                { "id": "", "text": "Seleccione..." },
                { "id": "collective_vacations", "text": "Colectiva" },
                { "id": "vacation_period", "text": "Salidas individuales" },
            ],
            salary_types: [
                { "id": "", "text": "Seleccione..." },
                { "id": "base_salary", "text": "Salario Base" },
                { "id": "comprehensive_salary", "text": "Salario Integral" },
                { "id": "normal_salary", "text": "Salario Normal" },
                { "id": "dialy_salary", "text": "Salario Diario" }
            ],
            panel: 'vacationPolicyForm',
        }
    },
    props: {
        start_operations_date: {
            type: [Date, String],
            required: false,
            default: ''
        }
    },
    computed: {
    /**
    * Método que obtiene el nombre de la agrupación de los tabuladores
    *
    * @author    Henry Paredes <hparedes@cenditel.gob.ve>
    * @return    {string}
    */
    getGroupBy: function() {
        const vm = this;
        let response = '';
        $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
            if (typeof(field['children']) != 'undefined') {
                $.each(field['children'], function(index, field) {
                    if (vm.record.group_by == field['id']) {
                        response = field['text'];
                        if (field['type'] == 'list') {
                            vm.options = [];
                            axios.get(
                                `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                            ).then(response => {
                                vm.options = response.data;
                            });
                        }
                    }
                });
            }
        });
        return response;
    }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'name': 'Nombre',
            'application_date': 'Fecha de aplicación',
            'vacation_type': 'Tipo de vacaciones',
            'active': 'Activa',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['name', 'application_date', 'vacation_type'];
        vm.table_options.filterable = ['name', 'application_date', 'vacation_type'];
        vm.table_options.columnsClasses = {
            'id': 'col-xs-2'
        };
    },
    mounted() {
        const vm = this;
        $("#add_payroll_vacation_policy").on('show.bs.modal', function() {
            vm.assign_options_lists = [];

            vm.reset();
            vm.getPayrollPaymentTypes();
            vm.getInstitutions();
            vm.getPayrollSalaryTabulatorsGroups();
            vm.getPayrollConceptAssignTo();
            vm.getHolidays();

        });
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        reset() {
            const vm = this;
            vm.errors = [];
            vm.record = {
                id: '',
                name: '',
                start_date: '',
                end_date: '',
                vacation_type: '',
                vacation_periods_accumulated_per_year: '',
                vacation_days: '',
                vacation_period_per_year: '',
                years_for_additional_days: '',
                additional_days_per_year: '',
                minimum_additional_days_per_year: '',
                maximum_additional_days_per_year: '',
                from_year: '',
                salary_type: '',
                institution_id: '',
                payroll_payment_type_id: '',
                vacation_periods: [],
                active: false,
                vacation_advance: false,
                vacation_postpone: false,
                staff_antiquity: false,
                on_scale: false,
                group_by: '',
                type: '',
                payroll_scales: [],
                assign_to: '',
                assign_options: {},
                worker_arises: false,
                generate_worker_arises: 0,
                min_days_advance: '',

                days_on_scale: false,
                days_group_by: '',
                payroll_days_scales: [],
                days_type: '',
                hasAssociated: false,
            };

            vm.resetScales();
            vm.resetDaysScales();

            vm.panel = 'vacationPolicyForm';
            document.getElementById("vacationPolicyForm").click();
        },
        /**
         * Método que borra los campos del formulario de la escala
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         */
        resetScales() {
            const vm = this;
            vm.scale = {
                id:    '',
                name:  '',
                value: ''
            };
            vm.editIndex = null;
            $("#scale-value-from").val('');
            $("#scale-value-to").val('');
            if (vm.resetScale) {
                vm.record.payroll_scales = [];
            } else {
                vm.resetScale = true;
            }
            vm.record.type = vm.type;
        },
        /**
         * Método que borra los campos del formulario de la escala
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         */
        resetDaysScales() {
            const vm = this;
            vm.days_scale = {
                id:    '',
                name:  '',
                value: ''
            };
            vm.editDaysIndex = null;
            $("#days-scale-value-from").val('');
            $("#days-scale-value-to").val('');
            if (vm.resetDaysScale) {
                vm.record.payroll_days_scales = [];
            } else {
                vm.resetDaysScale = true;
            }
            vm.record.days_type = vm.days_type;
        },
        /**
         * Método que habilita o deshabilita el botón siguiente
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        isDisableNextStep() {
            const vm = this;
            if (vm.panel == 'vacationPolicyForm') {
                if (vm.record.vacation_type == 'collective_vacations') {
                    if ((vm.record.name != '') &&
                        (vm.record.start_date != '') &&
                        (vm.record.institution_id != '') &&
                        (vm.record.assign_to != '') &&
                        (vm.record.vacation_periods.length > 0)) {
                        return false;
                    } else {
                        return true;
                    }

                } else if (vm.record.vacation_type == 'vacation_period') {
                    if ((vm.record.name != '') &&
                        (vm.record.start_date != '') &&
                        (vm.record.institution_id != '') &&
                        (vm.record.additional_days_per_year != '') &&
                        (vm.record.vacation_days != '') &&
                        //(vm.record.minimum_additional_days_per_year != '') &&
                        (vm.record.from_year != '') &&
                        (vm.record.maximum_additional_days_per_year != '') &&
                        (vm.record.vacation_period_per_year != '') &&
                        (vm.record.years_for_additional_days != '') &&
                         //(vm.record.vacation_periods_accumulated_per_year != '') &&
                        (vm.record.assign_to != '')) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else if (vm.panel == 'vacationPaymentForm') {
                if ((vm.record.payroll_payment_type_id != '')) {
                    return false;
                } else {
                    return true;
                }
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
         * Método que permite inicializar los campos del formulario según sea el tipo de vacaciones
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        switchTypeVacation() {
            const vm = this;
            if (vm.record.vacation_type == 'collective_vacations') {
                vm.record.vacation_days = '';
                vm.record.vacation_period_per_year = '';
                vm.record.years_for_additional_days = '';
                vm.record.additional_days_per_year = '';
                vm.record.from_year = '';
                vm.record.maximum_additional_days_per_year = '';
            } else if (vm.record.vacation_type == 'vacation_period') {
                vm.record.vacation_periods = [];
            }
        },
        /**
         * Reescribe el método initRecords para cambiar su comportamiento por defecto
         * Inicializa los registros base del formulario
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param     {string}    url         Ruta que obtiene los datos a ser mostrado en listados
         * @param     {string}    modal_id    Identificador del modal a mostrar con la información solicitada
         */
        initRecords(url, modal_id) {
            const vm = this;
            vm.errors = [];
            vm.records = [];
            vm.reset();

            url = vm.setUrl(url);

            axios.get(url).then(response => {
                if (typeof(response.data.records) !== "undefined") {
                    let records = [];
                    $.each(response.data.records, function(index, field) {
                        records.push({
                            id: field['id'],
                            name: field['name'],
                            start_date: field['start_date'],
                            end_date: field['end_date'],
                            active: field['active'],
                            institution_id: field['institution_id'],
                            institution: field['institution'],
                            vacation_type: field['vacation_type'],
                            vacation_periods: JSON.parse(field['vacation_periods']),
                            vacation_days: field['vacation_days'],
                            vacation_period_per_year: field['vacation_period_per_year'],
                            years_for_additional_days: field['years_for_additional_days'],
                            additional_days_per_year: field['additional_days_per_year'],
                            from_year: field['from_year'],
                            maximum_additional_days_per_year: field['maximum_additional_days_per_year'],
                            salary_type: field['salary_type'],
                            vacation_advance: field['vacation_advance'],
                            vacation_postpone: field['vacation_postpone'],
                            staff_antiquity: field['staff_antiquity'],
                            payroll_payment_type_id: field['payroll_payment_type_id'],
                            payroll_payment_type: field['payroll_payment_type'],
                            assign_to: field['assign_to'],
                            assign_options: field['assign_options'],
                            min_days_advance: field['min_days_advance'],
                            payroll_scales: field['payroll_scales'],
                            on_scale: field['on_scale'],
                            group_by: field['group_by'],
                            type: field['type'],
                            payroll_concept_assign_options: field['payroll_concept_assign_options'],
                            days_on_scale: field['days_on_scale'],
                            days_group_by: field['days_group_by'],
                            days_type: field['days_type'],
                            payroll_days_scales: field['payroll_days_scales'],
                            old_jobs: field['old_jobs'],
                            business_days: field['business_days'],
                        });
                    });
                    vm.records = records;
                }
                if ($("#" + modal_id).length) {
                    $("#" + modal_id).modal('show');
                }
            }).catch(error => {
                if (typeof(error.response) !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    } else {
                        vm.logs('resources/js/all.js', 343, error, 'initRecords');
                    }
                }
            });
        },
        /**
         * Método que agrega un nuevo Salidas individuales
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        addVacationPeriod() {
            const vm = this;
            vm.record.vacation_periods.push({
                start_date: '',
                end_date: '',
                business_days: false
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
         * Método que actualiza los inputs de opciones a asignar
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         * @return    {void}
         */
         updateAssignOptions: function(){
            const vm = this;
            $.each(vm.record.assign_to, async(index, field) => {
                if (field['type'] == 'list') {
                    if (typeof(vm.record.assign_options[field['id']] ) == 'undefined') {
                        vm.record.assign_options[field['id']] = [];
                        vm.assign_options[field['id']] = [];
                        vm.assign_options_loading = true;

                        await axios.get(`${window.app_url}/payroll/get-concept-assign-options/${field['id']}`)
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

                        //Consulta todos assign_options asociados al registro a editar
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
         * Método que obtiene los grupos de tabuladores salariales registrados
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getPayrollSalaryTabulatorsGroups() {
            const vm = this;
            vm.payroll_salary_tabulators_groups = [];
            axios.get(`${window.app_url}/payroll/get-salary-tabulators-groups`).then(response => {
                vm.payroll_salary_tabulators_groups = response.data;
            });
        },
        /**
         * Método que obtiene los parámetros de opciones
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getOptions() {
            const vm = this;
            if (vm.record.id == '') {
                $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                    if (typeof(field['children']) != 'undefined') {
                        $.each(field['children'], function(index, field) {
                            if (vm.record.group_by == field['id']) {
                                vm.type = field['type'];
                                vm.record.type = vm.type;
                                if (field['type'] == 'list') {
                                    vm.options = [];
                                    axios.get(
                                        `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                    ).then(response => {
                                        vm.options = response.data;
                                    });
                                }
                            }
                        });
                    }
                });
                vm.resetScales();
            } else {
                if (vm.resetGroup == true) {
                    $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                        if (typeof(field['children']) != 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (vm.record.group_by == field['id']) {
                                    vm.type = field['type'];
                                    vm.record.type = vm.type;
                                    if (field['type'] == 'list') {
                                        vm.options = [];
                                        axios.get(
                                            `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                        ).then(response => {
                                            vm.options = response.data;
                                        });
                                    }
                                }
                            });
                        }
                    });
                    vm.resetScales();
                } else {
                    $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                        if (typeof(field['children']) != 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (vm.record.group_by == field['id']) {
                                    vm.type = vm.record.type;
                                    if (field['type'] == 'list') {
                                        vm.options = [];
                                        axios.get(
                                            `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                        ).then(response => {
                                            vm.options = response.data;
                                        });
                                    }
                                }
                            });
                        }
                    });
                    vm.resetGroup = true;
                }
            }
        },
        /**
         * Método que obtiene los parámetros de opciones
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getDaysOptions() {
            const vm = this;
            if (vm.record.id == '') {
                $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                    if (typeof(field['children']) != 'undefined') {
                        $.each(field['children'], function(index, field) {
                            if (vm.record.days_group_by == field['id']) {
                                vm.days_type = field['type'];
                                vm.record.days_type = vm.days_type;
                                if (field['type'] == 'list') {
                                    vm.options = [];
                                    axios.get(
                                        `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                    ).then(response => {
                                        vm.options = response.data;
                                    });
                                }
                            }
                        });
                    }
                });
                vm.resetScales();
            } else {
                if (vm.resetGroup == true) {
                    $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                        if (typeof(field['children']) != 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (vm.record.days_group_by == field['id']) {
                                    vm.days_type = field['type'];
                                    vm.record.days_type = vm.days_type;
                                    if (field['type'] == 'list') {
                                        vm.options = [];
                                        axios.get(
                                            `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                        ).then(response => {
                                            vm.options = response.data;
                                        });
                                    }
                                }
                            });
                        }
                    });
                    vm.resetScales();
                } else {
                    $.each(vm.payroll_salary_tabulators_groups, function(index, field) {
                        if (typeof(field['children']) != 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (vm.record.days_group_by == field['id']) {
                                    vm.days_type = vm.record.days_type;
                                    if (field['type'] == 'list') {
                                        vm.options = [];
                                        axios.get(
                                            `${window.app_url}/payroll/get-parameter-options/${field['id']}`
                                        ).then(response => {
                                            vm.options = response.data;
                                        });
                                    }
                                }
                            });
                        }
                    });
                    vm.resetGroup = true;
                }
            }
        },
        /**
         * Método que obtiene el valor de la escala según el identificador único
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {string}    value    Objeto json que contiene el id de la escala
         * @return    {string}
         */
        getValueScale(value) {
            const vm = this;
            let id = JSON.parse(value);
            let response = '';
            $.each(vm.options, function(index, field) {
                if (id == field['id']) {
                    response = field['text'];
                }
            });
            return response;
        },
        /**
         * Método que agrega una nueva escala
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {object}    event    Objeto que gestiona los eventos
         */
        addScale(event) {
            const vm = this;
            var field = {};
            if ((vm.scale.name == '') ||
                ((vm.scale.value == '') && (vm.type != 'boolean') && (vm.type != 'range'))) {
                return false;
            }

            for (var index in vm.scale) {
                if (index == 'value') {
                    if (vm.type == 'range') {
                        var fromValue = $("#scale-value-from").val();
                        var toValue = $("#scale-value-to").val();
                        if ((fromValue == '') ||
                            (toValue == '')) {
                            return false;
                        }
                        field[index] = {
                            from: fromValue,
                            to: toValue
                        };
                    } else {
                        field[index] = vm.scale[index];
                    }

                } else {
                    field[index] = vm.scale[index];
                }
            }
            if (vm.editIndex == null)
                vm.record.payroll_scales.push(field);
            else {
                vm.record.payroll_scales[vm.editIndex] = field;
            }
            vm.resetScale = false;
            vm.resetScales();
            event.preventDefault();
        },
        /**
         * Método que agrega una nueva escala
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {object}    event    Objeto que gestiona los eventos
         */
        addDaysScale(event) {
            const vm = this;
            var field = {};
            if ((vm.days_scale.name == '') ||
                ((vm.days_scale.value == '') && (vm.days_type != 'boolean') && (vm.days_type != 'range'))) {
                return false;
            }

            for (var index in vm.days_scale) {
                if (index == 'value') {
                    if (vm.days_type == 'range') {
                        var fromValue = $("#days-scale-value-from").val();
                        var toValue = $("#days-scale-value-to").val();
                        if ((fromValue == '') ||
                            (toValue == '')) {
                            return false;
                        }
                        field[index] = {
                            from: fromValue,
                            to: toValue
                        };
                    } else {
                        field[index] = vm.days_scale[index];
                    }

                } else {
                    field[index] = vm.days_scale[index];
                }
            }
            if (vm.editDaysIndex == null)
                vm.record.payroll_days_scales.push(field);
            else {
                vm.record.payroll_days_scales[vm.editDaysIndex] = field;
            }
            vm.resetDaysScale = false;
            vm.resetDaysScales();
            event.preventDefault();
        },
        /**
         * Método que carga el formulario de la escala con los datos a modificar
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {integer}    index    Identificador del registro a ser modificado
         * @param     {object}     event    Objeto que gestiona los eventos
         */
        editScale(index, event) {
            const vm = this;
            vm.editIndex = index;
            if (vm.type == 'range') {
                vm.scale = {
                    id: vm.record.payroll_scales[index].id,
                    name: vm.record.payroll_scales[index].name,
                    value: ''
                };
                $("#scale-value-from").val(vm.record.payroll_scales[index].value.from);
                $("#scale-value-to").val(vm.record.payroll_scales[index].value.to);
            } else {
                vm.scale = {
                    id: vm.record.payroll_scales[index].id,
                    name: vm.record.payroll_scales[index].name,
                    value: JSON.parse(vm.record.payroll_scales[index].value)
                };
            }
            event.preventDefault();
        },
                /**
         * Método que carga el formulario de la escala con los datos a modificar
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {integer}    index    Identificador del registro a ser modificado
         * @param     {object}     event    Objeto que gestiona los eventos
         */
        editDaysScale(index, event) {
            const vm = this;
            vm.editDaysIndex = index;
            if (vm.days_type == 'range') {
                vm.days_scale = {
                    id: vm.record.payroll_days_scales[index].id,
                    name: vm.record.payroll_days_scales[index].name,
                    value: ''
                };
                $("#days-scale-value-from").val(vm.record.payroll_days_scales[index].value.from);
                $("#days-scale-value-to").val(vm.record.payroll_days_scales[index].value.to);
            } else {
                vm.days_scale = {
                    id: vm.record.payroll_days_scales[index].id,
                    name: vm.record.payroll_days_scales[index].name,
                    value: JSON.parse(vm.record.payroll_days_scales[index].value)
                };
            }
            event.preventDefault();
        },
        /**
         * Método que elimina el elemento seleccionado
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {integer}    index    Identificador del registro a eliminar
         * @param     {object}     event    Objeto que gestiona los eventos
         */
        removeScale(index, event) {
            const vm = this;
            vm.record.payroll_scales.splice(index, 1);
            vm.editIndex = null;
            event.preventDefault();
        },
        /**
         * Método que elimina el elemento seleccionado
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param     {integer}    index    Identificador del registro a eliminar
         * @param     {object}     event    Objeto que gestiona los eventos
         */
        removeDaysScale(index, event) {
            const vm = this;
            vm.record.payroll_days_scales.splice(index, 1);
            vm.editDaysIndex = null;
            event.preventDefault();
        },

        generatePaymentVacation() {
            const vm = this;

            if (vm.record.worker_arises) {
                axios.post(
                    `${window.app_url}/payroll/calculate-payment/`, {
                        payroll_concepts: [
                            {
                                id: vm.record.payroll_payment_type_id,
                            }
                        ],
                    }
                ).then(response => {
                    vm.record.generate_worker_arises = response.data.result;
                });
            }
        },
        		/**
		 * Método que carga el formulario con los datos a modificar
		 *
		 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @author  José Briceño <josejorgebriceno9@gmail.com>
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
		 *
		 * @param  {integer} index Identificador del registro a ser modificado
		 * @param {object} event   Objeto que gestiona los eventos
		 */
		async initUpdate(id, event) {
			const vm = this;
			vm.errors = [];
            event.preventDefault();
            vm.loading = true;

            let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                return rec.id === id;
            })[0])) || vm.reset();

            vm.record = await recordEdit;
            await this.hasAssociated(id);

            setTimeout(() => {
                if (vm.record.vacation_type == 'collective_vacations') {
                    vm.record.vacation_periods = recordEdit.vacation_periods;
                    for (const [i, v] of vm.record.vacation_periods.entries()) {
                        vm.getCalculateTime(i);
                    }
                } else {
                    vm.record.vacation_periods = [];
                    vm.record.vacation_days = recordEdit.vacation_days;
                }
            }, 1000);

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
            vm.updateAssignOptionsMethod();
            vm.loading = false;
            if (vm.record.hasAssociated) {
                vm.showMessage(
    					"custom",
    					"Modificacion Parcial",
    					"info",
    					"screen-info",
    					"No se puede modificar completamente este registro debido a que tiene solicitudes asociadas"
    				);
            }

		},
        /**
         * Determina si una politica de vacaciones está asociadoa con una solicitud
         *
         * @param {type} id - el ID de la politica a consultar
         * @return {Promise} - una promesa que se resuelve con el resultado
         */
        async hasAssociated(id){
            const vm = this;
             await axios.get(`${window.app_url}/payroll/get-vacation-policy/verify_assignment/${id}`).then(response => {
                        vm.record.hasAssociated = response.data.hasAssociated && vm.record.vacation_type == 'vacation_period'
                    });
        },

        getCalculateTime(index) {
            const vm = this;

            if (vm.record.vacation_periods[index].start_date && vm.record.vacation_periods[index].end_date) {
                let start_date = new Date(document.getElementById('start_date_vacation_' + index).value.replaceAll('-', '/'));
                let end_date   = new Date(document.getElementById('end_date_vacation_' + index).value.replaceAll('-', '/'));
                let holidayDiscount = [];

                let diff = end_date.getTime() - start_date.getTime()
                let dias = diff/(1000*60*60*24)
                let cont = 0;

                if (vm.record.vacation_periods[index].business_days) {
                    for (let holiday of vm.holidays) {
                        if (holiday.text != 'Seleccione...') {
                            let holidayDate = new Date(holiday.text)
                            if (holidayDate.getTime() >= start_date && holidayDate.getTime() <= end_date) {
                                holidayDiscount.push(holiday.text);
                            }
                        }
                    }

                    const sumarLaborables = (f, n) => {
                        const options = { weekday: 'long'};
                        for(var i=0; i<n; i++) {
                            f.setTime( f.getTime() + (1000*60*60*24) );
                            /* Se identifica si existen sabados o domingos en el periodo establecido */
                            if( (f.getDay()==6) || (f.getDay()==0) ) {
                                /* Si existe un dia no laborable se hace el bucle una unidad mas larga */
                                dias--;
                            }
                        }
                    }

                    sumarLaborables(start_date, dias);
                    dias = dias - holidayDiscount.length;
                }

                $(`#vacations_days_${index}`).val(`${(dias + 1)} días`);
            }
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
                if (typeof(response.data) !== "undefined") {
                    vm.holidays = response.data;
                }
            });
        },
    },
};
</script>
