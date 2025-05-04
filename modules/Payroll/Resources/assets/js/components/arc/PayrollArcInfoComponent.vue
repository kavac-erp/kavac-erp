<template>
    <div id="PayrollArcInfo" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="PayrollArcInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Comprobante de Retenciones Acumuladas ARC
                    </h6>
                </div>

                <div class="modal-body" style="color: black" v-if="record.id">
                    <div class="row" style="place-content: center;">
                        <div class="col-8 text-center">
                            <span class="text-uppercase">Comprobante de retención de impuesto sobre la renta anual o de cese de actividades para personas residentes perceptoras de sueldos, salarios y demás remuneraciones similares (ARC)</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-10">
                            <div class="row">
                                <div class="col-12">
                                    <span class="text-uppercase"><strong>Beneficiario de las remuneraciones</strong></span>
                                </div>
                                <br>
                                <div class="col-12">
                                    <table width="100%">
                                        <thead>
                                            <tr><th colspan="2"></th></tr>
                                            <tr>
                                                <th colspan="2" style="text-align: left;">
                                                    <span style="text-transform: uppercase; font-weight: normal;">Apellidos y Nombres: </span>&nbsp;&nbsp;
                                                    <strong style="font-size: x-large; text-transform: uppercase;">{{ record.payroll_staff.name }}</strong>
                                                </th>
                                            </tr>
                                            <tr><th colspan="2"></th></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th width="40%" style="text-align: left;">
                                                    <strong style="text-transform: uppercase;">Numero de RIF:</strong>&nbsp;&nbsp;
                                                    <span style="text-transform: uppercase; font-weight: normal;">{{ record.payroll_staff.rif }}</span>
                                                </th>
                                                <th style="text-align: left;">
                                                    <strong style="text-transform: uppercase;">Cédula de Identidad N°:</strong>&nbsp;&nbsp;
                                                    <span style="text-transform: uppercase; font-weight: normal;">{{ record.payroll_staff.id_number }}</span>
                                                </th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col-12">
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <strong style="text-transform: uppercase;">Periodo</strong>
                                                </th>
                                                <th></th>
                                            </tr>
                                            <tr><th></th></tr>
                                        </thead>
                                    </table>
                                    <table width="100%">
                                            <tr>
                                                <th>
                                                    <span style="text-transform: uppercase; font-weight: normal;">Desde:</span>&nbsp;&nbsp;
                                                    <span style="text-transform: uppercase; font-weight: normal;">{{ record.start_date }}</span>
                                                </th>
                                            </tr>
                                            <tr><th></th></tr>
                                            <tr>
                                                <th>
                                                    <span style="text-transform: uppercase; font-weight: normal;">Hasta:</span>&nbsp;&nbsp;
                                                    <span style="text-transform: uppercase; font-weight: normal;">{{ record.end_date }}</span>
                                                </th>
                                            </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12" style="text-align: center; font-size: large">
                            <span class="text-uppercase"><strong>Tipo de Agente de Retención</strong></span>
                        </div>
                        <div class="col-12">
                            <table width="100%">
                                <tr>
                                    <th width="70%" style="text-align: left" colspan="2">
                                        <strong style="text-transform: uppercase;">Dependencia oficial, organismos y otros:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" style="text-transform: uppercase; font-weight: normal;">
                                                {{ record.institution.name }}
                                            </span>
                                        </div>
                                    </th>
                                    <th width="30%" style="text-align: right">
                                        <strong style="text-transform: uppercase;">Numero de RIF:</strong>&nbsp;&nbsp;
                                        <span style="text-transform: uppercase; font-weight: normal;">{{ record.institution.rif }}</span>
                                    </th>
                                </tr>
                                <tr><th colspan="3"></th></tr>
                                <tr>
                                    <th width="15%" colspan="1" style="text-align: left">
                                        <strong style="text-transform: uppercase;">Dirección:</strong>
                                    </th>
                                    <th width="82.5%" colspan="2" style="text-align: left">
                                        <span style="font-weight: normal;">{{ record.retention_agent.address }}</span>
                                        <span style="font-weight: normal;">{{ record.retention_agent.phone }}</span>
                                        <span style="font-weight: normal;">{{ record.retention_agent.po_box }}</span>
                                        <span style="font-weight: normal;">{{ record.retention_agent.postal_code }}</span>
                                        <span style="font-weight: normal;">{{ record.retention_agent.city }}</span>
                                        <span style="font-weight: normal;">{{ record.retention_agent.estate }}</span>
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row" style="place-content: center;">
                        <div class="col-11">
                            <table border="1">
                                <thead>
                                    <tr style="color: white; background-color: #53a0e4;">
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>Mes</strong></th>
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="30%"><strong>Remuneraciones Pagadas En Cuenta</strong></th>
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>Porcentaje de Retención</strong></th>
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>Impuesto Retenido</strong></th>
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>Remuneraciones Pagadas Acumuladas</strong></th>
                                        <th style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>Impuesto Retenido Acumulado</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(month, index) in months" :key="index"
                                        :style="((index % 2) == 0) ?'background-color: #DAD8D8' : ''">
                                        <td style="padding:0rem 0.5rem !important" class="text-uppercase text-center" width="17.5%"><strong>{{ month['name'] }}</strong></td>
                                        <td style="padding:0rem 0.5rem !important" class="text-right" width="30%">{{ formatToCurrency(record.months_remunerations[month['id']] ? record.months_remunerations[month['id']] : 0, record.symbol) }}</td>
                                        <td style="padding:0rem 0.5rem !important" class="text-right" width="17.5%">0,00</td>
                                        <td style="padding:0rem 0.5rem !important" class="text-right" width="17.5%">0,00</td>
                                        <td style="padding:0rem 0.5rem !important" class="text-right" width="17.5%">{{ formatToCurrency(accumulatedMonthsRemunerations(month['id']), record.symbol) }}</td>
                                        <td style="padding:0rem 0.5rem !important" class="text-right" width="17.5%">0,00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <table width="100%">
                            <tr style="font-size: x-large">
                                <th width="60%">
                                    <strong style="text-transform: uppercase;">Total Remuneraciones Pagadas:</strong>
                                </th>
                                <th width="40%">
                                    <strong style="text-transform: uppercase;"><span>{{ formatToCurrency(accumulatedMonthsRemunerations('12'), record.symbol) }}</span></strong>
                                </th>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-12 pt-4" style="padding-left: 30px">
                            <div class="col-12 mt-4 d-flex flex-column align-items-center" style="padding: 0px">
                                <div class="w-50" style="border-bottom: 1px solid black; align-items: center; margin-top: 50px;"></div>
                                <span><small><strong>{{ record.arc_responsible.name }}</strong></small></span>
                                <span v-if="record.arc_responsible.rif"><small><strong>{{ record.arc_responsible.rif }}</strong></small></span>
                                <span class="text-uppercase" style="font-size: smaller"><strong>Responsable de ARC</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            record: {},
            months: [
                {'id': '01', 'name': 'Enero'},
                {'id': '02', 'name': 'Febrero'},
                {'id': '03', 'name': 'Marzo'},
                {'id': '04', 'name': 'Abril'},
                {'id': '05', 'name': 'Mayo'},
                {'id': '06', 'name': 'Junio'},
                {'id': '07', 'name': 'Julio'},
                {'id': '08', 'name': 'Agosto'},
                {'id': '09', 'name': 'Septiembre'},
                {'id': '10', 'name': 'Octubre'},
                {'id': '11', 'name': 'Noviembre'},
                {'id': '12', 'name': 'Diciembre'},
            ],
            agents: ['I.V.S.S.', 'F.A.O.V.', 'R.P.E.', 'Cja. Aho.', 'Fondo Jub.', 'Otros'],
        }
    },
    async created() {
    },
    methods: {
        accumulatedMonthsRemunerations(id) {
            const vm = this;
            let accumulated = 0;
            if (vm.record.months_remunerations) {
                for (let index in vm.record.months_remunerations) {
                    accumulated += vm.record.months_remunerations[index];
                    if (index === id) break;
                }
            }

            return accumulated;
        }
    }

}
</script>
