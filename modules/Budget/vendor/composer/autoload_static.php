<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6dca1433eacbf30474ac61f4d46fe62b
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Modules\\Budget\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Modules\\Budget\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Modules\\Budget\\Database\\Seeders\\BudgetAccountsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/BudgetAccountsTableSeeder.php',
        'Modules\\Budget\\Database\\Seeders\\BudgetDatabaseSeeder' => __DIR__ . '/../..' . '/Database/Seeders/BudgetDatabaseSeeder.php',
        'Modules\\Budget\\Database\\Seeders\\BudgetFinancementTypesAndSourcesTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/BudgetFinancementTypesAndSourcesTableSeeder.php',
        'Modules\\Budget\\Database\\Seeders\\BudgetNotificationSettingsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/BudgetNotificationSettingsTableSeeder.php',
        'Modules\\Budget\\Database\\Seeders\\BudgetRoleAndPermissionsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/BudgetRoleAndPermissionsTableSeeder.php',
        'Modules\\Budget\\Exports\\RecordsExport' => __DIR__ . '/../..' . '/Exports/RecordsExport.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetAccountController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetAccountController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetAditionalCreditController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetAditionalCreditController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetCentralizedActionController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetCentralizedActionController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetCompromiseController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetCompromiseController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetFinancementSourcesController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetFinancementSourcesController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetFinancementTypesController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetFinancementTypesController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetModificationController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetModificationController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetProjectController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetProjectController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetReductionController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetReductionController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetSettingController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetSettingController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetSpecificActionController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetSpecificActionController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetSubSpecificFormulationController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetSubSpecificFormulationController.php',
        'Modules\\Budget\\Http\\Controllers\\BudgetTransferController' => __DIR__ . '/../..' . '/Http/Controllers/BudgetTransferController.php',
        'Modules\\Budget\\Http\\Controllers\\Reports\\BudgetReportsController' => __DIR__ . '/../..' . '/Http/Controllers/Reports/BudgetReportsController.php',
        'Modules\\Budget\\Models\\BudgetAccount' => __DIR__ . '/../..' . '/Models/BudgetAccount.php',
        'Modules\\Budget\\Models\\BudgetAccountOpen' => __DIR__ . '/../..' . '/Models/BudgetAccountOpen.php',
        'Modules\\Budget\\Models\\BudgetAditionalCredit' => __DIR__ . '/../..' . '/Models/BudgetAditionalCredit.php',
        'Modules\\Budget\\Models\\BudgetAditionalCreditAccount' => __DIR__ . '/../..' . '/Models/BudgetAditionalCreditAccount.php',
        'Modules\\Budget\\Models\\BudgetCentralizedAction' => __DIR__ . '/../..' . '/Models/BudgetCentralizedAction.php',
        'Modules\\Budget\\Models\\BudgetCompromise' => __DIR__ . '/../..' . '/Models/BudgetCompromise.php',
        'Modules\\Budget\\Models\\BudgetCompromiseDetail' => __DIR__ . '/../..' . '/Models/BudgetCompromiseDetail.php',
        'Modules\\Budget\\Models\\BudgetFinancementSources' => __DIR__ . '/../..' . '/Models/BudgetFinancementSources.php',
        'Modules\\Budget\\Models\\BudgetFinancementTypes' => __DIR__ . '/../..' . '/Models/BudgetFinancementTypes.php',
        'Modules\\Budget\\Models\\BudgetModification' => __DIR__ . '/../..' . '/Models/BudgetModification.php',
        'Modules\\Budget\\Models\\BudgetModificationAccount' => __DIR__ . '/../..' . '/Models/BudgetModificationAccount.php',
        'Modules\\Budget\\Models\\BudgetProject' => __DIR__ . '/../..' . '/Models/BudgetProject.php',
        'Modules\\Budget\\Models\\BudgetSpecificAction' => __DIR__ . '/../..' . '/Models/BudgetSpecificAction.php',
        'Modules\\Budget\\Models\\BudgetStage' => __DIR__ . '/../..' . '/Models/BudgetStage.php',
        'Modules\\Budget\\Models\\BudgetSubSpecificFormulation' => __DIR__ . '/../..' . '/Models/BudgetSubSpecificFormulation.php',
        'Modules\\Budget\\Models\\Currency' => __DIR__ . '/../..' . '/Models/Currency.php',
        'Modules\\Budget\\Models\\Department' => __DIR__ . '/../..' . '/Models/Department.php',
        'Modules\\Budget\\Models\\DocumentStatus' => __DIR__ . '/../..' . '/Models/DocumentStatus.php',
        'Modules\\Budget\\Models\\Institution' => __DIR__ . '/../..' . '/Models/Institution.php',
        'Modules\\Budget\\Providers\\BudgetServiceProvider' => __DIR__ . '/../..' . '/Providers/BudgetServiceProvider.php',
        'Modules\\Budget\\Providers\\RouteServiceProvider' => __DIR__ . '/../..' . '/Providers/RouteServiceProvider.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6dca1433eacbf30474ac61f4d46fe62b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6dca1433eacbf30474ac61f4d46fe62b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6dca1433eacbf30474ac61f4d46fe62b::$classMap;

        }, null, ClassLoader::class);
    }
}
