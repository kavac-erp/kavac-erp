<template>
  <div class="text-center">
    <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="" title="Registros de Tipos de Producto" data-toggle="tooltip" @click="addRecord('add_frecuency', 'sale/frecuencies', $event)">
      <i class="icofont icofont-ui-calendar ico-3x"></i>
      <span>Periodos de Tiempo</span>
    </a>
    <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_frecuency">
      <div class="modal-dialog vue-crud" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
            <h6><i class="icofont icofont-ui-calendar ico-3x"></i> Periodos de Tiempo</h6>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="alert-icon">
                    <i class="now-ui-icons objects_support-17"></i>
                </div>
                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                    <span aria-hidden="true">
                        <i class="now-ui-icons ui-1_simple-remove"></i>
                    </span>
                </button>
                <ul>
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group is-required">
                  <label for="name">Nombre del periodo:</label>
                  <input type="text" id="name" placeholder="Nombre" class="form-control input-sm" v-model="record.name" data-toggle="tooltip" title="Indique el nombre (requerido)">
                  <input type="hidden" name="id" id="id" v-model="record.id">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group is-required">
                  <label for="days">Días equivalentes del período:</label>
                  <input type="text" id="days" placeholder="Días equivalentes del período" class="form-control input-sm" v-model="record.days" data-toggle="tooltip" title="Indique los días equivalentes del período (requerido)">
                  <input type="hidden" name="id" id="id" v-model="record.id">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="form-group">
              <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
									@click="clearFilters" data-dismiss="modal">
								Cerrar
							</button>
							<button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
									@click="reset()">
								Cancelar
							</button>
							<button type="button" @click="createRecord('sale/frecuencies')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
            </div>
          </div>
          <div class="modal-body modal-table">
            <v-client-table :columns="columns" :data="records" :options="table_options">
              <div slot="id" slot-scope="props" class="text-center">
                <button @click="initUpdate(props.row.id, $event)" class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar periodo" data-toggle="tooltip" type="button">
                  <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, 'sale/frecuencies')" class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar periodo" data-toggle="tooltip" type="button">
                  <i class="fa fa-trash-o"></i>
                </button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
/**
 * Define la interfaz para la gestión de periodos de Tiempo
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 */
export default {
  data() {
    return {
      record: {
        id: '',
        name: '',
        days: ''
      },
      errors: [],
      records: [],
      columns: ['name', 'days', 'id'],
    }
  },
  methods: {
    reset() {
      this.record = {
        id: '',
        name: '',
        days: ''
      };
    },
  },
  created() {
    this.table_options.headings = {
      'name': 'Nombre del periodo',
      'days': 'Días equivalentes del periodo',
      'id': 'Acción'
    };
    this.table_options.sortable = ['name', 'days'];
    this.table_options.filterable = ['name', 'days'];
    this.table_options.columnsClasses = {
      'name': 'col-md-5',
      'days': 'col-md-5',
      'id': 'col-md-2'
    };
  },
};
</script>
