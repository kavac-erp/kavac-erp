/**
 * Componente para la configuración del código para las referencias de los asientos contables
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-setting-code", () =>
    import(
        /* webpackChunkName: "accounting-setting-code" */
        "./components/settings/AccountingSettingCodeComponent.vue"
    )
);

/**
 * Componente para la configuración de categorias de origen para asientos contables
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-setting-category", () =>
    import(
        /* webpackChunkName: "accounting-setting-category" */
        "./components/settings/AccountingSettingCategoryComponent.vue"
    )
);

/**
 * Componente para el CRUD en ventana modal de cuentas patrimoniales
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-setting-account", () =>
    import(
        /* webpackChunkName: "accounting-setting-account" */
        "./components/settings/AccountingAccountComponent.vue"
    )
);

/**
 * Componente para Listar cuentas patrimoniales
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-accounts-list", () =>
    import(
        /* webpackChunkName: "accounting-accounts-list" */
        "./components/accounts/AccountingListComponent.vue"
    )
);

/**
 * Componente para la creación y edición de cuentas patrimoniales
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-form", () =>
    import(
        /* webpackChunkName: "accounting-form" */
        "./components/accounts/AccountingFormComponent.vue"
    )
);

/**
 * Componente con el formulario para importar cuentas patrimoniales desde un excel
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-import-form", () =>
    import(
        /* webpackChunkName: "accounting-import-form" */
        "./components/accounts/AccountingImportComponent.vue"
    )
);

/**
 * Componente para la consulta de los registros del convertidor de cuentas
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-converter-index", () =>
    import(
        /* webpackChunkName: "accounting-converter-index" */
        "./components/account_converter/AccountingIndexComponent.vue"
    )
);

/**
 * Componente para listar cuentas con conversiones
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-conversion-list", () =>
    import(
        /* webpackChunkName: "accounting-conversion-list" */
        "./components/account_converter/AccountingListComponent.vue"
    )
);

/**
 * Componente para el formulario de creación
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-conversion-form", () =>
    import(
        /* webpackChunkName: "accounting-conversion-form" */
        "./components/account_converter/AccountingFormComponent.vue"
    )
);

/**
 * Componente para el formulario de edición de conversión de cuentas
 *
 * @author
 */
Vue.component("accounting-conversion-edit", () =>
    import(
        /* webpackChunkName: "accounting-conversion-edit" */
        "./components/account_converter/AccountingFormEditComponent.vue"
    )
);

/**
 * Componente para la consulta de asientos contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry", () =>
    import(
        /* webpackChunkName: "accounting-entry" */
        "./components/entries/AccountingIndexComponent.vue"
    )
);

/**
 * Componente para cargar la tabla de asientos contables
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-list-approved", () =>
    import(
        /* webpackChunkName: "accounting-entry-list-approved" */
        "./components/entries/AccountingListComponent.vue"
    )
);

/**
 * Componente para la creación de asientos contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-form", () =>
    import(
        /* webpackChunkName: "accounting-entry-form" */
        "./components/entries/AccountingFormComponent.vue"
    )
);

/**
 * Componente para viasualizar en modal asiento contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-show", () =>
    import(
        /* webpackChunkName: "accounting-entry-show" */
        "./components/entries/AccountingShowComponent.vue"
    )
);

/**
 * Componente para cargar la tabla de cuentas patrimoniales para el asiento contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-form-account", () =>
    import(
        /* webpackChunkName: "accounting-entry-form-account" */
        "./components/entries/AccountingAccountFormsComponent.vue"
    )
);

/**
 * Componente que permite enviar los datos correspondientes para para realizar un reverso
 *
 * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
 */
Vue.component("accounting-entry-reverse", () =>
    import(
        /* webpackChunkName: "dashboard-accounting-report-histories" */
        "./components/entries/AccountingEntryReverseComponent.vue"
    )
);

/**
 * Componente index para el reporte de balance de comprobación
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-report-checkup-balance", () =>
    import(
        /* webpackChunkName: "accounting-report-checkup-balance" */
        "./components/reports/AccountingCheckupBalanceComponent.vue"
    )
);

/**
 * Componente index para el reporte del libro diario
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-report-daily-book", () =>
    import(
        /* webpackChunkName: "accounting-report-daily-book" */
        "./components/reports/AccountingDailyBookComponent.vue"
    )
);

/**
 * Componente index para el reporte del Mayor Analítico
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-report-analytical-major", () =>
    import(
        /* webpackChunkName: "accounting-report-analytical-major" */
        "./components/reports/AccountingAnalyticalMajorComponent.vue"
    )
);

/**
 * Componente index para el reporte del Mayor Analítico
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-report-auxiliary-book", () =>
    import(
        /* webpackChunkName: "accounting-report-auxiliary-book" */
        "./components/reports/AccountingAuxiliaryBookComponent.vue"
    )
);

/**
 * Componente index para el reporte de Balance General y reporte de satdo de resultados
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-report-balance-sheet-state-of-results", () =>
    import(
        /* webpackChunkName: "accounting-report-balance-sheet-state-of-results" */
        "./components/reports/AccountingBalanceSheetAndStateOfResultsComponent.vue"
    )
);

/**
 * Componente index para el reporte de Balance General y reporte de satdo de resultados
 *
 * @author  Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
 */
Vue.component("accounting-report-patrimonial-movement", () =>
    import(
        /* webpackChunkName: "accounting-report-patrimonial-movement" */
        "./components/reports/AccountingPatrimonialMovementComponent.vue"
    )
);

/**
 * Componente index para el reporte de Balance General y reporte de satdo de resultados
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("dashboard-accounting-info", () =>
    import(
        /* webpackChunkName: "dashboard-accounting-info" */
        "./components/dashboard/AccountingEntryHistoriesComponent.vue"
    )
);

/**
 * Componente index para el reporte de Balance General y reporte de satdo de resultados
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("dashboard-accounting-report-histories", () =>
    import(
        /* webpackChunkName: "dashboard-accounting-report-histories" */
        "./components/dashboard/AccountingReportHistoriesComponent.vue"
    )
);

/**
 * Evento global Bus del modulo de Contabilidad
 */
window.EventBus = new Vue();

/**
 * Opciones de configuración global del módulo de contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.mixin({
    data() {
        return {
            errors: [],
            months: [
                { id: 1, text: "Enero" },
                { id: 2, text: "Febrero" },
                { id: 3, text: "Marzo" },
                { id: 4, text: "Abril" },
                { id: 5, text: "Mayo" },
                { id: 6, text: "Junio" },
                { id: 7, text: "Julio" },
                { id: 8, text: "Agosto" },
                { id: 9, text: "Septiembre" },
                { id: 10, text: "Octubre" },
                { id: 11, text: "Noviembre" },
                { id: 12, text: "Diciembre" },
            ],
            years: [],
            year_init: new Date().getFullYear(),
            year_end: new Date().getFullYear(),
            month_init: 1,
            month_end: 12,
        };
    },
    methods: {
        /**
         * Crea un array con los años desde el dado hasta el actual
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {integer} year_old fecha del año de inicio
         * @param  {boolean} optionExtra bandera para determinar si agregar un registro extra al pricipio del array de los años
         */
        CalculateOptionsYears: function (year_old, optionExtra = false) {
            var date = new Date();
            if (optionExtra) {
                this.years.push({
                    id: 0,
                    text: "Todos",
                });
                this.year_init = 0;
            }
            for (var year = date.getFullYear(); year >= year_old; year--) {
                this.years.push({
                    id: year,
                    text: year,
                });
            }
        },

        /**
         * Abre una nueva ventana en el navegador
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {string} url para la nueva ventana
         * @param  {string} type tipo de ventana que se desea abrir
         * @return {boolean} Devuelve falso si no se ha indicado alguna información requerida
         */
        OpenPdf: function (url, type) {
            const vm = this;
            if (!url) {
                return;
            }
            url = vm.setUrl(url).replace("/pdf", "/pdfVue");
            vm.loading = true;
            axios.get(url).then((response) => {
                if (!response.data.result) {
                    vm.showMessage(
                        "custom",
                        "Error en conversión",
                        "danger",
                        "screen-error",
                        response.data.message
                    );
                } else {
                    url = url.split("/pdf")[0];
                    url += "/" + response.data.id;
                    window.open(url, type);
                }
                vm.loading = false;
            });
        },

        /**
         * Se aprueba el asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        approve: function (index) {
            var url = `${window.app_url}/accounting/entries/approve`;
            var records = this.records;
            var confirmated = false;
            index = index - 1;
            const vm = this;

            bootbox.confirm({
                title: "¿Aprobar Asiento?",
                message: "¿Esta seguro de aprobar este asiento?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                        className: "btn btn-default btn-sm btn-round",
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                        className: "btn btn-primary btn-sm btn-round",
                    },
                },
                callback: function (result) {
                    if (result) {
                        confirmated = true;
                        vm.loading = true;
                        axios
                            .post(url + "/" + records[index].id)
                            .then((response) => {
                                if (
                                    typeof response.data.error !== "undefined"
                                ) {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage(
                                        "custom",
                                        "Alerta!",
                                        "danger",
                                        "screen-error",
                                        response.data.message
                                    );
                                    return false;
                                }
                                //records.splice(index, 1);
                                EventBus.$emit("entry:index.searchRecords");
                                vm.showMessage("update");
                                vm.reload = true;
                                if (
                                    typeof response.data.redirect !==
                                    "undefined"
                                ) {
                                    location.href = response.data.redirect;
                                }
                                vm.loading = false;
                            })
                            .catch((error) => {
                                if (typeof error.response != "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            "custom",
                                            "Acceso Denegado",
                                            "danger",
                                            "screen-error",
                                            error.response.data.message
                                        );
                                    }
                                    if (error.response.status == 500) {
                                        const messages =
                                            error.response.data.message;
                                        vm.showMessage(
                                            messages.type,
                                            messages.title,
                                            messages.class,
                                            messages.icon,
                                            messages.text
                                        );
                                    }
                                }
                                vm.loading = false;
                                console.log(error);
                            });
                    }
                },
            });

            if (confirmated) {
                vm.records = records;
                vm.reload = true;
            }
        },

        /**
         * cambia el formato para la fecha de YYYY/mm/dd a dd/mm/YYYY
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {string} date fecha en formato YYYY/mm/dd
         * @return {string} f_date fecha en formato dd/mm/YYYY
         */
        formatDate(date) {
            var f_date =
                date.split("-")[2] +
                "/" +
                date.split("-")[1] +
                "/" +
                date.split("-")[0];
            return f_date;
        },

        /**
         * Despliega y oculta los tr de una tabla que tengas el nombre dado
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return String con la cadena permitida
         */
        displayDetails(id) {
            if (!document.getElementById) return false;
            let fila = document.getElementsByName("details_" + id);
            for (var i = 0; i < fila.length; i++) {
                if (fila[i].style.display != "none") {
                    fila[i].style.display = "none"; //ocultar fila
                    this.minimized = true;
                    $("#i-" + id + "-show").css("display", "none");
                    $("#i-" + id + "-none").css("display", "");
                } else {
                    fila[i].style.display = ""; //mostrar fila
                    this.minimized = false;
                    $("#i-" + id + "-show").css("display", "");
                    $("#i-" + id + "-none").css("display", "none");
                }
            }
        },

        /**
         * Solo permite escribir en los input los caracteres establecidos
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {String} con la cadena permitida
         */
        justAllow(prop, string, filter = "1234567890") {
            const vm = this;
            vm.record[prop] = "";

            /** Recorrer el texto y verificar si el caracter se encuentra en la lista de validos  */
            for (var i = 0; i < string.length; i++) {
                //Se añaden a la salida los caracteres validos
                if (filter.indexOf(string.charAt(i)) != -1) {
                    vm.record[prop] += string.charAt(i);
                }
            }
        },
    },
});
