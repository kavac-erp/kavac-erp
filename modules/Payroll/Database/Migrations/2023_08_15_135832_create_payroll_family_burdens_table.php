<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Payroll\Models\PayrollChildren;
use Modules\Payroll\Models\PayrollFamilyBurden;
use Modules\Payroll\Models\PayrollRelationship;
use Modules\Payroll\Models\PayrollSocioeconomic;

/**
 * @class CreatePayrollFamilyBurdensTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollFamilyBurdensTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_family_burdens')) {
            Schema::create('payroll_family_burdens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('first_name', 100)->comment('Nombre del pariente del trabajador')->nullable();
                $table->string('last_name', 100)->comment('Apellido del pariente del trabajador')->nullable();
                $table->string('id_number', 12)->nullable()->comment('Cédula del pariente del trabajador')->nullable();
                $table->date('birthdate')->comment('Fecha de nacimiento del pariente del trabajador')->nullable();
                $table->string('age', 10)->comment('Edad  del pariente del trabajador')->nullable();
                $table->string('address', 250)->comment('direccion del pariente del trabajador')->nullable();
                $table->foreignId('payroll_gender_id')->nullable()->comment('Genero del pariente');
                $table->foreignId('payroll_relationships_id')->nullable()->comment('Identificador del parentesco');
                $table->foreignId('payroll_schooling_level_id')->nullable()
                    ->comment('Identificador del nivel de escolaridad')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->foreignId('payroll_disability_id')->nullable()
                    ->comment('Identificador de la discapacidad')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->foreignId('payroll_scholarship_types_id')->nullable()
                    ->comment('Identificador del tipo de beca')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->boolean('is_student')->default(false)
                    ->comment('Indica si el pariente del trabajador es estudiante');
                $table->boolean('has_disability')->default(false)
                    ->comment('Indica si el pariente del trabajador tiene una discapacidad');
                $table->boolean('has_scholarships')->default(false)
                    ->comment('Indica si el pariente del trabajador tiene una beca');
                $table->string('study_center', 100)->nullable()->comment('Centro de estudio del hijo del trabajador');
                $table->foreignId('payroll_socioeconomic_id')->nullable()
                    ->comment('Identificador del dato socioeconómico')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }

        DB::transaction(function () {
            /** Parentesco con el trabajador */
            $payrollSocioeconomic = PayrollSocioeconomic::with([
                'payrollStaff', 'payrollChildrens',
            ])->get();
            foreach ($payrollSocioeconomic as $socioEconomic) {
                if (
                    !$socioEconomic->full_name_twosome &&
                    !$socioEconomic->id_number_twosome &&
                    !$socioEconomic->birthdate_twosome
                ) {
                    if (Schema::hasTable('payroll_childrens')) {
                        /** Parentesco con los hijos */
                        $payrollRelationship = PayrollRelationship::updateOrCreate(['name' => 'Hijo(a)'], ['name' => 'Hijo(a)']);
                        $payrollChildrens = PayrollChildren::where('payroll_socioeconomic_id', $socioEconomic->id)->toBase()->get();
                        foreach ($payrollChildrens as $payrollChildren) {
                            PayrollFamilyBurden::create([
                                'id_number' => $payrollChildren->id_number,
                                'birthdate' => $payrollChildren->birthdate,
                                'first_name' => $payrollChildren->first_name,
                                'last_name' => $payrollChildren->last_name ?? '',
                                'has_disability' => $payrollChildren->has_disability,
                                'study_center' => $payrollChildren->study_center,
                                'age' => Carbon::parse($payrollChildren->birthdate)->age,
                                'is_student' => $payrollChildren->is_student,
                                'payroll_schooling_level_id' => $payrollChildren->payroll_schooling_level_id,
                                'payroll_disability_id' => $payrollChildren->payroll_disability_id,
                                'payroll_relationships_id' => $payrollRelationship->id,
                                'payroll_socioeconomic_id' => $socioEconomic->id,
                            ]);
                        }
                    }
                } else {
                    $payrollRelationship = PayrollRelationship::where(['name' => 'Esposo(a)'])->first();
                    if (!$payrollRelationship) {
                        $payrollRelationship = PayrollRelationship::create(['name' => 'Esposo(a)']);
                    }
                    $fullNames = preg_split('/[\s,]+/', $socioEconomic->full_name_twosome);
                    $firstName = $fullNames[0] . ((count($fullNames) > 2) ? " {$fullNames[1]}" : '');
                    $lastName = (count($fullNames) === 3) ? $fullNames[2] : ((count($fullNames) > 3) ? "{$fullNames[2]} {$fullNames[3]}" : '');
                    PayrollFamilyBurden::create([
                        'id_number' => $socioEconomic->id_number_twosome,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'birthdate' => $socioEconomic->birthdate_twosome,
                        'age' => Carbon::parse($socioEconomic->birthdate_twosome)->age,
                        //'payroll_gender_id' => ($socioEconomic["payrollStaff"]["payroll_gender_id"] == 2) ? 1 : 2,
                        'payroll_gender_id' => $socioEconomic->payrollStaff->payroll_gender_id,
                        'payroll_relationships_id' => $payrollRelationship->id,
                        'payroll_socioeconomic_id' => $socioEconomic->id,
                    ]);

                    if (Schema::hasTable('payroll_childrens')) {
                        /** Parentesco con los hijos */
                        $payrollRelationship = PayrollRelationship::updateOrCreate(['name' => 'Hijo(a)'], ['name' => 'Hijo(a)']);
                        $payrollChildrens = PayrollChildren::where('payroll_socioeconomic_id', $socioEconomic->id)->toBase()->get();
                        foreach ($payrollChildrens as $payrollChildren) {
                            PayrollFamilyBurden::create([
                                'id_number' => $payrollChildren->id_number,
                                'birthdate' => $payrollChildren->birthdate,
                                'first_name' => $payrollChildren->first_name,
                                'last_name' => $payrollChildren->last_name ?? '',
                                'has_disability' => $payrollChildren->has_disability,
                                'study_center' => $payrollChildren->study_center,
                                'age' => Carbon::parse($payrollChildren->birthdate)->age,
                                'is_student' => $payrollChildren->is_student,
                                'payroll_schooling_level_id' => $payrollChildren->payroll_schooling_level_id,
                                'payroll_disability_id' => $payrollChildren->payroll_disability_id,
                                'payroll_relationships_id' => $payrollRelationship->id,
                                'payroll_socioeconomic_id' => $socioEconomic->id,
                            ]);
                        }
                    }

                }
            }
        });
        //borrar Tablas
        if (Schema::hasTable('payroll_socioeconomics')) {
            Schema::table('payroll_socioeconomics', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_socioeconomics', 'full_name_twosome')) {
                    $table->dropColumn('full_name_twosome');
                }
                if (Schema::hasColumn('payroll_socioeconomics', 'id_number_twosome')) {
                    $table->dropColumn('id_number_twosome');
                }
                if (Schema::hasColumn('payroll_socioeconomics', 'birthdate_twosome')) {
                    $table->dropColumn('birthdate_twosome');
                }
            });
        }
        if (Schema::hasTable('payroll_childrens')) {
            Schema::dropIfExists('payroll_childrens');
        }
    }
    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('payroll_socioeconomics')) {
            Schema::create('payroll_socioeconomics', function (Blueprint $table) {
                $table->string('full_name_twosome', 200)->nullable()
                    ->comment('Nombres y apellidos de la pareja del trabajador');
                $table->string('id_number_twosome', 12)->unique()->nullable()
                    ->comment('cédula de la pareja del trabajador');
                $table->date('birthdate_twosome')->nullable()
                    ->comment('Fecha de nacimiento de la pareja del trabajador');
            });
        } else {
            Schema::table('payroll_socioeconomics', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_socioeconomics', 'full_name_twosome')) {
                    $table->string('full_name_twosome', 200)->nullable()
                        ->comment('Nombres y apellidos de la pareja del trabajador');
                }
                if (!Schema::hasColumn('payroll_socioeconomics', 'id_number_twosome')) {
                    $table->string('id_number_twosome', 12)->unique()->nullable()
                        ->comment('cédula de la pareja del trabajador');
                }
                if (!Schema::hasColumn('payroll_socioeconomics', 'birthdate_twosome')) {
                    $table->date('birthdate_twosome')->nullable()
                        ->comment('Fecha de nacimiento de la pareja del trabajador');
                }
            });
        }
        if (!Schema::hasTable('payroll_childrens')) {
            Schema::create('payroll_childrens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('first_name', 100)->comment('Nombre del hijo del trabajador');
                $table->string('last_name', 100)->comment('Apellido del hijo del trabajador');
                $table->string('id_number', 12)->nullable()->comment('Cédula del hijo del trabajador');
                $table->date('birthdate')->comment('Fecha de nacimiento del hijo del trabajador');
                $table->boolean('is_student')->default(false)
                    ->comment('Indica si el hijo del trabajador es estudiante');
                $table->boolean('has_disability')->default(false)
                    ->comment('Indica si el hijo del trabajador tiene una discapacidad');
                $table->string('study_center', 100)->nullable()->comment('Centro de estudio del hijo del trabajador');

                $table->foreignId('payroll_socioeconomic_id')->constrained()
                    ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('payroll_schooling_level_id')->nullable()
                    ->comment('Identificador del nivel de escolaridad')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->foreignId('payroll_disability_id')->nullable()
                    ->comment('Identificador de la discapacidad')->constrained()
                    ->onUpdate('cascade')->onDelete('restrict');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        } else {
            Schema::table('payroll_childrens', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_childrens', 'payroll_schooling_level_id')) {
                    $table->foreignId('payroll_schooling_level_id')->nullable()
                        ->comment('Identificador del nivel de escolaridad')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }
                if (!Schema::hasColumn('payroll_childrens', 'payroll_disability_id')) {
                    $table->foreignId('payroll_disability_id')->nullable()
                        ->comment('Identificador de la discapacidad')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }
                if (!Schema::hasColumn('payroll_childrens', 'is_student')) {
                    $table->boolean('is_student')->default(false)->comment('Indica si el hijo del trabajador es estudiante');
                }
                if (!Schema::hasColumn('payroll_childrens', 'has_disability')) {
                    $table->boolean('has_disability')->default(false)->comment('Indica si el hijo del trabajador tiene una discapacidad');
                }
                if (!Schema::hasColumn('payroll_childrens', 'study_center')) {
                    $table->string('study_center', 100)->nullable()->comment('Centro de estudio del hijo del trabajador');
                }
            });
        }
        if (Schema::hasTable('payroll_family_burdens')) {
            DB::transaction(function () {
                $payrollSocioeconomic = PayrollSocioeconomic::with([
                    'payrollStaff', 'payrollChildrens',
                ])->get();
                foreach ($payrollSocioeconomic as $socioEconomic) {
                    $payrollFamilyBurdens = PayrollFamilyBurden::where('payroll_socioeconomic_id', $socioEconomic->id)
                        ->toBase()->get();
                    foreach ($payrollFamilyBurdens as $payrollFamilyBurden) {
                        $payrollRelationship = PayrollRelationship::find($payrollFamilyBurden->payroll_relationships_id);
                        if ($payrollRelationship->name === 'Hijo(a)') {
                            PayrollChildren::create([
                                'id_number' => $payrollFamilyBurden->id_number,
                                'birthdate' => $payrollFamilyBurden->birthdate,
                                'first_name' => $payrollFamilyBurden->first_name,
                                'last_name' => $payrollFamilyBurden->last_name ?? '',
                                'has_disability' => $payrollFamilyBurden->has_disability,
                                'study_center' => $payrollFamilyBurden->study_center,
                                'payroll_socioeconomic_id' => $socioEconomic->id,
                                'age' => Carbon::parse($payrollFamilyBurden->birthdate)->age,
                                'is_student' => $payrollFamilyBurden->is_student,
                                'payroll_schooling_level_id' => $payrollFamilyBurden->payroll_schooling_level_id,
                                'payroll_disability_id' => $payrollFamilyBurden->payroll_disability_id,
                            ]);
                        } elseif ($payrollRelationship->name === 'Esposo(a)') {
                            $socioEconomic->update([
                                'full_name_twosome' => trim($payrollFamilyBurden->first_name . ' ' . $payrollFamilyBurden->last_name),
                                'id_number_twosome' => $payrollFamilyBurden->id_number,
                                'birthdate_twosome' => $payrollFamilyBurden->birthdate ?? null,
                            ]);
                        }
                    }
                }
            });
        }

        Schema::dropIfExists('payroll_family_burdens');
    }
}
