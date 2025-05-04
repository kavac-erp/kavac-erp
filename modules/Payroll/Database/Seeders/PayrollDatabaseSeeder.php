<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class PayrollDatabaseSeeder
 * @brief Carga de datos iniciales del módulo de talento humano
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        /* Seeder para roles y permisos disponibles en el módulo */
        $this->call(PayrollRoleAndPermissionsTableSeeder::class);

        /* Seeder para tipos de personal disponibles en el módulo */
        $this->call(PayrollStaffTypesTableSeeder::class);

        /* Seeder para las nacionalidades disponibles en el módulo */
        $this->call(PayrollNationalitiesTableSeeder::class);

        /* Seeder para los grados de instrucción disponibles en el módulo */
        $this->call(PayrollInstructionDegreesTableSeeder::class);

        /* Seeder para los tipos de estudio disponibles en el módulo */
        $this->call(PayrollStudyTypesTableSeeder::class);

        /* Seeder para los niveles de idioma disponibles en el módulo */
        $this->call(PayrollLanguageLevelsTableSeeder::class);

        /* Seeder para los idiomas disponibles en el módulo */
        $this->call(PayrollLanguagesTableSeeder::class);

        /* Seeder para los tipos de contrato disponibles en el módulo */
        $this->call(PayrollContractTypesTableSeeder::class);

        /* Seeder para los tipos de sectores disponibles en el módulo */
        $this->call(PayrollSectorTypesTableSeeder::class);

        /* Seeder para los tipos de inactividad disponibles en el módulo */
        $this->call(PayrollInactivityTypesTableSeeder::class);

        /* Seeder para la configuración general del módulo nómina */
        $this->call(PayrollLanguageLevelsTableSeeder::class);

        /* Seeder para la configuración general del módulo nómina */
        $this->call(PayrollSettingsTableSeeder::class);

        /* Seed para datos iniciales de tipos de sangre */
        $this->call(PayrollBloodTypesTableSeeder::class);

        /* Seed para datos iniciales de licencias de conducir */
        $this->call(PayrollLicenseDegreesTableSeeder::class);

        /* Seed para la configuración de reporte de nómina */
        $this->call(PayrollReportConfigurationTableSeeder::class);
        $this->call(PayrollRelationshipsTableSeeder::class);

        /* Seeder para la carga de datos estáticos de la planilla de nómina */
        $this->call(PayrollLoadBasicPayrollStaffDataTableSeeder::class);
    }
}
