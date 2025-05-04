/** Corrige el uso del campo de búsqueda en elementos select2 en ventanas modales con el uso de bootstrap 4 */
$.fn.modal.Constructor.prototype._enforceFocus = function() {};

/** Instrucciones a ejecutar una vez se haya cargado la página */
$(document).ready(function() {
    /** Tooltip para opciones de la barra de navegación superior */
    $('.dropdown-toggle').tooltip({ delay: { hide: 100 } });
    $('.dropdown-toggle').on('click', function() {
        $('.tooltip:last').remove();
        $(this).tooltip({ delay: { hide: 100 } });
    });
    $('.dropdown-toggle').on('shown.bs.tooltip', function() {
        setTimeout(function() {
            $('.dropdown-toggle').tooltip('hide');
        }, 1500);
    });

    /** Maximizar / minimizar panel de menú izquierdo */
    $('.menu-collapse').click(function() {
        if (!$('body').hasClass('hidden-left')) {
            if ($('.container-left').hasClass('collapsed')) {
                $('.container-left, .content-wrapper').removeClass('collapsed');
                $(this).find('i').removeClass('arrows-1_minimal-right');
                $(this).find('i').addClass('arrows-1_minimal-left');
                $('.menu-option').removeClass('text-center');
                $('.menu-option a i').removeClass('fa-2x');
                $('.menu-option a span').show("slow");
                $('.menu-collapse').attr("data-original-title", "Minimizar panel de menú");
            } else {
                $('.container-left, .content-wrapper').addClass('collapsed');
                $(this).find('i').removeClass('arrows-1_minimal-left');
                $(this).find('i').addClass('arrows-1_minimal-right');
                $('.children').hide("slow"); // hide sub-menu if leave open
                $('.menu-option').addClass('text-center');
                $('.menu-option a i').addClass('fa-2x');
                $('.menu-option a span').hide("slow");
                $('.menu-collapse').attr("data-original-title", "Maximizar panel de menú");
            }
            $('.menu-collapse').tooltip();
            setTimeout(function() {
                $('.menu-collapse').tooltip('hide');
            }, 2000);
        } else {
            if (!$('body').hasClass('show-left')) {
                $('body').addClass('show-left');
            } else {
                $('body').removeClass('show-left');
            }
        }
        return false;
    });


    if ($('select').length) {
        /** Implementación del plugin selec2 para los elementos del DOM de tipo Select */
        $('select:not([id^="VueTables__limit_"])').select2({});
        $('.select2').attr({
            'title': 'Seleccione un registro de la lista',
            'data-toggle': 'tooltip'
        });
        $('.select2').tooltip({ delay: { hide: 100 } });
        $('.select2').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.select2').tooltip('hide');
            }, 1500);
        });
    }

    // Close card
    $('.card .card-close').click(function() {
        $(this).closest('.card').fadeOut(200);
        return false;
    });

    // Minimize Panel
    $('.card .card-minimize').click(function() {
        let el = $(this);
        var p = el.closest('.card');
        $('.tooltip:last').remove();

        if (!$(this).hasClass('maximize')) {
            p.find('hr').addClass('nodisplay');
            p.find('.card-body, .card-footer').fadeOut('fast');
            el.addClass('maximize');
            el.find('i').removeClass('arrows-1_minimal-up').addClass('arrows-1_minimal-down');
            el.attr('data-original-title', 'Maximize Panel').tooltip({ delay: { hide: 100 } });
        } else {
            p.find('hr').removeClass('nodisplay');
            p.find('.card-body, .card-footer').fadeIn('fast');
            el.removeClass('maximize');
            el.find('i').removeClass('arrows-1_minimal-down').addClass('arrows-1_minimal-up');
            el.attr('data-original-title', 'Minimize Panel').tooltip({ delay: { hide: 100 } });
        }

        el.on('shown.bs.tooltip', function() {
            setTimeout(function() {
                el.tooltip('hide');
            }, 1500);
        });

        return false;
    });

    /** Maximinizar / Minimizar secciones */
    if ($(".btn-collapse").length) {
        $(".btn-collapse").on("click", function() {
            if ($(this).hasClass('collapsed')) {
                $(this).find("i").removeClass('arrows-1_minimal-down');
                $(this).find("i").addClass('arrows-1_minimal-up');
            }
            else {
                $(this).find("i").removeClass('arrows-1_minimal-up');
                $(this).find("i").addClass('arrows-1_minimal-down');
            }
        });
        $(".btn-collapse").tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-collapse').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.close').tooltip('hide');
            }, 1500);
        });
    }

    /** Implementación de sliders sencillos */
    if ($('#sliderRegular').length) {
        /** @type {Object} [Estilos personalizados para el uso de esliders] */
        var slider = document.getElementById('sliderRegular');
        noUiSlider.create(slider, {
            start: 40,
            connect: [true, false],
            range: {
                min: 0,
                max: 100
            }
        });
    }

    /** Implementación de sliders dobles */
    if ($('#sliderDouble').length) {
        var slider2 = document.getElementById('sliderDouble');
        noUiSlider.create(slider2, {
            start: [20, 60],
            connect: true,
            range: {
                min: 0,
                max: 100
            }
        });
    }

    /** Tooltips personalizados */
    if ($('.close').length) {
        $('.close').attr({
            'title': 'Presione para cerrar la ventana',
            'data-toggle': 'tooltip',
            'data-placement': 'left',
        });
        $('.close').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.close').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.close').tooltip('hide');
            }, 1500);
        });
    }

    if ($('.btn-modal-close').length) {
        $('.btn-modal-close').attr({
            'title': 'Presione para cerrar la ventana',
            'data-toggle': 'tooltip'
        });
        $('.btn-modal-close').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-modal-close').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-modal-close').tooltip('hide');
            }, 1500);
        });
    }

    if ($('.btn-modal-clear').length) {
        $('.btn-modal-clear').attr({
            'title': 'Presione para reestablecer los campos del formulario',
            'data-toggle': 'tooltip'
        });
        $('.btn-modal-clear').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-modal-clear').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-modal-clear').tooltip('hide');
            }, 1500);
        });
    }

    if ($('.btn-modal-save').length) {
        $('.btn-modal-save').attr({
            'title': 'Presione para guardar el registro',
            'data-toggle': 'tooltip'
        });
        $('.btn-modal-save').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-modal-save').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-modal-save').tooltip('hide');
            }, 1500);
        });
    }
    if ($('.btn-add-record').length) {
        $('.btn-add-record').attr({
            'title': 'Agregar un nuevo registro',
            'data-toggle': 'tooltip'
        });
        $('.btn-add-record').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-add-record').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-add-record').tooltip('hide');
            }, 1500);
        });
    }
    if ($('.btn-tooltip').length) {
        $('.btn-tooltip').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-tooltip').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-tooltip').tooltip('hide');
            }, 1500);
        });
    }
    if ($('.btn-file').length) {
        $('.btn-file').attr({
            'title': 'Seleccione un archivo a cargar',
            'data-toggle': 'tooltip'
        });
        $('.btn-file').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
        $('.btn-file').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.btn-file').tooltip('hide');
            }, 1500);
        });
    }

    /** Reemplazo de icono indicador en el menú del panel izquierdo */
    $('.submenu-indicator').html('<i class="ion-ios-arrow-left text-center text-info"></i>');

    /** Acciones para ocultar los mensajes tooltip cuando se posiciona o se hace clic en otro elemento */
    $('a').on('hover, click', function() {
        $('.tooltip:last').remove();
        $('.tooltip:last').tooltip({
            trigger: "hover",
            delay: { hide: 100 }
        });
    });

    /** Formularios */
    $('form').each(function() {
        if ($(this).find('.is-required').length) {
            $(this).find('.card-body').prepend(
                "<div class='row' style='margin:10px 0'>" +
                "<div class='col-12 form-group'>" +
                "<span class='text-muted'>" +
                "Los campos con <span class='text-required'>*</span> son obligatorios" +
                "</span>" +
                "</div>" +
                "</div>"
            );
        }
    });

    /** Campos del tipo Fecha */
    if ($('input[type=date]').length) {
        !$('input[type=date]').on('keydown', () => false);
        /** Establece la fecha límite a seleccionar si no esta presente la clase no-restrict */
        if (!$('input[type=date]').hasClass('no-restrict') && !$('input[type=date]').hasClass('fiscal-year-restrict')) {
            let today = new Date();
            let dd = today.getDate();
            let mm = today.getMonth() + 1;
            let yyyy = today.getFullYear();
            if(dd<10) {
                dd='0'+dd;
            }
            if(mm<10) {
                mm='0'+mm;
            }
            let now = `${yyyy}-${mm}-${dd}`;
            $('input[type=date]').attr('max', now);
        }

        const timeOpen = setTimeout(restrictDateByFiscalYear, 2000);
        function restrictDateByFiscalYear () {
            if ($('input[type=date]').hasClass('fiscal-year-restrict')) {
                let today = new Date();
                let dd = today.getDate();
                let mm = today.getMonth() + 1;
                let yyyy = today.getFullYear();
                if(dd<10) {
                    dd='0'+dd;
                }
                if(mm<10) {
                    mm='0'+mm;
                }
                if (yyyy > window.execution_year) {
                    dd = 31;
                    mm = 12;
                    yyyy = window.execution_year;
                }

                let now = `${yyyy}-${mm}-${dd}`;

                $('input[type=date]').attr('max', now);
            }
        }
    }

    if ($('.datatable').length) {
        /** Configuración de atributos para tablas con datatable */
        $.extend($.fn.dataTableExt.oStdClasses, {
            "sFilterInput": "form-control input-sm",
            "sLengthSelect": "input-sm select2",
        });
        dt_options = {
            "dom": '<"row"<"col-sm-6"f><"col-sm-6"l>>tip',
            "language": {
                //"url": "http://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "Lo sentimos - no existen registros",
                "infoEmpty": "No hay registros disponibles",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Página _PAGE_ de _PAGES_",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "infoThousands": ",",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "infoPostFix": "",
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            "ordering": true,
            "order": [
                [0, 'asc']
            ],
            "bDestroy": true,
            "bPaginate": true,
            "bInfo": true,
            "bAutoWidth": false,
            "initComplete": function(settings, json) {
                $('div.dataTables_wrapper div.dataTables_filter').css('text-align', 'left');
                $('div.dataTables_wrapper div.dataTables_length').css('text-align', 'right');
                $('.dataTables_length select').select2();
            }
        };
        $('.datatable').dataTable(dt_options);
        $('.dataTables_length .selection').attr({
            'title': 'Seleccione la cantidad de registros a mostrar por cada página',
            'data-toggle': 'tooltip'
        });
        $('.dataTables_length .selection').tooltip({ delay: { hide: 100 } });
        $('.dataTables_length .selection').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.dataTables_length .selection').tooltip('hide');
            }, 1500);
        });
        $('.dataTables_filter input').attr({
            'title': 'Indique los datos del registro a buscar',
            'data-toggle': 'tooltip'
        });
        $('.dataTables_filter input').tooltip({ delay: { hide: 100 } });
        $('.dataTables_filter input').on('shown.bs.tooltip', function() {
            setTimeout(function() {
                $('.dataTables_filter input').tooltip('hide');
            }, 1500);
        });
        $('.modal').on('hidden.bs.modal', function() {
            $("input[class^='VueTables__search']").val('');
        });
    }

    /** Gestiona elementos de tablas VueTables */
    if ($('.VueTables__search__input').length > 0 && !$('.VueTables__search__input').hasClass('input-sm')) {
        $('.VueTables__search__input').addClass('input-sm');
    }

    /** Evento que permite mostrar datos sobre la aplicación (acerca de) */
    $('.about_app').on('click', function(e) {
        e.preventDefault();
        const d = new Date();
        const appInfo = new AppInfo([
            {
                name: 'Roldan Vargas',
                email: '<a href="mailto:rvargas@cenditel.gob.ve">rvargas@cenditel.gob.ve</a> | '+
                       '<a href="mailto:roldandvg@gmail.com">roldandvg@gmail.com</a>',
                group: `Lider de proyecto / Diseño / Desarrollo / Autor / Director de Desarrollo (2021 - ${d.getFullYear()})`
            },
            {
                name: 'Cipriano Alvarado',
                email: '<a href="mailto:calvarado@cenditel.gob.ve">calvarado@cenditel.gob.ve</a>',
                group: 'Diseñadores'
            },
            {
                name: 'Jessica Ferreira',
                email: '<a href="mailto:jferreira@cenditel.gob.ve">jferreira@cenditel.gob.ve</a>',
                group: 'Diseñadores'
            },
            {
                name: 'Julie Vera',
                email: '<a href="mailto:jvera@cenditel.gob.ve">jvera@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'María González',
                email: '<a href="mailto:mgonzalez@cenditel.gob.ve">mgonzalez@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'María Rujano',
                email: '<a href="mailto:mrujano@cenditel.gob.ve">mrujano@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Mariangel Molero',
                email: '<a href="mailto:mmolero@cenditel.gob.ve">mmolero@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Francisco Berbesí',
                email: '<a href="mailto:fberbesi@cenditel.gob.ve">fberbesi@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Hildayra Colmenares',
                email: '<a href="mailto:hcolmenares@cenditel.gob.ve">hcolmenares@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Kleivymar Montilla',
                email: '<a href="mailto:kmontilla@cenditel.gob.ve">kmontilla@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Alberto Gil',
                email: '<a href="mailto:rgil@cenditel.gob.ve">rgil@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'María Morales',
                email: '<a href="mailto:mmorales@cenditel.gob.ve">mmorales@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Dianicth Monsalve',
                email: '<a href="mailto:dsmonsalve@cenditel.gob.ve">dsmonsalve@cenditel.gob.ve</a>',
                group: 'Analistas'
            },
            {
                name: 'Luis Ramírez',
                email: '<a href="mailto:lramirez@cenditel.gob.ve">lramirez@cenditel.gob.ve</a>',
                group: 'Manuales'
            },
            {
                name: 'Marilyn Caballero',
                email: '<a href="mailto:mcaballero@cenditel.gob.ve">mcaballero@cenditel.gob.ve</a>',
                group: 'Manuales'
            },
            {
                name: 'William Páez',
                email: '<a href="mailto:wpaez@cenditel.gob.ve">wpaez@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Henry Paredes',
                email: '<a href="mailto:hparedes@cenditel.gob.ve">hparedes@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Juan Rosas',
                email: '<a href="mailto:jrosas@cenditel.gob.ve">jrosas@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Yennifer Ramírez',
                email: '<a href="mailto:yramirez@cenditel.gob.ve">yramirez@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Pedro Buitrago',
                email: '<a href="mailto:pbuitrago@cenditel.gob.ve">pbuitrago@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Argenis Osorio',
                email: '<a href="mailto:aosorio@cenditel.gob.ve">aosorio@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Angelo Osorio',
                email: '<a href="mailto:adosorio@cenditel.gob.ve">adosorio@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Daniel Contreras',
                email: '<a href="mailto:dcontreras@cenditel.gob.ve">dcontreras@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Francisco Ruiz',
                email: '<a href="mailto:fruiz@cenditel.gob.ve">fruiz@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Francisco Escala',
                email: '<a href="mailto:fescala@cenditel.gob.ve">fescala@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'José Briceño',
                email: '<a href="mailto:jbriceno@cenditel.gob.ve">jbriceno@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Fabian Palmera',
                email: '<a href="mailto:fpalmera@cenditel.gob.ve">fpalmera@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Manuel Zambrano',
                email: '<a href="mailto:mzambrano@cenditel.gob.ve">mzambrano@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'José Puentes',
                email: '<a href="mailto:jpuentes@cenditel.gob.ve">jpuentes@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Juan Vizcarrondo',
                email: '<a href="mailto:jvizcarrondo@cenditel.gob.ve">jvizcarrondo@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Miguel Narváez',
                email: '<a href="mailto:mnarvaez@cenditel.gob.ve">mnarvaez@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Oscar J. González',
                email: '<a href="mailto:ojgonzalez@cenditel.gob.ve">ojgonzalez@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Pedro Contreras',
                email: '<a href="mailto:pmcontreras@cenditel.gob.ve">pmcontreras@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Natanael Rojo',
                email: '<a href="mailto:nrojo@cenditel.gob.ve">nrojo@cenditel.gob.ve</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Marco Ocanto - CNTI',
                email: '<a href="mailto:sanchezmarco8882@gmail.com">sanchezmarco8882@gmail.com</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Jonathan Alvarado - CNTI',
                email: '<a href="mailto:jonathanalvarado1407@gmail.com">jonathanalvarado1407@gmail.com</a>',
                group: 'Desarrolladores'
            },
            {
                name: 'Laura Colina',
                email: '<a href="mailto:lcolina@cenditel.gob.ve">lcolina@cenditel.gob.ve</a>',
                group: 'Director(a) de Desarrollo (2020)'
            },
            {
                name: 'Argenis Osorio',
                email: '<a href="mailto:adosorio@cenditel.gob.ve">aosorio@cenditel.gob.ve</a>',
                group: 'Director de Desarrollo (2018-2019)'
            },
            {
                name: 'Santiago Roca',
                email: '<a href="mailto:sroca@cenditel.gob.ve">sroca@cenditel.gob.ve</a>',
                group: 'Colaborador'
            }
        ]);
        bootbox.alert({
            className: 'modal-credits',
            closeButton: false,
            message: appInfo.showAbout(),
            buttons: {
                ok: {
                    label: "OK",
                    className: 'btn-primary'
                }
            },
        });

        $('.bootbox.modal [data-bb-handler="ok"]').attr('title', 'Haga clic para cerrar esta ventana');
        $('.bootbox.modal [data-bb-handler="ok"]').attr('data-toggle', 'tooltip');
        $('.bootbox.modal [data-bb-handler="ok"]').tooltip();
        $('.bootbox.modal a').tooltip();
    });

    /** Evento que permite mostrar datos sobre el licenciamiento de la aplicación */
    $('.license_app').on('click', function(e) {
        e.preventDefault();
        const appInfo = new AppInfo([
            { name: 'Roldan Vargas' },
            { name: 'Cipriano Alvarado' },
            { name: 'Jessica Ferreira' },
            { name: 'Julie Vera' },
            { name: 'María González' },
            { name: 'María Rujano' },
            { name: 'Mariangel Molero' },
            { name: 'Francisco Berbesí' },
            { name: 'Luis Ramírez' },
            { name: 'Hyildayra Colmenares' },
            { name: 'Kleivymar Montilla' },
            { name: 'Alberto Gil' },
            { name: 'María Morales' },
            { name: 'Dianicth Monsalve' },
            { name: 'Marilyn Caballero' },
            { name: 'William Páez' },
            { name: 'Henry Paredes' },
            { name: 'Juan Rosas' },
            { name: 'Yennifer Ramírez' },
            { name: 'Pedro Buitrago' },
            { name: 'Argenis Osorio' },
            { name: 'Angelo Osorio' },
            { name: 'Daniel Contreras' },
            { name: 'Laura Colina' },
            { name: 'Santiago Roca' },
            { name: 'José Puentes' },
            { name: 'Juan Vizcarrondo' },
            { name: 'Fabian Palmera' },
            { name: 'Manuel Zambrano' },
            { name: 'Oscar J. González' },
            { name: 'Pedro Contreras' },
            { name: 'Miguel Narváez' },
            { name: 'Marco Ocanto' },
            { name: 'Jonathan Alvarado' },
            { name: 'Francisco Ruíz' },
            { name: 'Francisco Escala' },
            { name: 'José Briceño' },
            { name: 'Natanael Rojo' },
        ]);
        bootbox.alert({
            className: 'modal-credits',
            closeButton: false,
            message: appInfo.showLicense()
        });

        $('.bootbox.modal [data-bb-handler="ok"]').attr('title', 'Haga clic para cerrar esta ventana');
        $('.bootbox.modal [data-bb-handler="ok"]').attr('data-toggle', 'tooltip');
        $('.bootbox.modal [data-bb-handler="ok"]').tooltip();
        $('.bootbox.modal a').tooltip();
    });

    /** Oculta el tooltip de los elementos bootstrap switch después de unos segundos */
    $('.bootstrap-switch').on('shown.bs.tooltip', function() {
        setTimeout(function() {
            $('.bootstrap-switch').tooltip('hide');
        }, 1500);
    });

    /**
     * Función que realiza la verificación de la frase de paso del certificado
     * p12 guardado en el modulo de Firma electrónica.
     *
     * @author Ing. Angelo Osorio  <adosorio@cenditel.gob.ve> | <adosorio@gmail.com>
     */
    $('#verify-modal').click(function() {
        let data = { 'passphrase': $('#phasepass-modal').val() };
        axios.post('/digitalsignature/validateAuthApi', data).then(function (response) {
            if (response.data.auth === true) {
                $('#signed-modal').removeClass('d-none');
                $('#authentication').addClass('d-none');
            } else {
                $('#authentication').removeClass('d-none');
                $('#signed-modal').addClass('d-none');
            }
        }).catch(error => {
            if (typeof(error.response) !="undefined") {
                for (var index in error.response.data.errors) {
                    if (error.response.data.errors[index]) {
                        vm.errors.push(error.response.data.errors[index][0]);
                    }
                }
            }
        });
    });

    /**
     * Función que define el atributo href para dirigir a la documentación
     * de usuario según la ubicación en el sistema
     *
     * @author Luis Ramírez  <lgramirez@cenditel.gob.ve>
     */
    $('#list_options_user').on('click', function(){
        let link = document.getElementById('doc-user');
        let path= window.location.pathname.split('/');
        let location= path[1];
        let module;

        if (modules) {
            modules.forEach(element => {
                if (location === element.toLowerCase()) {
                    module = element.toLowerCase();
                    return module;
                }
            });
            if (module) {
                link.href= `${app_url}`+'/docs/user'+'_' +`${module}`+'/';
            }else{
                link.href= `${app_url}`+'/docs/user/';
            }
        }else {
            link.href= `${app_url}`+'/docs/user/';
        }
    });

});

/**
 * Permite mostrar alerta de mensajes de acciones realizadas con vue o js
 *
 * @author Ing. Roldan Vargas  <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @param  {string} msg_title  Título de la ventana de alerta
 * @param  {string} msg_class  Clase de estilo a usar en la ventana de alerta
 * @param  {string} msg_icon   Ícono a usar en la ventana de alerta
 * @param  {string} msg_custom Mensaje personalizado
 * @param  {string} type       Tipo de mensaje a mostrar (store|update|destroy)
 */
function gritter_messages(msg_title, msg_class, msg_icon, type, msg_custom) {
    msg_title = (!msg_title) ? 'Éxito' : msg_title;
    msg_class = (!msg_class) ? 'growl-success' : 'glowl-' + msg_class;
    msg_icon = (!msg_icon) ? 'screen-ok' : msg_icon;

    var msg_text = (!msg_custom) ? '' : msg_custom;
    if (type == 'store') {
        msg_text = 'Registro almacenado con éxito';
    } else if (type == 'update') {
        msg_text = 'Registro actualizado con éxito';
    } else if (type == 'destroy') {
        msg_text = 'Registro eliminado con éxito';
    } else if (type === 'load') {
        msg_text = 'Los datos fueron cargados correctamente';
    }

    $.gritter.add({
        title: msg_title,
        text: msg_text,
        class_name: msg_class,
        image: "/images/" + msg_icon + ".png",
        sticky: false,
        time: ''
    });
}

/**
 * Función que permite eliminar registros mediante ajax
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @param {string} url URL del controlador que realiza la acción de eliminación
 * @return Un mensaje al usuario solicitando confirmación de la eliminación del registro
 */
function delete_record(url) {
    bootbox.confirm('Esta seguro de querer eliminar este registro?', function(result) {
        if (result) {
            /** Ajax config csrf token */
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            /** Ajax delete record */
            $.ajax({
                type: 'DELETE',
                cache: false,
                dataType: 'JSON',
                url: url,
                data: {},
                success: function(data) {
                    if (data.result) {
                        location.reload();
                    }
                },
                error: function(jqxhr, textStatus, error) {
                    var err = textStatus + ", " + error;
                    bootbox.alert('Error interno del servidor al eliminar el registro.');
                    logs('resources/js/custom.js', 406, `Error con la petición solicitada. Detalles: ${err}`, 'delete_record');
                }
            });
        }
    });
}






