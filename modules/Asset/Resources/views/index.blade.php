@section('modules-js')
    @parent
    {!! Html::script(mix('modules/asset/js/app.js'), [], Request::secure()) !!}
@endsection

{{-- Gráficos Estadísticos --}}
{{-- <div class="row">
    <div class="col-12">
        <div class="card" id="cardAssetGraph">
            <div class="card-header">
                <h6 class="card-title">
                    Gráficos del Inventario de Bienes Institucionales
                    @include('buttons.help', [
                        'helpId' => 'AssetGraph',
                        'helpSteps' => get_json_resource('ui-guides/dashboard/graph.json', 'asset')
                    ])
                </h6>
                <div class="card-btns">
                    @include('buttons.previous', ['route' => url()->previous()]) 
                    @include('buttons.minimize')
                </div>
            </div>
            <div class="card-body">
                <asset-dashboard-graphs></asset-dashboard-graphs>
            </div>
        </div>
    </div>
</div> --}}

{{-- Histórico de Operaciones --}}
@permission('asset.dashboard')
{{--
<div class="row">
	<div class="col-12">
		<div class="card" id="cardAssetOperationsHistoryList">
			<div class="card-header">
				<h6 class="card-title">
					Histórico de Operaciones del Módulo de Bienes
					@include('buttons.help', [
                        'helpId' => 'AssetOperationsHistory',
                        'helpSteps' => get_json_resource('ui-guides/dashboard/operations_history.json', 'asset')
                    ])
				</h6>
				<div class="card-btns">
					@include('buttons.previous', ['route' => url()->previous()])
					@include('buttons.minimize')
				</div>
			</div>
			<div class="card-body">
				<asset-operations-history-list
					route_list="{{ url('asset/dashboard/operations/vue-list') }}">
				</asset-operations-history-list>
			</div>
		</div>
	</div>
</div>
--}}
@endpermission
