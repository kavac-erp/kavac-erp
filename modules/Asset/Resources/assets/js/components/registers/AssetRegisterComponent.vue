<template>
    <section class="row">
        <div class="col-12">
            <div class="card" id="cardRegisterForm">
                <div class="card-header">
                    <h6 class="card-title">
                        Registro de bienes institucionales
                        <a
                            href="javascript:void(0)"
                            title="haz click para ver la ayuda guiada de este elemento"
                            data-toggle="tooltip"
                            class="btn-help"
                            @click="initUIGuide(helpFile)"
                        >
                            <i class="ion ion-ios-help-outline cursor-pointer"></i>
                        </a>
                    </h6>
                    <div class="card-btns">
                        <div class="d-inline-flex">
                            <a
                                href="#" class="btn btn-sm btn-primary btn-custom"
                                @click="redirect_back(route_list)"
                                title="Ir atrás" data-toggle="tooltip"
                            >
                                <i class="fa fa-reply"></i>
                            </a>
                            <a
                                href="javascript:void(0)"
                                class="card-minimize btn btn-card-action btn-round"
                                title="Minimizar" data-toggle="tooltip"
                            >
                                <i class="now-ui-icons arrows-1_minimal-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger" v-if="errors.length > 0">
                        <div class="container">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                            <button
                                type="button" class="close" data-dismiss="alert"
                                aria-label="Close"
                                @click.prevent="errors = []"
                            >
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                            <ul>
                                <li
                                    v-for="error in errors"
                                    :key="error">{{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <section
                        class="row" id="assetPurchaseSection"
                        v-if="modules.includes('Purchase')"
                    >
                        <div class="col-md-4" id="helpInstitution">
                            <div class="form-group is-required">
                                <label>Organización:</label>
                                <select2
                                    :options="institutions"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    @input="getDepartments(); getAssetInstitutionStorages();"
                                    v-model="record.institution_id">
                                </select2>
                                <input type="hidden" v-model="record.id">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" id="helpAssetPurchaseSupplier">
                                <label>Proveedor</label>
                                <select2
                                    :options="purchase_suppliers"
                                    v-model="record.purchase_supplier_id">
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpDocument">
                            <div class="form-group is-required">
                                <label>No. Documento:</label>
                                <input
                                    type="text" placeholder="Número del documento"
                                    data-toggle="tooltip"
                                    title="Indique el número del documento o N/P"
                                    class="form-control input-sm"
                                    v-model="record.document_num" v-input-mask
                                    data-inputmask-regex="^([0-9]{1,10}|[nN]\/[pP])$"
                                    @input="(event) =>
                                        (record.document_num =
                                            event.target.value.toUpperCase())"
                                >
                            </div>
                        </div>
                    </section>
                    <section class="row" v-else>
                        <div class="col-md-4" id="helpInstitution">
                            <div class="form-group is-required">
                                <label>Organización:</label>
                                <select2
                                    :options="institutions"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    @input="getDepartments(); getAssetInstitutionStorages();"
                                    v-model="record.institution_id">
                                </select2>
                                <input type="hidden" v-model="record.id">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" id="helpAssetPurchaseSupplier">
                                <label>Proveedor</label>
                                <select2
                                    :options="purchase_suppliers"
                                    v-model="record.purchase_supplier_id">
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpDocument">
                            <div class="form-group is-required">
                                <label>No. Documento:</label>
                                <input
                                    type="text"
                                    placeholder="Número del documento"
                                    data-toggle="tooltip"
                                    title="Indique el numero del documento"
                                    class="form-control input-sm"
                                    v-model="record.document_num" v-input-mask
                                    data-inputmask-regex="^([0-9]{1,10}|[nN]\/[pP])$"
                                    @input="(event) =>
                                        (record.document_num =
                                            event.target.value.toUpperCase())"
                                >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label> Documeto </label>
                                <input
                                    id="files" name="files" type="file"
                                    data-toggle="tooltip"
                                    title="Adjuntar archivos"
                                    accept=".doc, .docx, .odt, .pdf, .png, .jpg, .jpeg">
                            </div>
                            <div class="form-group">
                                <label><strong>Archivos Subidos</strong></label>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="archive"></span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="row" id="assetGeneralSection">
                        <div class="col-md-4" id="helpAssetAcquisitionType">
                            <div class="form-group is-required">
                                <label>Forma de adquisición</label>
                                <select2
                                    :options="asset_acquisition_types"
                                    v-model="record.asset_acquisition_type_id"
                                >
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetAcquisitionYear">
                            <div class="form-group  is-required">
                                <label>Fecha de adquisición</label>
                                <input
                                    type="date"
                                    placeholder="Fecha de Adquisición"
                                    data-toggle="tooltip"
                                    title="Indique la fecha de adquisición"
                                    class="form-control input-sm"
                                    v-model="record.acquisition_date"
                                >
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetCurrency">
                            <div class="form-group">
                                <label>Moneda</label>
                                <select2
                                    :options="currencies"
                                    v-model="record.currency_id">
                                </select2>
                            </div>
                        </div>
                    </section>
                    <hr>
                    <section class="row mb-4" id="assetClasificationSection">
                        <div class="col-md-4" id="helpAssetType">
                            <div class="form-group is-required">
                                <label>Tipo de bien:</label>
                                <select2
                                    :options="asset_types"
                                    @input="getAssetCategories()"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    v-model="record.asset_type_id">
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetCategory">
                            <div class="form-group is-required">
                                <label>Categoría general:</label>
                                <select2
                                    :options="asset_categories"
                                    @input="getAssetSubcategories();"
                                    :disabled="(!this.record.asset_type_id != '')"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    v-model="record.asset_category_id">
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetSubCategory">
                            <div class="form-group is-required">
                                <label>Subcategoria:</label>
                                <select2
                                    :options="asset_subcategories"
                                    @input="getAssetSpecificCategories()"
                                    :disabled="(!this.record.asset_category_id != '')"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    v-model="record.asset_subcategory_id"
                                >
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetSpecificCategory">
                            <div class="form-group is-required">
                                <label>Categoría específica:</label>
                                <select2
                                    :options="asset_specific_categories"
                                    :disabled="(!this.record.asset_subcategory_id != '')"
                                    @input="getNewFields('asset_specific_category_id')"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    v-model="record.asset_specific_category_id"
                                >
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetAssetGroupCode">
                            <div class="form-group is-required">
                                <label>Código SIGECOF</label>
                                <input
                                    type="text"
                                    placeholder="Código del bien"
                                    data-toggle="tooltip"
                                    title="Código del bien según catálogo SIGECOF"
                                    disabled="disabled"
                                    class="form-control input-sm"
                                    v-model="record.code_sigecof"
                                    v-input-mask
                                    data-inputmask="'mask': '99999-9999'"
                                />
                            </div>
                        </div>
                        <div class="col-md-4" id="helpAssetAssetGroupCode">
                            <div class="form-group is-required">
                                <label>Número de bienes</label>
                                <input
                                    type="text"
                                    placeholder="Número de bienes"
                                    data-toggle="tooltip"
                                    :disabled="assetid != null"
                                    v-input-mask
                                    data-inputmask="'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                                    title="Número de bienes bajo esta clasificación"
                                    class="form-control input-sm"
                                    v-model="total"
                                >
                            </div>
                        </div>
                    </section>
                    <section
                        id="assetDetailsSection"
                        class="border border-secondary p-3 mb-4"
                    >
                        <h6 class="text-center"> Detalles del Bien </h6>
                        <span
                            class="badge badge-info float-right"
                            style="margin-top: -25px;"
                            v-if="total != ''"
                        >
                            {{`${current} / ${total}`}}
                        </span>
                        <div class="row">
                            <div class="col-md-4" id="helpAssetAssetGroupCode">
                                <div class="form-group is-required">
                                    <label>Código interno</label>
                                    <input
                                        type="text"
                                        placeholder="Código interno del bien"
                                        data-toggle="tooltip"
                                        title="Indique el código interno del bien"
                                        class="form-control input-sm"
                                        v-model="record.asset_details[current - 1]['code']"
                                        @input="(event) =>
                                            (record.asset_details[current - 1]['code'] =
                                                event.target.value.toUpperCase())
                                            "
                                    >
                                </div>
                            </div>
                            <div class="col-md-4" id="helpAssetCondition">
                                <div class="form-group is-required">
                                    <label>Condición física</label>
                                    <select2
                                        :options="asset_conditions"
                                        data-toggle="tooltip"
                                        title="Seleccione un registro de la lista"
                                        v-model="record.asset_details[current - 1]['asset_condition_id']">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-4" id="helpAssetStatus">
                                <div class="form-group is-required">
                                    <label>Estado del uso del bien</label>
                                    <select2
                                        :options="asset_status"
                                        data-toggle="tooltip"
                                        title="Seleccione un registro de la lista"
                                        v-model="record.asset_details[current - 1]['asset_status_id']">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-4" id="helpAssetStatus">
                                <div class="form-group">
                                    <label>Depósito</label>
                                    <select2
                                        :options="asset_institution_storages"
                                        :disabled="(!this.record.institution_id != '' || this.asset_institution_storages.length <= 1)"
                                        data-toggle="tooltip"
                                        title="Seleccione un registro de la lista"
                                        v-model="record.asset_details[current - 1]['asset_institution_storages_id']">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-4" id="helpAssetCondition">
                                <div class="form-group is-required">
                                    <label>Unidad administrativa</label>
                                    <select2
                                        :options="departments"
                                        :disabled="(!this.record.institution_id != '')"
                                        data-toggle="tooltip"
                                        title="Seleccione un registro de la lista"
                                        v-model="record.asset_details[current - 1]['department_id']">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-8" id="helpAssetSpecification">
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        v-model="record.asset_details[current - 1]['description']"
                                        title="Indique las especificaciones del bien (opcional)"
                                        data-toggle="tooltip"
                                        :config="ckeditor.editorConfig"
                                        tag-name="textarea">
                                    </ckeditor>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" v-for="(field, index) in fields" :key="index">
                                <div class="form-group" :class="{ 'is-required': field['required'] }">
                                    <label> {{ field['label'] }} </label>
                                    <input
                                        v-if="field['type'] == 'date'"
                                        type="date"
                                        :placeholder="field['label']"
                                        data-toggle="tooltip"
                                        :title="'Indique el ' + field['label']"
                                        class="form-control input-sm"
                                        :tabindex="index + 18"
                                        v-model="record.asset_details[current - 1][field['name']]"
                                        :required="field['required']"
                                        />

                                    <input
                                        v-else-if="field['type'] == 'text' && field['mask']"
                                        :id="field['name']"
                                        type="text"
                                        :placeholder="field['label']"
                                        data-toggle="tooltip"
                                        :title="'Indique el ' + field['label']"
                                        class="form-control input-sm"
                                        :tabindex="index + 18"
                                        v-model="record.asset_details[current - 1][field['name']]"
                                        :required="field['required']"
                                        v-input-mask
                                        :data-inputmask="field['mask']"
                                        @input="calculateAge(field['name'])"
                                    />

                                    <input
                                        v-else-if="field['type'] == 'text' && !field['mask']"
                                        :id="field['name']"
                                        v-model="record.asset_details[current - 1][field['name']]"
                                        :required="field['required']"
                                        :placeholder="field['label']"
                                        data-toggle="tooltip"
                                        :title="'Indique el ' + field['label'] + ' o N/P'"
                                        class="form-control input-sm"
                                        :tabindex="index + 18"
                                        v-input-mask
                                        data-inputmask-regex="^([0-9]+|[nN]/[pP]|[a-zA-Z0-9\s\-]+)$"
                                        @input="(event) =>
                                            (record.asset_details[current - 1][field['name']] =
                                                event.target.value.toUpperCase())"
                                        type="text"
                                    />

                                    <select2
                                        v-else-if="field['type'] == 'select'"
                                        :id="field['name']"
                                        :options="getOptions(field['options'])"
                                        data-toggle="tooltip" :tabindex="index + 18"
                                        title="Seleccione un registro de la lista"
                                        @input="evaluateInput(field['event'])"
                                        v-model="record.asset_details[current - 1][field['name']]"
                                    >
                                    </select2>

                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="total != ''">
                            <div class="col-md-12">
                                <button
                                    v-if="current == total && assetid == null"
                                    type="button"
                                    @click="AddAssetGroup($event)"
                                    class="btn btn-sm btn-primary btn-custom float-right"
                                    title="Ir al siguiente registro"
                                    data-toggle="tooltip"
                                >
                                    Agregar
                                    <i class="fa fa-angle-plus"></i>
                                </button>
                                <button
                                    v-if="current < total"
                                    type="button"
                                    @click="nextAsset($event)"
                                    class="btn btn-sm btn-primary btn-custom float-right"
                                    title="Ir al siguiente registro"
                                    data-toggle="tooltip"
                                >
                                    Siguiente
                                    <i class="fa fa-angle-right"></i>
                                </button>
                                <button
                                    v-if="current > 1"
                                    type="button"
                                    @click="prevAsset($event)"
                                    class="btn btn-sm btn-primary btn-custom float-right"
                                    title="Ir al registro anterior"
                                    data-toggle="tooltip"
                                >
                                    <i class="fa fa-angle-left"></i>
                                    Anterior
                                </button>
                            </div>
                        </div>
                    </section>
                    <section id="assetListSection">
                        <v-client-table
                            v-if="records.length > 0"
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
                            <div
                                slot="code_sigecof"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span>
                                    {{ (props.row.code_sigecof) ? props.row.code_sigecof : 'N/A' }}
                                </span>
                            </div>
                            <div
                                slot="department"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span>
                                    {{ (props.row.department_id) ? props.row.department_id : 'N/A' }}
                                </span>
                            </div>
                            <div
                                slot="asset_specific_category"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span>
                                    {{ (props.row.asset_specific_category_id) ? props.row.asset_specific_category_id : 'N/A'
                                    }}
                                </span>
                            </div>
                            <div
                                slot="asset_count"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span>
                                    {{ (props.row.asset_details) ? props.row.asset_details.length : 0 }}
                                </span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button
                                        @click="initUpdate(props.row.code_sigecof, props.index, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro"
                                        data-toggle="tooltip"
                                        type="button"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button
                                        @click="removeRow(props.index - 1, records)"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro"
                                        data-toggle="tooltip"
                                        type="button"
                                    >
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </v-client-table>
                    </section>
                </div>

                <div class="card-footer text-right">
                    <div class="row">
                        <div
                            v-show="records.length || assetid != null"
                            class="col-md-3 offset-md-9"
                            id="helpParamButtons"
                        >
                            <button
                                type="button"
                                @click="reset()"
                                class="btn btn-default btn-icon btn-round"
                                data-toggle="tooltip"
                                title="Borrar datos del formulario">
                                <i class="fa fa-eraser"></i>
                            </button>

                            <button
                                type="button"
                                @click="redirect_back(route_list)"
                                class="btn btn-warning btn-icon btn-round"
                                data-toggle="tooltip"
                                title="Cancelar y regresar">
                                <i class="fa fa-ban"></i>
                            </button>

                            <button
                                type="button"
                                @click="createRecord('asset/registers')"
                                class="btn btn-success btn-icon btn-round"
                                data-toggle="tooltip"
                                :title="assetid ? 'Actualizar registro' : 'Guardar registro'"
                            >
                                <i class="fa fa-save"></i>
                            </button>
                        </div>
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
                id: '',
                code_sigecof: '',
                institution_id: '',
                purchase_supplier_id: '',
                document_num: '',
                asset_acquisition_type_id: '',
                acquisition_date: '',
                currency_id: '',
                asset_type_id: '',
                asset_category_id: '',
                asset_subcategory_id: '',
                asset_specific_category_id: '',

                asset_details: [
                    {
                        code: '',
                        asset_condition_id: '',
                        asset_status_id: '',
                        department_id: '',
                        description: '',
                        asset_institution_storages_id: '',
                    }
                ],
            },

            details: {
                code: '',
                asset_condition_id: '',
                asset_status_id: '',
                department_id: '',
                description: '',
            },
            modules: window.modules,

            current: 1,
            total: '',
            required: {},

            editIndex: false,

            fields: [],
            records: [],
            columns: [
                'code_sigecof',
                'department.name',
                'asset_specific_category.name',
                'asset_count',
                'id'
            ],
            table_options: {
                headings: {
                    'code_sigecof': 'Código SIGECOF',
                    'department.name': 'Unidad administrativa',
                    'asset_specific_category.name': 'Categoría específica',
                    'asset_count': 'Número de bienes',
                    'id': 'Acción'
                },
                sortable: [
                    'code_sigecof',
                    'department.name',
                    'asset_specific_category.name',
                    'asset_count'
                ],
                filterable: [
                    'code_sigecof',
                    'department.name',
                    'asset_specific_category.name',
                    'asset_count'
                ],
            },
            errors: [],
            fiscal_year: "",
            institutions: [],
            purchase_suppliers: [],
            departments: [],
            asset_types: [],
            asset_categories: [],
            asset_subcategories: [],
            asset_specific_categories: [],

            asset_acquisition_types: [],
            asset_conditions: [],
            asset_status: [],
            asset_institution_storages: [],
            asset_use_functions: [],

            countries: [],
            options: [],
            estates: [],
            municipalities: [],
            parishes: [],
            currencies: [],
            headquarters: [],
        }
    },
    props: {
        documentNum: Number,
        assetid: Number,
        parameters: {
            type: Object,
            required: true,
            default: null
        },
        institution_id: {
            type: [Number, String],
            required: true,
            default: null
        },
    },
    methods: {
        /**
         * Obtiene los datos de los años fiscales registrados
         *
         * @author Natanael Rojo <rojonatanael99@gmail.com>
         */
        getFiscalYear() {
            const vm = this;
            axios.get(`${window.app_url}/fiscal-years/opened/list`).then(response => {
                vm.fiscal_year = response.data.records[0].id;
            });
        },

        /**
         * Calcula la diferencia entre el año fiscal actual, y el año de construcción
         * @param {string} field_name variable que indica el nombre del campo a partir del cual se hará el cálculo
         *
         * @author Natanael Rojo <rojonatanael99@gmail.com>
         */
        calculateAge(field_name) {
            const vm = this;
            if (field_name === 'construction_year' && vm.record.asset_details[0].construction_year.length >= 4) {
                this.record.asset_details[0].construction_age = parseInt(vm.fiscal_year - vm.record.asset_details[0].construction_year);
            }
        },

        reset(all = true) {
            const vm = this;
            if (all) {
                vm.total = '';
                vm.current = 1;
                vm.editIndex = false;
                vm.record = {
                    id: '',
                    code_sigecof: '',
                    institution_id: '',
                    purchase_supplier_id: '',
                    document_num: '',
                    asset_acquisition_type_id: '',
                    acquisition_date: '',
                    currency_id: vm.currencies[0].id,
                    asset_type_id: '',
                    asset_category_id: '',
                    asset_subcategory_id: '',
                    asset_specific_category_id: '',

                    asset_details: [
                        {
                            code: '',
                            asset_condition_id: '',
                            asset_status_id: '',
                            department_id: '',
                            description: '',
                            asset_institution_storages_id: '',
                        }
                    ],
                };
                vm.details = {
                    code: '',
                    asset_condition_id: '',
                    asset_status_id: '',
                    department_id: '',
                    description: '',
                    asset_institution_storages_id: '',
                };
            } else {
                vm.record.code_sigecof = '';
                vm.record.asset_type_id = '';
                vm.record.asset_category_id = '';
                vm.record.asset_subcategory_id = '';
                vm.record.asset_specific_category_id = '';
                vm.record.asset_details = [
                    {
                        code: '',
                        asset_condition_id: '',
                        asset_status_id: '',
                        department_id: '',
                        description: '',
                    }
                ];
            }

        },
        /**
         * Obtiene los datos de las formas de adquisición de los bienes institucionales registrados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getAssetAcquisitionTypes() {
            const vm = this;
            vm.asset_acquisition_types = [];
            axios.get(`${window.app_url}/asset/get-acquisition-types`).then(response => {
                vm.asset_acquisition_types = response.data;
            });
        },
        /**
         * Obtiene los datos de la condición física de los bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getAssetConditions() {
            const vm = this;
            vm.asset_conditions = [];
            axios.get(`${window.app_url}/asset/get-conditions`).then(response => {
                vm.asset_conditions = response.data;

            });
        },
        /**
         * Obtiene los datos de los estatus de uso de los bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getAssetStatus() {
            const vm = this;
            vm.asset_status = [];
            axios.get(`${window.app_url}/asset/get-status`).then(response => {
                vm.asset_status = response.data.filter((item) => item.id !== 1
                    && item.id !== 6 && item.id !== 11);
            });
        },

        /**
         * Obtiene una lista con los depositos institucionales
         *
         * @author Manuel Zambrano <mazambranos@cenditel.gob.ve>
         */
        getAssetInstitutionStorages() {
            const vm = this;
            vm.asset_institution_storages = [];
            axios.get(`${window.app_url}/asset/get-storages/${vm.record.institution_id}`).then(response => {
                let storages = response.data.filter(item => item.storage !== null).map((item) => {
                    return {
                        id: item.id,
                        text: item.storage.name
                    }
                });
                if(storages.length > 0) {
                    storages.unshift({
                    id: '',
                    text: "Seleccione..."
                });
                }else{
                    storages.unshift({
                        id: '',
                        text: 'No hay Depósitos Activos registrados'
                    });

                }
                vm.asset_institution_storages = storages;
            });
        },
        /**
         * Obtiene los datos de las funciones de uso de los bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getAssetUseFunctions() {
            const vm = this;
            vm.asset_use_functions = [];
            axios.get(`${window.app_url}/asset/get-use-functions`).then(response => {
                vm.asset_use_functions = response.data;
            });
        },
        /**
         * Obtiene los datos de la condición física de los bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getHeadquarters() {
            const vm = this;
            vm.headquarters = [];
            axios.get(`${window.app_url}/get-headquarters`).then(response => {
                vm.headquarters = response.data;
            });
        },
        /**
         * Metodo que carga la información en el formulario de edición
         *
         * @param [Integer] $id Identificador único del registro a editar
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async loadForm(id) {
            const vm = this;
            vm.loading = true;
            await axios.get(`${window.app_url}/asset/registers/info/${id}`).then(response => {
                if (typeof (response.data.records != "undefined")) {
                    let recordEdit = response.data.records;

                    vm.total = 1;
                    let details = recordEdit.asset_details;
                    vm.record = {
                        id: recordEdit.id,
                        code_sigecof: recordEdit.code_sigecof,
                        institution_id: recordEdit.institution_id,
                        purchase_supplier_id: recordEdit.purchase_supplier_id,
                        document_num: recordEdit.document_num,
                        asset_acquisition_type_id: recordEdit.asset_acquisition_type_id,
                        acquisition_date: recordEdit.acquisition_date,
                        currency_id: recordEdit.currency_id,
                        asset_type_id: recordEdit.asset_type_id,
                        asset_category_id: recordEdit.asset_category_id,
                        asset_category: recordEdit.asset_category,
                        asset_subcategory_id: recordEdit.asset_subcategory_id,
                        asset_subcategory: recordEdit.asset_subcategory,
                        asset_specific_category_id: recordEdit.asset_specific_category_id,
                        asset_specific_category: recordEdit.asset_specific_category,
                        asset_details: [details],
                    };
                    setTimeout(() => {
                        vm.record.asset_details[0]['department_id'] = recordEdit.department_id
                        vm.record.asset_details[0]['asset_institution_storages_id'] = recordEdit.asset_institution_storages_id
                    }, 2000);
                }
            });

            if (vm.record.asset_details[0]['country_id']) {
                vm.record.country_id = vm.record.asset_details[0]['country_id'];
                vm.getEstates();
            }
        },

        /**
         * Reescribe el método getEstates para cambiar su comportamiento por defecto
         * Obtiene los Estados del Pais seleccionado
         *
         */
        async getEstates() {
            const vm = this;
            vm.estates = [];
            if (vm.record.asset_details[vm.current - 1]['country_id']) {
                await axios.get(`${window.app_url}/get-estates/${vm.record.asset_details[vm.current - 1]['country_id']}`).then(response => {
                    vm.estates = response.data;
                });
                if ((vm.record.asset_details[vm.current - 1]['estate_id'])) {
                    vm.record.estate_id = vm.record.asset_details[vm.current - 1]['estate_id'];
                    vm.getMunicipalities();
                }
            }
        },
        /**
         * Reescribe el método getMunicipalities para cambiar su comportamiento por defecto
         * Obtiene los Municipios del Estado seleccionado
         *
         */
        async getMunicipalities() {
            const vm = this;
            vm.municipalities = [];

            if (vm.record.asset_details[vm.current - 1]['estate_id']) {
                await axios.get(`${window.app_url}/get-municipalities/${vm.record.asset_details[vm.current - 1]['estate_id']}`).then(response => {
                    vm.municipalities = response.data;
                });
            }
            if ((vm.record.asset_details[vm.current - 1]['municipality_id'])) {
                vm.record.municipality_id = vm.record.asset_details[vm.current - 1]['municipality_id'];
                vm.getParishes();
            }
        },
        /**
         * Reescribe el método getParishes para cambiar su comportamiento por defecto
         * Obtiene las parroquias del municipio seleccionado
         *
         */
        async getParishes() {
            const vm = this;
            vm.parishes = [];

            if (vm.record.asset_details[vm.current - 1]['municipality_id']) {
                await axios.get(`${window.app_url}/get-parishes/${vm.record.asset_details[vm.current - 1]['municipality_id']}`).then(response => {
                    vm.parishes = response.data;
                });
            }
            if ((vm.record.asset_details[vm.current - 1]['parish_id'])) {
                vm.record.parish_id = vm.record.asset_details[vm.current - 1]['parish_id'];
            }
        },
        /**
         * * realizado por Francisco Escala fjescala@gmail.com
         *
         */
        async getPurchaseSuppliers() {
            const vm = this;
            vm.purchase_suppliers = [];
            await axios.get(`${window.app_url}/asset/suppliers-list`).then(response => {
                vm.purchase_suppliers = response.data;
            });
        },
        getDocument(id) {
            var rs;
            axios.get(`${window.app_url}/asset/get-documents/${id}`).then((response) => {
                rs = response.data;

                let fileText = ``;
                documents.records.forEach(function (files) {
                    fileText += `<div class ="row">`;
                    fileText += `<a href='${window.app_url}/asset/get-documents/show/${files.file}'>${files.file}</a>`;
                    fileText += "</div>";
                });
                document.getElementById("archive").innerHTML = fileText;
            }).catch((error) => {
                if (typeof error.response !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            "custom",
                            "Acceso Denegado",
                            "danger",
                            "screen-error",
                            error.response.data.message
                        );
                    } else {
                        vm.logs("resources/js/all.js", 343, error, "initRecords");
                    }
                }
            });
        },
        AddAssetGroup(event) {
            const vm = this
            let fields = {};
            if (!vm.validateAssets(vm.record)) return false;
            for (const [key, value] of Object.entries(vm.record)) {
                fields[key] = value;
            }
            let name;
            for (let category of vm.asset_specific_categories) {
                if (category.id == fields['asset_specific_category_id']) {
                    name = category.text;
                }
            }
            let department_id;
            let department_name;
            for (let department of vm.departments) {
                for (let details of vm.record.asset_details) {
                    if (department.id == details['department_id']) {
                        department_id = department.id;
                        department_name = department.text;
                    }
                }
            }

            fields['asset_category'] = {
                id: fields['asset_category_id'],
                name: 'name'
            };
            fields['asset_subcategory'] = {
                id: fields['asset_subcategory_id'],
                name: 'name'
            };
            fields['asset_specific_category'] = {
                id: fields['asset_specific_category_id'],
                name: name
            };
            fields['department'] = {
                id: department_id,
                name: department_name
            };
            if (vm.editIndex === false) {
                vm.records.push(fields);
            } else if (vm.editIndex >= 0) {
                vm.records.splice(vm.editIndex - 1, 1);
                vm.records.push(fields);
                vm.editIndex = false;
            }

            vm.record.asset_category = null;
            vm.record.asset_subcategory = null;
            vm.record.asset_specific_category = null;
            vm.reset(false);
            vm.current = 1;
            vm.total = '';
            event.preventDefault();
            return true
        },
        /**
         * Método que carga el formulario con los datos a modificar
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} index Identificador del registro a ser modificado
         * @param {object} event   Objeto que gestiona los eventos
         */
        async initUpdate(code, index, event) {
            let vm = this;
            vm.errors = [];
            vm.editIndex = index;

            let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                return rec.code_sigecof === code;
            })[0])) || vm.reset();

            vm.record = recordEdit;
            vm.total = vm.record.asset_details.length;

            event.preventDefault();
        },
        validateAssets(record) {
            const vm = this;
            const assetDetails = record.asset_details[vm.current - 1];
            let errors = [];

            const requiredFields = [
                {
                    field: 'institution_id',
                    message: 'El campo institución es requerido.'
                },
                {
                    field: 'document_num',
                    message: 'El campo Número de documento es requerido.'
                },
                {
                    field: 'asset_acquisition_type_id',
                    message: 'El campo Forma de adquisición es requerido.'
                },
                {
                    field: 'acquisition_date',
                    message: 'El campo Fecha de adquisición es requerido.'
                },
                {
                    field: 'currency_id',
                    message: 'El campo Moneda es requerido.'
                },
                {
                    field: 'asset_type_id',
                    message: 'El campo tipo de Bien requerido.'
                },
                {
                    field: 'asset_category_id',
                    message: 'El campo Categoría general es requerido.'
                },
                {
                    field: 'asset_subcategory_id',
                    message: 'El campo Subcategoría es requerido.'
                },
                {
                    field: 'asset_specific_category_id',
                    message: 'El campo Categoría específica es requerido.'
                },
                {
                    field: 'code_sigecof',
                    message: 'El campo Código sigecof es requerido.'
                },
                {
                    field: 'code',
                    message: 'El campo Código interno es requerido.',
                    object: assetDetails
                },
                {
                    field: 'asset_condition_id',
                    message: 'El campo Condición física es requerido.',
                    object: assetDetails
                },
                {
                    field: 'asset_status_id',
                    message: 'El campo Estado del uso del bien es requerido.',
                    object: assetDetails
                },
                {
                    field: 'department_id',
                    message: 'El campo Unidad administrativa es requerido.',
                    object: assetDetails
                },
            ];

            requiredFields.forEach(({ field, message, object }) => {
                const value = object ? object[field] : record[field];
                if (value === '') {
                    errors.push(message);
                }
            });

            vm.fields.forEach(function (field) {
                if (assetDetails[field['name']] === undefined || assetDetails[field['name']] === '') {
                    if (field['required']) {
                        errors.push('El campo ' + field['label'] + ' es requerido.');
                    }
                }
                if (field['type'] === 'date' && assetDetails[field['name']]) {
                    const dateNameToCheck = field['name'];
                    const dateValueToCheck = assetDetails[dateNameToCheck];
                    const contractDates = ['contract_start_date', 'contract_end_date', 'registration_date'];
                    const acquisitionDate = record['acquisition_date'];

                    if (dateValueToCheck && dateNameToCheck === contractDates[0] && assetDetails[contractDates[1]] &&
                        Date.parse(dateValueToCheck) > Date.parse(assetDetails[contractDates[1]])) {
                        errors.push('La fecha de inicio del contrato no puede ser mayor a la fecha de fin del contrato.');
                    }

                    if (dateValueToCheck && dateNameToCheck === contractDates[1] && assetDetails[contractDates[0]] &&
                        Date.parse(dateValueToCheck) < Date.parse(assetDetails[contractDates[0]])) {
                        errors.push('La fecha de fin del contrato no puede ser menor a la fecha de inicio del contrato.');
                    }

                    if (dateValueToCheck && dateNameToCheck === contractDates[0] && acquisitionDate &&
                        Date.parse(dateValueToCheck) < Date.parse(acquisitionDate)) {
                        errors.push('La fecha de inicio de contrato no puede ser menor a la fecha de adquisición.');
                    }

                    if (dateValueToCheck && dateNameToCheck ===  contractDates[2] && acquisitionDate &&
                        Date.parse(dateValueToCheck) <= Date.parse(acquisitionDate)) {
                        errors.push('La fecha de registro del inmueble no puede ser menor o igual a la fecha de adquisición.');
                    }
                }
            });
            const isValid = errors.length === 0;
            vm.errors = errors;

            if (!isValid) {
                // Realiza el desplazamiento al inicio de la página
                window.scrollTo(0, 0);
            }

            return isValid;
        },
        /**
         * Crea un objeto con los valores predeterminados a cargar en el fornulario de detalles del bien
         *
         * @return {Object} El objeto con los valores por defecto a
         *  cargar en el formulario de detalles del bien
         */
        createDefaultValues() {
            const defaultValueField = {
                code: '',
                asset_condition_id: '',
                asset_status_id: '10',
                department_id: '12',
                description: '',
            }
            return defaultValueField;
        },
        nextAsset(event) {
            const vm = this;
            if (!vm.validateAssets(vm.record)) return false;
            if (vm.record.asset_details.length < vm.total) {
                vm.record.asset_details.push(vm.createDefaultValues());
            }
            vm.current++;
            event.preventDefault();
        },
        prevAsset(event) {
            const vm = this;
            vm.current--;
            event.preventDefault();
        },
        async getNewFields(field) {
            const vm = this;
            if (vm.isLoading) {
                // Si la solicitud ya está en curso, no hacer nada
                return;
            }
            vm.loading = true;
            vm.isLoading = true; // Establecer la bandera isLoaad en true

            try {
                vm.fields = [];
                let filters = {
                    asset_type_id: vm.record.asset_type_id,
                    asset_category_id: vm.record.asset_category_id,
                    asset_subcategory_id: vm.record.asset_subcategory_id,
                    asset_specific_category_id: vm.record.asset_specific_category_id,
                };

                const detail = vm.record.asset_details;

                if (vm.record[field] != '') {
                    if (vm.parameters[field] == true) {
                        await axios.get(
                            `${window.app_url}/asset/registers/get-fields`,
                             { params: filters }
                        ).then(response => {
                            if (typeof (response.data.options != 'undefined')) {
                                for (const [key, value] of Object.entries(response.data.options)) {
                                    if (!vm.options[key]) {
                                        vm.options[key] = value;
                                    }
                                }
                                vm.record.asset_details = detail;
                            }
                            vm.fields = response.data.records;
                        });
                    }
                }
            } catch (error) {
                console.log(error);
            } finally {
                vm.isLoading = false;
                vm.loading = false;
            }
        },
        evaluateInput(func, param = null) {
            const vm = this;
            eval(func);
            return;

        },
        getOptions(text) {
            const vm = this;

            if (typeof (vm.options[text]) !== 'undefined') return vm.options[text];
            else if (typeof (vm[text]) !== 'undefined') return vm[text];
            return [];
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
        async createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            if (vm.record.id) {
                vm.AddAssetGroup(event) ? vm.updateRecord(url) : false;
            }
            else {
                vm.loading = true;
                await axios.post(url, vm.records).then(response => {
                    if (typeof (response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.errors = [];
                        if (reset) {
                            vm.reset();
                        }

                        vm.showMessage('store');
                    }
                }).catch(error => {
                    vm.errors = [];

                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom',
                                'Acceso Denegado',
                                'danger', 'screen-error',
                                error.response.data.message
                            );
                        }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });

                vm.loading = false;
            }
        },
        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta de la accion que modificará los datos
         */
        async updateRecord(url) {
            const vm = this;
            vm.loading = true;
            url = vm.setUrl(url);

            for (var index in vm.record) {
                vm.records[index] = vm.record[index];
            }
            await axios.patch(`${url}${(url.endsWith('/')) ? '' : '/'}${vm.record.id}`, vm.records).then(response => {
                if (typeof (response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
                else {
                    vm.readRecords(url);
                    vm.reset();
                    vm.showMessage('update');
                }
            }).catch(error => {
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
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
            });
            vm.loading = false;
        },
    },
    computed: {
        code: {
            get() {
                const vm = this;
                let fieldCode = '';
                let fieldCat = null;
                let fieldSub = null;
                let fieldSpc = null;
                if (vm.record.asset_category_id != '') {
                    fieldCat = vm.asset_categories.filter(r => {
                        return r.id == vm.record.asset_category_id;
                    })[0];
                }
                fieldCode += (fieldCat) ? fieldCat['code'] : '';
                if (vm.record.asset_subcategory_id != '') {
                    fieldSub = vm.asset_subcategories.filter(r => {
                        return r.id == vm.record.asset_subcategory_id;
                    })[0];
                }
                fieldCode += (fieldSub) ? fieldSub['code'] : '';
                if (vm.record.asset_specific_category_id != '') {
                    fieldSpc = vm.asset_specific_categories.filter(r => {
                        return r.id == vm.record.asset_specific_category_id;
                    })[0];
                }
                fieldCode += (fieldSpc) ? fieldSpc['code'] : '';
                this.record.code_sigecof = fieldCode;
                return fieldCode;
            }
        }
    },

    watch: {
        'record.acquisition_date'(newDate) {
            const today = new Date().toISOString().split('T')[0]; // Obtiene la fecha actual en formato YYYY-MM-DD
            (newDate > today) && (this.record.acquisition_date = today);
        }
    },

    created() {
        const vm = this;
        vm.getInstitutions();
        vm.getHeadquarters();
        vm.getPurchaseSuppliers();
        vm.getAssetAcquisitionTypes();
        vm.getCurrencies();
        vm.getAssetTypes();
        vm.getAssetConditions();
        vm.getAssetStatus();
        vm.record.asset_details = [vm.createDefaultValues()];
        vm.getFiscalYear();
    },

    mounted() {
        const vm = this;
        if (vm.assetid) {
            vm.loadForm(vm.assetid);
        } else {
            // Selecciona la organización por defecto
            setTimeout(() => vm.record.institution_id = vm.institution_id, 2000);
            //selecciona los detalles del bien por defecto
            setTimeout(() => vm.record.asset_details = [vm.createDefaultValues()], 5000);
        }

        if ($('select').data('select2')) {
            $('select').select2('destroy');
        }
    },
};
</script>