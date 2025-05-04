@extends('layouts.app')

@section('maproute-icon')
    <i class="icofont icofont-ui-user"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-ui-user"></i>
@stop

@section('maproute-actual')
    {{ __('Mi Perfil') }}
@stop

@section('maproute-title')
    {{ __('Mi Perfil') }}
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">
                {{ __('Perfil de usuario') }}
                @include('buttons.help', [
                    'helpId' => 'myProfile',
                    'helpSteps' => get_json_resource('ui-guides/my_profile.json')
                ])
            </h6>
            <div class="card-btns">
                @include('buttons.previous', ['route' => url()->previous()])
                @include('buttons.minimize')
            </div>
        </div>
        <div class="card-body" id="helpProfile">
            <div class="row">
                <div class="col-md-3 col-right-border">
                    <div class="row">
                        <div class="col-12 text-center">
                            {!! Form::open([
                                'id' => 'formImgProfile', 'method' => 'POST', 'route' => 'upload-image.store',
                                'role' => 'form', 'class' => 'form', 'enctype' => 'multipart/form-data'
                            ]) !!}
                                @php
                                    $prf = auth()->user()->profile;
                                    $img_profile = ($prf && $prf->image_id) ? $prf->image->url : null;
                                @endphp
                                <img src="{{ asset($img_profile ?? 'images/default-avatar.png') }}"
                                     alt="{{ $model->name ?? auth()->user()->name }}"
                                     class="img-profile" style="cursor:pointer" id="helpProfilePicture"
                                     title="{{ __('Click para modificar imagen de perfil') }}"
                                     data-toggle="tooltip" onclick="$('input[name=profile_image]').click()">
                                <input type="file" id="profile_image" name="profile_image" style="display:none"
                                       onchange="uploadProfileImage()" accept="image/*">
                            {!! Form::close() !!}
                        </div>
                        <div class="col-12 text-center">
                            <h4 id="helpProfileName">{{ auth()->user()->name }}</h4>
                            <h5 id="helpProfilePosition">{{ __('Cargo') }}</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-info btn-block" id="helpProfileLockScreen" 
                                    @click="lockScreenNow">
                                {{ __('Bloquear Pantalla') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-9" id="helpProfileContent">
                    <ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
                        <li class="nav-item" id="helpProfileData">
                            <a class="nav-link profile active" data-toggle="tab" href="#profile" role="tab">
                                <i class="ion-android-person"></i> {{ __('Perfil') }}
                            </a>
                        </li>
                        <li class="nav-item" id="helpProfileActivity">
                            <a class="nav-link activity" data-toggle="tab" href="#activity" role="tab">
                                <i class="ion-arrow-swap"></i> {{ __('Actividad') }}
                            </a>
                        </li>
                        {{-- <li class="nav-item" id="helpProfileMessages">
                            <a class="nav-link messages" data-toggle="tab" href="#messages" role="tab">
                                <i class="ion-android-mail"></i> {{ __('Mensajes') }}
                            </a>
                        </li> --}}
                        <li class="nav-item" id="helpProfileDirectory">
                            <a class="nav-link directory" data-toggle="tab" href="#directory" role="tab">
                                <i class="ion-android-contacts"></i> {{ __('Directorio') }}
                            </a>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            {!! Form::model($model, $header) !!}
                                @include('layouts.form-errors')
                                <input type="hidden" name="source" value="profile">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('name', __('Nombre y Apellido'), []) !!}
                                            <div class="input-group input-sm">
                                                <span class="input-group-addon readonly">
                                                    <i class="now-ui-icons users_single-02"></i>
                                                </span>
                                                {!! Form::text('name', $model->name, [
                                                    'class' => 'form-control input-sm',
                                                    'readonly' => 'readonly', 'id' => 'first_name',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => __(
                                                        'Nombre y Apellido. Este dato solo ' .
                                                        'puede ser modificado por personal ' .
                                                        'autorizado.'
                                                    )
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('position', __('Cargo'), []) !!}
                                            <div class="input-group input-sm">
                                                <span class="input-group-addon readonly">
                                                    <i class="now-ui-icons education_agenda-bookmark">
                                                    </i>
                                                </span>
                                                {!! Form::text('position', /*$model->position*/null, [
                                                    'class' => 'form-control input-sm',
                                                    'readonly' => 'readonly',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => __(
                                                        'Cargo en la organización. Este dato ' .
                                                        'solo puede ser modificado por ' .
                                                        'personal autorizado.'
                                                    )
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('email', __('Correo Electrónico'), []) !!}
                                            <div class="input-group input-sm">
                                                <span class="input-group-addon readonly">
                                                    <i class="now-ui-icons ui-1_email-85"></i>
                                                </span>
                                                {!! Form::email('email', $model->email, [
                                                    'class' => 'form-control input-sm',
                                                    'readonly' => 'readonly',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => __(
                                                        'Correo electrónico. Este dato solo ' .
                                                        'puede ser modificado por personal ' .
                                                        'autorizado.'
                                                    )
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('username', __('Usuario'), []) !!}
                                            <div class="input-group input-sm">
                                                <span class="input-group-addon readonly">
                                                    <i class="now-ui-icons users_circle-08"></i>
                                                </span>
                                                {!! Form::text('username', $model->username, [
                                                    'class' => 'form-control', 'readonly' => 'readonly',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => __(
                                                        'Nombre de usuario para acceso al ' .
                                                        'sistema. Este dato solo puede ser ' .
                                                        'modificado por personal autorizado.'
                                                    )
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (!config('auth.active_directory.enabled', false))
                                    <div class="row" id="helpProfilePassword">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('password', __('Nueva Contraseña'), []) !!}
                                                <div class="input-group input-sm">
                                                    <span class="input-group-addon">
                                                        <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                                    </span>
                                                    {!! Form::password('password', [
                                                        'class' => 'form-control input-sm',
                                                        'data-toggle' => 'tooltip',
                                                        'title' => __('Introduzca la nueva contraseña de acceso al sistema (Para más información sobre las contraseñas permitidas consulte el botón de ayuda)')
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="progress-container">
                                                    <span class="progress-badge">{{ __('Débil') }}</span>
                                                    <div class="progress">
                                                        <div id="complexity-bar" class="progress-bar" role="progressbar">
                                                        </div>
                                                    </div>
                                                </div>
                                                <p id="complexity" class="float-right font-bold">0%</p>
                                                {!! Form::hidden('complexity-level', null, ['id' => 'complexity-level']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('password_confirmation', __('Confirmar Contraseña'), []) !!}
                                                <div class="input-group input-sm">
                                                    <span class="input-group-addon">
                                                        <i class="now-ui-icons ui-1_lock-circle-open"></i>
                                                    </span>
                                                    {!! Form::password('password_confirmation', [
                                                        'class' => 'form-control input-sm',
                                                        'data-toggle' => 'tooltip',
                                                        'title' => __(
                                                            'Introduzca nuevamente la nueva contraseña de acceso al sistema'
                                                        )
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <div class="col-sm-12 text-right">
                                            {!! Form::button('<i class="fa fa-save"></i>', [
                                                'class' => 'btn btn-success btn-icon btn-round', 'type' => 'submit',
                                                'data-toggle' => 'tooltip', 'title' => __('Guardar registro'),
                                            ]) !!}
                                        </div>
                                    </div>
                                @endif
                            {!! Form::close() !!}
                        </div>
                        <div class="tab-pane" id="activity" role="tabpanel">
                            <h4 class="text-center">Últimos 20 registros de actividad en la aplicación</h4>
                            <div class="row">
                                <div class="col-sm-12">
                                    <ul class="timeline timeline-inverse" style="max-height:500px; overflow-y: auto;">
                                        @foreach (auth()->user()->audits->take(20) as $audit)
                                            <li class="time-label">
                                                @if ($audit->event === 'created')
                                                    @php 
                                                        $bgcolor = 'green';
                                                        $event = 'creó';
                                                        $title = 'Registro de datos'
                                                    @endphp
                                                @elseif ($audit->event === 'updated')
                                                    @php 
                                                        $bgcolor = 'orange';
                                                        $event = 'actualizó';
                                                        $title = 'Actualización de datos';
                                                    @endphp
                                                @elseif ($audit->event === 'deleted')
                                                    @php 
                                                        $bgcolor = 'red';
                                                        $event = 'eliminó';
                                                        $title = 'Eliminación de datos';
                                                    @endphp
                                                @endif

                                                @if (array_key_exists('last_login', $audit->new_values))
                                                    @php
                                                        $title = 'Último acceso'
                                                    @endphp
                                                @endif
                                                <span class="bg-{{ $bgcolor }}">
                                                    {{ $audit->updated_at->format('d M. Y') }}
                                                </span>
                                            </li>
                                            <li>
                                                <i class="fa fa-envelope bg-blue"></i>
                                                <div class="timeline-item">
                                                    <span class="time">
                                                        <i class="fa fa-clock-o"></i> 
                                                        {{ $audit->updated_at->format('H:i A') }}
                                                    </span>
                                                    <h3 class="timeline-header">
                                                        <a href="javascript::void">{{ $title }}</a>
                                                    </h3>
                                                    <div class="timeline-body">
                                                        @if (array_key_exists('last_login', $audit->new_values))
                                                            Se ha registrado un acceso a su cuenta desde la IP 
                                                            {{ $audit->ip_address }}
                                                        @else
                                                            Usted {{ $event }} la siguiente información:
                                                            <ul>
                                                                @foreach ($audit->new_values as $item)
                                                                    <li>{{ $item }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="directory" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-12">
                                    {{-- Búsqueda de contacto en el directorio institucional --}}

                                    {{-- Directorio institucional --}}
                                    <div class="list-group contact-group">
                                        <table 
                                            class="table table-hover table-striped dt-responsive nowrap @if (count($directory) > 0) datatable @endif"
                                        >
                                            <thead>
                                                <tr><td></td></tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($directory as $dir)
                                                    <tr>
                                                        <td>
                                                            <a href="mailto:{{ $dir['email'] }}" class="list-group-item">
                                                                <div class="media">
                                                                    <div class="float-left">
                                                                        @php
                                                                            $img = ($dir['profile'] && $dir['profile']['image'])
                                                                                   ? ($dir['profile']['image']['url'])
                                                                                   :'images/default-avatar.png';
                                                                        @endphp
                                                                        <img 
                                                                            class="img-circle img-online" 
                                                                            src="{{ asset($img) }}"
                                                                            alt="{{ __('usuario') }}">
                                                                    </div>
                                                                    <div class="media-body">
                                                                        <h4 class="media-heading">
                                                                            {{ (isset($dir['profile']))?$dir['profile']['full_name']:'' }} 
                                                                        </h4>
                                                                        <div class="media-content">
                                                                            <ul class="list-unstyled">
                                                                                <li>
                                                                                    <i class="fa fa-envelope-o"></i> 
                                                                                    {{ $dir['email'] }}
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div><!-- media -->
                                                            </a><!-- list-group -->
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td>
                                                            <h5 class="h5 text-center">Sin registros</h5>
                                                        </td>
                                                    </tr>
                                                @endforelse 
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script>
        $(document).ready(function() {
            /** Script para medir la fortaleza de la contraseña */
            $('#password').complexify({}, function(valid, complexity) {
                var progressBar = $('#complexity-bar');
                var progressContainer = progressBar.closest('.progress-container');
                var color;
                $('#complexity').removeClass(['text-danger', 'text-warning', 'text-success']);
                progressContainer.toggleClass('progress-danger', (complexity < 43));
                progressContainer.toggleClass('progress-warning', (complexity >= 43 && complexity <= 70));
                progressContainer.toggleClass('progress-success', (complexity > 70));
        
                if ((complexity < 43)) {
                    color = "text-danger";
                    progressContainer.find('.progress-badge').html('Débil');
                } else if (complexity >= 43 && complexity <= 70) {
                    color = "text-warning";
                    progressContainer.find('.progress-badge').html('Aceptable');
                } else if (complexity > 70) {
                    color = "text-success";
                    progressContainer.find('.progress-badge').html('Fuerte');
                }
        
                progressBar.css({ 'width': complexity + '%' });
        
                $('#complexity').addClass(color);
                $('#complexity').text(Math.round(complexity) + '%');
                $('#complexity-level').val(Math.round(complexity));
            });
        });
        function uploadProfileImage() {
            $('.preloader').show();
            var url = $("#formImgProfile").attr('action');
            var formData = new FormData();
            var imageFile = document.querySelector('#profile_image');
            if (imageFile.files[0].type.indexOf("image") < 0) {
                $.gritter.add({
                    title: '{{ __('Alerta') }}!',
                    text: "{{ __('El formato de la imagen es incorrecto') }}",
                    class_name: 'growl-warning',
                    image: "{{ asset('images/screen-warning.png') }}",
                    sticky: false,
                    time: 2500
                });
                $('.preloader').fadeOut(2000);
                return false;
            }
            formData.append("image", imageFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                var up = response.data;
                if (up.result) {
                    $.gritter.add({
                        title: '{{ __('Exito') }}!',
                        text: "{{ __('La imagen de perfil ha sido actualizada') }}",
                        class_name: 'growl-success',
                        image: "{{ asset('images/screen-ok.png') }}",
                        sticky: false,
                        time: 2500
                    });
                    axios.get('/get-image/' + up.image_id).then(response => {
                        var image = response.data.image;
                        $(".img-profile").attr('src', `${window.app_url}/${image.url}`);
                        $(".img-profile-mini").attr('src', `${window.app_url}/${image.url}`);
                        axios.post('{{ route('profiles.store') }}', {
                            first_name: $("#first_name").val(),
                            user_id: {{ auth()->user()->id }},
                            image_id:  image.id
                        }).then(response => {
                            $('.preloader').fadeOut(2000);
                        }).catch(error => {
                            logs('profile', 781, error);
                            $('.preloader').fadeOut(2000);
                        });
                    }).catch(error => {
                        logs('profile', 784, error);
                        $('.preloader').fadeOut(2000);
                    });
                }
                $('.preloader').fadeOut(2000);
            }).catch(error => {
                logs('profile', 788, error);
                $('.preloader').fadeOut(2000);
            });
        }

        @if (request()->input('tab')!==null)
            $(document).ready(function() {
                $('.{!! request()->input('tab') !!}').click();
            });
        @endif
    </script>
@endsection
