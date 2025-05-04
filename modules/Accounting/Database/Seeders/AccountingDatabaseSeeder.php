<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class AccountingDatabaseSeeder
 * @brief Información por defecto para datos iniciales del módulo Accounting
 *
 * Gestiona la información por defecto a registrar inicialmente para los datos iniciales del módulo Accounting
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingDatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders del módulo de contabilidad
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(AccountingRoleAndPermissionsTableSeeder::class);
        /* Seeder para registrar cuentas patrimoniales */
        $this->call(AccountingAccountsTableSeeder::class);
        /* Seeder para registrar categorias */
        $this->call(AccountingEntryCategoriesTableSeeder::class);
    }
}
