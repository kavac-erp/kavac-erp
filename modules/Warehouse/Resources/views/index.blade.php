@section('modules-js')
    @parent
    {!! Html::script(mix('modules/warehouse/js/app.js'), [], Request::secure()) !!}
@endsection
@permission('warehouse.dashboard')
    {{-- Minimo de Productos --}}
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardWarehouseInventoryStatus">
                <div class="card-header">
                    <h6 class="card-title">
                        Estado del Inventario de Almacén
                        @include('buttons.help', [
                            'helpId' => 'WarehouseInventory',
                            'helpSteps' => get_json_resource(
                                'ui-guides/dashboard/inventory_status.json',
                                'warehouse'),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <section id="helpWarehouseInventoryStatusLeyend">
                        <div class="row">
                            <div class="col-12">
                                <span class="text-muted">
                                    A continuación se muestra la disponibilidad de
                                    los productos inventariados en el almacén.
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 panel-legend" id="helpWarehouseInventoryStatusLeyendHigh">
                                <i class="ion-android-checkbox-blank text-success"
                                    title="El nivel de existencia del producto es elevado" data-toggle="tooltip"></i>
                                <span>Alto</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 panel-legend" id="helpWarehouseInventoryStatusLeyendMedium">
                                <i class="ion-android-checkbox-blank text-warning"
                                    title="El nivel de existencia del producto es intermedio" data-toggle="tooltip">
                                </i>
                                <span>Medio</span>
                            </div>
                        </div>
                        <div class="row mg-bottom-20">
                            <div class="col-md-4 panel-legend" id="helpWarehouseInventoryStatusLeyendLow">
                                <i class="ion-android-checkbox-blank text-danger"
                                    title="El nivel de existencia del producto se está agotando" data-toggle="tooltip"></i>
                                <span>Bajo</span>
                            </div>
                        </div>
                    </section>
                    <hr>
                    <warehouse-dashboard-product-list route_list="warehouse/dashboard/vue-list-min-products">
                    </warehouse-dashboard-product-list>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardWarehouseOperationsHistoryList">
                <div class="card-header">
                    <h6 class="card-title">
                        Histórico de Operaciones del Módulo de Almacén
                        @include('buttons.help', [
                            'helpId' => 'warehouseOperationsHistory',
                            'helpSteps' => get_json_resource(
                                'ui-guides/dashboard/operations_history.json',
                                'warehouse'),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <warehouse-operations-history-list route_list="warehouse/dashboard/operations/vue-list">
                    </warehouse-operations-history-list>
                </div>
            </div>
        </div>
    </div>
@endpermission
