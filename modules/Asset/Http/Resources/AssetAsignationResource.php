<?php

namespace Modules\Asset\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Asset\Repositories\AssetParametersRepository;

/**
 * @class AssetAsignationResource
 * @brief Clase que maneja el recurso para las asignaciones
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAsignationResource extends JsonResource
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
        $common_fields = [
            'id' => $this->id,

            'institution' => [
                'id' => $this->institution->id,
                'label' => "Institución",
                'name' => $this->institution->name,
            ],
            'headquarter' => [
                'id' => $this->headquarter?->id,
                'label' => "Nodo",
                'name' => $this->headquarter?->name,
            ],

            'asset_type' => [
                'id' => $this->assetType->id,
                'label' => "Tipo",
                'name' => $this->assetType->name
            ],

            'asset_category' => [
                'id' => $this->assetCategory->id,
                'label' => "Categoría",
                'name' => $this->assetCategory->name
            ],

            'asset_subcategory' => [
                'id' => $this->assetSubcategory->id,
                'label' => "Subcategoía",
                'name' => $this->assetSubcategory->name
            ],

            'asset_specific_category' => [
                'id' => $this->assetSpecificCategory->id,
                'label' => "Categoría Específica",
                'name' => $this->assetSpecificCategory->name
            ],

            'asset_acquisition_type' => [
                'id' => $this->assetAcquisitionType->id,
                'label' => "Tipo de Adquisicion",
                'name' => $this->assetAcquisitionType->name
            ],

            'asset_condition' => [
                'id' => $this->assetCondition->id,
                'label' => "Condicion",
                'name' => $this->assetCondition->name
            ],

            'asset_status' => [
                'id' => $this->assetStatus->id,
                'label' => "Status",
                'name' => $this->assetStatus->name
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
                'id' => $this->currency->id,
                'label' => "Moneda",
                'name' => $this->currency->name
            ],

            'document_num' => [
                'label' => "Número de Documento",
                'name' => $this->document_num
            ],

            'department' => [
                'id' => $this->department->id,
                'label' => "Departemento",
                'name' => $this->department->name,
                'acronym' => $this->department->acronym,
                'active' => $this->department->active,
                'administrative' => $this->department->administrative,
            ],
        ];

        $parameters = new AssetParametersRepository;
        if ($this->asset_type_id === 1) {
            if ($this->asset_category_id === 2) { //VEHICULOS
                $details_parameters = $parameters->loadParametersData('vehiculos');
                $common_fields['asset_specific_type'] = 'vehiculo';
            } elseif ($this->asset_category_id === 8) { //SEMOVIENTES
                $details_parameters = $parameters->loadParametersData('semovientes');
                $common_fields['asset_specific_type'] = 'semoviente';
            } else { //MUEBLES
                $details_parameters = $parameters->loadParametersData('muebles');
                $common_fields['asset_specific_type'] = 'mueble';
            }
        } else { //INMUEBLE
            $details_parameters = $parameters->loadParametersData('inmuebles');
            $common_fields['asset_specific_type'] = 'inmueble';
        }

        $excluded_fields = ['acquisition_value', 'residual_value', 'depresciation_years'];

        foreach ($details_parameters as $value) {
            if (strpos($value['name'], '_id')) {
                $name_field = substr($value['name'], 0, strlen($value['name']) - 3);
                if (in_array($value['name'], $excluded_fields)) {
                    continue;
                }
                if (!array_key_exists($name_field, $common_fields)) {
                    $details_fields[$name_field] = ['label' => $value['label'], 'value' => $this->$name_field];
                } else {
                    continue;
                }
            } else {
                if (in_array($value['name'], $excluded_fields)) {
                    continue;
                }
                $details_fields[$value['name']] = [
                    'label' => $value['label'],
                    'value' => $this->asset_details[$value['name']] ?? 'N/P',
                ];
            }
        }
        $common_fields['asset_details'] = $details_fields;
        if ($this->assetAsignationAsset && $this->assetAsignationAsset->assetAsignation) {
            if ($this->assetAsignationAsset->assetAsignation->payrollStaff) {
                $common_fields['asset_asignation_name'] = $this->assetAsignationAsset->assetAsignation->payrollStaff->fullName;
                $common_fields['asset_asignation_date'] = $this->assetAsignationAsset->assetAsignation->created_at;
                $common_fields['asset_asignation_location'] = $this->assetAsignationAsset->assetAsignation->location_place;
                $common_fields['asset_asignation_building'] = isset($this->assetAsignationAsset->assetAsignation->building) ? $this->assetAsignationAsset->assetAsignation->building->name : '';
                $common_fields['asset_asignation_floor'] = isset($this->assetAsignationAsset->assetAsignation->floor) ? $this->assetAsignationAsset->assetAsignation->floor->name : '';
                $common_fields['asset_asignation_section'] = isset($this->assetAsignationAsset->assetAsignation->section) ? $this->assetAsignationAsset->assetAsignation->section->name : '';
            }
        }

        if ($this->assetDisincorporationAsset && $this->assetDisincorporationAsset->assetDisincorporation) {
            $common_fields['asset_disincorporation_date'] = $this->assetDisincorporationAsset->assetDisincorporation->date;
            $common_fields['asset_disincorporation_motive'] = $this->assetDisincorporationAsset->assetDisincorporation->assetDisincorporationMotive->name;
            $common_fields['asset_disincorporation_observation'] = $this->assetDisincorporationAsset->assetDisincorporation->observation;
        }

        return $common_fields;
    }
}
