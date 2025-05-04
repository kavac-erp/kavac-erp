{{-- Gestión de nómina --}}
<li>
    <a href="javascript:void(0)" title="Datos de personal y nómina" data-toggle="tooltip" data-placement="right">
        <i class="ion-ios-folder-outline"></i><span>Talento Humano</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('payroll') !!}">
        <li class="{!! set_active_menu(['payroll.settings.index']) !!}">
            <a href="{{ route('payroll.settings.index') }}" data-toggle="tooltip" data-placement="right" title="Configuración de nómina">Configuración</a>
        </li>
        <li class="{!! set_active_menu(['payroll.salary-adjustments.index']) !!}">
            <a href="{{ route('payroll.salary-adjustments.index') }}" data-toggle="tooltip" data-placement="right" title="Gestiona las actualizaciones de tablas salariales, de acuerdo a un aumento oficial de salarios.">
                Ajustes en Tablas Salariales
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right" title="Expediente del personal">Expediente</a>
            <ul class="submenu" style="{!! display_submenu([
                    'staffs', 'socioeconomics', 'professionals',
                    'employments', 'financial'
                ]) !!}">
                <li class="{!! set_active_menu(['payroll.staffs.index', 'payroll.staffs.create', 'payroll.staffs.edit']) !!}">
                    <a href="{{ route('payroll.staffs.index') }}">Datos Personales</a>
                </li>
                <li class="{!! set_active_menu(['payroll.professionals.index']) !!}">
                    <a href="{{ route('payroll.professionals.index') }}">Datos Profesionales</a>
                </li>
                <li class="{!! set_active_menu(['payroll.socioeconomics.index']) !!}">
                    <a href="{{ route('payroll.socioeconomics.index') }}">Datos Socioeconómicos</a>
                </li>
                <li class="{!! set_active_menu(['payroll.employments.index']) !!}">
                    <a href="{{ route('payroll.employments.index') }}">Datos Laborales</a>
                </li>
                <li class="{!! set_active_menu(['payroll.financials.index']) !!}">
                    <a href="{{ route('payroll.financials.index') }}">Datos Financieros</a>
                </li>
                <li class="{!! set_active_menu(['payroll.staff-accounts.index']) !!}">
                    <a href="{{ route('payroll.staff-accounts.index') }}">Datos Contables</a>
                </li>
            </ul>
        </li>
        <li class="{!! set_active_menu(['payroll.guard-schemes.index']) !!}">
            <a
                href="{{ route('payroll.guard-schemes.index') }}"
                data-toggle="tooltip"
                data-placement="right"
                title="Gestión de registros de esquemas de guardias."
            >
                Esquema de Guardias
            </a>
        </li>
        <li>
            <a
                href="javascript:void(0)"
                data-toggle="tooltip"
                data-placement="right"
                title="Registros de hoja de tiempo"
            >
                Hoja de Tiempo
            </a>
            <ul class="submenu" style="{!! display_submenu([
                    'staffs', 'socioeconomics', 'professionals',
                    'employments', 'financial'
                ]) !!}">
                <li class="{!! set_active_menu(['payroll.time-sheet.index', 'payroll.time-sheet.create', 'payroll.time-sheet.edit']) !!}">
                    <a href="{{ route('payroll.time-sheet.index') }}">Periodo activo</a>
                </li>
                <li class="{!! set_active_menu(['payroll.time-sheet-pending.index']) !!}">
                    <a href="{{ route('payroll.time-sheet-pending.index') }}">Pendientes</a>
                </li>
            </ul>
        </li>
        <li class="{!! set_active_menu(['payroll.registers.index']) !!}">
            <a href="{{ route('payroll.registers.index') }}" data-toggle="tooltip" data-placement="right" title="Gestión de registros de la relación de pago de la nómina.">
                Registros de Nómina
            </a>
        </li>
        <li class="{!! set_active_menu(['payroll.ari-register.index']) !!}">
            <a href="{{ route('payroll.ari-register.index') }}" data-toggle="tooltip" data-placement="right" title="Gestión de registros de la planilla ARI">
                Registro ARI
            </a>
        </li>
        <li class="{!! set_active_menu(['payroll.text-file.index']) !!}">
            <a href="{{ route('payroll.text-file.index') }}" data-toggle="tooltip" data-placement="right" title="Gestión para la generación de archivos txt de nómina">
                Archivo txt de Nómina
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right" title="Gestiona las solicitudes de vacaciones, prestaciones y constancias.">Solicitudes</a>
            <ul class="submenu" style="{!! display_submenu(['vacation-requests', 'benefits-requests', 'permission-requests', 'arc']) !!}">
                <li class="{!! set_active_menu(['payroll.arc.index']) !!}">
                    <a href="{{ route('payroll.arc.index') }}" data-toggle="tooltip" data-placement="right" title="Solicitud de la planilla ARC">
                        Solicitud de ARC
                    </a>
                </li>
                <li class="{!! set_active_menu(['payroll.vacation-requests.index']) !!}">
                    <a href="{{ route('payroll.vacation-requests.index') }}">Solicitud de vacaciones</a>
                </li>
                <li class="{!! set_active_menu(['payroll.benefits-requests.index']) !!}">
                    <a href="{{ route('payroll.benefits-requests.index') }}">Solicitud de prestaciones</a>
                </li>
                <li class="{!! set_active_menu(['payroll.permission-requests.index']) !!}">
                    <a href="{{ route('payroll.permission-requests.index') }}">Solicitud de permisos</a>
                </li>
            </ul>

        <li>
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right" title="Gestiona la generación de reportes de resumen de disfrute de vacaciones, solicitudes de vacaciones, reporte detallado de trabajadores y personal en disfrute de vacaciones.">Reportes</a>
            <ul class="submenu" style="{!! display_submenu('reports') !!}">
                <li title="Reporte de solicitudes de vacaciones" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.vacation-requests') !!}">
                    <a href="{{ route('payroll.reports.vacation-requests') }}">
                        Solicitudes de vacaciones
                    </a>
                </li>
                <li title="Reporte del status del empleado" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.employment-status') !!}">
                    <a href="{{ route('payroll.reports.employment-status') }}">
                        Reporte detallado de trabajadores
                    </a>
                </li>
                <li title="Reporte del status del empleado" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.staffs') !!}">
                    <a href="{{ route('payroll.reports.staffs') }}">
                        Reporte de trabajadores
                    </a>
                </li>
                <!--li title="Reporte del cálculo de bono vacacional" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.vacation-status') !!}">
                    <a href="{{ route('payroll.reports.vacation-bonus-calculations') }}">
                        Cálculo de bono vacacional
                    </a>
                </li>
                <li><a href="#">Pago de bono vacacional</a></li-->
                <li title="Personal en Disfrute de Vacaciones" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.staff-vacation-enjoyment') !!}">
                    <a href="{{ route('payroll.reports.staff-vacation-enjoyment') }}">
                        Personal en Disfrute de Vacaciones
                    </a>
                </li>
                <!--
                <li title="Reporte de acumulado de prestaciones sociales"
                    data-toggle="tooltip" data-placement="right"
                    class="{!! set_active_menu('payroll.reports.benefits.benefit-advances') !!}">
                    <a href="{{ route('payroll.reports.benefits.benefit-advances') }}">
                        Acumulado de prestaciones sociales
                    </a>
                </li> -->
                <li title="Reporte de conceptos" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.concepts') !!}">
                    <a href="{{ route('payroll.reports.concepts') }}">
                        Reporte de conceptos
                    </a>
                </li>
               <li title="Relación de conceptos" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.relationship-concepts') !!}">
                    <a href="{{ route('payroll.reports.relationship-concepts') }}">
                        Relación de conceptos
                    </a>
                </li>
                <li title="Relación de conceptos" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.workers-by-payroll') !!}">
                    <a href="{{ route('payroll.reports.workers-by-payroll') }}">
                        Reporte de trabajadores por nómina
                    </a>
                </li>
                <li title="Reporte hoja de tiempo" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.time-sheets') !!}">
                    <a href="{{ route('payroll.reports.time-sheets') }}">
                        Reporte de hoja de tiempo
                    </a>
                </li>
                <li title="Carga familiar" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.family-burden') !!}">
                    <a href="{{ route('payroll.reports.family-burden') }}">
                        Reporte de carga familiar
                    </a>
                </li>
                <li title="Reporte de recibos de pago" data-toggle="tooltip" data-placement="right" class="{!! set_active_menu('payroll.reports.payment-receipts') !!}">
                    <a href="{{ route('payroll.reports.payment-receipts') }}">
                        Recibos de pago
                    </a>
                </li>
            </ul>

        </li>
    </ul>
</li>