<template>
  <div>
    <a class="btn btn-info btn-xs btn-icon btn-action" href="#" title="Ver información del registro" data-toggle="tooltip"
      @click="addRecord('view_asignation' + index, route_list, $event)">
      <i class="fa fa-eye"></i>
    </a>
    <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_asignation' + index">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="reset()">
              <span aria-hidden="true">×</span>
            </button>
            <h6>
              <i class="icofont icofont-read-book ico-2x"></i>
              Información de la Asignación Registrada
            </h6>
          </div>

          <div class="modal-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
              <ul>
                <li v-for="error in errors" :key="error">
                  {{ error }}
                </li>
              </ul>
            </div>
            <ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" :href="'#general' + index" :id="'info_general' + index"
                  role="tab">
                  <i class="ion-android-person"></i>
                  Información general
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" :href="'#assets' + index" role="tab">
                  <i class="ion-arrow-swap"></i> Bienes
                </a>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" :id="'general' + index" role="tabpanel">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <strong>Fecha de registro</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'date_init' + index"> </span>
                      </div>
                      <input type="hidden" id="id" />
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <strong>Trabajador responsable de los bienes asignados</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'staff' + index"> </span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <strong>Lugar de ubicación de los bienes asignados</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'location' + index"> </span>
                      </div>
                      <strong>Edificación</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'building' + index"> </span>
                      </div>
                      <strong>Nivel</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'floor' + index"> </span>
                      </div>
                      <strong>Sección</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" :id="'section' + index"> </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" :id="'assets' + index" role="tabpanel">
                <div class="row">
                  <div class="col-md-12">
                    <hr />
                    <v-client-table :columns="columns" :data="records" :options="table_options">
                      <div slot="asset_details.code" slot-scope="props" class="text-center">
                        <span>
                          {{
                            props.row.code
                            ? props.row.code.name
                            : ""
                          }}
                        </span>
                      </div>
                      <div slot="asset_specific_category.name" slot-scope="props" class="text-center">
                        <span>
                          {{
                            props.row.category
                            ? props.row.category.name
                            : ""
                          }}
                        </span>
                      </div>
                      <div slot="asset_details" slot-scope="props">
                        <span>
                          <div v-for="(att, index) in props.row.details" :key="index">
                            <b>{{ att.label + ":" }}</b> {{ att.value }}
                          </div>
                        </span>
                      </div>
                    </v-client-table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal"
              @click="reset()">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      records: [],
      assets: [],
      errors: [],
      flag: false,
      columns: [
        "asset_details.code",
        "asset_specific_category.name",
        "asset_details"
      ],
    };
  },
  props: {
    index: Number
  },
  created() {
    this.table_options.headings = {
      "asset_details.code": "Codigo",
      "asset_specific_category.name": "Categoría Específica",
      "asset_details": "Detalles"
    };
    this.table_options.sortable = [
      "asset_details.code",
      "asset_specific_category.name"
    ];
    this.table_options.filterable = [
      "asset_details.code",
      "asset_specific_category.name",
      "asset_details"
    ];
    this.table_options.orderBy = { column: "asset.id" };
  },
  methods: {
    /**
     * Método que borra todos los datos del formulario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
     */
    reset() {
      const vm = this;
      vm.records = [];
      vm.assets = [];
      vm.flag = false;
    },

    /**
     * Inicializa los registros base del formulario
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     */
    initRecords(url, modal_id) {
      this.errors = [];

      const vm = this;
      var fields = {};

      document.getElementById("info_general" + vm.index).click();
      url = vm.setUrl(url);

      axios
        .get(url)
        .then((response) => {
          vm.records = [];
          if (typeof response.data.records !== "undefined") {
            fields = response.data.records;

            for (const field of fields) {
              let arrFields = {
                'details': field.asset_details,
                'code': field.asset_institutional_code,
                'category': field.asset_specific_category
              };

              vm.records.push(arrFields);
            }

            $(".modal-body #id").val(fields.id);
            document.getElementById("date_init" + vm.index).innerText = vm.format_date(fields[0].asset_asignation_date);
            document.getElementById("staff" + vm.index).innerText = fields[0].asset_asignation_name;
            document.getElementById("location" + vm.index).innerText = fields[0].asset_asignation_location;
            document.getElementById("building" + vm.index).innerText = fields[0].asset_asignation_building;
            document.getElementById("floor" + vm.index).innerText = fields[0].asset_asignation_floor;
            document.getElementById("section" + vm.index).innerText = fields[0].asset_asignation_section;
          }
          if ($("#" + modal_id).length) {
            $("#" + modal_id).modal("show");
          }
        })
        .catch((error) => {
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
  },
};
</script>
