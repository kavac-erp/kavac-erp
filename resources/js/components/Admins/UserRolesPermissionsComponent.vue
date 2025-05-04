<template>
    <div>
        <div class="card-body">
            <form-errors :listErrors="errors"></form-errors>
            <div class="row">
                <div class="col-12">
                    <h5 class="card-title text-center mt-4">Roles</h5>
                    <div class="row">
                        <div class="col-md-2 text-center" v-for="(role, index) in roles" :key="index">
                            <div class="form-group">
                                <label for="" class="control-label">{{ role.name }}</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox" class="custom-control-input" :id="'role_'+role.id" :value="role.id"
                                        v-model="record.roles" @click="setPermissionsToRole(role, $event)"
                                    >
                                    <label class="custom-control-label" :for="'role_'+role.id"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h5 class="card-title text-center mt-4">Permisos</h5>
                </div>
                <div class="col-12" style="padding:20px 0" v-for="(module, index) in modules" :key="index">
                    <hr>
                    <h6 class="card-title text-center">
                        MÓDULO [{{ getModuleName(module) }}]
                    </h6>
                    <hr>
                    <div class="row">
                        <div
                            class="col-md-3 text-center"
                            v-for="(perm, idx) in permissions.filter(p => p.model_prefix === module)"
                            :key="idx"
                        >
                            <div class="form-group">
                                <label for="" class="control-label">{{ perm.name }}</label>
                                <div class="custom-control custom-switch">
                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox" class="custom-control-input perm-switch"
                                            :id="'perm_'+perm.id" :value="perm.id"
                                            v-model="record.permissions" :checked="record.permissions.includes(perm.id)"
                                            :disabled="disablePermissions()"
                                        >
                                        <label class="custom-control-label" :for="'perm_'+perm.id"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <buttonsDisplay :route_list="app_url"></buttonsDisplay>
            <buttonsDisplay :route_list="app_url" display="false"></buttonsDisplay>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                errors: [],
                record: {
                    user: '',
                    roles: [],
                    permissions: []
                },
                modules: []
            }
        },
        props: {
            user: {
                type: Object,
                required: true
            },
            userRoles: {
                type: Array,
                required: true
            },
            userPermissions: {
                type: Array,
                required: true
            },
            roles: {
                type: Array,
                required: true
            },
            permissions: {
                type: Array,
                required: true
            }
        },
        methods: {
            reset() {
                this.record.roles = [];
                this.record.permissions = [];
            },
            redirect_back() {
                location.href = this.app_url;
            },
            disablePermissions() {
                const vm = this;

                let disablePerms = vm.record.roles.includes(
                    vm.$options.propsData.roles.filter(r => r.slug === 'admin')[0].id
                );

                if (disablePerms) {
                    $('.perm-switch').attr('title', 'No puede deshabilitar este permiso del usuario administrador');
                    $('.perm-switch').tooltip();
                }
                return disablePerms;
            },
            /**
             * Muestra el nombre de un módulo en mayúsculas
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param      {String}         module    Nombre del módulo
             *
             * @return     {String}     Devuelve el nombre del módulo en mayúsculas
             */
            getModuleName(module) {
                let m = module.toUpperCase().replace('_', ' ');
                return (m.startsWith('0')) ? m.substring(1) : m;
            },
            /**
             * Selecciona / Deselecciona los permisos asociados a un módulo
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param      {Object}     role    Datos del role y sus respectivos permisos si los tiene
             */
            setPermissionsToRole(role, e) {
                const vm = this;

                if (role.permissions.length > 0) {
                    let permissions = role.permissions.map(rp => rp.id);
                    if (e.target.checked) {
                        permissions.forEach(p => vm.record.permissions.push(p));
                    } else {
                        vm.record.permissions = JSON.parse(JSON.stringify(
                            vm.record.permissions.filter(p => permissions.includes(p.id))
                        ));
                    }
                }
            },
            async createRecord() {
                const vm = this;

                await axios.post(`${vm.app_url}/auth/assign/roles-permissions`, {
                    user: vm.record.user,
                    role: vm.record.roles,
                    permission: vm.record.permissions
                }).then(response => {
                    if (response.data.result) {
                        window.location.reload();
                    }
                }).catch(error => {
                    console.log(error);
                });
            }
        },
        async mounted() {
            const vm = this;

            vm.record.user = vm.user.id;
            vm.record.roles = await vm.userRoles.map(r => r.id);
            vm.record.permissions = await vm.userPermissions.map(p => p.id);
            vm.modules = [...new Set(vm.permissions.map(m => m.model_prefix))];
        }
    }
</script>