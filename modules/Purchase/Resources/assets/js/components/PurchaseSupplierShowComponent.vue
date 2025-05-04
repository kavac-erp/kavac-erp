<template>
    <section>
        <button @click="addRecord('show_purchase_supplier_'+id, route_show, $event)" class="btn btn-info btn-xs btn-icon btn-action" title="Visualizar registro" data-toggle="tooltip" v-has-tooltip>
            <i class="fa fa-eye"></i>
        </button>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'show_purchase_supplier_'+id">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            Información de proveedor
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body" v-if="records">
                        <h6>Datos Básicos</h6>
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Número de Expediente:</strong>
                                {{ records.file_number || 'NO REGISTRADO' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-3 ">
                                <strong>Tipo de Persona:</strong>
                                {{
                                    (records.person_type === 'N')
                                    ? "Natural" :
                                    (records.person_type === 'J')
                                    ? "Jurídica" :
                                    (records.person_type === 'G')
                                    ? "Gubernamental" :
                                    (records.person_type === 'E')
                                    ? "Extranjero" :
                                    'No definido'
                                }}
                            </div>
                            <div class="col-3 ">
                                <strong>Tipo de Empresa:</strong> {{ records.company_type==='PU' ? 'Pública' : 'Privada' }}
                            </div>
                            <div class="col-3 ">
                                <strong>Activo:</strong> {{ records.active ? 'Sí' : 'No' }}
                            </div>
                            <div class="col-3 ">
                                <strong>R.I.F.:</strong> {{ records.rif  }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-3 ">
                                <strong>Nombre o Razón Social:</strong> {{ records.name }}
                            </div>
                            <div class="col-3 ">
                                <strong>Objeto Social de la organización:</strong> {{ records.social_purpose }}
                            </div>
                            <div class="col-3 ">
                                <strong>Denominación Comercial:</strong> {{ records.purchase_supplier_type.name }}
                            </div>
                            <div class="col-3 ">
                                <strong>Objeto Principal:</strong>
                                <span v-for="object in records.purchase_supplier_objects" :key="object.id">
                                    {{ object.name }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-3 ">
                                <strong>Rama:</strong>
                                <span v-for="object in records.purchase_supplier_branch" :key="object.id">
                                    {{ object.name }}
                                </span>
                                <!--{{ records.purchase_supplier_branch.name }}-->
                            </div>
                            <div class="col-3 ">
                                <strong>Especialidad:</strong> <span v-for="object in records.purchase_supplier_specialty" :key="object.id">
                                    {{ object.name }}
                                </span>
                                <!--{{ records.purchase_supplier_specialty.name }}-->
                            </div>
                            <div class="col-3 ">
                                <strong>Sitio Web:</strong> {{ records.website }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-3 ">
                                <strong>País:</strong> {{ records.city.estate.country.name }}
                            </div>
                            <div class="col-3 ">
                                <strong>Estado:</strong> {{ records.city.estate.name }}
                            </div>
                            <div class="col-3 ">
                                <strong>Ciudad:</strong> {{ records.city.name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-3 ">
                                <strong>Dirección Fiscal:</strong> {{ getData(records.direction) }}
                            </div>
                            <div class="col-3 ">
                                <strong>Información de contactos:</strong>
                                <br>
                                <ul>
                                    <li v-for="contact in records.contacts" :key="'contact_'+contact.id">
                                        {{ contact.name }} - {{ contact.email }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-3"><strong>Números de contacto:</strong>
                                <br>
                                <ul>
                                    <li v-for="phone in records.phones" :key="'phone_'+phone.id">
                                        {{ phone.type==='M'?'Móvil':phone.type==='T'?'Teléfono':'Fax' }}:
                                        +{{phone.extension}}-{{ phone.area_code }}-{{phone.number}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <h6>Datos del RNC</h6>
                        <div class="row">
                            <div class="col-3">
                                <strong>Inscrito y no habilitado:</strong> <br> {{ records.rnc_status==='INH'?'Si':'NO' }}
                            </div>
                            <div class="col-3">
                                <strong>Inscrito y habilitado para contratar:</strong> <br> {{ records.rnc_status==='ISH'?'Si':'NO' }}
                            </div>
                            <div class="col-3">
                                <strong>Número de Certificado:</strong> <br> {{ records.rnc_certificate_number }}
                            </div>
                        </div>
                        <hr>
                        <h6>Documentos</h6>
                        <div class="row">
                            <div class="col-12">
                                <ul class="feature-list list-group list-group-flush">
                                    <li class="list-group-item" v-for="doc in records.documents" :key="'doc_'+doc.id">
                                        <div class="feature-list-indicator bg-info"></div>
                                        <div class="feature-list-content p-0">
                                            <div class="feature-list-content-wrapper">
                                                <a class="btn btn-simple btn-primary btn-events"
                                                    title="Presione para descargar el documento"
                                                    data-toggle="tooltip"
                                                    target="_blank"
                                                    :href="'/purchase/document/download/'+doc.file"
                                                    :download="records.rif + ' - ' + doc.purchase_document_required_document.required_document.name+'.pdf'"
                                                    >
                                                    <i class="fa fa-cloud-download fa-2x"></i>
                                                </a>
                                                <div class="feature-list-content-left ml-4">
                                                    <div class="feature-list-subheading">
                                                        <i class="font-weight-bold">
                                                            {{ doc.purchase_document_required_document.required_document.name }}
                                                        </i>
                                                        <p v-html="doc.purchase_document_required_document.required_document.description"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <br>
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
                </div>
            </div>
        </div>
    </section>
</template>
<script>
export default {
    props: ['id'],
    data() {
        return {
            records: null,
            files: {},
        }
    },
    created() {
        //
    },
    mounted() {
    },
    methods: {

        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {
            //
        },

        getData(str) {
            return (str.replace('<p>','')).replace('</p>','');
        }
    },
    computed: {
        //
    }
};
</script>
