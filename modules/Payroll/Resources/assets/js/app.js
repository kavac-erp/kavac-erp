/**
 *--------------------------------------------------------------------------
 * App Scripts
 *--------------------------------------------------------------------------
 *
 * Scripts del Modulo de Nomina a compilar por la aplicación
 */

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-staff-types', () =>
    import(
        /* webpackChunkName: "payroll-staff-types" */
        './components/settings/PayrollStaffTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de cargo
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-position-types', () =>
    import(
        /* webpackChunkName: "payroll-position-types" */
        './components/settings/PayrollPositionTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de cargos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-positions', () =>
    import(
        /* webpackChunkName: "payroll-positions" */
        './components/settings/PayrollPositionsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de las Coordinaciones
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('payroll-coordinations', () =>
    import(
        /* webpackChunkName: "payroll-coordinations" */
        './components/settings/PayrollCoordinationsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar los Niveles de
 * responsabilidades.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('payroll-responsibilities-level', () =>
    import(
        /* webpackChunkName: "payroll-responsibilities-level" */
        './components/settings/PayrollResponsibilitiesLevelComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de la clasificación del personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-staff-classifications', () =>
    import(
        /* webpackChunkName: "payroll-staff-classifications" */
        './components/settings/PayrollStaffClassificationsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos del grado de instrucción
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-instruction-degrees', () =>
    import(
        /* webpackChunkName: "payroll-instruction-degrees" */
        './components/settings/PayrollInstructionDegreesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos del tipo de estudio
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-study-types', () =>
    import(
        /* webpackChunkName: "payroll-study-types" */
        './components/settings/PayrollStudyTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de nacionalidades
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-nationalities', () =>
    import(
        /* webpackChunkName: "payroll-nationalities" */
        './components/settings/PayrollNationalitiesComponent.vue'
    )
);
/**
 * Componente para reporte de conceptos
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-report-concepts', () =>
    import(
        /* webpackChunkName: "payroll-report-concepts" */
        './components/reports/PayrollReportConcepts.vue'
    )
);
/**
 * Componente para reporte de hoja de tiempo
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-report-time-sheets', () =>
    import(
        /* webpackChunkName: "payroll-report-concepts" */
        './components/reports/PayrollReportTimeSheetComponent.vue'
    )
);
/**
 * Componente para reporte de relación de conceptos
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-report-relationship-concepts', () =>
    import(
        /* webpackChunkName: "payroll-report-relationship-concepts" */
        './components/reports/PayrollReportRelationshipConceptsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de los idiomas
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-languages', () =>
    import(
        /* webpackChunkName: "payroll-languages" */
        './components/settings/PayrollLanguagesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de los niveles de idioma
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-language-levels', () =>
    import(
        /* webpackChunkName: "payroll-language-levels" */
        './components/settings/PayrollLanguageLevelsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de inactividad
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-inactivity-types', () =>
    import(
        /* webpackChunkName: "payroll-inactivity-types" */
        './components/settings/PayrollInactivityTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de contrato
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-contract-types', () =>
    import(
        /* webpackChunkName: "payroll-contract-types" */
        './components/settings/PayrollContractTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de sector
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-sector-types', () =>
    import(
        /* webpackChunkName: "payroll-sector-types" */
        './components/settings/PayrollSectorTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de grados de licencia de conducir
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-license-degrees', () =>
    import(
        /* webpackChunkName: "payroll-license-degrees" */
        './components/settings/PayrollLicenseDegreesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de sangre
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-blood-types', () =>
    import(
        /* webpackChunkName: "payroll-blood-types" */
        './components/settings/PayrollBloodTypesComponent.vue'
    )
);

/**
 * Componente para mostrar listado de información personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-staffs-list', () =>
    import(
        /* webpackChunkName: "payroll-staffs-list" */
        './components/PayrollStaffListComponent.vue'
    )
);

/**
 * Componente para mostrar la información detallada de datos personales
 *
 * @author Pablo Sulbarán <psulbaran@cenditel.gob.ve>
 */
Vue.component('payroll-staff-info', () =>
    import(
        /* webpackChunkName: "payroll-staff-info" */
        './components/PayrollStaffInfoComponent.vue'
    )
);

/**
 * Componente para registrar o actualizar información personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-staff', () =>
    import(
        /* webpackChunkName: "payroll-staff" */
        './components/PayrollStaffComponent.vue'
    )
);

/**
 * Componente para mostrar listado de información socioeconómica
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-socioeconomic-list', () =>
    import(
        /* webpackChunkName: "payroll-socioeconomic-list" */
        './components/PayrollSocioeconomicListComponent.vue'
    )
);

/**
 * Componente para mostrar la información detallada de datos socioeconómicos
 *
 * @author Pablo Sulbarán <psulbaran@cenditel.gob.ve>
 */

Vue.component('payroll-socioeconomic-info', () =>
    import(
        /* webpackChunkName: "payroll-socioeconomic-info" */
        './components/PayrollSocioeconomicInfoComponent.vue'
    )
);

/**
 * Componente para registrar o actualizar información socioeconómica
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-socioeconomic', () =>
    import(
        /* webpackChunkName: "payroll-socioeconomic" */
        './components/PayrollSocioeconomicComponent.vue'
    )
);

/**
 * Componente para mostrar listado de información profesional
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-professional-list', () =>
    import(
        /* webpackChunkName: "payroll-professional-list" */
        './components/PayrollProfessionalListComponent.vue'
    )
);

/**
 * Componente para mostrar la información detallada de información profesional
 *
 * @author Pablo Sulbarán <psulbaran@cenditel.gob.ve>
 */
Vue.component('payroll-professional-info', () =>
    import(
        /* webpackChunkName: "payroll-professional-info" */
        './components/PayrollProfessionalInfoComponent.vue'
    )
);

/**
 * Componente para registrar o actualizar información profesional
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-professional', () =>
    import(
        /* webpackChunkName: "payroll-professional" */
        './components/PayrollProfessionalComponent.vue'
    )
);

/**
 * Componente para mostrar listado de datos laborales
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-employment-list', () =>
    import(
        /* webpackChunkName: "payroll-employment-list" */
        './components/PayrollEmploymentListComponent.vue'
    )
);

/**
 * Componente para registrar o actualizar datos laborales
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component('payroll-employment', () =>
    import(
        /* webpackChunkName: "payroll-employment" */
        './components/PayrollEmploymentComponent.vue'
    )
);

/**
 * Componente para mostrar la información detallada de datos laborales
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-employment-info', () =>
    import(
        /* webpackChunkName: "payroll-employment-info" */
        './components/PayrollEmploymentInfoComponent.vue'
    )
);

/**
 * Componente para la gestión de los parámetros de excepciones de jornada laboral
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-exception-types', () =>
    import(
        /* webpackChunkName: "payroll-exception-types" */
        './components/settings/PayrollExceptionTypesComponent.vue'
    )
);

/**
 * Componente para la gestión de los parámetros de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-parameters', () =>
    import(
        /* webpackChunkName: "payroll-parameters" */
        './components/settings/PayrollParametersComponent.vue'
    )
);

/**
 * Componente para la gestión de los escalafones de nomina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-salary-scales', () =>
    import(
        /* webpackChunkName: "payroll-salary-scales" */
        './components/settings/PayrollSalaryScalesComponent.vue'
    )
);

/**
 * Componente para la gestión de tabuladores de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-salary-tabulators', () =>
    import(
        /* webpackChunkName: "payroll-salary-tabulators" */
        './components/settings/PayrollSalaryTabulatorsComponent.vue'
    )
);

/**
 * Componente para la gestión de tipos de conceptos de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-concept-types', () =>
    import(
        /* webpackChunkName: "payroll-concept-types" */
        './components/settings/PayrollConceptTypesComponent.vue'
    )
);

/**
 * Componente para la gestión de conceptos de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-concepts', () =>
    import(
        /* webpackChunkName: "payroll-concepts" */
        './components/settings/PayrollConceptsComponent.vue'
    )
);

/**
 * Componente para la gestión de tipos de pago
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-payment-types', () =>
    import(
        /* webpackChunkName: "payroll-payment-types" */
        './components/settings/PayrollPaymentTypesComponent.vue'
    )
);

/**
 * Componente para la gestión de políticas vacacionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-vacation-policies', () =>
    import(
        /* webpackChunkName: "payroll-vacation-policies" */
        './components/settings/PayrollVacationPoliciesComponent.vue'
    )
);

/**
 * Componente para la gestión de políticas de prestaciones sociales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-benefits-policies', () =>
    import(
        /* webpackChunkName: "payroll-benefits-policies" */
        './components/settings/PayrollBenefitsPoliciesComponent.vue'
    )
);

/**
 * Componente para registrar o actualizar la nómina de sueldos
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-registers-form', () =>
    import(
        /* webpackChunkName: "payroll-registers-form" */
        './components/registers/PayrollFormComponent.vue'
    )
);

/**
 * Componente para mostrar listado de registros de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-registers-show', () =>
    import(
        /* webpackChunkName: "payroll-registers-show" */
        './components/registers/PayrollShowComponent.vue'
    )
);

/**
 * Componente para mostrar listado de registros de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-registers-list', () =>
    import(
        /* webpackChunkName: "payroll-registers-list" */
        './components/registers/PayrollListComponent.vue'
    )
);

/**
 * Componente para registrar la disponibilidad presupuetaría de la nómina de sueldos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-availability-form', () =>
    import(
        /* webpackChunkName: "payroll-availability-form" */
        './components/registers/availability/PayrollBudgetAvailabilityFormComponent.vue'
    )
);

/**
 * Componente para registrar ajustes en tablas salariales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-salary-adjustments-form', () =>
    import(
        /* webpackChunkName: "payroll-salary-adjustments-form" */
        './components/salary_adjustments/PayrollSalaryAdjustmentsFormComponent.vue'
    )
);

/**
 * Componente para listar ajustes en tablas salariales
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-salary-adjustments-list', () =>
    import(
        /* webpackChunkName: "payroll-salary-adjustments-list" */
        './components/salary_adjustments/PayrollSalaryAdjustmentsListComponent.vue'
    )
);

/**
 * Componente para mostrar el listado de las solicitudes vacacionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-vacation-request-list', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollVacationRequestListComponent.vue'
    )
);

/**
 * Componente para mostrar el listado de las solicitudes de suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-vacation-request-pending-list', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollVacationRequestPendingListComponent.vue'
    )
);

/**
 * Componente para suspender vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-suspend-vacation', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollSuspendVacationComponent.vue'
    )
);

/**
 * Componente para editar una solicitud de suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-edit-suspension-vacation', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollEditSuspensionVacationComponent.vue'
    )
);

/**
 * Componente para suspender vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-reschedule-vacation', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollRescheduleVacationComponent.vue'
    )
);

/**
 * Componente para aprobar una solicitud de suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-approve-suspension-vacation-request', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollApproveSuspensionVacationRequest.vue'
    )
);
/**
 * Componente para aprobar una solicitud de suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component('payroll-reject-suspension-vacation-request', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-list" */
        './components/requests/vacations/PayrollRejectSuspensionVacationRequest.vue'
    )
);

/**
 * Componente para mostrar la información de una solicitud de vacaciones
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-vacation-request-show', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-show" */
        './components/requests/vacations/PayrollVacationRequestShowComponent.vue'
    )
);

/**
 * Componente para registrar las solicitudes vacacionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-vacation-request-form', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-form" */
        './components/requests/vacations/PayrollVacationRequestFormComponent.vue'
    )
);

/**
 * Componente para mostrar el listado de las solicitudes vacacionales pendientes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-vacation-request-pending-list', () =>
    import(
        /* webpackChunkName: "payroll-vacation-request-pending-list" */
        './components/requests/vacations/PayrollVacationRequestPendingListComponent.vue'
    )
);

/**
 * Componente para aprobar/rechazar las solicitudes vacacionales pendientes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-review-vacation-request-pending-form', () =>
    import(
        /* webpackChunkName: "payroll-review-vacation-request-pending-form" */
        './components/requests/vacations/PayrollReviewVacationRequestPendingFormComponent.vue'
    )
);

/**
 * Componente para mostrar el listado de las solicitudes de adelanto de prestaciones
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-benefits-request-list', () =>
    import(
        /* webpackChunkName: "payroll-benefits-request-list" */
        './components/requests/benefits/PayrollBenefitsRequestListComponent.vue'
    )
);

/**
 * Componente para mostrar la información de una solicitud de adelanto de prestaciones
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-benefits-request-show', () =>
    import(
        /* webpackChunkName: "payroll-benefits-request-show" */
        './components/requests/benefits/PayrollBenefitsRequestShowComponent.vue'
    )
);

/**
 * Componente para registrar las solicitudes de adelanto de prestaciones
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-benefits-request-form', () =>
    import(
        /* webpackChunkName: "payroll-benefits-request-form" */
        './components/requests/benefits/PayrollBenefitsRequestFormComponent.vue'
    )
);

/**
 * Componente para aprobar/rechazar las solicitudes de adelanto de prestaciones pendientes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-review-benefits-request-pending-form', () =>
    import(
        /* webpackChunkName: "payroll-review-benefits-request-pending-form" */
        './components/requests/benefits/PayrollReviewBenefitsRequestPendingFormComponent.vue'
    )
);

/**
 * Componentes para gestionar la creación de los reportes de talento humano para los empleados
 *
 * @author Ezequiel <ebaptistae@cenditel.gob.ve>
 */
Vue.component('payroll-report-employment-status', () =>
    import(
        /* webpackChunkName: "payroll-report-emplyment-status" */
        './components/reports/PayrollReportEmploymentStatusComponent.vue'
    )
);


Vue.component('payroll-report-vacation-bonus-calculations', () =>
    import(
        /* webpackChunkName: "payroll-report-vacation-bonus-calculations" */
        './components/reports/PayrollReportVacationBonusCalculationsComponent.vue'
    )
);

Vue.component('payroll-report-staff-vacation-enjoyment', () =>
    import(
        /* webpackChunkName: "payroll-report-staff-vacation-enjoyment" */
        './components/reports/PayrollReportStaffVacationEnjoymentComponent.vue'
    )
);

/**
 * Componentes para gestionar la creación de los reportes de talento humano
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-report-vacation-requests', () =>
    import(
        /* webpackChunkName: "payroll-report-vacation-requests" */
        './components/reports/PayrollReportVacationRequestsComponent.vue'
    )
);

Vue.component('payroll-report-staffs', () =>
    import(
        /* webpackChunkName: "payroll-report-staffs" */
        './components/reports/PayrollReportStaffsComponent.vue'
    )
);
/**
 * Componentes para gestionar la creación de los reportes de talento humano / Reporte de Trabajadores por nómina
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 */
Vue.component('payroll-report-workers-by-payroll', () =>
    import(
        /* webpackChunkName: "payroll-report-workers-by-payroll" */
        './components/reports/PayrollReportWorkersByPayrollComponent.vue'
    )
);
/**
 * Componentes para gestionar la creación de los reportes de talento humano / Reporte de Trabajadores por nómina
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 */
Vue.component('payroll-report-payment-receipt', () =>
    import(
        /* webpackChunkName: "payroll-report-workers-by-payroll" */
        './components/reports/PayrollReportPaymentReceiptsComponent.vue'
    )
);

/**
 * Componente para la gestión de gráficos estadísticos del módulo de bienes
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 */
Vue.component('payroll-graph-charts', () =>
    import(
        /* webpackChunkName: "payroll-graph-charts" */
        './components/PayrollGraphChartsComponent.vue'
    )
);

/**
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-report-benefit-advances', () =>
    import(
        /* webpackChunkName: "payroll-report-advance-benefits" */
        './components/reports/benefits/PayrollReportBenefitAdvancesComponent.vue'
    )
);

/**
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-report-family-burden', () =>
    import(
        /* webpackChunkName: "payroll-report-advance-benefits" */
        './components/reports/PayrollReportFamilyBurden.vue'
    )
);

/**
 * Componente para gestionar políticas de permisos
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-permission-policies', () =>
    import(
        /* webpackChunkName: "payroll-permission-policies" */
        './components/settings/PayrollPermissionPoliciesComponent.vue'
    )
);
/**
 * Componente para gestionar solicitud de permiso
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-permission-request-create', () =>
    import(
        /* webpackChunkName: "payroll-permission-request-create" */
        './components/requests/permissions/PayrollPermissionRequestCreateComponent.vue'
    )
);
/**
 * Componente para mostrar el listado de las solicitudes de permisos
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-permission-request-list', () =>
    import(
        /* webpackChunkName: "payroll-permission-request-list" */
        './components/requests/permissions/PayrollPermissionRequestListComponent.vue'
    )
);
/**
 * Componente que muestra información del permiso
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-permission-request-info', () =>
    import(
        /* webpackChunkName: "payroll-permission-request-info" */
        './components/requests/permissions/PayrollPermissionRequestInfoComponent.vue'
    )
);
/**
 * Componente para mostrar el listado de las solicitudes de permisos pendientes
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component('payroll-permission-request-pending-list', () =>
    import(
        /* webpackChunkName: "payroll-permission-request-pending-list" */
        './components/requests/permissions/PayrollPermissionRequestPendingListComponent.vue'
    )
);

/**
 * Componente para registrar los datos financieros
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 */
Vue.component('payroll-financial-form', () =>
    import(
        /* webpackChunkName: "payroll-benefits-request-form" */
        './components/PayrollFinancialFormComponent.vue'
    )
);

/**
 * Componente para mostrar el listado de datos financieros
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 */
Vue.component('payroll-financial-list', () =>
    import(
        /* webpackChunkName: "payroll-financial-list" */
        './components/PayrollFinancialListComponent.vue'
    )
);

/**
 * Componente para mostrar detalle de datos financieros
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 */
Vue.component('payroll-financial-info', () =>
    import(
        /* webpackChunkName: "payroll-financial-info" */
        './components/PayrollFinancialInfoComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de tipos de liquidación
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 */
Vue.component('payroll-settlement-types', () =>
    import(
        /* webpackChunkName: "payroll-settlement-types" */
        './components/settings/PayrollSettlementTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de parentescos
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 */
Vue.component('payroll-relationships', () =>
    import(
        /* webpackChunkName: "payroll-relationships" */
        './components/settings/PayrollRelationshipsComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de discapacidades
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 */
Vue.component('payroll-disabilities', () =>
    import(
        /* webpackChunkName: "payroll-disabilities" */
        './components/settings/PayrollDisabilitiesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos niveles de escolaridad
 * 
 * @author José Briceño <josejorgebriceno9@gmail.com>
 */
Vue.component('payroll-schooling-levels', () =>
    import(
        /* webpackChunkName: "schooling-level" */
        './components/settings/PayrollSchoolingLevelComponent.vue')
);

/**
 * Componente para listar, crear, actualizar y borrar datos de días feriados
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-holidays', () =>
    import(
        /* webpackChunkName: "payroll-holidays" */
        './components/settings/PayrollHolidaysComponent.vue')
);

/**
 * Componente para la gestión de calculos de salario
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
//Vue.component('payroll-salary-simulator', () => import('./components/PayrollSalarySimulatorComponent.vue'));

/**
 * Componente para crear un archivo txt de nómina
 * 
 * @author José Briceño <josejorgebriceno9@gmail.com>
 */
Vue.component('payroll-text-file', () =>
    import(
        /* webpackChunkName: "payroll-text-file" */
        './components/payroll_text_file/PayrollTextFileComponent.vue')
);

/**
 * Componente para listar, crear, actualizar y borrar datos de grupos de supervisados
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-supervised-groups', () =>
    import(
        /* webpackChunkName: "payroll-supervised-groups" */
        './components/settings/PayrollSupervisedGroupsComponent.vue')
);

/**
 * Componente para listar, actualizar, generar y borrar un archivo txt de nómina
 * 
 * @author José Briceño <josejorgebriceno9@gmail.com>
 */
Vue.component('payroll-text-file-list', () =>
    import(
        /* webpackChunkName: "payroll-text-file-list" */
        './components/payroll_text_file/PayrollTextFileListComponent.vue')
);

/**
 * Componente para crear, listar, actualizar y borrar datos de Tipos de beca
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
 */
Vue.component('payroll-scholarship-types', () =>
    import(
        /* webpackChunkName: "payroll-scholarship-types" */
        './components/settings/PayrollScolarshipTypesComponent.vue'
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de parámetros de hoja de tiempo
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-time-sheet-parameters', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-parameters" */
        './components/settings/PayrollTimeSheetParametersComponent.vue')
);

/**
 * Componentes para listar, crear y actualizar los datos contables de los trabajadores
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-staff-account-form', () =>
    import(
        /* webpackChunkName: "payroll-staff-account-form" */
        './components/PayrollStaffAccountComponent.vue')
);

Vue.component('payroll-staff-account-list', () =>
    import(
        /* webpackChunkName: "payroll-staff-account-list" */
        './components/PayrollStaffAccountListComponent.vue')
);

Vue.component('payroll-staff-account-info', () =>
    import(
        /* webpackChunkName: "payroll-staff-account-info" */
        './components/PayrollStaffAccountInfoComponent.vue')
);

Vue.component('payroll-ari-register-form', () =>
    import(
        /* webpackChunkName: "payroll-ari-register-form" */
        './components/ari_register/PayrollAriRegisterFormComponent.vue')
);

Vue.component('payroll-ari-register-list', () =>
    import(
        /* webpackChunkName: "payroll-ari-register-list" */
        './components/ari_register/PayrollAriRegisterListComponent.vue')
);

Vue.component('payroll-ari-register-info', () =>
    import(
        /* webpackChunkName: "payroll-ari-register-info" */
        './components/ari_register/PayrollAriRegisterInfoComponent.vue')
);

/**
 * Componentes para la gestión de los esquemas de guardias
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component('payroll-guard-scheme-form', () =>
    import(
        /* webpackChunkName: "payroll-guard-scheme-form" */
        './components/guard_schemes/PayrollGuardSchemeFormComponent.vue')
);

Vue.component('payroll-guard-scheme-list', () =>
    import(
        /* webpackChunkName: "payroll-guard-scheme-list" */
        './components/guard_schemes/PayrollGuardSchemeListComponent.vue')
);

Vue.component('payroll-guard-scheme-info', () =>
    import(
        /* webpackChunkName: "payroll-guard-scheme-info" */
        './components/guard_schemes/PayrollGuardSchemeInfoComponent.vue')
);

Vue.component('payroll-guard-scheme-periods-info', () =>
    import(
        /* webpackChunkName: "payroll-guard-scheme-periods-info" */
        './components/guard_schemes/PayrollGuardSchemePeriodsInfoComponent.vue')
);

Vue.component('payroll-guard-scheme-period-confirm', () =>
    import(
        /* webpackChunkName: "payroll-guard-scheme-period-confirm" */
        './components/guard_schemes/PayrollGuardSchemePeriodConfirmComponent.vue')
);

/**
 * Componente para la gestión de hoja de tiempo
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-time-sheet-form', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-form" */
        './components/time_sheet/PayrollTimeSheetFormComponent.vue')
);

Vue.component('payroll-time-sheet-list', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-list" */
        './components/time_sheet/PayrollTimeSheetListComponent.vue')
);

Vue.component('payroll-time-sheet-info', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-info" */
        './components/time_sheet/PayrollTimeSheetInfoComponent.vue')
);

/**
 * Componente para la gestión de hoja de tiempo pendiente
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-time-sheet-pending-form', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-pending-form" */
        './components/time_sheet_pending/PayrollTimeSheetPendingFormComponent.vue')
);

Vue.component('payroll-time-sheet-pending-list', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-pending-list" */
        './components/time_sheet_pending/PayrollTimeSheetPendingListComponent.vue')
);

Vue.component('payroll-time-sheet-pending-info', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-pending-info" */
        './components/time_sheet_pending/PayrollTimeSheetPendingInfoComponent.vue')
);

Vue.component('payroll-time-sheet-pending-concepts', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-pending-concepts" */
        './components/time_sheet_pending/PayrollTimeSheetPendingConceptsComponent.vue')
);

Vue.component('payroll-time-sheet-pending-observations', () =>
    import(
        /* webpackChunkName: "payroll-time-sheet-pending-observations" */
        './components/time_sheet_pending/PayrollTimeSheetPendingObservationsComponent.vue')
);

Vue.component('payroll-arc-responsibles', () =>
    import(
        /* webpackChunkName: "payroll-arc-responsibles" */
        './components/settings/PayrollArcResponsiblesComponent.vue')
);

Vue.component('payroll-arc-list', () =>
    import(
        /* webpackChunkName: "payroll-arc-list" */
        './components/arc/PayrollArcListComponent.vue')
);

Vue.component('payroll-arc-info', () =>
    import(
        /* webpackChunkName: "payroll-arc-info" */
        './components/arc/PayrollArcInfoComponent.vue')
);

/**
 * Opciones de configuración global del módulo de Nómina
 */
Vue.mixin({
    methods: {
        /**
         * Método que obtiene un arreglo con los conceptos registrados
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getPayrollConcepts() {
            const vm = this;
            vm.payroll_concepts = [];
            await axios.get(`${window.app_url}/payroll/get-concepts`).then(response => {
                vm.payroll_concepts = response.data;
            });
        },
        /**
         * Método que obtiene un arreglo con los tipos de conceptos registrados
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getPayrollConceptTypes() {
            const vm = this;
            vm.payroll_concept_types = [];
            await axios.get(`${window.app_url}/payroll/get-concept-types`).then(response => {
                vm.payroll_concept_types = response.data;
            });
        },
        /**
         * Método que obtiene un arreglo con los tipos de pago registrados
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getPayrollPaymentTypes(enabled = false) {
            const vm = this;
            vm.payroll_payment_types = [];
            await axios.get(`${window.app_url}/payroll/get-payment-types`).then(response => {
                vm.payroll_payment_types = response.data.map((item) => {
                    if (!enabled) {
                        item['disabled'] = (item['payroll_ids'].length > 0) && (!item['payroll_ids'].includes(vm.payroll_id));
                    }
                    return item;
                });;
            });
        },
        /**
         * Método que obtiene un arreglo con los tipos de conceptos registrados
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getPayrollSalaryTabulators() {
            const vm = this;
            vm.payroll_salary_tabulators = [];
            axios.get(`${window.app_url}/payroll/get-salary-tabulators`).then(response => {
                vm.payroll_salary_tabulators = response.data;
            });
        },

        /**
         * Obtiene los datos de las nacionalidades registradas
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollNationalities() {
            this.payroll_nationalities = [];
            await axios.get(`${window.app_url}/payroll/get-nationalities`).then(response => {
                this.payroll_nationalities = response.data;
            });
        },


        /**
         * Obtiene los datos de los tipos de cargo registrados en la institucion
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollPositionTypes() {
            const vm = this;
            vm.payroll_position_types = [];
            axios.get(`${window.app_url}/payroll/get-position-types`).then(response => {
                vm.payroll_position_types = response.data;
            });
        },

        /**
         * Obtiene los datos de los cargos registrados en la institucion
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollPositions() {
            const vm = this;
            vm.payroll_positions = [];
            axios.get(`${window.app_url}/payroll/get-positions`).then(response => {
                vm.payroll_positions = response.data;
            });
        },

        /**
         * Obtiene los datos registrados de la tabla intermedia entre
         * PayrollEmployment y PayrollPosition.
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        getPayrollEmploymentsPositions() {
            const vm = this;
            vm.employments_positions = [];
            axios.get(`${window.app_url}/payroll/get-employments-positions`).then(response => {
                vm.employments_positions = response.data;
            });
        },

        /**
         * Obtiene los datos de las coordinaciones registrados en la institucion.
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        getPayrollCoordinations() {
            const vm = this;
            vm.payroll_coordinations = [];
            axios.get(`${window.app_url}/payroll/get-coordinations`).then(response => {
                vm.payroll_coordinations = response.data;
            });
        },

        /**
         * Obtiene los datos de los trabajadores registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
         *
         * @param string filter establece una condición bajo la cual filtrar los resultados
         */
        async getPayrollStaffs(type = '') {
            this.payroll_staffs = [];
            await axios.get(`${window.app_url}/payroll/get-staffs/${type}`).then(response => {
                this.payroll_staffs = response.data;
            });
        },

        /**
         * Obtiene los datos de los trabajadores registrados
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
         * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
         *
         * @param string filter establece una condición bajo la cual filtrar los resultados
         */
        async getPayrollSocioeconomic(type = '') {
            this.payroll_socioeconomic = [];
            await axios.get(`${window.app_url}/payroll/get-socioeconomic/${type}`).then(response => {
                this.payroll_socioeconomic = response.data;
            });
        },

        /**
         * Obtiene los datos de los trabajadores registrados
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
         * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
         *
         * @param string filter establece una condición bajo la cual filtrar los resultados
         */
        async getPayrollProfession(type = '') {
            this.payroll_professional = [];
            await axios.get(`${window.app_url}/payroll/get-professional/${type}`).then(response => {
                this.payroll_professional = response.data;
            });
        },
        /**
         * Obtiene los datos de los tipos de estudio registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollStudyTypes() {
            this.payroll_study_types = [];
            await axios.get(`${window.app_url}/payroll/get-study-types`).then(response => {
                this.payroll_study_types = response.data;
            });
        },

        /**
         * Obtiene los datos del idioma que manejan los trabajdores
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollLanguages() {
            this.payroll_languages = [];
            await axios.get(`${window.app_url}/payroll/get-languages`).then(response => {
                this.payroll_languages = response.data;
            });
        },

        /**
         * Obtiene los datos del nivel de idioma que tienen los trabajadores
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollLanguageLevels() {
            this.payroll_language_levels = [];
            await axios.get(`${window.app_url}/payroll/get-language-levels`).then(response => {
                this.payroll_language_levels = response.data;
            });
        },

        /**
         * Obtiene los datos de los grados de instrucción registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollInstructionDegrees() {
            this.payroll_instruction_degree = [];
            await axios.get(`${window.app_url}/payroll/get-instruction-degrees`).then(response => {
                this.payroll_instruction_degrees = response.data;
            });
        },

        /**
         * Obtiene los datos de las profesiones (pruebas)
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getJsonProfessions() {
            this.json_professions = [];
            axios.get(`${window.app_url}/payroll/get-json-professions`).then(response => {
                this.json_professions = response.data.jsonProfessions;
            });
        },

        /**
         * Obtiene los datos de tipos de inactividad registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollInactivityTypes() {
            this.payroll_inactivity_types = [];
            axios.get(`${window.app_url}/payroll/get-inactivity-types`).then(response => {
                this.payroll_inactivity_types = response.data;
            });
        },

        /**
         * Obtiene los datos de tipos de personal registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollStaffTypes() {
            this.payroll_staff_types = [];
            axios.get(`${window.app_url}/payroll/get-staff-types`).then(response => {
                this.payroll_staff_types = response.data;
            });
        },

        /**
         * Obtiene los datos de tipos de contrato registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollContractTypes() {
            this.payroll_contract_types = [];
            axios.get(`${window.app_url}/payroll/get-contract-types`).then(response => {
                this.payroll_contract_types = response.data;
            });
        },

        /**
         * Obtiene los datos de tipos de contrato registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        getPayrollSectorTypes() {
            this.payroll_sector_types = [];
            axios.get(`${window.app_url}/payroll/get-sector-types`).then(response => {
                this.payroll_sector_types = response.data;
            });
        },

        /**
         * Obtiene los datos de grados de licencia de conducir registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollLicenseDegrees() {
            this.payroll_license_degrees = [];
            await axios.get(`${window.app_url}/payroll/get-license-degrees`).then(response => {
                this.payroll_license_degrees = response.data;
            });
        },

        /**
         * Obtiene los datos de tipos de sangre registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         */
        async getPayrollBloodTypes() {
            this.payroll_blood_types = [];
            await axios.get(`${window.app_url}/payroll/get-blood-types`).then(response => {
                this.payroll_blood_types = response.data;
            });
        },

        /**
         * Obtiene los datos de políticas de permiso
         *
         * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
         */
        getPayrollPermissionPolicies() {
            this.payroll_permission_policies = [];
            axios.get(`${window.app_url}/payroll/get-permission-policies`).then(response => {
                this.payroll_permission_policies = response.data;
            });
        },

        /**
         * Obtiene los datos de discapacidades registradas
         *
         * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
         */
        async getPayrollDisabilities() {
            this.payroll_disabilities = [];
            await axios.get(`${window.app_url}/payroll/get-disabilities`).then(response => {
                this.payroll_disabilities = response.data;
            });
        },

        /**
         * Obtiene los datos de nivel de escolaridad
         *
         * @author José Briceño <josejorgebriceno9@gmail.com>
         */
        async getPayrollSchoolingLevels() {
            this.payroll_schooling_levels = [];
            await axios.get(`${window.app_url}/payroll/get-schooling-levels`).then(response => {
                this.payroll_schooling_levels = response.data;
            });
        },


        /**
         * Obtiene los datos de tipos de becas
         *
         * @author Francisco Escala
         */
        async getPayrollScholarshipTypes() {
            this.payroll_scholarship_types = [];
            await axios.get(`${window.app_url}/payroll/get-scholarship-types`).then(response => {
                this.payroll_scholarship_types = response.data;
            });
        },


        /**
         * Obtiene los datos de nivel de escolaridad
         *
         * @author José Briceño <josejorgebriceno9@gmail.com>
         */
        async getPayrollRelationships() {
            this.payroll_relationships = [];
            await axios.get(`${window.app_url}/payroll/get-relationships`).then(response => {
                this.payroll_relationships = response.data;
            });
        },

        /**
         * Obtiene los datos de las entidades bancarias registradas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getBanks() {
            const vm = this;
            await axios.get(`${vm.app_url}/finance/get-banks`).then(response => {
                vm.banks = response.data;
            }).catch(error => {
                vm.logs('Finance/Resources/assets/js/_all.js', 90, error, 'getBanks');
            });
        },

        /**
         * Obtiene los datos de los tipos de cuenta bancaria
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAccountTypes() {
            const vm = this;
            await axios.get(`${vm.app_url}/finance/get-account-types`).then(response => {
                vm.account_types = response.data;
            }).catch(error => {
                console.log(error);
            });
        },

        /**
         * Método que obtiene un arreglo con los tipos de excepciones de jornada laboral
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getPayrollExceptionTypes() {
            const vm = this;
            await axios.get(`${window.app_url}/payroll/get-exception-types`).then(response => {
                vm.exception_types = response.data;
            });
        },

        normalizeText(text, model) {
            const vm = this;
            vm.record[model] = text.normalize('NFKD').replace(/[^\x00-\x7F]/g, '');
        },

        normalizeParameterText(text, model) {
            const vm = this;
            vm.record[model] = text.normalize('NFKD').replace(/[^a-zA-Z\s]/g, '');
        },

        /**
         * Obtiene los datos de los grupos de supervisados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         *
         */
        async getPayrollSupervisedGroups(id = null, type = null) {
            const vm = this;
            vm.payroll_supervised_groups = [];
            await axios.get(`${window.app_url}/payroll/get-supervised-groups`, { params: { id, type } }).then(response => {
                vm.payroll_supervised_groups = response.data;
            });
        },

        /**
         * Obtiene los datos de los trabajadores registrados agrupados por departamento
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        async getPayrollTimeParameters() {
            const vm = this;
            vm.payroll_time_parameters = [];
            await axios.get(`${window.app_url}/payroll/get-time-parameters`).then(response => {
                vm.payroll_time_parameters = Object.values(response.data);
            });
        },
    }
});
