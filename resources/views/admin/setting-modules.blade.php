@extends('layouts.app')

@section('maproute-icon')
	<i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-settings"></i>
@stop

@section('maproute-actual')
	{{ __('Módulos') }}
@stop

@section('maproute-title')
	{{ __('Módulos') }}
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">{{ __('Módulos') }}</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
					</div>
				</div>
				<div class="card-body" style="padding-bottom: 2.25rem">
                    <manage-modules :modules='{!! json_encode($listModules) !!}'></manage-modules>
				</div>
			</div>
		</div>
	</div>
@stop

@section('extra-js')
	@parent
	<script>
		$(document).ready(function() {
			$(".btn-enable").on("click", function() {
				axios.post('{{ route('module.enable') }}', {
					module: $(this).data('module')
				}).then(response => {
					if (response.data.result) {
						$.gritter.add({
                            title: 'Éxito!',
                            text: 'Módulo habilitado satisfactoriamente',
                            class_name: 'growl-success',
                            image: "{{ asset('images/screen-ok.png') }}",
                            sticky: false,
                            time: 2500
                        });

                        setTimeout(function() {
                        	location.href = '{{ route('module.list') }}'
                        }, 2500);
					}
				}).catch(error => {
					logs('setting-modules', 130, error);
				});
			});
			$(".btn-disable").on("click", function() {
				axios.post('{{ route('module.disable') }}', {
					module: $(this).data('module')
				}).then(response => {
					if (response.data.result) {
						$.gritter.add({
                            title: 'Éxito!',
                            text: 'Módulo deshabilitado satisfactoriamente',
                            class_name: 'growl-success',
                            image: "{{ asset('images/screen-ok.png') }}",
                            sticky: false,
                            time: 2500
                        });

                        setTimeout(function() {
                        	location.href = '{{ route('module.list') }}'
                        }, 2500);
					}
				}).catch(error => {
					logs('setting-modules', 152, error);
				});
			});
		});
	</script>
@stop
