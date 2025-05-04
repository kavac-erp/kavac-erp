<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h6 class="card-title">
					{{ __('Herramientas para Desarrolladores') }}&#160;
					@include('buttons.help', [
                        'helpId' => 'developmentTools',
                        'helpSteps' => get_json_resource('ui-guides/development_tools.json')
                    ])
				</h6>
				<div class="card-btns">
                    @include('buttons.previous', ['route' => url()->previous()])
					@include('buttons.minimize')
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-12">
						<span class="text-muted">
							{{ __(
                                'Acceso a herramientas exclusivamente para desarrolladores o usuarios avanzados ' .
                                'que requieran construir nuevas funcionalidades de la aplicación.'
                            ) }}
						</span>
						<hr>
					</div>
				</div>
				<h6>{{ __('Interfaz') }}</h6>
                <div id="helpUIButtons" class="row">
					<div id="helpIconButton" class="col-12 col-md-6 col-lg-3">
						<a href="{{ route('dev.show.element', ['el' => 'icons']) }}"
						   class="btn btn-primary btn-simple btn-block text-nowrap text-truncate"
						   data-toggle="tooltip" title="{{ __('Listado de íconos disponibles en la aplicación') }}">
							<i class="icofont icofont-idea"></i> {{ __('Iconos') }}
						</a>
					</div>
					<div id="helpComponentsButton" class="col-12 col-md-6 col-lg-3">
						<a href="{{ route('dev.show.element', ['el' => 'components']) }}"
						   class="btn btn-primary btn-simple btn-block text-nowrap text-truncate" data-toggle="tooltip"
                           title="{{ __('Listado de componentes y elementos disponibles en la aplicación') }}">
							<i class="icofont icofont-idea"></i> {{ __('Componentes') }}
						</a>
					</div>
					<div id="helpButtons" class="col-12 col-md-6 col-lg-3">
						<a href="{{ route('dev.show.element', ['el' => 'buttons']) }}"
						   class="btn btn-primary btn-simple btn-block text-nowrap text-truncate"
						   data-toggle="tooltip" title="{{ __('Listado de estilos de botones') }}">
							<i class="icofont icofont-idea"></i> {{ __('Botones') }}
						</a>
					</div>
					<div id="helpFormsButton" class="col-12 col-md-6 col-lg-3">
						<a href="{{ route('dev.show.element', ['el' => 'forms']) }}"
						   class="btn btn-primary btn-simple btn-block text-nowrap text-truncate"
						   data-toggle="tooltip" title="{{ __('Listado de componentes de formulario') }}">
							<i class="icofont icofont-idea"></i> {{ __('Formularios') }}
						</a>
					</div>
				</div>
				<hr>
				<h6>{{ __('Ajustes') }}</h6>
				<div id="helpUIAdjustements" class="row">
					<div id="helpMaintenanceMode" class="col-12 col-md-3">
						<div class="form-group">
							<label for="" class="control-label">{{ __('Mantenimiento') }}</label>
							<div class="col-12">
								<div class="custom-control custom-switch" data-toggle="tooltip" 
									 title="{{ __('Establecer la aplicación en modo de mantenimiento') }}">
									{!! Form::checkbox('maintenance', true, false, [
    									'id' => 'maintenance', 'class' => 'custom-control-input'
    								]) !!}
									<label class="custom-control-label" for="maintenance">&#160;</label>
								</div>
							</div>
						</div>
					</div>
					<div id="helpDemoMode" class="col-12 col-md-3">
						<div class="form-group">
							<label for="" class="control-label">{{ __('Demostración') }}</label>
							<div class="col-12">
								<div class="custom-control custom-switch" data-toggle="tooltip" 
									 title="{{ __('Establecer la aplicación en modo de demostración') }}">
									 {!! Form::checkbox('demo', true, false, [
    									'id' => 'demo', 'class' => 'custom-control-input'
    								]) !!}
									<label class="custom-control-label" for="demo">&#160;</label>
								</div>
							</div>
						</div>
					</div>
					<div id="helpDebugMode" class="col-12 col-md-3">
						<div class="form-group">
							<label for="" class="control-label">{{ __('Debug') }}</label>
							<div class="col-12">
								<div class="custom-control custom-switch" data-toggle="tooltip" 
									 title="{{ __('Establecer la aplicación en modo de desarrollo') }}">
									 {!! Form::checkbox('debug', true, false, [
    									'id' => 'debug', 'class' => 'custom-control-input'
    								]) !!}
									<label class="custom-control-label" for="debug">&#160;</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<h6>{{ __('Eventos') }}</h6>
				<div id="helpLogEvents" class="row">
					<div class="col-12 col-md-6 col-lg-4 col-xl-3">
						<a href="{{ route('log-viewer::details') }}"
						   class="btn btn-danger btn-simple btn-lg btn-block text-nowrap text-truncate"
						   data-toggle="tooltip" title="{{ __('Registros de eventos del sistema') }}">
							<i class="ion ion-ios-bookmarks"></i> {{ __('Logs del sistema') }}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@section('extra-js')
	@parent
	<script>
		$(document).ready(function() {
			$('input[name=demo]').closest('.bootstrap-switch-wrapper').attr({
	            'title': '{{ __('Establecer la aplicación en modo demostración') }}',
	            'data-toggle': 'tooltip'
	        }).tooltip({
	        	trigger:"hover"
	        });
	        $('input[name=maintenance]').closest('.bootstrap-switch-wrapper').attr({
	            'title': '{{ __('Establecer la aplicación en modo de mantenimiento') }}',
	            'data-toggle': 'tooltip'
	        }).tooltip({
	        	trigger:"hover"
	        });
	        $('input[name=debug]').closest('.bootstrap-switch-wrapper').attr({
	            'title': '{{ __('Mostrar eventos y/o errores de la aplicación') }}',
	            'data-toggle': 'tooltip'
	        }).tooltip({
	        	trigger:"hover"
	        });
		});
	</script>
@stop
