<template>
    <a
        class="dropdown-item"
        href="javascript:void(0)"
        id="lock-screen-option"
        @click="lockScreenNow"
        title="Bloquear pantalla de la aplicación"
        data-toggle="tooltip"
        data-placement="left"
    >
        <i class="ion-android-lock"></i>Bloquear Pantalla
    </a>
</template>

<script>
export default {
    data() {
        return {};
    },
    async mounted() {
        let vm = this;
        /** @type {Object} Datos del usuario para el bloqueo de pantalla por inactividad */
        const response = await axios.get(`${window.app_url}/get-lockscreen-data`);
        vm.lockscreen.lock = response.data.lock_screen;
        vm.lockscreen.time = response.data.time_lock;
        vm.loadLockScreen = true;
        var initScreenLock = function() {
            if (!$(".modal-lockscreen").is(':visible')) {
                window.screen_locked = false;
                vm.lockscreen.lock = false;
                vm.lockScreen();
            }
        }

        /**
         * Evento que detecta las pulsaciones del teclado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param   {String}    keydown   Evento al presionar una tecla
         * @param   {Callback}  $event    Callback de la función
         */
        document.addEventListener('keydown', initScreenLock, true);
        document.addEventListener('scroll', initScreenLock, true);
        document.addEventListener('mouseleave', initScreenLock, true);
    },
};
</script>
