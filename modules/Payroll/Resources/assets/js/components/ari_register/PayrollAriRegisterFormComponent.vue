<template>
    <section id="PayrollAriRegisterFormComponent">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Porcentaje de cálculo ARI </h6>
                <div class="card-btns">
                    <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                        title="Ir atrás" data-toggle="tooltip">
                        <i class="fa fa-reply"></i>
                    </a>
                    <a href="#" class="card-minimize btn btn-card-action btn-round" title="Minimizar" data-toggle="tooltip">
                        <i class="now-ui-icons arrows-1_minimal-up"></i>
                    </a>
                </div>
            </div>

            <!-- Registro planilla ARI -->
            <div class="card-body">
                <!-- mensajes de error -->
                <div class="alert alert-danger" v-if="errors.length > 0">
                    <div class="container">
                        <div class="alert-icon">
                            <i class="now-ui-icons objects_support-17"></i>
                        </div>
                        <strong>Cuidado!</strong> Debe verificar los siguientes
                        errores antes de continuar:
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                            @click.prevent="errors = []">
                            <span aria-hidden="true">
                                <i class="now-ui-icons ui-1_simple-remove"></i>
                            </span>
                        </button>
                        <ul>
                            <li v-for="error in errors" :key="error">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- mensajes de error -->
                <div class="row">
                    <div class="col-4 mt-4">
                        <label for="fileName" class="is-required">Trabajador</label>
                        <select2 :options="payroll_staffs" v-model="record.payroll_staff_id" :disabled="block_worker">
                        </select2>
                    </div>
                    <div class="col-4 mt-4">
                        <label for="fileName" class="is-required">Porcentaje</label>
                        <input type="text" maxlength="6" class="form-control" id="fileName" v-model="record.percetage"
                            @input="filterString">
                    </div>
                    <div id="date" class="col-4 mt-4">
                        <label for="periodStartDate" class="is-required">Desde</label>
                        <div class="input-group input-sm">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_calendar-60"></i>
                            </span>
                            <input type="date" class="form-control no-restrict" data-toggle="tooltip" title="Desde la fecha"
                                v-model="record.startDate" id="periodStartDate" placeholder="Fecha">
                        </div>
                    </div>
                    <div id="date" class="col-4 mt-4">
                        <label for="periodStartDate">Hasta</label>
                        <div class="input-group input-sm">
                            <span class="input-group-addon">
                                <i class="now-ui-icons ui-1_calendar-60"></i>
                            </span>
                            <input type="date" class="form-control no-restrict" data-toggle="tooltip" title="Desde la fecha"
                                v-model="record.endDate" id="periodStartDate" placeholder="Fecha">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Registro planilla ARI -->

            <div class="card-footer text-right">
                <button type="button" @click="reset" class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                    title="Borrar datos del formulario">
                    <i class="fa fa-eraser"></i>
                </button>
                <button type="button" @click="redirect_back(route_list)" class="btn btn-warning btn-icon btn-round"
                    data-toggle="tooltip" title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="createForm('payroll/ari-register-save')"
                    class="btn btn-success btn-icon btn-round" title="Guardar datos del formulario">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </section>
</template>
<script>

export default {
    props: {
        ari_register: {
            type: [Object, String],
            required: false,
            default: () => ''
        }
    },

    data() {
        return {
            errors: [],
            payroll_staffs: [],
            block_worker: false,
            record: {
                id: '',
                percetage: '',
                startDate: '',
                endDate: '',
                payroll_staff_id: ''
            },
            prevValue: '',
            prevSelectionStart: 0
        };
    },

    async created() {
        const vm = this;
        await vm.getPayrollStaffs()

        try {
            if (vm.ari_register) {
                vm.record.id = vm.ari_register.id;
                vm.record.percetage = (vm.ari_register.percetage * 100).toFixed(2);
                vm.record.startDate = vm.ari_register.from_date;
                vm.record.endDate = vm.ari_register.to_date;
                vm.record.payroll_staff_id = vm.ari_register.payroll_staff_id;
                vm.block_worker = true;
            }
        } catch (error) {
            console.log(error);
        }
    },

    methods: {

        async filterString(event) {
            const input = event.target
            let value = event.target.value

            // Check if value is number
            let isValid = +value == +value

            if (isValid) {
                // preserve input state
                this.prevValue = value
                this.prevSelectionStart = input.selectionStart
            } else {
                // restore previous valid input state.
                // we have to fire one more Input event in  order to reset cursor position.
                var resetEvent = new InputEvent('input')
                input.value = this.prevValue
                input.selectionStart = this.prevSelectionStart
                input.selectionEnd = this.prevSelectionStart
                input.dispatchEvent(resetEvent)
            }
        },

        async reset() {
            const vm = this;
            vm.record = {
                id: '',
                percetage: '',
                startDate: '',
                endDate: '',
                payroll_staff_id: ''
            }
        },

        async createForm(url) {
            const vm = this;

            let redirect = '';

            try {
                vm.loading = true;
                let response = await axios.post(`${window.app_url}/${url}`, vm.record);
                if (response.data.success) {
                    redirect = response.data.redirect_back;
                    location.href = `${window.app_url}/${redirect}`;
                    vm.loading = false;
                }
            } catch (errors) {
                vm.errors = [];

                for (var index in errors.response.data.errors) {
                    if (errors.response.data.errors[index]) {
                        vm.errors.push(errors.response.data.errors[index][0]);
                    }
                }

                vm.loading = false;
            }
        },
    },
}
</script>
