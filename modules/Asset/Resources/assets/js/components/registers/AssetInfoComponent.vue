<template>
    <div
      class="modal fade text-left"
      tabindex="-1"
      role="dialog"
      id="AssetInfo"
    >
    <div class="modal-dialog vue-crud" role="document">
        <div class="modal-content">
          <!-- modal-header -->
          <div class="modal-header">
            <button
              type="reset"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">×</span>
            </button>
            <h6>
              <i class="fa fa-list inline-block text-uppercase"></i>
              Información del Bien Registrado
            </h6>
          </div>
          <!-- Final modal-header -->
          <!-- modal-body -->
          <div class="modal-body">
            <ul
              class="nav nav-tabs custom-tabs justify-content-center"
              role="tablist"
            >
              <li class="nav-item">
                <a
                  class="nav-link active"
                  data-toggle="tab"
                  href="#general"
                  id="info_general"
                  role="tab"
                >
                  <i class="ion-android-person"></i> Información General
                </a>
              </li>

              <li class="nav-item">
                <a
                  class="nav-link"
                  data-toggle="tab"
                  href="#purchase"
                  role="tab"
                >
                  <i class="ion-social-dropbox-outline"></i>
                  Detalles
                </a>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="general" role="tabpanel">
                <div class="mb-3">
                  <div class="row mt-3">
                    <div class="col-4">
                      <strong>{{record.asset_institutional_code.label}}:</strong>
                        {{record.asset_institutional_code.name}}
                    </div>
                    <div class="col-4">
                      <strong>{{record.code_sigecof.label}}:</strong>
                        {{record.code_sigecof.name}}
                    </div>
                  </div>
                </div>
                <hr>
                <div class="mb-3" v-for="(infoGroup, index) in record.groups" :key="index">
                  <h6 class="card-title">
                            {{infoGroup.group_name}}:
                  </h6>
                  <div class="row mt-3">
                    <div :class="{'col-4': item.value}" :style="{'display': item.value ? 'block' : 'none'}" v-for="(item, index) in infoGroup.items" :key="index">
                        <strong>{{item.label}}:</strong>
                        {{`${item.value} ${item.unit ? ' ' + item.unit : ''}`}}
                    </div>
                  </div>
                  <hr>
                </div>
                <div class="mb-3" v-if="record.description.name">
                  <h6 class="card-title">
                      {{record.description.label}}:
                  </h6>
                  <div class="row mt-3">
                    <div class="col-12" style="display:block">
                        <strong>{{record.description.label}}:</strong>
                        <span v-html="record.description.name"></span>
                    </div>
                  </div>
                  <hr>
                </div>
              </div>
              <div class="tab-pane" id="purchase" role="tabpanel">
                <div class="row">
                  <div v-for="(item, index) in record.asset_details"  class="col-md-6" :key="index">
                    <div class="form-group">
                      <strong>{{item.label}}</strong>
                      <div class="row" style="margin: 1px 0">
                        <span class="col-md-12">{{ item.value }} </span>
                      </div>
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

</template>

<script>
    export default {
    data() {
        return {
        errors: [],
        record: {
          asset_institutional_code: '',
          code_sigecof: '',
          groups: [],
        },
        };
    },
  };
</script>
