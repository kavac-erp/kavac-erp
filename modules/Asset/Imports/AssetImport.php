<?php

namespace Modules\Asset\Imports;

use Modules\Asset\Models\Asset;
use Modules\Asset\Models\AssetType;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Models\AssetSubcategory;
use Modules\Asset\Models\AssetSpecificCategory;
use Modules\Asset\Models\AssetCondition;
use Modules\Asset\Models\AssetAcquisitionType;
use Modules\Asset\Models\AssetStatus;
use Modules\Asset\Models\AssetUseFunction;
use App\Models\Currency;
use App\Models\Headquarter;
use App\Models\Institution;
use App\Models\Parish;
use App\Models\User;
use App\Notifications\SystemNotification;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Payroll\Models\Department;
use Modules\Purchase\Models\PurchaseSupplier;

class AssetImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    SkipsOnFailure,
    SkipsEmptyRows,
    WithChunkReading,
    ShouldQueue,
    WithEvents
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    protected AssetParametersRepository $params;
    protected array $selects;

    public function __construct(
        protected string $type,
        protected int $user_id,
    ) {
        $this->params = new AssetParametersRepository();
        $this->selects = $this->params->loadAllParameters();
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function headingRow(): int
    {
        HeadingRowFormatter::default('none');

        return 4;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        /** @var int|null Contiene el identificador del tipo de bien asociado al bien */
        $assetTypeId = AssetType::query()
            ->whereRaw('LOWER(name) = LOWER(?)', ['Mueble'])
            ->value('id');

        /** @var AssetCategory|null Contiene el identificador de la categoría asociada al bien */
        $assetCategory = AssetCategory::query()
            ->where('name', $row['CATEGORÍA GENERAL'])
            ->first() ?? null;

        /** @var AssetSubCategory|null Contiene el identificador de la sub-categoría asociada al bien */
        $assetSubCategory = AssetSubCategory::query()
            ->where('name', $row['SUBCATEGORÍA'])
            ->first() ?? null;

        /** @var AssetSpecificCategory|null Contiene el identificador de la categoría específica asociada al bien */
        $assetSpecificCategory = AssetSpecificCategory::query()
            ->where('name', $row['CATEGORÍA ESPECÍFICA'])
            ->first() ?? null;

        /** @var string|null Contiene el código asociada al bien */
        $code = $assetCategory->code . $assetSubCategory->code . $assetSpecificCategory->code;

        /** @var int|null Contiene el identificador de la sede asociada al bien */
        $headquarterId = Headquarter::query()
            ->where('name', $row['SEDE'])
            ->value('id');

        /** @var int|null Contiene el identificador de la organización asociada al bien */
        $institutionId = Institution::query()
            ->where('name', $row['ORGANIZACIÓN'])
            ->orWhere(
                function ($query) {
                    $query
                        ->where('active', true)
                        ->where('default', true);
                }
            )
            ->value('id');

        /** @var int|null Contiene el identificador del proveedor asociada al bien */
        $supplier = explode(' - ', $row['PROVEEDOR']);

        if (count($supplier) == 2) {
            $purchaseSupplierId = PurchaseSupplier::query()
                ->where('rif', $supplier[0])
                ->where('name', $supplier[1])
                ->value('id');
        }

        /** @var int|null Contiene el identificador de la unidad administrativa asociada al bien */
        $departmentId = Department::query()
            ->where('name', $row['UNIDAD ADMINISTRATIVA'])
            ->value('id');

        /** @var int|null Contiene el identificador de la forma de adquisición asociada al bien */
        $assetAcquisitionTypeId = AssetAcquisitionType::query()
            ->where('name', $row['FORMA ADQUISICIÓN'])
            ->value('id');

        /** @var int|null Contiene el identificador de la moneda asociada al bien */
        $currencyId = Currency::query()
            ->where('name', $row['MONEDA'])
            ->value('id');

        /** @var int|null Contiene el identificador del estado de uso asociada al bien */
        $assetStatusId = AssetStatus::query()
            ->where('name', $row['ESTADO DEL USO DEL BIEN'])
            ->value('id');

        /** @var int|null Contiene el identificador de la condición física asociada al bien */
        $assetConditionId = AssetCondition::query()
            ->where('name', $row['CONDICIÓN FÍSICA'])
            ->value('id');

        /** @var int|null Contiene el identificador del color asociado al bien */
        $colorId = $this->getArraysSelect('colors', $row['COLOR'] ?? null);

        /** @var array Datos de los bienes a importar */
        $data = [
            'asset_type_id' => $assetTypeId,
            'asset_category_id' => $assetCategory?->id,
            'asset_subcategory_id' => $assetSubCategory?->id,
            'asset_specific_category_id' => $assetSpecificCategory?->id,
            'asset_condition_id' => $assetConditionId,
            'asset_acquisition_type_id' => $assetAcquisitionTypeId,
            'acquisition_date' => !empty($row['FECHA ADQUISICIÓN']) ? new DateTime($row['FECHA ADQUISICIÓN']) : null,
            'asset_status_id' => $assetStatusId,
            'acquisition_value' => $row['VALOR ADQUISICIÓN'],
            'description' => $row['DESCRIPCIÓN'],
            'institution_id' => $institutionId,
            'department_id' => $departmentId,
            'asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN'],
            'code_sigecof' => $code,
            'currency_id' => $currencyId,
            'document_num' => $row['No. DOCUMENTO'],
            'purchase_supplier_id' => $purchaseSupplierId ?? null,
            'asset_details' => [
                'code' => $row['CÓDIGO INTERNO DEL BIEN'],
                'asset_condition_id' => $assetConditionId,
                'asset_status_id' => $assetStatusId,
                'department_id' => $departmentId,
                'description' => $row['DESCRIPCIÓN'],
                'headquarter_id' => $headquarterId,
                'serial' => $row['SERIAL'],
                'brand' => $row['MARCA'],
                'model' => $row['MODELO'],
                'color_id' => $colorId,
                'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                'residual_value' => $row['VALOR RESIDUAL'],
                'depresciation_years' => $row['AÑOS DE VIDA ÚTIL'],
            ],
            'headquarter_id' => $headquarterId,
            'deleted_at' => null,
        ];

        $asset = Asset::withTrashed()->updateOrCreate(
            ['asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN']],
            $data
        );

        return $asset;
    }

    public function prepareForValidation($data, $index)
    {
        return $data;
    }

    public function rules(): array
    {
        return [
            'SEDE' => ['required'],
            'ORGANIZACIÓN' => ['required'],
            'UNIDAD ADMINISTRATIVA' => ['required'],
            'CÓDIGO INTERNO DEL BIEN' => ['required'],
            'FORMA ADQUISICIÓN' => ['required'],
            'FECHA ADQUISICIÓN' => ['required'],
            'CATEGORÍA GENERAL' => ['required'],
            'SUBCATEGORÍA' => ['required'],
            'CATEGORÍA ESPECÍFICA' => ['required'],
            'ESTADO DEL USO DEL BIEN' => ['required'],
            'CONDICIÓN FÍSICA' => ['required'],
        ];
    }

    public function getArraysSelect(?string $type, ?string $name): ?int
    {
        if (!empty($type) && !empty($name)) {
            $list = Arr::first(
                $this->selects[$type],
                function ($item) use ($name) {
                    return strtolower($item['text']) === strtolower($name);
                }
            );

            if ($list !== null) {
                return $list['id'];
            }
        }
        return null;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $user = User::find($this->user_id);
                $exception = $event->getException();

                if ($exception instanceof QueryException) {
                    $bindingsString = implode(',', $exception->getBindings() ?? []);
                    $message = str_replace("\n", "", $exception->getMessage());
                    if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                        $pattern = '/ERROR:(.*?)DETAIL/';
                        preg_match($pattern, $message, $matches);
                        $errorMessage = trim($matches[1]);
                    } else {
                        $errorMessage = $message;
                    }
                    $user->notify(new SystemNotification('Error', 'Importación fallida. ' . ucfirst($errorMessage) . ' ' . $bindingsString));
                } else {
                    Log::error($event->getException());
                    $user->notify(new SystemNotification('Error', 'Importación fallida. Para mas información comuniquese con el administrador'));
                }
            },
            AfterImport::class => function (AfterImport $event) {
                $user = User::find($this->user_id);
                $user->notify(new SystemNotification('Éxito', 'Importación exitosa.'));
            },
        ];
    }
}
