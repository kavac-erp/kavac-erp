<?php

namespace Modules\Asset\Repositories;

/**
 * @class      AssetParametersRepository
 * @brief      Gestiona los parámetros requeridos para el registro de biene
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetParametersRepository
{
    /**
     * Arreglo con los registros de los parámetros
     *
     * @var array $parameters
     */
    protected $parameters;

    /**
     * Arreglo con los registros de los colores
     *
     * @var array $colors
     */
    protected $colors;

    /**
     * Arreglo con los registros de los tipos de ganado
     *
     * @var array $cattleTypes
     */
    protected $cattleTypes;

    /**
     * Arreglo con los registros de los propósitos del semoviente
     *
     * @var array $purposes
     */
    protected $purposes;

    /**
     * Arreglo con los registros de los estado de ocupación del terreno
     *
     * @var array $occupancyStatus
     */
    protected $occupancyStatus;

    /**
     * Arreglo con los registros de casos de uso del terreno
     *
     * @var array $assetUseFunctions
     */
    protected $assetUseFunctions;

    /**
     * Arreglo con los registros de los géneros para el terreno
     *
     * @var array $genders
     */
    protected $genders;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return void
     */
    public function __construct()
    {
        /* Define los valores de colores a emplear en el formulario */
        $this->colors = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1,  "text" => "Negro"],
            ["id" => 2,  "text" => "Azul"],
            ["id" => 3,  "text" => "Palo Rosa"],
            ["id" => 4,  "text" => "Naranja"],
            ["id" => 5,  "text" => "Verde"],
            ["id" => 6,  "text" => "Beige"],
            ["id" => 7,  "text" => "Cromático"],
            ["id" => 8,  "text" => "Vinotinto"],
            ["id" => 9,  "text" => "Gris / Negro"],
            ["id" => 10, "text" => "Plateado"],
            ["id" => 11, "text" => "Beige Astralia"],
            ["id" => 12, "text" => "Plateado Ferroso"],
            ["id" => 13, "text" => "Perla"],
            ["id" => 14, "text" => "Beige Olímpico"],
            ["id" => 15, "text" => "Arena Mtalizado"],
            ["id" => 16, "text" => "Plata"],
            ["id" => 17, "text" => "Rojo"],
            ["id" => 18, "text" => "Amarillo"],
            ["id" => 19, "text" => "Beige Duna"],
            ["id" => 20, "text" => "Marrón / Negro"],
            ["id" => 21, "text" => "Azul / Biege"],
            ["id" => 22, "text" => "Marrón / Beige"],
            ["id" => 23, "text" => "Blanco"],
            ["id" => 24, "text" => "Gris"],
            ["id" => 25, "text" => "Marrón"],
            ["id" => 26, "text" => "Caoba"],
            ["id" => 27, "text" => "Caramelo"],
            ["id" => 28, "text" => "Verde / Blanco"],
            ["id" => 29, "text" => "Azul / Gris"],
            ["id" => 30, "text" => "Azul / Blanco"],
            ["id" => 31, "text" => "Negro / Plateado"],
            ["id" => 32, "text" => "Azul / Blanco"],
            ["id" => 33, "text" => "Amarillo / Negro"],
            ["id" => 34, "text" => "Amarillo / Blanco"],
            ["id" => 35, "text" => "Rojo / Blanco"],
            ["id" => 36, "text" => "Neveri"],
            ["id" => 37, "text" => "Madera Claro"],
            ["id" => 38, "text" => "Madera / Blanco"],
            ["id" => 39, "text" => "Gris / Blanco"],
            ["id" => 40, "text" => "Beige / Blanco"],
            ["id" => 41, "text" => "Stucco"],
            ["id" => 42, "text" => "Verde / Blanco"],
            ["id" => 43, "text" => "Azul / Negro"],
            ["id" => 44, "text" => "Negro / Naranja"],
        ];

        /* Define los valores de tipos de ganado a emplear en el formulario */
        $this->cattleTypes = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1, "text" => "Vacunos"],
            ["id" => 2, "text" => "Equinos"],
            ["id" => 3, "text" => "Caprinos"],
            ["id" => 4, "text" => "Porcinos"],
            ["id" => 5, "text" => "Aves"],
            ["id" => 6, "text" => "Búfalos"],
            ["id" => 7, "text" => "Peces"],
            ["id" => 8, "text" => "Otro tipo"],
        ];

        /* Define los valores de propositos del semoviente a emplear en el formulario */
        $this->purposes = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1, "text" => "Engorde"],
            ["id" => 2, "text" => "Ordeño"],
            ["id" => 3, "text" => "Doble propósito"],
            ["id" => 4, "text" => "Cría"],
            ["id" => 5, "text" => "Otro propósito"],
        ];

        /* Define los valores de estado de ocupación a emplear en el formulario */
        $this->occupancyStatus = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1, "text" => "Ocupado"],
            ["id" => 2, "text" => "Parcialmente ocupado"],
            ["id" => 3, "text" => "Desocupado"],
        ];

        /* Define los valores de los géneros en semovientes a emplear en el formulario */
        $this->genders = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1, "text" => "Macho"],
            ["id" => 2, "text" => "Hembra"],
        ];

        /* Define los valores de casos de uso a emplear en el formulario */
        $this->assetUseFunctions = [
            ["id" => '', "text" => "Seleccione..."],
            ["id" => 1, "text" => "Residencial"],
            ["id" => 2, "text" => "Agrícola"],
            ["id" => 3, "text" => "Turístico"],
            ["id" => 4, "text" => "Comercial"],
            ["id" => 5, "text" => "Educativo"],
            ["id" => 6, "text" => "Asistencial"],
            ["id" => 7, "text" => "De Oficina"],
            ["id" => 8, "text" => "Industrial"],
        ];

        /* Define los campos de la configuración de parámetros a emplear en el formulario */
        $this->parameters = [
            "muebles" => [
                [
                    'label' => 'Sede',
                    'name' => 'headquarter_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'headquarters',
                    'mask' => null
                ],
                [
                    'label' => 'Serial',
                    'name' => 'serial',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Marca',
                    'name' => 'brand',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Modelo',
                    'name' => 'model',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Color',
                    'name' => 'color_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'colors',
                    'mask' => null
                ],
                [
                    'label' => 'Valor de adquisición',
                    'name' => 'acquisition_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
                [
                    'label' => 'Valor residual',
                    'name' => 'residual_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
                [
                    'label' => 'Años de vida útil',
                    'name' => 'depresciation_years',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                ],
            ],
            "inmuebles" => [
                [
                    'label' => 'Sede',
                    'name' => 'headquarter_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'headquarters',
                    'mask' => null
                ],
                [
                    'label' => 'Año de construcción',
                    'name' => 'construction_year',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0, 'max':" . date("Y")
                ],
                [
                    'label' => 'Edad de construcción',
                    'name' => 'construction_age',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                ],
                [
                    'label' => 'Número de contrato del inmueble',
                    'name' => 'contract_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'RIF del comodatario',
                    'name' => 'rif',
                    'type' => 'text',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Estado de ocupación',
                    'name' => 'occupancy_status_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'occupancy_statuses',
                    'mask' => null
                ],
                [
                    'label' => 'Área de construcción',
                    'name' => 'construction_area',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                ],
                [
                    'label' => 'Unidad de medida del área de construcción',
                    'name' => 'construction_measurement_unit_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'measurement_units',
                    'mask' => null
                ],
                [
                    'label' => 'Área del terreno',
                    'name' => 'land_area',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                ],
                [
                    'label' => 'Unidad de medida del área del terreno',
                    'name' => 'land_measurement_unit_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'measurement_units',
                    'mask' => null
                ],
                [
                    'label' => 'Uso actual',
                    'name' => 'asset_use_function_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'asset_use_functions',
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de inicio del contrato',
                    'name' => 'contract_start_date',
                    'type' => 'date',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de fin del contrato',
                    'name' => 'contract_end_date',
                    'type' => 'date',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Oficina de registro del inmueble',
                    'name' => 'registry_office',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de registro del inmueble',
                    'name' => 'registration_date',
                    'type' => 'date',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Número de registro del inmueble',
                    'name' => 'registration_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Tomo',
                    'name' => 'tome',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Folio',
                    'name' => 'folio',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'País',
                    'name' => 'country_id',
                    'type' => 'select',
                    'event' => 'vm.getEstates();',
                    'required' => true,
                    'options' => 'countries',
                    'mask' => null
                ],
                [
                    'label' => 'Estado',
                    'name' => 'estate_id',
                    'type' => 'select',
                    'event' => 'vm.getMunicipalities();',
                    'required' => true,
                    'options' => 'estates',
                    'mask' => null
                ],
                [
                    'label' => 'Municipio',
                    'name' => 'municipality_id',
                    'type' => 'select',
                    'event' => 'vm.getParishes();',
                    'required' => true,
                    'options' => 'municipalities',
                    'mask' => null
                ],
                [
                    'label' => 'Parroquia',
                    'name' => 'parish_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'parishes',
                    'mask' => null
                ],
                [
                    'label' => 'Urbanización/Sector',
                    'name' => 'urbanization_sector',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Avenida/Calle',
                    'name' => 'avenue_street',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Casa/Edificio',
                    'name' => 'house',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Piso',
                    'name' => 'floor',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Localización',
                    'name' => 'location',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos norte',
                    'name' => 'north_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos sur',
                    'name' => 'south_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos este',
                    'name' => 'east_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos oeste',
                    'name' => 'west_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Coordenadas de ubicación',
                    'name' => 'location_coordinates',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Valor de adquisición',
                    'name' => 'acquisition_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
            ],
            "vehiculos" => [
                [
                    'label' => 'Sede',
                    'name' => 'headquarter_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'headquarters',
                    'mask' => null
                ],
                [
                    'label' => 'Marca',
                    'name' => 'brand',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Modelo',
                    'name' => 'model',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Color',
                    'name' => 'color_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'colors',
                    'mask' => null
                ],
                [
                    'label' => 'Año de fabricación',
                    'name' => 'manufacture_year',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0, 'max':" . date("Y")
                ],
                [
                    'label' => 'Serial de carroceria',
                    'name' => 'bodywork_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Serial del motor',
                    'name' => 'engine_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Placa',
                    'name' => 'license_plate',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Valor de adquisición',
                    'name' => 'acquisition_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
                [
                    'label' => 'Valor residual',
                    'name' => 'residual_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
                [
                    'label' => 'Años de vida útil',
                    'name' => 'depresciation_years',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                ],
            ],
            "terrenos" => [
                [
                    'label' => 'Sede',
                    'name' => 'headquarter_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'headquarters',
                    'mask' => null
                ],
                [
                    'label' => 'Año de construcción',
                    'name' => 'construction_year',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0, 'max':" . date("Y")
                ],
                [
                    'label' => 'Edad de construcción',
                    'name' => 'construction_age',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                ],
                [
                    'label' => 'Número de contrato del inmueble',
                    'name' => 'contract_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'RIF del comodatario',
                    'name' => 'rif',
                    'type' => 'text',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Estado de ocupación',
                    'name' => 'occupancy_status_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'occupancy_statuses',
                    'mask' => null
                ],
                [
                    'label' => 'Área de construcción',
                    'name' => 'construction_area',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                ],
                [
                    'label' => 'Unidad de medida del área de construcción',
                    'name' => 'construction_measurement_unit_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'measurement_units',
                    'mask' => null
                ],
                [
                    'label' => 'Área del terreno',
                    'name' => 'land_area',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                ],
                [
                    'label' => 'Unidad de medida del área del terreno',
                    'name' => 'land_measurement_unit_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'measurement_units',
                    'mask' => null
                ],
                [
                    'label' => 'Uso actual',
                    'name' => 'asset_use_function_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'asset_use_functions',
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de inicio del contrato',
                    'name' => 'contract_start_date',
                    'type' => 'date',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de fin del contrato',
                    'name' => 'contract_end_date',
                    'type' => 'date',
                    'required' => false,
                    'mask' => null
                ],
                [
                    'label' => 'Oficina de registro del inmueble',
                    'name' => 'registry_office',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de registro del inmueble',
                    'name' => 'registration_date',
                    'type' => 'date',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Número de registro del inmueble',
                    'name' => 'registration_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Tomo',
                    'name' => 'tome',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Folio',
                    'name' => 'folio',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'País',
                    'name' => 'country_id',
                    'type' => 'select',
                    'event' => 'vm.getEstates();',
                    'required' => true,
                    'options' => 'countries',
                    'mask' => null
                ],
                [
                    'label' => 'Estado',
                    'name' => 'estate_id',
                    'type' => 'select',
                    'event' => 'vm.getMunicipalities();',
                    'required' => true,
                    'options' => 'estates',
                    'mask' => null
                ],
                [
                    'label' => 'Municipio',
                    'name' => 'municipality_id',
                    'type' => 'select',
                    'event' => 'vm.getParishes();',
                    'required' => true,
                    'options' => 'municipalities',
                    'mask' => null
                ],
                [
                    'label' => 'Parroquia',
                    'name' => 'parish_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'parishes',
                    'mask' => null
                ],
                [
                    'label' => 'Urbanización/Sector',
                    'name' => 'urbanization_sector',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Avenida/Calle',
                    'name' => 'avenue_street',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Casa/Edificio',
                    'name' => 'house',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Piso',
                    'name' => 'floor',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Localización',
                    'name' => 'location',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos norte',
                    'name' => 'north_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos sur',
                    'name' => 'south_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos este',
                    'name' => 'east_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Linderos oeste',
                    'name' => 'west_boundaries',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Coordenadas de ubicación',
                    'name' => 'location_coordinates',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Valor de adquisición',
                    'name' => 'acquisition_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
            ],
            "semovientes" => [
                [
                    'label' => 'Sede',
                    'name' => 'headquarter_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'headquarters',
                    'mask' => null
                ],
                [
                    'label' => 'Raza',
                    'name' => 'race',
                    'type' => 'text',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Tipo',
                    'name' => 'type',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'types',
                    'mask' => null
                ],
                [
                    'label' => 'Propósito',
                    'name' => 'purpose',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'purposes',
                    'mask' => null
                ],
                [
                    'label' => 'Peso',
                    'name' => 'weight',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 0"
                ],
                [
                    'label' => 'Unidad de medida',
                    'name' => 'measurement_unit_id',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'measurement_units',
                    'mask' => null
                ],
                [
                    'label' => 'Fecha de nacimiento',
                    'name' => 'date_of_birth',
                    'type' => 'date',
                    'required' => true,
                    'mask' => null
                ],
                [
                    'label' => 'Género',
                    'name' => 'gender',
                    'type' => 'select',
                    'event' => 'javascript:void(0);',
                    'required' => true,
                    'options' => 'gender',
                    'mask' => null
                ],
                [
                    'label' => 'Número de hierro',
                    'name' => 'iron_number',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2"
                ],
                [
                    'label' => 'Valor de adquisición',
                    'name' => 'acquisition_value',
                    'type' => 'text',
                    'required' => true,
                    'mask' => "'alias': 'numeric', 'allowMinus': 'false', 'digits': 2, 'groupSeparator': '.', 'radixPoint': ','"
                ],
            ],
        ];
    }

    /**
     * Listado de parámetros
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadParametersData($type)
    {
        return $this->parameters[$type] ?? null;
    }

    /**
     * Listado de todos los parametros
     *
     * @author   Fabian Palmera <fabianp@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadAllParameters()
    {
        return [
            'colors' => $this->colors,
            'cattle_types' => $this->cattleTypes,
            'purposes' => $this->purposes,
            'occupancy_status' => $this->occupancyStatus,
            'use_functions' => $this->assetUseFunctions,
            'genders' => $this->genders,
        ];
    }

    /**
     * Listado de colores
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadColorsData()
    {
        return $this->colors;
    }

    /**
     * Listado de tipos de ganado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadCattleTypesData()
    {
        return $this->cattleTypes;
    }

    /**
     * Listado de propositos del semoviente
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadPurposesData()
    {
        return $this->purposes;
    }

    /**
     * Listado de estado de ocupación del terreno
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadOccupancyStatusData()
    {
        return $this->occupancyStatus;
    }

    /**
     * Listado de estado de ocupación del terreno
     *
     * @author   Fabian Palmera <fabianp@cenditel.gob.ve>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadAssetUseFunctionsData()
    {
        return $this->assetUseFunctions;
    }

    /**
     * Listado de los géneros para semovientes
     *
     * @author   Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return    array      Devuelve un arreglo con todas las opciones correspondientes
     */
    public function loadGendersData()
    {
        return $this->genders;
    }
}
