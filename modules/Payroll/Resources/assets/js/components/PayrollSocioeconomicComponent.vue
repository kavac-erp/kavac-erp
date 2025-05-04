<template>
    <section id="PayrollSocioeconomicForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                        Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li
                            v-for="(error, index) in errors"
                            :key="index"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpSocioeconomicStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_socioeconomic"
                            v-model="record.payroll_staff_id"
                            :disabled="isEditMode"
                        ></select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>

                <div
                    class="col-md-4"
                    id="helpSocioeconomicMaritalStatus"
                    v-if="marital_status.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Estado Civil:</label>
                        <select2 :options="marital_status"
                            v-model="record.marital_status_id"></select2>
                    </div>
                </div>
            </div>
            <hr>
            <h6 class="card-title" id="helpSocioeconomicChildren">
                Carga Familiar
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addPayrollChildren"
                ></i>
            </h6>
            <div
                class="row"
                v-for="(payroll_children, index) in record.payroll_childrens"
                :key="index"
            >
                <div class="col-4">
                    <div
                        class="form-group is-required"
                        id="helpChildSchoolingLevelname"
                        v-if="payroll_relationships.length > 0"
                    >
                        <label>Parentesco</label>
                        <select2
                            :options="payroll_relationships"
                            v-model="payroll_children.payroll_relationships_id"
                            @input="
                                setRelationships(
                                    index,
                                    payroll_children.payroll_relationships_id
                                )
                            "
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label>Nombres:</label>
                        <input
                            type="text"
                            placeholder="Nombres de familiar"
                            data-toggle="tooltip"
                            title="Indique nombres de familiar"
                            v-model="payroll_children.first_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label>Apellidos:</label>
                        <input
                            type="text"
                            placeholder="Apellidos de familiar"
                            data-toggle="tooltip"
                            title="Indique apellidos de familiar"
                            v-model="payroll_children.last_name"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label>Fecha de Nacimiento:</label>
                        <input
                            type="date"
                            placeholder="Fecha de Nacimiento"
                            data-toggle="tooltip"
                            title="Indique la fecha de nacimiento"
                            v-model="payroll_children.birthdate"
                            @change="setAge(index)"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <label>Edad:</label>
                        <input
                            type="text"
                            data-toggle="tooltip"
                            readonly tabindex="-1"
                            title="Indique la Edad"
                            id="age"
                            name="age"
                            min="1"
                            max="3"
                            placeholder="0"
                            v-model="payroll_children.age"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Cédula de identidad :</label>
                        <input
                            type="text"
                            placeholder="Cédula de Identidad"
                            data-toggle="tooltip"
                            title="Indique la cédula de indentidad "
                            v-model="payroll_children.id_number"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required" id="helpChildSchoolingLevelname" v-if="genders.length > 0">
                        <div class="form-group is-required">
                        <label>Género</label>
                        <select2
                            :options="genders"
                            v-model="payroll_children.payroll_gender_id"
                        ></select2>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Direccion:</label>
                        <input
                            type="text"
                            placeholder="Direccion"
                            data-toggle="tooltip"
                            title="Indique los Direccion"
                            v-model="payroll_children.address"
                            class="form-control input-sm"
                        >
                    </div>
                </div>
                <div
                    class="col-4"
                    v-if="payroll_children.payroll_relationship.name == 'Hijo(a)'"
                >
                </div>
                <div
                    class="col-3"
                    v-if="payroll_children.payroll_relationship.name == 'Hijo(a)'"
                >
                    <div class="row col-md-6">
                        <div class="form-group">
                            <label>¿Es estudiante?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el hijo es estudiante o no"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="`mySwicth${index}`"
                                    v-model="payroll_children.is_student"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`mySwicth${index}`"
                                ></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-14" v-if="payroll_children.is_student">
                        <div
                            class="form-group is-required"
                            id="helpChildSchoolingLevelname"
                            v-if="payroll_schooling_levels.length > 0"
                        >
                            <label>¿Nivel de escolaridad?</label>
                            <select2
                                :options="payroll_schooling_levels"
                                v-model="
                                    payroll_children.payroll_schooling_level_id
                                "
                            >
                            </select2>
                        </div>
                        <div class="form-group is-required">
                            <label>Centro de estudio</label>
                            <input
                                type="text"
                                placeholder="Nombre del centro de estudio"
                                data-toggle="tooltip"
                                title="Indique el nombre del centro de estudio"
                                v-model="payroll_children.study_center"
                                class="form-control input-sm"
                            >
                        </div>
                    </div>
                    <br>
                </div>
                <div
                    class="col-3"
                    v-if="
                        payroll_children.payroll_relationship.name
                        == 'Hijo(a)' && payroll_children.is_student
                    "
                >
                    <div class="row col-md-6">
                        <div class="form-group">
                            <label>¿Posee una Beca?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el hijo Posee una beca o no"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="`has_scholarships${index}`"
                                    v-model="payroll_children.has_scholarships"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`has_scholarships${index}`"
                                ></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-14" v-if="payroll_children.has_scholarships">
                        <div
                            class="form-group is-required"
                            id="helphas_scholarships"
                            v-if="payroll_scholarship_types.length > 0"
                        >
                            <label>¿Tipo de beca?</label>
                            <select2
                                :options="payroll_scholarship_types"
                                v-model="
                                    payroll_children.payroll_scholarship_types_id
                                "
                            >
                            </select2>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="col-3">
                    <div class="row col-md-10">
                        <div class="form-group">
                            <label>¿Posee una Discapacidad?</label>
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee una discapacidad o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input sel_has_disability"
                                    :id="`has_disability${index}`"
                                    :name="`has_disability${index}`"
                                    v-model="payroll_children.has_disability"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    :for="`has_disability${index}`"
                                ></label>
                            </div>
                        </div>
                    </div>
                    <div
                        class="col-md-14"
                        id="helpChildDisabilityName"
                        v-if="
                            payroll_children.has_disability
                            && payroll_disabilities.length > 0
                        "
                    >
                        <div class="form-group is-required">
                            <label>Discapacidad</label>
                            <select2
                                :options="payroll_disabilities"
                                v-model="payroll_children.payroll_disability_id"
                            >
                            </select2>
                        </div>
                    </div>
                </div>
                <div class="row col-1">
                    <div class="form-group">
                        <br>
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(index, record.payroll_childrens)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord('payroll/socioeconomics')"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        props: {
            payroll_socioeconomic_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                },
                errors: [],
                payroll_socioeconomic: [],
                marital_status: [],
                payroll_relationships: [],
                payroll_scholarship_types: [],
                payroll_schooling_levels: [],
                genders: [],
                payroll_childrens: [],
                payroll_disabilities: [],
                isEditMode: false,
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                };
            },

            /**
             * Método que carga los datos guardados.
             */
            async getSocioeconomic() {
                await axios.get(`${window.app_url}/payroll/socioeconomics/${this.payroll_socioeconomic_id}`).then(response => {
                    this.record = response.data.record;
                    // Bloquear el select del trabajador cuando esté en modo edit.
                    this.isEditMode = true;
                });
            },

            setRelationships(index,id) {
                var newArray = this.payroll_relationships.filter(function (el) {
                    if(el.id == id) {
                        return el ;
                    }
                });
                this.record.payroll_childrens[index].payroll_relationship = {
                        name: newArray[0].text,
                        id: newArray[0].id,
                        };
            },

            setAge(index) {
                const vm = this;
                let age = moment().diff(
                    vm.record.payroll_childrens[index].birthdate,
                    "years",
                    false
                );
                vm.record.payroll_childrens[index].age = age > -1 ? age : "";
            },

            /**
             * Agrega una nueva columna para el registro de hijos del trabajador
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            addPayrollChildren() {
                this.record.payroll_childrens.push({
                    first_name: '',
                    last_name: '',
                    id_number: '',
                    birthdate: '',
                    is_student: false,
                    has_scholarships: false,
                    has_disability: false,
                    payroll_schooling_level_id: '',
                    payroll_relationships_id: '',
                    payroll_scholarship_types_id: '',
                    payroll_disability_id:'',
                    payroll_relationship: {
                    name: '',
                    },
                    age:'0',
                    study_center: ''
                });
            },
        },

        async created() {
            this.loading = true;
            if (this.payroll_socioeconomic_id) {
                await this.getPayrollSocioeconomic(this.payroll_socioeconomic_id);
            } else {
                await this.getPayrollSocioeconomic('filter');
                this.record.payroll_childrens = [];
            }
            await this.getMaritalStatus();
            await this.getGenders();
            await this.getPayrollRelationships();
            await this.getPayrollScholarshipTypes();
            await this.getPayrollSchoolingLevels();
            await this.getPayrollDisabilities();
            this.loading = false;
        },

        async mounted() {
            this.loading = true;
            if (this.payroll_socioeconomic_id) {
                await this.getSocioeconomic();
            }
            this.loading = false;
        },
    };
</script>
