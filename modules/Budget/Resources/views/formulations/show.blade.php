@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    {{ __('Presupuesto') }}
@stop

@section('maproute-title')
    {{ __('Formulación') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Formulación de Presupuesto') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => route('budget.subspecific-formulations.index')])
                        @include('buttons.print', ['route' => route('print.formulated', ['id' => $formulation->id])])
                        <a href="{{ route('export', ['id' => $formulation->id]) }}" class="btn btn-sm btn-primary btn-custom" data-toggle="tooltip"
                        title="{{ __('Exportar registro') }}" target="_blank">
                            <i class="fa fa-file-excel-o"></i>
                        </a>

                        @if ($enable)
                            {{-- @include('buttons.sign', ['route' => route('print.formulatedsign', ['id' => $formulation->id])]) --}}
                        @endif
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title text-center">{{ __('Oficina de programación y Presupuesto') }}</h5>
                    <h6 class="card-title text-center">{{ __('Presupuesto de Gastos por Sub Específicas') }}</h6>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">
                            {{ ($formulation->assigned)?__('Presupuesto Asignado'):__('Asignar Presupuesto') }}:
                        </div>
                        <div class="col-9">
                            <label>
                                {!! Form::open([
                                    'route' => ['budget.subspecific-formulations.update', $formulation->id],
                                    'method' => 'PUT', 'id' => 'form_assign'
                                ]) !!}
                                    {!! Form::token() !!}
                                    <div class="custom-control custom-switch" data-toggle="tooltip"
                                        title="{{ __('Asignar presupuesto') }}">
                                        {!! Form::checkbox('assigned', true, ($formulation->assigned), [
                                            'class' => 'custom-control-input budget-assign', 'id' => 'assigned',
                                            'disabled' => ($formulation->assigned || $formulation->assigned==='1')
                                        ]) !!}
                                        <label class="custom-control-label" for="assigned"></label>
                                    </div>
                                {!! Form::close() !!}
                            </label>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Fecha de generación') }}:</div>
                        <div class="col-9">
                            {{ $formulation->date ? date("d/m/Y", strtotime($formulation->date)) : 'Sin fecha asignada' }}
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Institución') }}:</div>
                        <div class="col-9">{{ $formulation->specificAction->institution }}</div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Moneda') }}:</div>
                        <div class="col-9">{{ $formulation->currency->description }}</div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Presupuesto') }}:</div>
                        <div class="col-9">{{ $formulation->year }}</div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ $formulation->specificAction->type }}:</div>
                        <div class="col-9 text-justify">
                            {{ $formulation->specificAction->specificable->code }} -
                            {{ $formulation->specificAction->specificable->name }}
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Acción Específica') }}:</div>
                        <div class="col-9">
                            <div style="width:auto;display:inline-block;vertical-align:top">{{ $formulation->specificAction->code }} - </div>
                            <div class="text-justify" style="width:84%;display:inline-block;">{!! $formulation->specificAction->name !!}</div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Fuente de financiamiento') }}:</div>
                        <div class="col-9">
                            {{ $formulation->budgetFinancementType ? $formulation->budgetFinancementType->name : '' }}
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Tipo de financiamiento') }}:</div>
                        <div class="col-9">
                            {{ $formulation?->budgetFinancementSource ? $formulation->budgetFinancementSource->name : 'N/A' }}
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Monto del financiamiento') }}:</div>
                        <div class="col-9">
                            {{ $formulation->currency->symbol }}&#160;
                            {{ number_format($formulation->financement_amount, $formulation->currency->decimal_places, ",", ".") }}
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-3 text-bold text-uppercase">{{ __('Total Formulado') }}:</div>
                        <div class="col-9">
                            {{ $formulation->currency->symbol }}&#160;
                            {{ number_format(
                                $formulation->total_formulated, $formulation->currency->decimal_places, ",", "."
                            ) }}
                        </div>
                    </div>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Código') }}</th>
                                <th>{{ __('Denominación') }}</th>
                                <th>{{ __('Total Año') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($formulation->accountOpens as $accountOpen)
                                <tr class="{{ ($accountOpen->budgetAccount->specific==="00")?'text-dark text-bold':'' }}">
                                    <td class="text-center">
                                        {{ $accountOpen->budgetAccount->code }}
                                    </td>
                                    <td>{{ $accountOpen->budgetAccount->denomination }}</td>
                                    <td class="text-right">
                                        {{ number_format(
                                            $accountOpen->total_year_amount,
                                            $formulation->currency->decimal_places, ",", "."
                                        ) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="text-center text-dark text-bold">
                                    {{ __('Total Formulado') }}&#160;
                                    {{ $formulation->currency->symbol }}
                                </td>
                                <td></td>
                                <td class="text-right text-dark text-bold">
                                    {{ number_format(
                                        $formulation->total_formulated, $formulation->currency->decimal_places, ",", "."
                                    ) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script>
        $(document).ready(function() {
            @if ($formulation->assigned)
                /**
                 * Muestra un mensaje al usuario en caso de que la formulación de presupuesto
                 * ya se encuentra asignada
                 */
                $.gritter.add({
                    title: '{{ __('Advertencia!') }}',
                    text: '{{ __('Este presupuesto ya se encuentra asignado y no puede ser modificado') }}',
                    class_name: 'growl-danger',
                    image: "{{ asset('images/screen-warning.png') }}",
                    sticky: false,
                    time: 2000
                });
            @endif

            $('.budget-assign').on('change', function(e) {
                var el = $(this);
                if (el.is(':checked')) {
                    bootbox.confirm(
                        '{{ __(
                            'Esta seguro de asignar esta formulación?. Una vez asignado no puede ser modificado'
                            )
                        }}',
                        function(result) {
                            if (result) {
                                $("#form_assign").submit();
                            }
                            else {
                                el.is(':checked', false);
                            }
                        }
                    );
                }
            });
        });

        var printFormulated = (id, esp) => {
            location.href = window.app_url + '/budget/print-formulated/' + id;
        };

        var export = (id, esp) => {
            location.href = window.app_url + '/budget/export/' + id;
        };
    </script>
@endsection
