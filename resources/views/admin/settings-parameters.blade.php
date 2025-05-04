<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h6 class="card-title">
					{{ __('Parámetros Generales') }}
					@include('buttons.help', [
						'helpId' => 'GeneralParams',
						'helpSteps' => get_json_resource('ui-guides/general_parameters.json')
					])
				</h6>
				<div class="card-btns">
					@include('buttons.previous', ['route' => url()->previous()])
					@include('buttons.minimize')
				</div>
			</div>
			{!! Form::open($header_parameters) !!}
				<div class="card-body" id="card_general_params">
					<div class="row">
						<div class="col-12">
							<h6>{{ __('Activar funciones de la aplicación') }}</h6>
						</div>
					</div>
					@if (
						$errors->has('support') || $errors->has('chat') || $errors->has('notify') || 
						$errors->has('report_banner') || $errors->has('multi_institution') || 
						$errors->has('digital_sign') || $errors->has('online')
					)
						@include('layouts.form-errors')
					@endif
					<div class="row">
						<div id="switchNotify" class="col-12 col-md-2 text-center">
							<div class="form-group">
								<label for="" class="control-label">{{ __('Notificaciones') }}</label>
								<div class="custom-control custom-switch">
									{!! Form::checkbox('notify', true,
										(!is_null($paramNotify) && $paramNotify->p_value === 'true'), [
										'id' => 'notify', 'class' => 'custom-control-input'
									]) !!}
									<label class="custom-control-label" for="notify">&nbsp;</label>
								</div>
							</div>
						</div>
						<div id="switchBannerInReport" class="col-12 col-md-2 text-center">
							<div class="form-group">
								<label for="" class="control-label">{{ __('Banner en reportes') }}</label>
								<div class="custom-control custom-switch">
									{!! Form::checkbox('report_banner', true,
										(!is_null($paramReportBanner) && $paramReportBanner->p_value === 'true'), [
										'id' => 'report_banner', 'class' => 'custom-control-input'
									]) !!}
									<label class="custom-control-label" for="report_banner">&nbsp;</label>
								</div>
							</div>
						</div>
						{{-- <div id="switchMultiInstitution" class="col-12 col-md-2 text-center">
							<div class="form-group">
								<label for="" class="control-label">
                                    {{ __('Multi gestión') }}
                                </label>
								<div class="custom-control custom-switch">
									{!! Form::checkbox('multi_institution', true,
										(
											!is_null($paramMultiInstitution)
											&& $paramMultiInstitution->p_value === 'true'
										), [
										'id' => 'multi_institution', 'class' => 'custom-control-input'
									]) !!}
									<label class="custom-control-label" for="multi_institution">&#160;</label>
								</div>
							</div>
						</div> --}}
						<div id="switchSign" class="col-12 col-md-2 text-center">
							<div class="form-group">
								<label for="" class="control-label">{{ __('Firma electrónica') }}</label>
								<div class="custom-control custom-switch">
									{!! Form::checkbox('digital_sign', true,
										(!is_null($paramDigitalSign) && $paramDigitalSign->p_value === 'true'), [
										'id' => 'digital_sign', 'class' => 'custom-control-input'
									]) !!}
									<label class="custom-control-label" for="digital_sign">&#160;</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<div class="row">
						<div id="settingParamButtons" class="col-md-3 offset-md-9">
							@include('layouts.form-buttons')
						</div>
					</div>
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
