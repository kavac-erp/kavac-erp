<template>
    <section>
        <h6 class="card-title">
            Datos de Contacto <i class="fa fa-plus-circle cursor-pointer" @click="addRow"></i>
        </h6>
        <div class="row contact-row" v-for="(contact, index) in contacts">
            <div class="col-5">
                <div class="form-group is-required">
                    <label for="contact_names">Nombre del contacto</label>
                    <input type="text" placeholder="Nombre del contacto" data-toggle="tooltip" name="contact_names[]"
                           title="Indique el nombre del contacto" v-model="contact.name" class="form-control input-sm">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group is-required">
                    <label for="contact_emails">Correo del contacto</label>
                    <input type="email" placeholder="Electrónico del contacto" data-toggle="tooltip" name="contact_emails[]"
                           title="Indique el correo electrónico del contacto" v-model="contact.email" class="form-control input-sm">
                </div>
            </div>
            <div class="col-1">
                <div class="form-group">
                    <button class="btn btn-sm btn-danger btn-action" type="button" @click="removeRow(index, contacts)"
                            title="Eliminar este dato" data-toggle="tooltip">
                        <i class="fa fa-minus-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                contacts: [],
            }
        },
        watch: {
            contacts: function() {
                localStorage.removeItem('contacts');
                if (this.contacts) {
                    localStorage.contacts = JSON.stringify(this.contacts);
                }
            }
        },
        props: ['initial_data'],
        methods: {
            /**
             * Agrega un campo para introducir un número telefónico
             *
             * @method    addRow
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            addRow: function() {
                this.contacts.push({
                    name: '',
                    email: '',
                });
            },
        },
        mounted() {
            if (this.initial_data) {
                this.contacts = JSON.parse(this.initial_data);
            }
        }
    };
</script>
