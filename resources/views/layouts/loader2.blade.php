<!-- Loader 2 -->
<div
    id="loader"
    class="d-none"
    style="
        background-color: rgba(255, 255, 255, .6);
        position:fixed;
        z-index:9999;
        width:100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.3);
    "
>
    <div
        class="preloader-content2"
        style="
            background: rgba(255, 255, 255, .8);
            width:30%;
            margin: 15% auto;
        "
    >
        <p
            class="text-center"
            id="loading-message"
            style="padding-top: 20px;"
        >
            {{ __('Su petición se está cargando. Por favor espere') }}
        </p>
        <div class="lds-css ng-scope">
            <div
                style="
                    width:100%;
                    height:100%
                "
                class="lds-double-ring"
            >
                <div></div>
                <div></div>
                <div>
                    <div></div>
                </div>
                <div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Final de Loader 2 -->