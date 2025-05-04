@extends('sale::layouts.master')

@section('maproute-icon')
    <i class="ion-social-usd-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-social-usd-outline"></i>
@stop

@section('maproute-actual')
    Comercialización
@stop

@section('maproute-title')
    Pagos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Pagos Registrados</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payment.register.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payment-registered-list route_list="{{ url('sale/payment/vue-list') }}"
                        route_edit="{{ url('sale/payment/edit/{id}') }}" route_delete="{{ url('sale/payment/delete') }}">
                    </payment-registered-list>
                </div>
            </div>
        </div>
    </div>
    <sale-payment-info ref="PaymentInfo">
    </sale-payment-info>
@stop

@section('extra-js')
    @parent
    <script>
        function exportData() {
            location.href = '/sale/.......';
        };
    </script>
@endsection
