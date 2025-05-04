@extends('digitalsignature::layouts.master')

@section('maproute-icon')
  <div class="pt-2"> {{-- El ícono queda muy arriba --}}
    <i class="icofont icofont-ui-password"></i>
  </div>
@stop

@section('maproute-icon-mini')
  <div class="pt-1"> {{-- El ícono queda muy arriba --}}
    <i class="icofont icofont-ui-password"></i>
  </div>
@stop

@section('maproute-actual')
  {{ __('Firma electrónica') }}
@stop

@section('maproute-title')
  {{ __('Firma electrónica') }}
@stop

@section('content')

  @if (count($errors) > 0)
    <div class="alert alert-danger">
      <p>Corrige los siguientes errores:</p>
      <ul>
        @foreach ($errors->all() as $message)
          <li>{{ $message }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Gestión del certificado --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title"> Gestión de Certificado
          <a href data-toggle="tooltip" data-original-title="Ayuda para la gestión del certificado">
            <i class="ion ion-ios-help-outline cursor-pointer"></i>
          </a>
        </h6>
        <div class="card-btns">
          <a href="#" title="" data-toggle="tooltip" class="card-minimize btn btn-card-action btn-round" data-original-title="Minimizar">
            <i class="now-ui-icons arrows-1_minimal-up"></i>
          </a>
        </div>
      </div>
      <div class="card-body">
        <!--
        @php
          $user = Auth::User();
        @endphp
        -->

        @if($cert == 'true')
          <h6 class="mb-3">Datos del certificado</h6>
          <table class="table table-hover table-striped dt-responsive nowrap datatable">
            <thead>
              <tr class="text-center">
                <th>{{ __('Identidad') }}</th>
                <th>{{ __('Validado por') }}</th>
                <th>{{ __('Caduca') }}</th>
                <th>{{ __('Acción') }}</th>
              </tr>
            </thead>
            <tbody>
                <tr>
                  <td>{{ $Identidad }}</td>
                  <td class="text-center">{{ $Verificado }}</td>
                  <td class="text-center">{{ $Caduca }}</td>
                  <td class="text-center">
                    <a class="btn btn-info btn-xs btn-icon btn-action" href="#"
                      onclick="certificateDetails()"
                      data-target="#modalDetailCert"
                      data-toggle="modal" title="Detalles del certificado">
                      <i class="fa fa-eye"></i>
                    </a>
                    @if($permissionUpdate)
                      <a class="btn btn-warning btn-xs btn-icon btn-action"
                        href="#" data-target="#modalUpdateCert"
                        data-toggle="modal" title="Actualizar certificado">
                        <i class="fa fa-edit"></i>
                      </a>
                    @else
                      <a class="btn btn-warning btn-xs btn-icon btn-action"
                        href="#" title="Actualizar certificado"
                        onclick="showMessagePerms();">
                        <i class="fa fa-edit"></i>
                      </a>
                    @endif
                    @if($permissionDestroy)
                      <a class="btn btn-danger btn-xs btn-icon btn-action"
                        href="#" data-target="#modalConfirmDelete"
                        data-toggle="modal" title="Eliminar certificado">
                        <i class="fa fa-trash-o"></i>
                      </a>
                    @else
                      <a class="btn btn-danger btn-xs btn-icon btn-action"
                        href="#" title="Eliminar certificado"
                        onclick="showMessagePerms();">
                        <i class="fa fa-trash-o"></i>
                      </a>
                    @endif
                  </td>
                </tr>
            </tbody>
          </table>
        @else
          <h4>{{ __('No ha cargado un certificado') }}</h4>
          <p class="text-info">Para firmar y verificar archivos debe cargar un certificado p12.</p>
        @endif

        @if($cert == 'true')
        <div class="col-md-12">
          <!-- Update certificate button modal  -->
          <div class="modal fade" id="modalUpdateCert" tabindex="-1" aria-labelledby="modalUpdateCertLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg vue-crud">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 style="color:#636e7b">
                    <i class="icofont icofont-certificate-alt-1 ico-2x"></i>
                      Actualizar certificado
                  </h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form method="POST" enctype="multipart/form-data" accept-charset="UTF-8" action="{{ route('updateCertificate') }}">
                  <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <p>
                      <label for="pkcs12">Actualizar certificado del firmante</label>
                      <input id="pkcs12" type="file" class="form-control" name="pkcs12" accept=".p12" required />
                    </p>
                    <p>
                      <label for="phasepass">Contraseña del certificado</label>
                      <input id="phasepass" class="form-control" type="password" name="password" placeholder="XXXXXX"  autocomplete="off" required />
                    </p>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-default btn-sm btn-round btn-modal-close" type="button" @click="reset()" data-dismiss="modal">
                      Cerrar
                    </button>
                    <button class="btn btn-primary btn-sm btn-round btn-modal-close">
                      <i class="fa fa-upload"></i>
                      Subir certificado
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- End of the update certificate button modal -->

          <!-- Modal delete certificate -->
          <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-sm vue-crud">
              <div class="modal-content">
                <div class="modal-header">
                  <h6><i class="fa fa-times inline-block"></i> Eliminar certificado</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p class="pb-3"> ¿Está seguro de eliminar el certificado? </p>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-default btn-sm btn-round btn-modal-close" type="button" @click="reset()" data-dismiss="modal">
                    Cerrar
                  </button>
                  <a class="btn btn-primary btn-sm btn-round" href="{{ route('deleteCertificate') }}">
                    <i class="fa fa-check"></i> Confirmar
                  </a>
                </div>
              </div>
            </div>
          </div>
          <!-- End of delete certificate button modal -->

          <!-- Certificate details button modal  -->
          <div class="modal fade text-left" role="dialog" id="modalDetailCert" tabindex="-1" aria-labelledby="modalDetailCertLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm vue-crud">
              <div class="modal-content">
                <div class="modal-header">
                  <h6><i class="fa fa-eye"></i> Detalles del certificado</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <table class="table table-bordered">
                    <tbody>
                      <h6 class="text-info">Asunto</h6>
                      <div class="row">
                        <div class="col-3">
                          <b>C(País)</b><br><span id="idsubjC"></span>
                        </div>
                        <div class="col-3">
                          <b>ST(Estado)</b><br><span id="idsubjST"></span>
                        </div>
                        <div class="col-3">
                          <b>L(Localidad)</b><br><span id="idsubjL"></span>
                        </div>
                        <div class="col-3">
                          <b>OU(Unidad de organización)</b><br><span id="idsubjOU"></span>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        <div class="col-3">
                          <b>CN(Nombre común)</b><br><span id="idsubjCN"></span>
                        </div>
                        <div class="col-3">
                          <b>EMAIL(Dirección de correo electrónico)</b><br><span id="idsubjEMAIL"></span>
                        </div>
                      </div>
                      <h6 class="text-info mt-3">Nombre del emisor</h6>
                      <div class="row">
                        <div class="col-3">
                          <b>C(País)</b><br><span id="idissC"></span>
                        </div>
                        <div class="col-3">
                          <b>ST(Estado)</b><br><span id="idissST"></span>
                        </div>
                        <div class="col-3">
                          <b>L(Localidad)</b><br><span id="idissL"></span>
                        </div>
                        <div class="col-3">
                          <b>O(Organización)</b><br><span id="idissO"></span>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        <div class="col-3">
                          <b>OU(Unidad de organización)</b><br><span id="idissOU"></span>
                        </div>
                        <div class="col-3">
                          <b>CN(Nombre común)</b><br><span id="idissCN"></span>
                        </div>
                        <div class="col-3">
                          <b>EMAIL(Dirección de correo electrónico)</b><br><span id="idissEMAIL"></span>
                        </div>
                      </div>
                      <h6 class="text-info mt-3">Certificado</h6>
                      <div class="row">
                        <div class="col-3">
                          <b>Algoritmo de firma LN:</b><br><span id="idsignatureTypeLN"></span>
                        </div>
                        <div class="col-3">
                          <b>Algoritmo de firma NID:</b><br><span id="idsignatureTypeNID"></span>
                        </div>
                        <div class="col-3">
                          <b>Algoritmo de firma SN:</b><br><span id="idsignatureTypeSN"></span>
                        </div>
                        <div class="col-3">
                          <b>Serial:</b><br><span id="idserialNumber"></span>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        <div class="col-3">
                          <b>Válido desde:</b><br><span id="idvalidFrom"></span>
                        </div>
                        <div class="col-3">
                          <b>Válido hasta:</b><br><span id="idvalidTo"></span>
                        </div>
                        <div class="col-3">
                          <b>Versión:</b><br><span id="idversion"></span>
                        </div>
                      </div>
                    </tbody>
                  </table>
                </div>
                <div class="modal-footer">
                  <button type="button"
                    class="btn btn-default btn-sm btn-round btn-modal-close"
                    data-dismiss="modal"
                  >
                    Cerrar
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- End of certificate details button modal -->
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Acciones comunes --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title"> Acciones Comunes </h6>
        <div class="card-btns">
          <a href="#" data-toggle="tooltip"
            class="card-minimize btn btn-card-action btn-round"
            data-original-title="Minimize Panel"
          >
            <i class="now-ui-icons arrows-1_minimal-up"></i>
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          @if($cert == 'true')
            @if($permissionVerify)
              <digitalsignature-verifysign-component></digitalsignature-verifysign-component>
            @else
              <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                href="#" title="Formulario de actualización del certificado"
                onclick="showMessagePerms();">
                <i class="icofont icofont-file-pdf ico-3x"></i>
                <span>Verificar firma PDF</span>
              </a>
            @endif
            @if($permissionSign)
              <digitalsignature-signfile-component></digitalsignature-signfile-component>
            @else
              <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                href="#" title="Formulario de actualización del certificado"
                onclick="showMessagePerms();">
                <i class="icofont icofont-fountain-pen ico-3x"></i>
                <span>Firmar PDF</span>
              </a>
            @endif
          @endif

          <div class="col-xs-2 text-center">
            @if($permissionStore)
              @if($cert == 'true')
                @if($permissionUpdate)
                  <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                    href="#" title="Formulario de carga del certificado"
                    data-toggle="modal" data-target="#{!! 'modalUpdateCert' !!}">
                    <i class="icofont icofont-certificate-alt-1 ico-3x"></i>
                    <span class="pt-2"> Actualizar certificado </span>
                  </a>
                @else
                  <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                    href="#" title="Formulario de actualización del certificado"
                    onclick="showMessagePerms();">
                    <i class="icofont icofont-certificate-alt-1 ico-3x"></i>
                    <span class="pt-2"> Actualizar certificado </span>
                  </a>
                @endif
              @else
                <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                  href="#" title="Formulario de carga del certificado"
                  data-toggle="modal" data-target="#{!! 'modalUploadCert' !!}">
                  <i class="icofont icofont-upload-alt ico-3x"></i>
                  <span class="pt-2"> Cargar certificado </span>
                </a>
              @endif
            @else
              <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="#"
                title="Formulario de carga del certificado"
                data-toggle="modal" onclick="showMessagePerms();">
                @if($cert == 'true')
                  <i class="icofont icofont-certificate-alt-1 ico-3x"></i>
                  <span class="pt-2"> Actualizar certificado </span>
                @else
                  <i class="icofont icofont-upload-alt ico-3x"></i>
                  <span class="pt-2"> Cargar certificado </span>
                @endif
              </a>
            @endif

            <!-- upload certificate button modal  -->
            <div class="modal fade" id="modalUploadCert" tabindex="-1"
              aria-labelledby="modalUploadCertLabel" aria-hidden="true">
              <div class="modal-dialog vue-crud">
                <div class="modal-content">
                  <div class="modal-header">
                    <h6>
                      <i class="icofont icofont-upload-alt inline-block"></i> Cargar certificado
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body text-left">
                    <form method="POST" enctype="multipart/form-data"
                      accept-charset="UTF-8"
                      action="{{ route('signprofilestore') }}"
                    >
                      <input type="hidden" name="_token"
                        value="{{ csrf_token() }}" />
                      <p>
                        <label for="pkcs12">Cargar certificado del firmante</label>
                        <input id="pkcs12" class="form-control" type="file"
                          name="pkcs12" accept=".p12" autocomplete="off"
                          required />
                      </p>
                      <p>
                        <label for="phasepass">Contraseña del certificado</label>
                        <input id="phasepass" class="form-control" type="password"
                          name="password" placeholder="XXXXXX" autocomplete="off"
                          required />
                      </p>
                      <p class="text-right">
                        <button class="btn btn-warning btn-icon btn-round btn-modal-close"
                          data-dismiss="modal" data-original-title="Cancelar">
                          <i class="fa fa-ban"></i>
                        </button>
                        <button type="submit" class="btn btn-success btn-icon btn-round"
                          data-original-title="Subir certificado" title="Subir certificado">
                          <i class="fa fa-save"></i>
                        </button>
                      </p>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- End of upload certificate button modal -->
          </div>

        </div>
      </div>
    </div>
  </div>
@stop

<script>
  // Función que enviar el documento del formuario para la firma electrónica.
  function signFilePdf() {
    console.log('signFile');
    let data = new FormData();
    data.append('file', document.getElementById('pdf').files[0]);
    axios.post('{{ route('signFile') }}', data).then(function (response) {
      console.log(response.data);
    });
  }

  // Función que muestra el detalle del certificado firmante.
  function certificateDetails() {
    axios.get('{{ route('certificateDetails') }}', {
    })
    .then(response => {
      $("#idsubjC").text(response.data.records['certificateDetail']['subjCountry']);
      $("#idsubjST").text(response.data.records['certificateDetail']['subjState']);
      $("#idsubjL").text(response.data.records['certificateDetail']['subjLocality']);
      $("#idsubjO").text(response.data.records['certificateDetail']['subjOrganization']);
      $("#idsubjOU").text(response.data.records['certificateDetail']['subjUnitOrganization']);
      $("#idsubjCN").text(response.data.records['certificateDetail']['subjName']);
      $("#idsubjEMAIL").text(response.data.records['certificateDetail']['subjMail']);
      $("#idissC").text(response.data.records['certificateDetail']['issCountry']);
      $("#idissST").text(response.data.records['certificateDetail']['issState']);
      $("#idissjL").text(response.data.records['certificateDetail']['issLocality']);
      $("#idissO").text(response.data.records['certificateDetail']['issOrganization']);
      $("#idissOU").text(response.data.records['certificateDetail']['issUnitOrganization']);
      $("#idissCN").text(response.data.records['certificateDetail']['issName']);
      $("#idissEMAIL").text(response.data.records['certificateDetail']['issMail']);
      $("#idsignatureTypeLN").text(response.data.records['certificateDetail']['signatureTypeLN']);
      $("#idsignatureTypeNID").text(response.data.records['certificateDetail']['signatureTypeNID']);
      $("#idsignatureTypeSN").text(response.data.records['certificateDetail']['signatureTypeSN']);
      $("#idserialNumber").text(response.data.records['certificateDetail']['serialNumber']);
      $("#idvalidTo").text(response.data.records['certificateDetail']['validTo']);
      $("#idvalidFrom").text(response.data.records['certificateDetail']['validFrom']);
      $("#idversion").text(response.data.records['certificateDetail']['version']);
    });
  }

  /**
   * Método que muestra un mensaje al usuario sobre el resultado de una acción
   *
   * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
   *
   * @param  {string} type        Tipo de mensaje a mostrar
   * @param  {string} msg_title   Título del mensaje (opcional)
   * @param  {string} msg_class   Clase CSS a utilizar en el mensaje (opcional)
   * @param  {string} msg_icon    Ícono a mostrar en el mensaje (opcional)
   * @param  {string} custom_text Texto personalizado para el mensaje (opcional)
   */
  function showMessage(type, msg_title, msg_class, msg_icon, custom_text) {
    msg_title = (typeof(msg_title) == "undefined" || !msg_title)?'Éxito':msg_title;
    msg_class = (typeof(msg_class) == "undefined" || !msg_class)?'growl-success':'growl-'+msg_class;
    msg_icon = (typeof(msg_icon) == "undefined" || !msg_icon)?'screen-ok':msg_icon;
    custom_text = (typeof(custom_text)!=="undefined")?custom_text:'';
    var msg_text;
    if (type=='custom') {
      msg_text = custom_text;
    }
    $.gritter.add({
      title: msg_title,
      text: msg_text,
      class_name: msg_class,
      image: `${window.app_url}/images/${msg_icon}.png`,
      sticky: false,
      time: 3500
    });
  };

  /**
   * Método que invoca a showMessage para mostrar la notificación ante el
   * usuario sin permisos suficientes para acceder a la funcionalidad.
   *
   * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
   */
  function showMessagePerms() {
    showMessage('custom', 'Acceso Denegado', 'danger', 'screen-error', 'No dispone de permisos para acceder a esta funcionalidad');
  }
</script>
