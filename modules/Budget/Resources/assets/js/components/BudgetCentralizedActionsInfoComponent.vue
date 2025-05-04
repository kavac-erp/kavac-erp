<template>
    <div>
        <button
            @click="addRecord('show_entry_' + id, route_list, $event)"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Ver registro"
            data-toggle="tooltip"
            v-has-tooltip
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            :id="'show_entry_' + id"
            class="modal fade"
            tabindex="-1"
            role="dialog"
            aria-labelledby="BudgetCentralizedActionsInfoModalLabel"
            aria-hidden="true"
        >
            <div
                class="modal-dialog modal-lg text-left"
                role="document"
                style="max-width: 60rem; color: #636e7b; font-size: 13px"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6 style="font-size: 1em">
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información Detallada de Acciones Centralizadas
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Institución :</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ institution.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Dependencia:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ departments.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Código:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ budget.code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ budget.custom_date }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ budget.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre del Responsable:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ personal.fullname }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cargo de Responsable:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    {{ position }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                data-dismiss="modal"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        "id",
        "route_list"
    ],
    data() {
        return {
            records: [],
            budget: [],
            departments: [],
            institution: [],
            personal: [],
            position: [],
            errors: [],
        };
    },
    created() {},
    methods: {
        reset() {},
        initRecords(url, modal_id) {
            this.errors = [];
            this.reset();
            const vm = this;
            url = vm.setUrl(url);
            axios
                .get(url)
                .then((response) => {
                    vm.budget = response.data.budget;
                    vm.budget.custom_date = this.format_date(vm.budget.custom_date);
                    vm.personal = response.data.cargo;
                    vm.departments = response.data.departments;
                    vm.institution = response.data.departments.institution;
                    vm.personal.fullname =
                        response.data.cargo.first_name +
                        "." +
                        response.data.cargo.last_name;
                    vm.position = response.data.cargo.payroll_employment.payrollPosition.name;
                })
                .catch((error) => {});

            if ($("#" + modal_id).length) {
                $("#" + modal_id).modal("show");
            }
        },
    },
};
</script>
