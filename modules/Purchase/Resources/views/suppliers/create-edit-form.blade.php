@extends('purchase::layouts.master')

@section('maproute-icon')
    <i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-actual')
    Compras
@stop

@section('maproute-title')
    Proveedores
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">
                {{ __('Proveedor') }}
                @include('buttons.help', [
                    'helpId' => 'purchaseSuppliers',
                    'helpSteps' => get_json_resource('ui-guides/suppliers/purchase_suppliers.json', 'purchase'),
                ])
            </h6>
            <div class="card-btns">
                @include('buttons.previous', ['route' => route('purchase.suppliers.index')])
                @include('buttons.minimize')
            </div>
        </div>
        {!! !isset($model) ? Form::open($header) : Form::model($model, $header) !!}
        {!! Form::token() !!}
        <div id="helpSupplier" class="card-body pt-1">
            @include('layouts.form-errors')
            <ul class="nav nav-tabs custom-tabs px-0 pt-0" role="tablist">
                <li id="helpBasicData" class="nav-item">
                    <a href="#default_data" class="nav-link active" data-toggle="tab"
                        title="{{ __('Datos básicos del proveedor') }}">
                        {{ __('Datos Básicos') }}
                    </a>
                </li>
                <li id="helpNationalRegistry" class="nav-item">
                    <a href="#rnc" class="nav-link" data-toggle="tab"
                        title="{{ __('Datos de Información del Registro Nacional de Contratistas (RNC)') }}">
                        {{ __('Datos del RNC') }}
                    </a>
                </li>
                <li id="helpRequirementDocs" class="nav-item">
                    <a href="#requirement_docs" class="nav-link" data-toggle="tab"
                        title="{{ __('Consignación de requisitos en físico y digital') }}">
                        {{ __('Documentos') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="default_data" class="tab-pane active" role="tabpanel">
                    <h6 class="card-title">
                        {{ __('Datos básicos del Proveedor') }}
                    </h6>
                    <div class="row pb-3">
                        {{-- Tipos de persona --}}
                        <div id="helpPersontype" class="col-12 col-lg-4">
                            <div class="form-group is-required {{ $errors->has('person_type') ? 'has-error' : '' }}">
                                {!! Form::label('person_type', __('Tipo de Persona'), ['class' => 'control-label h6']) !!}
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="personTypeN">{{ __('Natural') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('person_type', __('N'), null, [
                                                'id' => 'personTypeN',
                                                'class' => 'custom-control-input reseteable',
                                                'title' => __('Seleccione si el tipo de persona es Natural'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="personTypeN"></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="personTypeJ">{{ __('Jurídica') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('person_type', 'J', null, [
                                                'id' => 'personTypeJ',
                                                'class' => 'custom-control-input reseteable',
                                                'title' => __('Seleccione si el tipo de persona es Juridica'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="personTypeJ"></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="personTypeG">{{ __('Gubernamental') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('person_type', 'G', null, [
                                                'id' => 'personTypeG',
                                                'class' => 'custom-control-input reseteable',
                                                'title' => __('Seleccione si el tipo de persona es Gubernamental'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="personTypeG"></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="personTypeE">{{ __('Extranjero') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('person_type', 'E', null, [
                                                'id' => 'personTypeE',
                                                'class' => 'custom-control-input reseteable',
                                                'title' => __('Seleccione si el tipo de persona es Extranjero'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="personTypeE"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tipo de Empresa --}}
                        <div id="helpCompanyType" class="col-12 col-lg-4">
                            <div class="form-group is-required {{ $errors->has('company_type') ? 'has-error' : '' }}">
                                {!! Form::label('company_type', __('Tipo de Empresa'), ['class' => 'control-label h6']) !!}
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="companyTypePU">{{ __('Pública') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('company_type', 'PU', null, [
                                                'id' => 'companyTypePU',
                                                'class' => 'custom-control-input',
                                                'title' => __('Seleccione si la empresa es Pública'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="companyTypePU"></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="companyTypePR">{{ __('Privada') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('company_type', 'PR', null, [
                                                'id' => 'companyTypePR',
                                                'class' => 'custom-control-input',
                                                'title' => __('Seleccione si la empresa es Privada'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="companyTypePR"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Estatus --}}
                        <div id="helpActive" class="col-12 col-lg-4">
                            <div class="form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                                {!! Form::label('active', __('Estado'), ['class' => 'control-label h6']) !!}
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-3 radio-inline text-center">
                                        <label for="activo">{{ __('Activo') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::checkbox('active', true, null, [
                                                'id' => 'activo',
                                                'class' => 'custom-control-input',
                                                'title' => __('Seleccione si el proveedor esta activo'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="activo"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpRif" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('rif') ? 'has-error' : '' }}">
                                {!! Form::label('rif', __('R.I.F.'), ['class' => 'control-label']) !!}
                                {!! Form::text('rif', old('rif'), [
                                    'class' => 'form-control input-sm',
                                    'placeholder' => __('R.I.F.'),
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Indique el R.I.F. del proveedor'),
                                ]) !!}
                            </div>
                        </div>
                        <div id="helpName" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('name') ? 'has-error' : '' }}">
                                {!! Form::label('name', __('Nombre o Razón Social'), ['class' => 'control-label']) !!}
                                {!! Form::text('name', old('name'), [
                                    'class' => 'form-control input-sm',
                                    'placeholder' => __('Nombre o razón'),
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Nombre o razón social del proveedor'),
                                ]) !!}
                            </div>
                        </div>
                        <div id="helpSocialPurpose" class="col-12 col-md-4">
                            <div class="form-group {{ $errors->has('social_purpose') ? ' has-error' : '' }}">
                                {!! Form::label('social_purpose', __('Objeto Social del proveedor'), ['class' => 'control-label']) !!}
                                {!! Form::text('social_purpose', null, [
                                    'class' => 'form-control input-sm',
                                    'placeholder' => __('Objeto Social'),
                                    'title' => __('Indique el objeto social del proveedor'),
                                    'data-toggle' => 'tooltip',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpPurchaseSupplierType" class="col-12 col-md-4">
                            <div
                                class="form-group is-required {{ $errors->has('purchase_supplier_type_id') ? 'has-error' : '' }}">
                                {!! Form::label('purchase_supplier_type_id', __('Denominación Comercial'), ['class' => 'control-label']) !!}
                                {!! Form::select('purchase_supplier_type_id', $supplier_types, null, [
                                    'class' => 'form-control select2',
                                    'title' => __('Seleccione la denominación comercial del proveedor'),
                                    'data-toggle' => 'tooltip',
                                ]) !!}
                            </div>
                        </div>
                        <div id="helpPurchaseSupplierObject" class="col-12 col-md-4">
                            <div
                                class="form-group is-required {{ $errors->has('purchase_supplier_object_id') ? 'has-error' : '' }}">
                                {!! Form::label('purchase_supplier_object_id', __('Objeto Principal'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'purchase_supplier_object_id',
                                    $supplier_objects,
                                    isset($model_supplier_objects) ? $model_supplier_objects : null,
                                    [
                                        'class' => 'form-control multiple',
                                        'multiple' => 'multiple',
                                        'name' => 'purchase_supplier_object_id[]',
                                        'title' => __('Seleccione el(los) objeto(s) principal(es) del proveedor'),
                                    ],
                                ) !!}
                            </div>
                        </div>
                        @php
                            if (($clave = array_search('Seleccione...', $supplier_branches)) !== false) {
                                unset($supplier_branches[$clave]);
                            }
                        @endphp
                        <div id="helpPurchaseSupplierBranch" class="col-12 col-md-4">
                            <div
                                class="form-group is-required {{ $errors->has('purchase_supplier_branch_id') ? 'has-error' : '' }}">
                                {!! Form::label('purchase_supplier_branch_id', __('Rama'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'purchase_supplier_branch_id',
                                    $supplier_branches,
                                    isset($model_supplier_branches) ? $model_supplier_branches : null,
                                    [
                                        'class' => 'form-control multiple',
                                        'multiple' => 'multiple',
                                        'name' => 'purchase_supplier_branch_id[]',
                                        'title' => __('Seleccione la rama del proveedor'),
                                    ],
                                ) !!}
                            </div>
                        </div>
                        @php
                            if (($clave = array_search('Seleccione...', $supplier_specialties)) !== false) {
                                unset($supplier_specialties[$clave]);
                            }
                        @endphp
                        <div id="helpPurchaseSupplierSpecialty" class="col-12 col-md-4">
                            <div
                                class="form-group is-required {{ $errors->has('purchase_supplier_specialty_id') ? 'has-error' : '' }}">
                                {!! Form::label('purchase_supplier_specialty_id', __('Especialidad'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'purchase_supplier_specialty_id',
                                    $supplier_specialties,
                                    isset($model_supplier_specialties) ? $model_supplier_specialties : null,
                                    [
                                        'class' => 'form-control multiple',
                                        'multiple' => 'multiple',
                                        'name' => 'purchase_supplier_specialty_id[]',
                                        'title' => __('Seleccione la especialidad del proveedor'),
                                    ],
                                ) !!}
                            </div>
                        </div>
                        <div id="helpAccountingAccount" class="col-12 col-md-4">
                            <div
                                class="form-group is-required {{ $errors->has('accounting_account_id') ? 'has-error' : '' }}">
                                {!! Form::label('accounting_account_id', __('Cuentas contables'), ['class' => 'control-label']) !!}
                                {!! Form::select('accounting_account_id', $accounting_accounts, null, [
                                    'class' => 'form-control select2',
                                    'title' => __('Seleccione la cuenta contable asociada al proveedor'),
                                    'data-toggle' => 'tooltip',
                                ]) !!}
                            </div>
                        </div>
                        <div id="helpFileNumber" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('file_number') ? ' has-error' : '' }}">
                                {!! Form::label('file_number', __('Número de Expediente'), ['class' => 'control-label']) !!}
                                {!! Form::text('file_number', null, [
                                    'class' => 'form-control input-sm',
                                    'title' => __('Indique el número de expediente del trabajador'),
                                    'data-toggle' => 'tooltip',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpCountry" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('country_id') ? 'has-error' : '' }}">
                                {!! Form::label('country_id', __('País'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'country_id',
                                    isset($countries) ? $countries : [],
                                    isset($model_institution) ? $model_institution->city->estate->country->id : null,
                                    [
                                        'id' => 'country_id',
                                        'class' => 'form-control select2 input-sm',
                                        'onchange' => 'updateSelect($(this), $("#estate_id"), "Estate")',
                                        'title' => __('Seleccione el país de ubicación del proveedor'),
                                        'data-toggle' => 'tooltip',
                                    ],
                                ) !!}
                            </div>
                        </div>
                        <div id="helpEstate" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('estate_id') ? 'has-error' : '' }}">
                                {!! Form::label('estate_id', __('Estado'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'estate_id',
                                    isset($estates) ? $estates : [],
                                    isset($model_institution) ? $model_institution->city->estate->id : old('estate_id'),
                                    [
                                        'id' => 'estate_id',
                                        'class' => 'form-control select2',
                                        'onchange' =>
                                            'updateSelect($(this), $("#municipality_id"), "Municipality"),updateSelect($(this), $("#city_id"), "City")',
                                        'title' => __('Seleccione el estado de ubicación del proveedor'),
                                        'data-toggle' => 'tooltip',
                                    ],
                                ) !!}
                            </div>
                        </div>
                        <div id="helpCity" class="col-12 col-md-4">
                            <div class="form-group is-required {{ $errors->has('city_id') ? ' has-error' : '' }}">
                                {!! Form::label('city_id', __('Ciudad'), ['class' => 'control-label']) !!}
                                {!! Form::select(
                                    'city_id',
                                    isset($cities) ? $cities : [],
                                    isset($model_institution) ? $model_institution->city_id : old('city_id'),
                                    [
                                        'id' => 'city_id',
                                        'class' => 'form-control select2',
                                        'title' => __('Seleccione la ciudad de ubicación del proveedor'),
                                        'data-toggle' => 'tooltip',
                                    ],
                                ) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpWebsite" class="col-12 col-md-4">
                            <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
                                {!! Form::label('website', __('Sitio Web'), ['class' => 'control-label']) !!}
                                {!! Form::url('website', null, [
                                    'class' => 'form-control input-sm',
                                    'title' => __('Indique el sítio web del proveedor'),
                                    'data-toggle' => 'tooltip',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpDirection" class="col-12">
                            <div class="form-group is-required {{ $errors->has('direction') ? ' has-error' : '' }}">
                                {!! Form::label('direction', __('Dirección Fiscal'), ['class' => 'control-label']) !!}
                                <ckeditor id="direction" rows="3" class="form-control" data-toggle="tooltip"
                                    placeholder="{!! __('Dirección Fiscal') !!}" title="{!! __('Dirección Fiscal') !!}"
                                    tag-name="textarea" value="{!! isset($model) ? $model->direction : old('direction') !!}" :config="ckeditor.editorConfig"
                                    name="direction" :editor="ckeditor.editor" />
                            </div>
                        </div>
                    </div>
                    <hr>
                    @php
                        $contacts = [];
                        $contact_names = session()->getOldInput('contact_names');
                        $contact_emails = session()->getOldInput('contact_emails');
                        if ($contact_names) {
                            for ($i = 0; $i < count($contact_names); $i++) {
                                array_push($contacts, ['name' => $contact_names[$i], 'email' => $contact_emails[$i]]);
                            }
                        } elseif (isset($model) && $model->contacts) {
                            foreach ($model->contacts as $contact) {
                                array_push($contacts, [
                                    'name' => $contact->name,
                                    'email' => $contact->email,
                                ]);
                            }
                        }
                    @endphp
                    <contacts id="helpContacts" initial_data="{{ $contacts ? json_encode($contacts) : '' }}">
                    </contacts>
                    <hr>
                    @php
                        $phones = [];
                        $phone_type = session()->getOldInput('phone_type');
                        $phone_area_code = session()->getOldInput('phone_area_code');
                        $phone_number = session()->getOldInput('phone_number');
                        $phone_extension = session()->getOldInput('phone_extension');

                        if ($phone_type) {
                            for ($i = 0; $i < count($phone_type); $i++) {
                                array_push($phones, [
                                    'type' => $phone_type[$i],
                                    'area_code' => $phone_area_code[$i],
                                    'number' => $phone_number[$i],
                                    'extension' => $phone_extension[$i] ?? '',
                                ]);
                            }
                        } elseif (isset($model) && $model->phones) {
                            foreach ($model->phones as $phone) {
                                array_push($phones, [
                                    'type' => $phone->type,
                                    'area_code' => $phone->area_code,
                                    'number' => $phone->number,
                                    'extension' => $phone->extension ?? '',
                                ]);
                            }
                        }
                    @endphp
                    <phones id="helpPhones" initial_data="{{ $phones ? json_encode($phones) : '' }}"></phones>
                </div>
                <div id="rnc" class="tab-pane" role="tabpanel">
                    <h6 class="card-title">
                        {{ __('Datos del Registro Nacional de Contratistas') }}
                    </h6>
                    <div class="row">
                        <div id="helpRncStatus" class="col-12 col-lg-6 col-xl-4">
                            <div class="form-group {{ $errors->has('rnc_status') ? ' has-error' : '' }}">
                                {!! Form::label('rnc_status', __('Situación Actual'), ['class' => 'control-label h6']) !!}
                                <div id="rnc_status" class="row">
                                    <div class="col-12 col-md-6 radio-inline text-center">
                                        <label for="rnc_status_inh">{{ __('Inscrito y no habilitado') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('rnc_status', 'INH', null, [
                                                'id' => 'rnc_status_inh',
                                                'class' => 'custom-control-input',
                                                'title' => __('Seleccione si el proveedor está inscrito y no habilitado'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="rnc_status_inh"></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 radio-inline text-center">
                                        <label
                                            for="rnc_status_ish">{{ __('Inscrito y habilitado para contratar') }}</label>
                                        <div class="custom-control custom-switch">
                                            {!! Form::radio('rnc_status', 'ISH', null, [
                                                'id' => 'rnc_status_ish',
                                                'class' => 'custom-control-input',
                                                'title' => __('Seleccione si el proveedor está inscrito y habilitado para contratar'),
                                                'data-toggle' => 'tooltip',
                                            ]) !!}
                                            <label class="custom-control-label" for="rnc_status_ish"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="helpRncCertificateNumber" class="col-12 col-lg-6 col-xl-4">
                            <div
                                class="form-group is-required {{ $errors->has('rnc_certificate_number') ? 'has-error' : '' }}">
                                {!! Form::label('rnc_certificate_number', __('Número de Certificado'), ['class' => 'control-label']) !!}
                                {!! Form::text('rnc_certificate_number', null, [
                                    'class' => 'form-control input-sm',
                                    'placeholder' => __('número de certificado'),
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Ingrese el número de certificado RNC del proveedor'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div id="requirement_docs" class="tab-pane" role="tabpanel">
                    <h6 class="card-title">
                        {{ __('Documentos a consignar') }}
                    </h6>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            {{-- Campo para registrar un arreglo de documentos --}}
                            <input id="documents" type="hidden" name="documents" readonly>
                            <ul class="feature-list list-group list-group-flush">
                                @foreach ($requiredDocuments as $reqDoc)
                                    @if ($reqDoc->type == 'Proveedor')
                                        <li class="list-group-item">
                                            <div class="feature-list-indicator bg-info"></div>
                                            <div class="feature-list-content p-0">
                                                <div class="feature-list-content-wrapper">
                                                    <div class="feature-list-content-right feature-list-content-actions">
                                                        <button class="btn btn-simple btn-success btn-events"
                                                            title="{{ __('Presione para cargar el documento') }}"
                                                            data-toggle="tooltip" type="button"
                                                            onclick="clickUploadDoc({{ $reqDoc->id }})">
                                                            <i class="fa fa-cloud-upload fa-2x"></i>
                                                        </button>
                                                        @if (isset($model) && isset($model->documents) && isset($docs_to_download['req_doc_' . $reqDoc->id]))
                                                            <a class="btn btn-simple btn-primary btn-events"
                                                                title="{{ __('Presione para descargar el documento') }}"
                                                                data-toggle="tooltip" target="_blank"
                                                                href="{{ '/purchase/document/download/' . $docs_to_download['req_doc_' . $reqDoc->id]->file }}"
                                                                download="{{ $model->rif . ' - ' . $reqDoc->name . '.pdf' }}">
                                                                <i class="fa fa-cloud-download fa-2x"></i>
                                                            </a>
                                                        @endif
                                                        <input id="{{ 'doc' . $reqDoc->id }}" class="d-none"
                                                            type="file" name="docs[]" onchange="uploadFile(event)"
                                                            accept=".doc, .pdf, .odt, .docx" />
                                                        <input id="{{ 'reqDoc' . $reqDoc->id }}" class="d-none"
                                                            type="number" name="reqDocs[]" />
                                                    </div>
                                                    <div class="feature-list-content-left">
                                                        @if (isset($docs_to_download['req_doc_' . $reqDoc->id]))
                                                            <div class="feature-list-subheading">
                                                                <span class="badge badge-success"
                                                                    title="{{ __('El documento se ha cargado') }}"
                                                                    data-toggle="tooltip">
                                                                    <strong>{{ __('Documento cargado') }}</strong>
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div id="{{ 'toload_doc' . $reqDoc->id }}"
                                                                class="feature-list-heading">
                                                                <div class="badge badge-danger ml-2"
                                                                    title="{{ __('El documento aún no ha sido cargado') }}"
                                                                    data-toggle="tooltip">
                                                                    {{ __('por cargar') }}
                                                                </div>
                                                            </div>
                                                            <div id="{{ 'loaded_doc' . $reqDoc->id }}"
                                                                class="feature-list-subheading d-none">
                                                                <span class="badge badge-success"
                                                                    title="{{ __('El documento se ha cargado') }}"
                                                                    data-toggle="tooltip">
                                                                    <strong>{{ __('Documento cargado') }}</strong>
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div class="feature-list-subheading">
                                                            <i class="font-weight-bold">{!! $reqDoc->name ?? '' !!}</i>
                                                            {!! $reqDoc->description ?? '' !!}
                                                        </div>
                                                    </div>
                                                </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            @if (!isset($hide_clear) || !$hide_clear)
                {!! Form::button('<i class="fa fa-eraser"></i>', [
                    'id' => 'reset-select',
                    'class' => 'btn btn-default btn-icon btn-round',
                    'data-toggle' => 'tooltip',
                    'type' => 'reset',
                    'title' => __('Borrar datos del formulario'),
                ]) !!}
            @endif
            @if (!isset($hide_previous) || !$hide_previous)
                {!! Form::button('<i class="fa fa-ban"></i>', [
                    'class' => 'btn btn-warning btn-icon btn-round',
                    'type' => 'button',
                    'data-toggle' => 'tooltip',
                    'title' => __('Cancelar y regresar'),
                    'onclick' => 'window.location.href="' . url()->previous() . '"',
                ]) !!}
            @endif
            @if (!isset($hide_save) || !$hide_save)
                {!! Form::button('<i class="fa fa-save"></i>', [
                    'class' => 'btn btn-success btn-icon btn-round',
                    'data-toggle' => 'tooltip',
                    'title' => __('Guardar registro'),
                    'type' => 'submit',
                ]) !!}
            @endif
        </div>
        {!! Form::close() !!}
    </div>
@stop

@section('extra-js')
    @parent
    {!! Html::script('js/ckeditor.js', [], Request::secure()) !!}
    <script>
        let idclicker = 0;
        $(document).ready(function() {
            //app.ckeditor.editorData = "{!! isset($model) ? $model->description : old('description') !!}";
            $(".nav-link").tooltip();
            $("#reset-select").on('click', function() {
                $('#purchase_supplier_type_id').val('').change();
                $('#purchase_supplier_object_id').val('').change();
                $('#purchase_supplier_branch_id').val('').change();
                $('#purchase_supplier_specialty_id').val('').change();
                $('#country_id').val('').change();
                $('#state_id').val('').change();
                $('#city_id').val('').change();
                $('#activo').prop('checked', false);
                $(":radio").prop('checked', false).change();
                $(":checkbox").prop('checked', false).change();
                app.ckeditor.editorData = '';
            });
            $(".multiple").select2({
                placeholder: "Seleccione..."
            });
        });

        function clickUploadDoc(id, ) {
            idclicker = id;
            $('#doc' + id).click();
        }

        function uploadFile(e) {
            const files = e.target.files;
            Array.from(files).forEach(file => addFile(file, idclicker));
        }

        function addFile(file, inputID) {
            $('#reqDoc' + inputID).val(inputID);
            $('#loaded_doc' + inputID).show("slow");
            $('#toload_doc' + inputID).hide("slow");
        }

        function conditi() {
            if (
                !file.type.match('application/pdf') ||
                !file.type.match('application/msword') ||
                !file.type.match('application/vnd.oasis.opendocument.text') ||
                !file.type.match('application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ) {
                this.showMessage(
                    'custom', 'Error', 'danger', 'screen-error', 'Solo se permiten archivos pdf.'
                );
                return;
            } else {
                $('#status_' + inputID).show("slow");
            }
        }
    </script>
    <script>
        let selectElement = document.querySelector('select[name="accounting_account_id"]');
        let selectedValue = selectElement.value;
        let options = selectElement.options;

        for (let i = 0; i < options.length; i++) {
            let institutional = options[i].text.split('.')[6];

            if (institutional) {
                if (institutional.split('-')[0] == 000) {
                    options[i].disabled = true;
                }
            }
        }
    </script>
@stop
