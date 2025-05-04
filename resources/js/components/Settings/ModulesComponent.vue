<template>
    <div class="row">
        <!-- Modal -->
        <div id="modalDetails" class="modal fade" aria-labelledby="modalDetailsLabel" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modalDetailsLabel" class="modal-title">Detalles del módulo {{ details.name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 mb-4">
                            <div class="row justify-content-center">
                                <div class="col-6 col-md-4 col-xl-2">
                                    <img :src="moduleDetailsLogo(details)" alt="" class="img-fluid">
                                    <div class="row">
                                        <div class="col-12 pt-3 pb-2 text-yellow text-center" title="1000 instalaciones"
                                            data-toggle="tooltip">
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8 offset-xl-1">
                                    <p>{{ details.long_description }}</p>
                                    <p>{{ details.description }}</p>
                                    <div>
                                        <ul class="pl-3">
                                            <li v-for="(author, index) in details.authors" :key="index">
                                                <a :href="'mailto:'+author.email[0]">{{ author.name }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-8 col-lg-4">
                                            <button type="button" class="btn btn-info btn-simple btn-block" :disabled="checkInstalled(details.lowerName)">
                                                Instalar
                                            </button>
                                        </div>
                                        <div class="col-8 col-lg-4">
                                            <button
                                                type="button" class="btn btn-primary btn-simple btn-block" @click="disableModule(details.lowerName)" 
                                                v-if="details.enabled"
                                            >
                                                Deshabilitar
                                            </button>
                                            <button type="button" class="btn btn-success btn-simple btn-block" @click="enableModule(details.lowerName)" v-else>
                                                Habilitar
                                            </button>
                                        </div>
                                        <div class="col-8 col-lg-4" v-if="details.withSetting">
                                            <button type="button" class="btn btn-success btn-simple btn-block">
                                                Configurar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" v-if="details.requirements">
                                        <div class="col-12 mt-4">
                                            <h6 class="md-title">Requerimientos:</h6>
                                            <ul v-if="details.requirements.length > 0">
                                                <li v-for="(version, require) in details.requirements" :key="require">
                                                    <i :class="checkRequirement(require)"></i>
                                                    {{ require }} v{{ version }}
                                                </li>
                                            </ul>
                                            <p v-else>No aplica</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-8 col-md-4 col-xl-2"></div>
                                <div class="col-12 col-md-8 offset-xl-1">
                                    <h6 class="md-title text-right" title="Versión del módulo" data-toggle="tooltip">
                                        v{{ details.version }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default btn-sm btn-round btn-modal-close" type="button"
                            data-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Lista de módulos -->
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-12 col-md-8">
                    <button
                        type="button" class="btn btn-primary btn-simple btn-sm" data-toggle="tooltip"
                        title="Listar todos los módulos disponibles" @click="showAll = true; showDisabled = false; showInstalled = false;"
                    >
                        Todos
                    </button>
                    <!-- TODO: Listar módulos a partir de repositorio de módulos -->
                    <!--<button
                        type="button" class="btn btn-primary btn-simple btn-sm" data-toggle="tooltip"
                        title="Listar sólo los módulos que no están instalados en la aplicación"
                    >
                        Sin instalar
                    </button>-->
                    <button
                        type="button" class="btn btn-primary btn-simple btn-sm" data-toggle="tooltip"
                        title="Listar sólo los módulos instalados en la aplicación" @click="showInstalled = true; showDisabled = false; showAll = false;"
                    >
                        Instalados
                    </button>
                    <button
                        type="button" class="btn btn-primary btn-simple btn-sm" data-toggle="tooltip"
                        title="Listar sólo los módulos instalados y deshabilitados" @click="showDisabled = true; showInstalled = false; showAll = false;"
                    >
                        Deshabilitados
                    </button>
                </div>
                <div class="col-12 col-md-4">
                    <div class="input-group input-sm">
                        <input placeholder="Buscar módulo..." data-toggle="tooltip" type="text"
                        data-original-title="Escriba el nombre o descripción del módulo que desea buscar"
                        class="form-control">
                        <span class="input-group-addon">
                            <i class="now-ui-icons ui-1_zoom-bold"></i>
                        </span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-4" v-for="(module, index) in filteredModules" :key="index">
                    <div class="list-group list-group-modules">
                        <a
                            href="javascript:void(0);" class="list-group-item" data-toggle="tooltip"
                            :data-original-title="'Ver detalles del módulo de ' + module.name"
                            @click="viewDetails(module.alias)"
                        >
                            <div class="media">
                                <div class="media-left">
                                    <img :src="moduleLogo(module)" alt="logotipo / imagen" class="img-fluid">
                                </div>
                                <div class="media-middle media-body">
                                    <h5 class="media-heading">{{ module.name }}</h5>
                                    <small class="text-muted">{{ module.description }}</small>
                                </div>
                                <div class="media-middle media-right">
                                    <span class="badge badge-success badge-small" v-if="module.installed">Instalado</span>
                                    <span class="badge badge-danger badge-small" v-if="module.disabled">Deshabilitado</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                details: {},
                showInstalled: false,
                showDisabled: false,
                showAll: true,
                listModules: this.modules
            }
        },
        props: ['modules'],
        computed: {
            filteredModules() {
                return this.listModules.filter(module => this.showAll || (this.showInstalled && module.installed) || (this.showDisabled && module.disabled) );
            }
        },
        methods: {
            /**
             * Obtiene los logos de los módulos listados
             *
             * @method     moduleLogo
             *
             * @author     Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
             *
             * @param      {Array}         module    Nombre del módulo del cual se desea obtener el logo
             */
            moduleLogo(module) {
                return `${process.env.MIX_APP_URL}/${module[0].logo}`;
            },
            /**
             * Obtiene el logo del módulo seleccionado
             *
             * @method     moduleDetailsLogo
             *
             * @author     Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
             *
             * @param      {string}         module    Nombre del módulo del cual se desea obtener el logo
             */
            moduleDetailsLogo(module) {
                let logo = (typeof module.logo !== 'undefined' && module.logo) ? module.logo : 'images/default-avatar.png';
                return `${process.env.MIX_APP_URL}/${logo}`;
            },
            /**
             * Obtiene información acerca del módulo seleccionado
             *
             * @method     viewDetails
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param      {string}         module    Nombre del módulo del cual se desea obtener información
             */
            viewDetails(module) {
                const vm = this;
                axios.post(`${process.env.MIX_APP_URL}/modules/details`, {
                    module: module
                }).then(response => {
                    vm.details = response.data.details;
                    $('#modalDetails').modal('show');
                }).catch(error => {
                    console.warn(error);
                });
            },
            /**
             * Reinicia los valores para ver detalles de los módulos
             *
             * @method     resetDetails
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            resetDetails() {
                const vm = this;
                vm.details = {};
            },
            /**
             * Verifica si se cumplen o no los requerimientos del módulo
             *
             * @method     checkRequirement
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param      {string}     moduleName    Nombre del módulo del cual se van a verificar los requerimientos
             *
             * @return     {string}     Estilo a mostrar en el icono que representa si se cumple el requerimiento
             */
            checkRequirement(moduleName) {
                return 'fa fa-check-square-o';
            },
            /**
             * Verifica si el módulo está instalado o no
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param   {string}  moduleName  Nombre del módulo del cual se va a verificar si está instalado
             *
             * @return  {boolean}             Determina si el módulo está instalado
             */
            async checkInstalled(moduleName) {
                return await axios.get(`${process.env.MIX_APP_URL}/modules/check/installed/${moduleName}`);
            },
            async disableModule(moduleName) {
                const disabled = await axios.get(`${process.env.MIX_APP_URL}/modules/disable/${moduleName}`);
                if (disabled) {
                    this.listModules = this.listModules.map(module => {
                        if (module.alias === moduleName) {
                            return { ...module, enabled: disabled };
                        }
                        return module;
                    });
                    this.showMessage('success', 'El módulo ha sido deshabilitado exitosamente.');
                }
                this.details.enabled = disabled;
                return disabled;
            },
            async enableModule(moduleName) {
                const enabled = await axios.get(`${process.env.MIX_APP_URL}/modules/enable/${moduleName}`);
                if (enabled) {
                    this.listModules = this.listModules.map(module => {
                        if (module.alias === moduleName) {
                            return { ...module, enabled: enabled };
                        }
                        return module;
                    });
                    this.showMessage('success', 'El módulo ha sido habilitado exitosamente.');
                }
                this.details.enabled = enabled;
                return enabled;
            }
        },
        mounted() {
            $("[data-toggle=tooltip]").tooltip();
        }
    };
</script>
