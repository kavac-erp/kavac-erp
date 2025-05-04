<?php

namespace Modules\Asset\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Asset\Repositories\AssetParametersRepository;
use Illuminate\Support\Str;

/**
 * @class AssetResource
 * @brief Clase que maneja el recurso de bienes
 *
 * @author Manuel Zambrano <mzambrano@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $record = [
            'id' => $this->id,

            'groups' => [
                [
                    'group_name' => 'Organización',
                    'items' => [
                        'institution' => [
                            'id' => $this->institution?->id,
                            'label' => "Organización",
                            'value' => $this->institution?->name ?? '',
                        ],
                        'headquarter' => [
                            'id' => $this->headquarter?->id,
                            'label' => "Sede",
                            'value' => $this->headquarter?->name ?? '',
                        ],
                        'department' => [
                            'id' => $this->department?->id,
                            'label' => "Departamento",
                            'value' => $this->department?->name ?? '',
                            'acronym' => $this->department?->acronym ?? '',
                            'active' => $this->department?->active ?? false,
                            'administrative' => $this->department?->administrative ?? false,
                        ],
                    ],
                ],
                [
                    'group_name' => 'Clasificación',
                    'items' => [
                        'asset_type' => [
                            'id' => $this->assetType?->id,
                            'label' => "Tipo",
                            'value' => $this->assetType?->name ?? '',
                        ],
                        'asset_category' => [
                            'id' => $this->assetCategory?->id,
                            'label' => "Categoría",
                            'value' => $this->assetCategory?->name ?? '',
                        ],
                        'asset_subcategory' => [
                            'id' => $this->assetSubcategory?->id,
                            'label' => "Subcategoría",
                            'value' => $this->assetSubcategory?->name ?? '',
                        ],
                        'asset_specific_category' => [
                            'id' => $this->assetSpecificCategory?->id,
                            'label' => "Categoría Específica",
                            'value' => $this->assetSpecificCategory?->name ?? '',
                        ],
                    ],
                ],
                [
                    'group_name' => 'Estado',
                    'items' => [
                        'asset_condition' => [
                            'id' => $this->assetCondition?->id,
                            'label' => "Condición",
                            'value' => $this->assetCondition?->name ?? '',
                        ],

                        'asset_status' => [
                            'id' => $this->assetStatus?->id,
                            'label' => "Estatus",
                            'value' => $this->assetStatus?->name ?? '',
                        ],

                        'asset_disincorporation_motive' => [
                            'id' => $this->assetDisincorporationAsset?->id,
                            'label' => "Motivo de Desincorporación",
                            'value' => $this->assetDisincorporationAsset?->assetDisincorporation
                                ->assetDisincorporationMotive->name ?? '',
                        ],
                    ],
                ],
                [
                    'group_name' => 'Adquisición',
                    'items' => [
                        'document_number' => [
                            'label' => 'N° de Documento',
                            'value' => $this->document_num,
                        ],
                        'purchase_supplier' => [
                            'label' => "Proveedor",
                            'value' => $this->purchaseSupplier?->name ?? 'N/P',
                        ],
                        'asset_acquisition_type' => [
                            'id' => $this->assetAcquisitionType?->id,
                            'label' => "Tipo de Adquisición",
                            'value' => $this->assetAcquisitionType?->name ?? '',
                        ],
                        'acquisition_date' => [
                            'label' => "Fecha de Adquisición",
                            'value' => $this->acquisition_date
                        ],
                        'acquisition_value' => [
                            'label' => "Valor de Adquisición",
                            'value' => $this->acquisition_value,
                            'unit' => $this->currency->symbol,
                        ],
                    ],
                ],
                [
                    'group_name' => 'Depreciación',
                    'items' => [
                        'residual_value' => [
                            'label' => 'Valor Residual',
                            'value' => 'N/P'
                        ],
                        'depresciation_years' => [
                            'label' => 'Años de vida Útil',
                            'value' => 'N/P'
                        ],
                    ],
                ],
            ],
            'headquarter' => [
                'id' => $this->headquarter?->id,
                'label' => "Nodo",
                'value' => $this->headquarter?->name ?? '',
            ],

            'asset_type' => [
                'id' => $this->assetType?->id,
                'label' => "Tipo",
                'name' => $this->assetType?->name ?? '',
            ],

            'asset_category' => [
                'id' => $this->assetCategory?->id,
                'label' => "Categoría",
                'name' => $this->assetCategory?->name ?? '',
            ],

            'asset_subcategory' => [
                'id' => $this->assetSubcategory?->id,
                'label' => "Subcategoría",
                'name' => $this->assetSubcategory?->name ?? '',
            ],

            'asset_specific_category' => [
                'id' => $this->assetSpecificCategory?->id,
                'label' => "Categoría Específica",
                'name' => $this->assetSpecificCategory?->name ?? '',
            ],


            'asset_condition' => [
                'id' => $this->assetCondition?->id,
                'label' => "Condición",
                'name' => $this->assetCondition?->name ?? '',
            ],

            'asset_status' => [
                'id' => $this->assetStatus?->id,
                'label' => "Status",
                'name' => $this->assetStatus?->name ?? '',
            ],

            'description' => [
                'label' => "Descripción",
                'name' => strip_tags($this->description)
            ],

            'acquisition_date' => [
                'label' => "Fecha de Adquisición",
                'name' => $this->acquisition_date
            ],

            'acquisition_value' => [
                'label' => "Valor de Adquisición",
                'name' => $this->acquisition_value
            ],

            'asset_institutional_code' => [
                'label' => "Código Institucional",
                'name' => $this->asset_institutional_code
            ],

            'code_sigecof' => [
                'label' => "Código SIGECOF",
                'name' => $this->code_sigecof
            ],

            'currency' => [
                'id' => $this->currency?->id,
                'label' => "Moneda",
                'name' => $this->currency?->name ?? ''
            ],

            'document_num' => [
                'label' => "Número de Documento",
                'name' => $this->document_num
            ],

            'department' => [
                'id' => $this->department?->id,
                'label' => "Departemento",
                'name' => $this->department?->name ?? '',
                'acronym' => $this->department?->acronym ?? '',
                'active' => $this->department?->active ?? false,
                'administrative' => $this->department?->administrative ?? false,
            ],
        ];

        $parameters = new AssetParametersRepository();
        $details_parameters = null;
        $assetType = $this->assetType;
        $assetTypeName = $assetType?->name;

        switch ($this->asset_type_id) {
            case 1:
                switch ($this->asset_category_id) {
                    case 2: // VEHICULOS
                        $details_parameters = $parameters->loadParametersData('vehiculos');
                        $record['groups'][1]['items']['asset_type']['value'] = $assetTypeName . ' - Vehículo';
                        break;
                    case 8: // SEMOVIENTES
                        $details_parameters = $parameters->loadParametersData('semovientes');
                        $record['groups'][1]['items']['asset_type']['value'] = $assetTypeName . ' - Semoviente';
                        break;
                    default: // MUEBLES
                        $details_parameters = $parameters->loadParametersData('muebles');
                        break;
                }
                break;
            default: // INMUEBLE
                $details_parameters = $parameters->loadParametersData('inmuebles');
                break;
        }

        foreach ($details_parameters as $parameter) {
            if (strpos($parameter['name'], '_id')) {
                $field_name = substr($parameter['name'], 0, strlen($parameter['name']) - 3);
                if (!array_key_exists($field_name, $record)) {
                    $details_fields[$field_name] = [
                        'label' => $parameter['label'],
                        'value' => $this->$field_name ?? $this->asset_details[$parameter['name']] ?? ''
                    ];
                }
            } elseif (strpos($parameter['name'], '_value') or strpos($parameter['name'], '_years')) {
                $field_name = $parameter['name'];
                if (!array_key_exists($field_name, $record)) {
                    $record['groups'][4]['items'][$field_name]['value'] = !empty($this->asset_details[$field_name])
                        ? $this->asset_details[$field_name] : '';
                    $record['groups'][4]['items'][$field_name]['unit'] = strpos($parameter['name'], '_value')
                        ? $this->currency->symbol : 'Años';
                }
            } else {
                $field_name = $parameter['name'];
                if (!array_key_exists($field_name, $record)) {
                    $allParameters = $parameters->loadAllParameters();
                    $pluralName = Str::plural($field_name);
                    if (array_key_exists($pluralName, $allParameters) || 'type' == $field_name) {
                        $details_fields[$field_name] = [
                            'label' => $parameter['label'],
                            'value' => (
                                $this->$field_name ?? $this->asset_details[$parameter['name']] ?? ''
                            )
                        ];
                    } else {
                        $details_fields[$field_name] = [
                            'label' => $parameter['label'],
                            'value' => (
                                !empty($this->asset_details[$parameter['name']]) ?
                                $this->asset_details[$parameter['name']] : ''
                            )
                        ];
                    }
                }
            }
        }
        $record['asset_details'] = $details_fields;

        return $record;
    }
}
