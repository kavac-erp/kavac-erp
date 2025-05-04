<?php

namespace Modules\Payroll\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * @class PayrollRoleAndPermissionsTableSeeder
 * @brief Carga los datos de roles y permisos del módulo de Talento Humano
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $payrollRole = Role::updateOrCreate(
            ['slug' => 'payroll'],
            ['name' => 'Talento Humano', 'description' => 'Coordinador de Talento Humano']
        );

        $permissions = [
            [
                'name' => 'Configuración del módulo de Talento Humano',
                'slug' => 'payroll.setting.index',
                'description' => 'Acceso a la configuración del módulo de Talento Humano',
                'model' => '', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'configuracion.ver',
            ],
            /* Staff types */
            /*[
            'name' => 'Ver tipos de personal',
            'slug' => 'payroll.staff.types.list',
            'description' => 'Acceso para ver tipos de personal',
            'model' => 'Modules\Payroll\Models\PayrollStaffType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.personal.ver'
            ],*/
            [
                'name' => 'Crear tipos de personal',
                'slug' => 'payroll.staff.types.create',
                'description' => 'Acceso para crear tipos de personal',
                'model' => 'Modules\Payroll\Models\PayrollStaffType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.personal.crear',
            ],
            [
                'name' => 'Editar tipos de personal',
                'slug' => 'payroll.staff.types.edit',
                'description' => 'Acceso para editar los tipos de personal',
                'model' => 'Modules\Payroll\Models\PayrollStaffType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.personal.editar',
            ],
            [
                'name' => 'Eliminar tipos de personal',
                'slug' => 'payroll.staff.types.delete',
                'description' => 'Acceso para eliminar tipos de personal',
                'model' => 'Modules\Payroll\Models\PayrollStaffType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.personal.eliminar',
            ],
            /* Position types */
            /*[
            'name' => 'Ver tipos de cargo',
            'slug' => 'payroll.position.types.list',
            'description' => 'Acceso para ver tipos de cargo',
            'model' => 'Modules\Payroll\Models\PayrollPositionType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.cargo.ver'
            ],*/
            [
                'name' => 'Crear tipos de cargo',
                'slug' => 'payroll.position.types.create',
                'description' => 'Acceso para crear tipos de cargo',
                'model' => 'Modules\Payroll\Models\PayrollPositionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.cargo.crear',
            ],
            [
                'name' => 'Editar tipos de cargo',
                'slug' => 'payroll.position.types.edit',
                'description' => 'Acceso para editar los tipos de cargo',
                'model' => 'Modules\Payroll\Models\PayrollPositionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.cargo.editar',
            ],
            [
                'name' => 'Eliminar tipos de cargo',
                'slug' => 'payroll.position.types.delete',
                'description' => 'Acceso para eliminar tipos de cargo',
                'model' => 'Modules\Payroll\Models\PayrollPositionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.cargo.eliminar',
            ],
            /* Positions */
            /*[
            'name' => 'Ver cargos',
            'slug' => 'payroll.positions.list',
            'description' => 'Acceso para ver los cargos',
            'model' => 'Modules\Payroll\Models\PayrollPosition', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'cargo.ver'
            ],*/
            [
                'name' => 'Crear cargos',
                'slug' => 'payroll.positions.create',
                'description' => 'Acceso para crear cargos',
                'model' => 'Modules\Payroll\Models\PayrollPosition', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cargo.crear',
            ],
            [
                'name' => 'Editar cargos',
                'slug' => 'payroll.positions.edit',
                'description' => 'Acceso para editar los cargos',
                'model' => 'Modules\Payroll\Models\PayrollPosition', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cargo.editar',
            ],
            [
                'name' => 'Eliminar cargos',
                'slug' => 'payroll.positions.delete',
                'description' => 'Acceso para eliminar cargos',
                'model' => 'Modules\Payroll\Models\PayrollPosition', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cargo.eliminar',
            ],
            /* Staff classifications */

            /*[
            'name' => 'Ver la clasificación del personal',
            'slug' => 'payroll.staff.classifications.list',
            'description' => 'Acceso para ver la clasificación del personal',
            'model' => 'Modules\Payroll\Models\PayrollStaffClassification', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'clasificacion.personal.ver'
            ],
            [
            'name' => 'Crear la clasificación del personal',
            'slug' => 'payroll.staff.classifications.create',
            'description' => 'Acceso para crear la clasificación del personal',
            'model' => 'Modules\Payroll\Models\PayrollStaffClassification', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'clasificacion.personal.crear'
            ],
            [
            'name' => 'Editar la clasificación del personal',
            'slug' => 'payroll.staff.classifications.edit',
            'description' => 'Acceso para editar la clasificación del personal',
            'model' => 'Modules\Payroll\Models\PayrollStaffClassification', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'payroll.relationships.list'clasificacion.personal.editar'
            ],
            [
            'name' => 'Eliminar la clasificación del personal',
            'slug' => 'payroll.staff.classifications.delete',
            'description' => 'Acceso para eliminar la clasificación del personal',
            'model' => 'Modules\Payroll\Models\PayrollStaffClassification', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'clasificacion.personal.eliminar'
            ],*/
            /* Staffs */
            [
                'name' => 'Ver el personal',
                'slug' => 'payroll.staffs.list',
                'description' => 'Acceso para ver el personal',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.ver',
            ],
            [
                'name' => 'Crear el personal',
                'slug' => 'payroll.staffs.create',
                'description' => 'Acceso para crear el personal',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.crear',
            ],
            [
                'name' => 'Editar el personal',
                'slug' => 'payroll.staffs.edit',
                'description' => 'Acceso para editar el personal',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.editar',
            ],
            [
                'name' => 'Eliminar el personal',
                'slug' => 'payroll.staffs.delete',
                'description' => 'Acceso para eliminar el personal',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.eliminar',
            ],
            [
                'name' => 'Importar datos personales',
                'slug' => 'payroll.staffs.import',
                'description' => 'Acceso para importar datos personales',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.importar',
            ],
            [
                'name' => 'Exportar datos personales',
                'slug' => 'payroll.staffs.export',
                'description' => 'Acceso para exportar datos personales',
                'model' => 'Modules\Payroll\Models\PayrollStaff', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'personal.exportar',
            ],

            /* Permisos de los Registros comúnes > Coordinaciones. */
            [
                'name' => 'Obtener listado de las coordinaciones',
                'slug' => 'payroll.coordinations.index',
                'description' => 'Acceso para obtener listado de las coordinaciones',
                'model' => 'Modules\Payroll\Models\Coordinations',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'coordinaciones.listado',
                'short_description' => 'Acceder al listado de las coordinaciones',
            ],
            [
                'name' => 'Registrar una coordinación',
                'slug' => 'payroll.coordinations.store',
                'description' => 'Acceso para registrar una coordinación',
                'model' => 'Modules\Payroll\Models\Coordinations',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'coordinaciones.crear',
                'short_description' => 'Registrar una coordinación',
            ],
            [
                'name' => 'Actualizar una coordinación',
                'slug' => 'payroll.coordinations.update',
                'description' => 'Acceso para actualizar una coordinación',
                'model' => 'Modules\Payroll\Models\Coordinations',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'coordinaciones.actualizar',
                'short_description' => 'Actualizar una coordinación',
            ],
            [
                'name' => 'Eliminar una coordinación',
                'slug' => 'payroll.coordinations.destroy',
                'description' => 'Acceso para eliminar una coordinación',
                'model' => 'Modules\Payroll\Models\Coordinations',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'coordinaciones.eliminar',
                'short_description' => 'Eliminar una coordinación',
            ],

            /* Permisos de los Registros comúnes > Responsabilidades. */
            [
                'name' => 'Obtener listado de las responsabilidades',
                'slug' => 'payroll.responsibilities.index',
                'description' => 'Acceso para obtener listado de las responsabilidades',
                'model' => 'Modules\Payroll\Models\Responsibility',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsabilidades.listado',
                'short_description' => 'Acceder al listado de las responsabilidades',
            ],
            [
                'name' => 'Registrar una responsabilidad',
                'slug' => 'payroll.responsibilities.store',
                'description' => 'Acceso para registrar una responsabilidad',
                'model' => 'Modules\Payroll\Models\Responsibility',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsabilidades.crear',
                'short_description' => 'Registrar una responsabilidad',
            ],
            [
                'name' => 'Actualizar una responsabilidad',
                'slug' => 'payroll.responsibilities.update',
                'description' => 'Acceso para actualizar una responsabilidad',
                'model' => 'Modules\Payroll\Models\Responsibility',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsabilidades.actualizar',
                'short_description' => 'Actualizar una responsabilidad',
            ],
            [
                'name' => 'Eliminar una responsabilidad',
                'slug' => 'payroll.responsibilities.destroy',
                'description' => 'Acceso para eliminar una responsabilidad',
                'model' => 'Modules\Payroll\Models\Responsibility',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsabilidades.eliminar',
                'short_description' => 'Eliminar una responsabilidad',
            ],

            /* Instruction degrees */
            /*[
            'name' => 'Ver el grado de instrucción',
            'slug' => 'payroll.instruction.degrees.list',
            'description' => 'Acceso para ver el grado de instrucción',
            'model' => 'Modules\Payroll\Models\PayrollInstructionDegree', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'grado.instruccion.ver'
            ],*/
            [
                'name' => 'Crear el grado de instrucción',
                'slug' => 'payroll.instruction.degrees.create',
                'description' => 'Acceso para crear el grado de instrucción',
                'model' => 'Modules\Payroll\Models\PayrollInstructionDegree', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.instruccion.crear',
            ],
            [
                'name' => 'Editar el grado de instrucción',
                'slug' => 'payroll.instruction.degrees.edit',
                'description' => 'Acceso para editar el grado de instrucción',
                'model' => 'Modules\Payroll\Models\PayrollInstructionDegree', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.instruccion.editar',
            ],
            [
                'name' => 'Eliminar el grado de instrucción',
                'slug' => 'payroll.instruction.degrees.delete',
                'description' => 'Acceso para eliminar el grado de instrucción',
                'model' => 'Modules\Payroll\Models\PayrollInstructionDegree', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.instruccion.eliminar',
            ],
            /* Study types */
            /*[
            'name' => 'Ver el tipo de estudio',
            'slug' => 'payroll.study.types.list',
            'description' => 'Acceso para ver el tipo de estudio',
            'model' => 'Modules\Payroll\Models\PayrollStudyType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.estudio.ver'
            ],*/
            [
                'name' => 'Crear el tipo de estudio',
                'slug' => 'payroll.study.types.create',
                'description' => 'Acceso para crear el tipo de estudio',
                'model' => 'Modules\Payroll\Models\PayrollStudyType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.estudio.crear',
            ],
            [
                'name' => 'Editar el tipo de estudio',
                'slug' => 'payroll.study.types.edit',
                'description' => 'Acceso para editar el tipo de estudio',
                'model' => 'Modules\Payroll\Models\PayrollStudyType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.estudio.editar',
            ],
            [
                'name' => 'Eliminar el tipo de estudio',
                'slug' => 'payroll.study.types.delete',
                'description' => 'Acceso para eliminar el tipo de estudio',
                'model' => 'Modules\Payroll\Models\PayrollStudyType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.estudio.eliminar',
            ],

            /* schooling-levels */
            [
                'name' => 'Crear nivel de escolaridad',
                'slug' => 'payroll.schooling.levels.create',
                'description' => 'Acceso para crear nivel de escolaridad',
                'model' => 'Modules\Payroll\Models\PayrollStudyType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.escolaridad.crear',
            ],
            [
                'name' => 'Editar nivel de escolaridad',
                'slug' => 'payroll.schooling.levels.edit',
                'description' => 'Acceso para editar nivel de escolaridad',
                'model' => 'Modules\Payroll\Models\PayrollStudyType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.escolaridad.editar',
            ],
            [
                'name' => 'Eliminar nivel de escolaridad',
                'slug' => 'payroll.schooling.levels.delete',
                'description' => 'Acceso para eliminar nivel de escolaridad',
                'model' => 'Modules\Payroll\Models\PayrollStudyType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.escolaridad.eliminar',
            ],

            /* Nationalities */
            /*[
            'name' => 'Ver la nacionalidad',
            'slug' => 'payroll.nationalities.list',
            'description' => 'Acceso para ver la nacionalidad',
            'model' => 'Modules\Payroll\Models\PayrollNationality', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'nacionalidad.ver'
            ],*/
            [
                'name' => 'Crear la nacionalidad',
                'slug' => 'payroll.nationalities.create',
                'description' => 'Acceso para crear la nacionalidad',
                'model' => 'Modules\Payroll\Models\PayrollNationality', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nacionalidad.crear',
            ],
            [
                'name' => 'Editar la nacionalidad',
                'slug' => 'payroll.nationalities.edit',
                'description' => 'Acceso para editar la nacionalidad',
                'model' => 'Modules\Payroll\Models\PayrollNationality', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nacionalidad.editar',
            ],
            [
                'name' => 'Eliminar la nacionalidad',
                'slug' => 'payroll.nationalities.delete',
                'description' => 'Acceso para eliminar la nacionalidad',
                'model' => 'Modules\Payroll\Models\PayrollNationality', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nacionalidad.eliminar',
            ],
            /* Concept types */
            /*[
            'name' => 'Ver los tipos de concepto',
            'slug' => 'payroll.concept.types.list',
            'description' => 'Acceso para ver los tipos de concepto',
            'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.concepto.ver'
            ],*/
            [
                'name' => 'Crear el tipo de concepto',
                'slug' => 'payroll.concept.types.create',
                'description' => 'Acceso para crear el tipo de concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.concepto.crear',
            ],
            [
                'name' => 'Editar el tipo de concepto',
                'slug' => 'payroll.concept.types.edit',
                'description' => 'Acceso para editar el tipo de concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.concepto.editar',
            ],
            [
                'name' => 'Eliminar el tipo de concepto',
                'slug' => 'payroll.concept.types.delete',
                'description' => 'Acceso para eliminar el tipo de concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.concepto.eliminar',
            ],

            /* Concept */

            [
                'name' => 'Crear concepto',
                'slug' => 'payroll.concept.create',
                'description' => 'Acceso para crear concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'concepto.crear',
            ],
            [
                'name' => 'Editar concepto',
                'slug' => 'payroll.concept.edit',
                'description' => 'Acceso para editar concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'concepto.editar',
            ],
            [
                'name' => 'Eliminar concepto',
                'slug' => 'payroll.concept.delete',
                'description' => 'Acceso para eliminar concepto',
                'model' => 'Modules\Payroll\Models\PayrollConceptType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'concepto.eliminar',
            ],
            /* language levels */
            /*[
            'name' => 'Ver los niveles de idioma',
            'slug' => 'payroll.language.levels.list',
            'description' => 'Acceso para ver los niveles de idioma',
            'model' => 'Modules\Payroll\Models\PayrollLanguageLevel', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'nivel.idioma.ver'
            ],*/
            [
                'name' => 'Crear el nivel de idioma',
                'slug' => 'payroll.language.levels.create',
                'description' => 'Acceso para crear el nivel de idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguageLevel', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.idioma.crear',
            ],
            [
                'name' => 'Editar el nivel de idioma',
                'slug' => 'payroll.language.levels.edit',
                'description' => 'Acceso para editar el nivel de idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguageLevel', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.idioma.editar',
            ],
            [
                'name' => 'Eliminar el nivel de idioma',
                'slug' => 'payroll.language.levels.delete',
                'description' => 'Acceso para eliminar el nivel de idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguageLevel', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'nivel.idioma.eliminar',
            ],
            /* Languages */
            /*[
            'name' => 'Ver los idiomas',
            'slug' => 'payroll.languages.list',
            'description' => 'Acceso para ver los idiomas',
            'model' => 'Modules\Payroll\Models\PayrollLanguage', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'idioma.ver'
            ],*/
            [
                'name' => 'Crear el idioma',
                'slug' => 'payroll.languages.create',
                'description' => 'Acceso para crear el idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguage', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'idioma.crear',
            ],
            [
                'name' => 'Editar el idioma',
                'slug' => 'payroll.languages.edit',
                'description' => 'Acceso para editar el idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguage', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'idioma.editar',
            ],
            [
                'name' => 'Eliminar el idioma',
                'slug' => 'payroll.languages.delete',
                'description' => 'Acceso para eliminar el idioma',
                'model' => 'Modules\Payroll\Models\PayrollLanguage', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'idioma.eliminar',
            ],
            /* Genders */
            /*[
            'name' => 'Ver los géneros',
            'slug' => 'payroll.genders.list',
            'description' => 'Acceso para ver los géneros',
            'model' => 'Modules\Payroll\Models\PayrollGender', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'genero.ver'
            ],*/
            [
                'name' => 'Crear el género',
                'slug' => 'payroll.genders.create',
                'description' => 'Acceso para crear el género',
                'model' => 'Modules\Payroll\Models\PayrollGender', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'genero.crear',
            ],
            [
                'name' => 'Editar el género',
                'slug' => 'payroll.genders.edit',
                'description' => 'Acceso para editar el género',
                'model' => 'Modules\Payroll\Models\PayrollGender', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'genero.editar',
            ],
            [
                'name' => 'Eliminar el género',
                'slug' => 'payroll.genders.delete',
                'description' => 'Acceso para eliminar el género',
                'model' => 'Modules\Payroll\Models\PayrollGender', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'genero.eliminar',
            ],
            /* Professional informations */
            [
                'name' => 'Ver los datos de información profesional',
                'slug' => 'payroll.professional.informations.list',
                'description' => 'Acceso para ver los datos de información socioeconómica',
                'model' => 'Modules\Payroll\Models\PayrollProfessionalInformation', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'informacion.profesional.ver',
            ],
            [
                'name' => 'Crear datos de información profesional',
                'slug' => 'payroll.professional.informations.create',
                'description' => 'Acceso para crear datos de información profesional',
                'model' => 'Modules\Payroll\Models\PayrollProfessionalInformation', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'informacion.profesional.crear',
            ],
            [
                'name' => 'Editar datos de información profesional',
                'slug' => 'payroll.professional.informations.edit',
                'description' => 'Acceso para editar datos de información profesional',
                'model' => 'Modules\Payroll\Models\PayrollProfessionalInformation', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'informacion.profesional.editar',
            ],
            [
                'name' => 'Eliminar datos de información profesional',
                'slug' => 'payroll.professional.informations.delete',
                'description' => 'Acceso para eliminar datos de información profesional',
                'model' => 'Modules\Payroll\Models\PayrollProfessionalInformation', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'informacion.profesional.eliminar',
            ],
            /* Inactivity types */
            /*[
            'name' => 'Ver los datos de tipos de inactividad',
            'slug' => 'payroll.inactivity.types.list',
            'description' => 'Acceso para ver los datos de tipos de inactividad',
            'model' => 'Modules\Payroll\Models\PayrollInactivityType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.inactividad.ver'
            ],*/
            [
                'name' => 'Crear datos de tipos de inactividad',
                'slug' => 'payroll.inactivity.types.create',
                'description' => 'Acceso para crear datos de tipos de inactividad',
                'model' => 'Modules\Payroll\Models\PayrollInactivityType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.inactividad.crear',
            ],
            [
                'name' => 'Editar datos de tipos de inactividad',
                'slug' => 'payroll.inactivity.types.edit',
                'description' => 'Acceso para editar datos de tipos de inactividad',
                'model' => 'Modules\Payroll\Models\PayrollInactivityType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.inactividad.editar',
            ],
            [
                'name' => 'Eliminar datos de tipos de inactividad',
                'slug' => 'payroll.inactivity.types.delete',
                'description' => 'Acceso para eliminar datos de tipos de inactividad',
                'model' => 'Modules\Payroll\Models\PayrollInactivityType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.inactividad.eliminar',
            ],
            /* Contract types */
            /*[
            'name' => 'Ver los datos de tipos de contrato',
            'slug' => 'payroll.contract.types.list',
            'description' => 'Acceso para ver los datos de tipos de contrato',
            'model' => 'Modules\Payroll\Models\PayrollContractType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.contrato.ver'
            ],*/
            [
                'name' => 'Crear datos de tipos de contrato',
                'slug' => 'payroll.contract.types.create',
                'description' => 'Acceso para crear datos de tipos de contrato',
                'model' => 'Modules\Payroll\Models\PayrollContractType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.contrato.crear',
            ],
            [
                'name' => 'Editar datos de tipos de contrato',
                'slug' => 'payroll.contract.types.edit',
                'description' => 'Acceso para editar datos de tipos de contrato',
                'model' => 'Modules\Payroll\Models\PayrollContractType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.contrato.editar',
            ],
            [
                'name' => 'Eliminar datos de tipos de contrato',
                'slug' => 'payroll.contract.types.delete',
                'description' => 'Acceso para eliminar datos de tipos de contrato',
                'model' => 'Modules\Payroll\Models\PayrollContractType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.contrato.eliminar',
            ],
            /* Sector types */
            /*[
            'name' => 'Ver los datos de tipos de sector',
            'slug' => 'payroll.sector.types.list',
            'description' => 'Acceso para ver los datos de tipos de sector',
            'model' => 'Modules\Payroll\Models\PayrollSectorType', 'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.sector.ver'
            ],*/
            [
                'name' => 'Crear datos de tipos de sector',
                'slug' => 'payroll.sector.types.create',
                'description' => 'Acceso para crear datos de tipos de sector',
                'model' => 'Modules\Payroll\Models\PayrollSectorType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sector.crear',
            ],
            [
                'name' => 'Editar datos de tipos de sector',
                'slug' => 'payroll.sector.types.edit',
                'description' => 'Acceso para editar datos de tipos de sector',
                'model' => 'Modules\Payroll\Models\PayrollSectorType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sector.editar',
            ],
            [
                'name' => 'Eliminar datos de tipos de sector',
                'slug' => 'payroll.sector.types.delete',
                'description' => 'Acceso para eliminar datos de tipos de sector',
                'model' => 'Modules\Payroll\Models\PayrollSectorType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sector.eliminar',
            ],
            /* driver licenses */
            /*[
            'name' => 'Ver los datos de grados de licencia de conducir',
            'slug' => 'payroll.license.degrees.list',
            'description' => 'Acceso para ver los datos de grados de licencia de conducir',
            'model' => 'Modules\Payroll\Models\PayrollLicenseDegree',
            'model_prefix' => 'Talento Humano',
            'slug_alt' => 'grado.licencia.ver'
            ],*/
            [
                'name' => 'Crear datos de grados de licencia de conducir',
                'slug' => 'payroll.license.degrees.create',
                'description' => 'Acceso para crear datos de grados de licencia de conducir',
                'model' => 'Modules\Payroll\Models\PayrollLicenseDegree',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.licencia.crear',
            ],
            [
                'name' => 'Editar datos de grados de licencia de conducir',
                'slug' => 'payroll.license.degrees.edit',
                'description' => 'Acceso para editar datos de grados de licencia de conducir',
                'model' => 'Modules\Payroll\Models\PayrollLicenseDegree',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.licencia.editar',
            ],
            [
                'name' => 'Eliminar datos de grado de licencia de conducir',
                'slug' => 'payroll.license.degrees.delete',
                'description' => 'Acceso para eliminar datos de grados de licencia de conducir',
                'model' => 'Modules\Payroll\Models\PayrollLicenseDegree',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grado.licencia.eliminar',
            ],
            /* blood types */
            /*[
            'name' => 'Ver los datos de tipos de sangre',
            'slug' => 'payroll.blood.types.list',
            'description' => 'Acceso para ver los datos de tipos de sangre',
            'model' => 'Modules\Payroll\Models\PayrollBloodType',
            'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.sangre.ver'
            ],*/
            [
                'name' => 'Crear datos de tipos de sangre',
                'slug' => 'payroll.blood.types.create',
                'description' => 'Acceso para crear datos de tipos de sangre',
                'model' => 'Modules\Payroll\Models\PayrollBloodType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sangre.crear',
            ],
            [
                'name' => 'Editar datos de tipos de sangre',
                'slug' => 'payroll.blood.types.edit',
                'description' => 'Acceso para editar datos de tipos de sangre',
                'model' => 'Modules\Payroll\Models\PayrollBloodType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sangre.editar',
            ],
            [
                'name' => 'Eliminar datos de tipos de sangre',
                'slug' => 'payroll.blood.types.delete',
                'description' => 'Acceso para eliminar datos de tipos de sangre',
                'model' => 'Modules\Payroll\Models\PayrollBloodType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.sangre.eliminar',
            ],
            /* Responsables de ARC */
            [
                'name' => 'Obtener listado de responsables de ARC',
                'slug' => 'payroll.arc.responsibles.index',
                'description' => 'Acceso para obtener listado de responsables de ARC',
                'model' => 'Modules\Payroll\Models\ArcResponsible',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsables.arc.listado',
                'short_description' => 'Acceder al listado de responsables de ARC',
            ],
            [
                'name' => 'Registrar un responsable de ARC',
                'slug' => 'payroll.arc.responsibles.store',
                'description' => 'Acceso para registrar un responsable de ARC',
                'model' => 'Modules\Payroll\Models\ArcResponsible',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsables.arc.crear',
                'short_description' => 'Registrar un responsable de ARC',
            ],
            [
                'name' => 'Actualizar un responsable de ARC',
                'slug' => 'payroll.arc.responsibles.update',
                'description' => 'Acceso para actualizar un responsable de ARC',
                'model' => 'Modules\Payroll\Models\ArcResponsible',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsables.arc.actualizar',
                'short_description' => 'Actualizar un responsable de ARC',
            ],
            [
                'name' => 'Eliminar un responsable de ARC',
                'slug' => 'payroll.arc.responsibles.destroy',
                'description' => 'Acceso para eliminar un responsable de ARC',
                'model' => 'Modules\Payroll\Models\ArcResponsible',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'responsables.arc.eliminar',
                'short_description' => 'Eliminar un responsable de ARC',
            ],
            /* Solicitudes de ARC */
            [
                'name' => 'Obtener listado de ARC',
                'slug' => 'payroll.arc.list',
                'description' => 'Acceso para obtener listado de ARC',
                'model' => 'Modules\Payroll\Models\Arc',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'arc.listado',
                'short_description' => 'Acceder al listado de ARC',
            ],
            [
                'name' => 'Exportar planilla ARC',
                'slug' => 'payroll.arc.export',
                'description' => 'Acceso para exportar planilla de ARC',
                'model' => 'Modules\Payroll\Models\Arc',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'arc.exportar',
                'short_description' => 'Exportar planilla ARC',
            ],
            [
                'name' => 'Enviar planilla ARC',
                'slug' => 'payroll.arc.send',
                'description' => 'Acceso para enviar planilla de ARC',
                'model' => 'Modules\Payroll\Models\Arc',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'arc.enviar',
                'short_description' => 'Enviar planilla ARC',
            ],
            /* socioeconomics */
            [
                'name' => 'Ver los datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.list',
                'description' => 'Acceso para ver los datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.ver',
            ],
            [
                'name' => 'Crear datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.create',
                'description' => 'Acceso para crear datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.crear',
            ],
            [
                'name' => 'Editar datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.edit',
                'description' => 'Acceso para editar datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.editar',
            ],
            [
                'name' => 'Eliminar datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.delete',
                'description' => 'Acceso para eliminar datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.eliminar',
            ],
            [
                'name' => 'Importar datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.import',
                'description' => 'Acceso para importar datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.importar',
            ],
            [
                'name' => 'Exportar datos socioeconómicos',
                'slug' => 'payroll.socioeconomics.export',
                'description' => 'Acceso para exportar datos socioeconómicos',
                'model' => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'socioeconomico.exportar',
            ],
            /* Professionals */
            [
                'name' => 'Ver los datos de profesionales',
                'slug' => 'payroll.professionals.list',
                'description' => 'Acceso para ver los datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.ver',
            ],
            [
                'name' => 'Crear datos profesionales',
                'slug' => 'payroll.professionals.create',
                'description' => 'Acceso para crear datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.crear',
            ],
            [
                'name' => 'Editar datos profesionales',
                'slug' => 'payroll.professionals.edit',
                'description' => 'Acceso para editar datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.editar',
            ],
            [
                'name' => 'Eliminar datos profesionales',
                'slug' => 'payroll.professionals.delete',
                'description' => 'Acceso para eliminar datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.eliminar',
            ],
            [
                'name' => 'Importar datos profesionales',
                'slug' => 'payroll.professionals.import',
                'description' => 'Acceso para importar datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.importar',
            ],
            [
                'name' => 'Exportar datos profesionales',
                'slug' => 'payroll.professionals.export',
                'description' => 'Acceso para exportar datos profesionales',
                'model' => 'Modules\Payroll\Models\PayrollProfessional',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'profesional.exportar',
            ],
            /* Employments */
            [
                'name' => 'Ver los datos laborales',
                'slug' => 'payroll.employments.list',
                'description' => 'Acceso para ver los datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.ver',
            ],
            [
                'name' => 'Crear datos laborales',
                'slug' => 'payroll.employments.create',
                'description' => 'Acceso para crear datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.crear',
            ],
            [
                'name' => 'Editar datos laborales',
                'slug' => 'payroll.employments.edit',
                'description' => 'Acceso para editar datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.editar',
            ],
            [
                'name' => 'Eliminar datos laborales',
                'slug' => 'payroll.employments.delete',
                'description' => 'Acceso para eliminar datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.eliminar',
            ],
            [
                'name' => 'Importar datos laborales',
                'slug' => 'payroll.employments.import',
                'description' => 'Acceso para importar datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.importar',
            ],
            [
                'name' => 'Exportar datos laborales',
                'slug' => 'payroll.employments.export',
                'description' => 'Acceso para exportar datos laborales',
                'model' => 'Modules\Payroll\Models\PayrollEmployment',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'laboral.exportar',
            ],
            /* Settlement types */
            /*[
            'name' => 'Ver los tipos de liquidación',
            'slug' => 'payroll.settlement.types.list',
            'description' => 'Acceso para ver los tipos de liquidación',
            'model' => 'Modules\Payroll\Models\PayrollSettlementType',
            'model_prefix' => 'Talento Humano',
            'slug_alt' => 'tipo.liquidacion.ver'
            ],
            [
                'name' => 'Crear tipos de liquidación',
                'slug' => 'payroll.settlement.types.create',
                'description' => 'Acceso para crear tipos de liquidación',
                'model' => 'Modules\Payroll\Models\PayrollSettlementType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.liquidacion.crear',
            ],
            [
                'name' => 'Editar tipos de liquidación',
                'slug' => 'payroll.settlement.types.edit',
                'description' => 'Acceso para editar tipos de liquidación',
                'model' => 'Modules\Payroll\Models\PayrollSettlementType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.liquidacion.editar',
            ],
            [
                'name' => 'Eliminar tipos de liquidación',
                'slug' => 'payroll.settlement.types.delete',
                'description' => 'Acceso para eliminar tipos de liquidación',
                'model' => 'Modules\Payroll\Models\PayrollSettlementType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.liquidacion.eliminar',
            ],*/
            /* Relationships */
            [
                'name' => 'Ver los parentescos',
                'slug' => 'payroll.relationships.list',
                'description' => 'Acceso para ver los parentescos',
                'model' => 'Modules\Payroll\Models\PayrollRelationship',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'parentesco.ver'
            ],
            [
                'name' => 'Crear parentescos',
                'slug' => 'payroll.relationships.create',
                'description' => 'Acceso para crear parentescos',
                'model' => 'Modules\Payroll\Models\PayrollRelationship',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'parentesco.crear'
            ],
            [
                'name' => 'Editar parentescos',
                'slug' => 'payroll.relationships.edit',
                'description' => 'Acceso para editar parentescos',
                'model' => 'Modules\Payroll\Models\PayrollRelationship',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'parentesco.editar'
            ],
            [
                'name' => 'Eliminar parentescos',
                'slug' => 'payroll.relationships.delete',
                'description' => 'Acceso para eliminar parentescos',
                'model' => 'Modules\Payroll\Models\PayrollRelationship',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'parentesco.eliminar'
            ],
            /* Disabilities */
            /*[
            'name' => 'Ver las discapacidades',
            'slug' => 'payroll.disabilities.list',
            'description' => 'Acceso para ver las discapacidades',
            'model' => 'Modules\Payroll\Models\PayrollDisability',
            'model_prefix' => 'Talento Humano',
            'slug_alt' => 'discapacidad.ver'
            ],*/
            [
                'name' => 'Crear discapacidades',
                'slug' => 'payroll.disabilities.create',
                'description' => 'Acceso para crear discapacidades',
                'model' => 'Modules\Payroll\Models\PayrollDisability',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'discapacidad.crear',
            ],
            [
                'name' => 'Editar discapacidades',
                'slug' => 'payroll.disabilities.edit',
                'description' => 'Acceso para editar discapacidades',
                'model' => 'Modules\Payroll\Models\PayrollDisability',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'discapacidad.editar',
            ],
            [
                'name' => 'Eliminar discapacidades',
                'slug' => 'payroll.disabilities.delete',
                'description' => 'Acceso para eliminar discapacidades',
                'model' => 'Modules\Payroll\Models\PayrollDisability',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'discapacidad.eliminar',
            ],
            /* Vacation requests */
            [
                'name' => 'Ver solicitudes de vacaciones',
                'slug' => 'payroll.vacation.requests.list',
                'description' => 'Acceso para ver solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.ver',
            ],
            [
                'name' => 'Crear solicitudes de vacaciones',
                'slug' => 'payroll.vacation.requests.create',
                'description' => 'Acceso para crear solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.crear',
            ],
            [
                'name' => 'Editar solicitudes de vacaciones',
                'slug' => 'payroll.vacation.requests.edit',
                'description' => 'Acceso para editar los solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.editar',
            ],
            [
                'name' => 'Eliminar solicitudes de vacaciones',
                'slug' => 'payroll.vacation.requests.delete',
                'description' => 'Acceso para eliminar solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.eliminar',
            ],
            [
                'name' => 'Aprobar solicitud de vacaciones',
                'slug' => 'payroll.vacation.requests.approved',
                'description' => 'Acceso para aprobar solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.aprobar',
            ],
            [
                'name' => 'Rechazar solicitud de vacaciones',
                'slug' => 'payroll.vacation.requests.rejected',
                'description' => 'Acceso para Rechazar solicitudes de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollVacationRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.vacaciones.rechazar',
            ],
            /* Suspension Vacation Requests */
            [
                'name' => 'Ver solicitudes de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.list',
                'description' => 'Acceso para ver solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.ver',
            ],
            [
                'name' => 'Crear solicitudes de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.create',
                'description' => 'Acceso para crear solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.crear',
            ],
            [
                'name' => 'Editar solicitudes de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.edit',
                'description' => 'Acceso para editar las solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.editar',
            ],
            [
                'name' => 'Eliminar solicitudes de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.delete',
                'description' => 'Acceso para eliminar solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.eliminar',
            ],
            [
                'name' => 'Aprobar solicitud de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.approved',
                'description' => 'Acceso para aprobar solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.aprobar',
            ],
            [
                'name' => 'Rechazar solicitud de suspensión de vacaciones',
                'slug' => 'payroll.suspension.vacation.requests.rejected',
                'description' => 'Acceso para Rechazar solicitudes de suspensión de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollSuspensionVacationRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.suspension.vacaciones.rechazar',
            ],
            /* Rescheduled vacations */

            /* Benefits requests */
            [
                'name' => 'Ver solicitudes de prestaciones',
                'slug' => 'payroll.benefits.requests.list',
                'description' => 'Acceso para ver solicitudes de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollBenefitsRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.prestaciones.ver',
            ],
            [
                'name' => 'Crear solicitudes de prestaciones',
                'slug' => 'payroll.benefits.requests.create',
                'description' => 'Acceso para crear solicitudes de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollBenefitsRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.prestaciones.crear',
            ],
            [
                'name' => 'Editar solicitudes de prestaciones',
                'slug' => 'payroll.benefits.requests.edit',
                'description' => 'Acceso para editar los solicitudes de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollBenefitsRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.prestaciones.editar',
            ],
            [
                'name' => 'Eliminar solicitudes de prestaciones',
                'slug' => 'payroll.benefits.requests.delete',
                'description' => 'Acceso para eliminar solicitudes de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollBenefitsRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.prestaciones.eliminar',
            ],
            [
                'name' => 'Procesar solicitud de prestaciones',
                'slug' => 'payroll.benefits.requests.update',
                'description' => 'Acceso para procesar solicitudes de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollBenefitsRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.prestaciones.editar',
            ],
            /* Benefits requests */
            [
                'name' => 'Ver solicitudes de permisos',
                'slug' => 'payroll.permission.requests.list',
                'description' => 'Acceso para ver solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.ver',
            ],
            [
                'name' => 'Crear solicitudes de permisos',
                'slug' => 'payroll.permission.requests.create',
                'description' => 'Acceso para crear solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.crear',
            ],
            [
                'name' => 'Editar solicitudes de permisos',
                'slug' => 'payroll.permission.requests.edit',
                'description' => 'Acceso para editar los solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.editar',
            ],
            [
                'name' => 'Eliminar solicitudes de permisos',
                'slug' => 'payroll.permission.requests.delete',
                'description' => 'Acceso para eliminar solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.eliminar',
            ],
            [
                'name' => 'Aprobar solicitud de permisos',
                'slug' => 'payroll.permission.requests.approved',
                'description' => 'Acceso para aprobar solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.aprobar',
            ],
            [
                'name' => 'Rechazar solicitud de permiso',
                'slug' => 'payroll.permission.requests.rejected',
                'description' => 'Acceso para rechazar solicitudes de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.permisos.rechazar',
            ],
            /* Report parameters */
            [
                'name' => 'Configuracion de parámetros para los reportes de nómina',
                'slug' => 'payroll.parameters.create',
                'description' => 'Acceso para modificar los parámetros para los reportes de nómina',
                'model' => 'App\Models\Parameter',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'parametro.crear',
            ],

            /* global parameters */
            [
                'name' => 'Crear parámetros globales',
                'slug' => 'payroll.parameters.create',
                'description' => 'Acceso para crear parámetros globales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.parametros.crear',
            ],
            [
                'name' => 'Editar parámetros globales',
                'slug' => 'payroll.parameters.edit',
                'description' => 'Acceso para editar parámetros globales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.parametro.editar',
            ],
            [
                'name' => 'Eliminar parámetros globales',
                'slug' => 'payroll.parameters.delete',
                'description' => 'Acceso para eliminar parámetros globales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'solicitud.parametro.eliminar',
            ],
            /* Salary scale */
            [
                'name' => 'Crear escalafón salarial',
                'slug' => 'payroll.setting.salary.scale.create',
                'description' => 'Acceso para crear escalafón salarial',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'escalafón.salarial.crear',
            ],
            [
                'name' => 'Editar escalafón salarial',
                'slug' => 'payroll.setting.salary.scale.edit',
                'description' => 'Acceso para editar los escalafones salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'escalafón.salarial.editar',
            ],
            [
                'name' => 'Eliminar escalafón salarial',
                'slug' => 'payroll.setting.salary.scale.delete',
                'description' => 'Acceso para eliminar escalafón salarial',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'escalafón.salarial.eliminar',
            ],
            /* Salary tabulator */
            [
                'name' => 'Crear Tabulador de nómina',
                'slug' => 'payroll.setting.salary.tabulator.create',
                'description' => 'Acceso para crear tabulador de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tabulador.nómina.crear',
            ],
            [
                'name' => 'Editar tabulador de nómina',
                'slug' => 'payroll.setting.salary.tabulator.edit',
                'description' => 'Acceso para editar tabulador de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tabulador.nómina.editar',
            ],
            [
                'name' => 'Eliminar tabulador de nómina',
                'slug' => 'payroll.setting.salary.tabulator.delete',
                'description' => 'Acceso para eliminar tabulador de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tabulador.nómina.eliminar',
            ],
            [
                'name' => 'Importar registros de la planilla de tabulador de nómina',
                'slug' => 'payroll.salary.tabulators.import',
                'description' => 'Acceso para importar los registros de la planilla de tabulador de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tabulador.nómina.importar',
            ],
            [
                'name' => 'Exportar registros del tabulador de nómina',
                'slug' => 'payroll.salary.tabulators.export',
                'description' => 'Acceso para exportar los registros de tabulador de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tabulador.nómina.exportar',
            ],
            /* Payment types */
            [
                'name' => 'Crear tipos de pago',
                'slug' => 'payroll.payment.types.create',
                'description' => 'Acceso para crear tipos de pago',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipos.pago.crear',
            ],
            [
                'name' => 'Editar tipos de pago',
                'slug' => 'payroll.payment.types.edit',
                'description' => 'Acceso para editar tipos de pago',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipos.pago.editar',
            ],
            [
                'name' => 'Eliminar tipos de pago',
                'slug' => 'payroll.payment.types.delete',
                'description' => 'Acceso para eliminar tipos de pago',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipos.pago.eliminar',
            ],
            /* Vacation policies */
            [
                'name' => 'Crear políticas vacacionales',
                'slug' => 'payroll.vacation.policies.create',
                'description' => 'Acceso para crear políticas vacacionales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.vacacional.crear',
            ],
            [
                'name' => 'Editar políticas vacacionales',
                'slug' => 'payroll.vacation.policies.edit',
                'description' => 'Acceso para editar políticas vacacionales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.vacacional.editar',
            ],
            [
                'name' => 'Eliminar políticas vacacionales',
                'slug' => 'payroll.vacation.policies.delete',
                'description' => 'Acceso para eliminar políticas vacacionales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.vacacional.eliminar',
            ],
            /* Benefits policies */
            [
                'name' => 'Crear políticas de prestaciones',
                'slug' => 'payroll.benefits.policies.create',
                'description' => 'Acceso para crear política de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.prestaciones.crear',
            ],
            [
                'name' => 'Editar políticas de prestaciones',
                'slug' => 'payroll.benefits.policies.edit',
                'description' => 'Acceso para editar política prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.prestaciones.editar',
            ],
            [
                'name' => 'Eliminar políticas de prestaciones',
                'slug' => 'payroll.benefits.policies.delete',
                'description' => 'Acceso para eliminar política de prestaciones',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.prestaciones.eliminar',
            ],
            /* Permission policies */
            [
                'name' => 'Crear políticas de permisos',
                'slug' => 'payroll.permission.policies.create',
                'description' => 'Acceso para crear política de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.permisos.crear',
            ],
            [
                'name' => 'Editar políticas de permisos',
                'slug' => 'payroll.permission.policies.edit',
                'description' => 'Acceso para editar política de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.permisos.editar',
            ],
            [
                'name' => 'Eliminar políticas de permisos',
                'slug' => 'payroll.permission.policies.delete',
                'description' => 'Acceso para eliminar política de permisos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'política.permisos.eliminar',
            ],
            /* Salary adjustments salary-adjustments */
            [
                'name' => 'Ver ajustes en tablas salariales',
                'slug' => 'payroll.salary.adjustments.list',
                'description' => 'Acceso para ver ajustes en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.ver',
            ],
            [
                'name' => 'Crear ajustes en tablas salariales',
                'slug' => 'payroll.salary.adjustments.create',
                'description' => 'Acceso para crear ajustes en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.crear',
            ],
            [
                'name' => 'Editar ajustes en tablas salariales',
                'slug' => 'payroll.salary.adjustments.edit',
                'description' => 'Acceso para editar ajustes en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.editar',
            ],
            [
                'name' => 'Eliminar ajustes en tablas salariales',
                'slug' => 'payroll.salary.adjustments.delete',
                'description' => 'Acceso para eliminar ajustes en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.eliminar',
            ],
            [
                'name' => 'Importar registros de la planilla de ajuste en tablas salariales',
                'slug' => 'payroll.salary.adjustments.import',
                'description' => 'Acceso para importar los registros de la planilla de ajuste en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.importar',
            ],
            [
                'name' => 'Exportar registros del ajuste en tablas salariales',
                'slug' => 'payroll.salary.adjustments.export',
                'description' => 'Acceso para exportar los registros de ajuste en tablas salariales',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'ajustes.tablas.salariales.exportar',
            ],
            /* registers */
            [
                'name' => 'Listar registros de nómina',
                'slug' => 'payroll.registers.list',
                'description' => 'Acceso para listar registros de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.list',
            ],
            [
                'name' => 'Crear registros de nómina',
                'slug' => 'payroll.registers.create',
                'description' => 'Acceso para crear registros de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.crear',
            ],
            [
                'name' => 'Editar registros de nómina',
                'slug' => 'payroll.registers.edit',
                'description' => 'Acceso para editar registros de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.editar',
            ],
            [
                'name' => 'Cerrar registros de nómina',
                'slug' => 'payroll.registers.close',
                'description' => 'Acceso para cerrar registros de nómina',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.close',
            ],
            [
                'name' => 'Generar reporte del registro',
                'slug' => 'payroll.registers.report',
                'description' => 'Acceso para generar reporte',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.report',
            ],
            /* Reports */
            // [
            //     'name' => 'Crear reporte en talento humano',
            //     'slug' => 'payroll.reports.create',
            //     'description' => 'Acceso para crear reporte en talento humano',
            //     'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
            //     'model_prefix' => 'Talento Humano',
            //     'slug_alt' => 'reporte.talento_humano.crear',
            // ],
            //Solicitudes de vacaciones
            [
                'name' => 'Crear reporte de Solicitud de Vacaciones',
                'slug' => 'payroll.reports.vacationrequests',
                'description' => 'Acceso para crear reporte de Solicitud de Vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.vacationRequests.crear',
            ],
            /* reporte de los registros de los empleados */
            [
                'name' => 'Crear reporte detallado de trabajadores',
                'slug' => 'payroll.reports.employment.status',
                'description' => 'Acceso para crear reporte detallado de trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.employment.status.crear',
            ],
            /* Permisos de Ruta que permite generar el reporte de los empleados */
            [
                'name' => 'Crear reporte de trabajadores',
                'slug' => 'payroll.reports.staff',
                'description' => 'Acceso para crear reporte de trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.staff.crear',
            ],
            /* Permisos de Ruta que permite generar el reporte de disfrute de vacaciones */
            [
                'name' => 'Crear reporte de disfrute de vacaciones',
                'slug' => 'payroll.reports.staffvacationenjoyment',
                'description' => 'Acceso para crear reporte de disfrute de vacaciones',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.staffVacationEnjoyment.crear',
            ],
            /* Permisos de Ruta que permite generar el reporte de conceptos */
            [
                'name' => 'Crear reporte de conceptos',
                'slug' => 'payroll.reports.concepts',
                'description' => 'Acceso para crear reporte de conceptos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.concepts.crear',
            ],
            /* Permisos de Ruta que permite generar el reporte de relación de conceptos */
            [
                'name' => 'Crear reporte de relación de conceptos',
                'slug' => 'payroll.reports.relationship.concepts',
                'description' => 'Acceso para crear reporte de relación de conceptos',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.relationship.concepts.crear',
            ],
            /* Permisos de Ruta que permite generar el reporte de recibos de pago */
            [
                'name' => 'Crear reporte de recibos de pago',
                'slug' => 'payroll.reports.payment.receipts',
                'description' => 'Acceso para crear reporte de recibos de pago',
                'model' => 'Modules\Payroll\Models\PayrollPermissionRequest',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.payment.receipts.crear',
            ],
            /* Permisos de datos financieros */
            [
                'name' => 'Crear datos financieros',
                'slug' => 'payroll.financials.create',
                'description' => 'Acceso para crear datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.crear',
                'short_description' => 'Crear datos financieros',
            ],
            [
                'name' => 'Ver datos financieros',
                'slug' => 'payroll.financials.list',
                'description' => 'Acceso para ver datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.ver',
                'short_description' => 'Ver datos financieros',
            ],
            [
                'name' => 'Editar datos financieros',
                'slug' => 'payroll.financials.edit',
                'description' => 'Acceso para editar datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.editar',
                'short_description' => 'Editar datos financieros',
            ],
            [
                'name' => 'Eliminar datos financieros',
                'slug' => 'payroll.financials.delete',
                'description' => 'Acceso para eliminar datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.eliminar',
                'short_description' => 'Eliminar datos financieros',
            ],
            [
                'name' => 'Importar datos financieros',
                'slug' => 'payroll.financials.import',
                'description' => 'Acceso para importar datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.importar',
                'short_description' => 'Importar datos financieros',
            ],
            [
                'name' => 'Exportar datos financieros',
                'slug' => 'payroll.financials.export',
                'description' => 'Acceso para exportar datos financieros en talento humanos',
                'model' => 'Modules\Payroll\Models\PayrollFinancial',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'financial.exportar',
                'short_description' => 'Exportar datos financieros',
            ],
            /* Permisos de grupos de supervisados */
            [
                'name' => 'Listar el grupo de supervisados',
                'slug' => 'payroll.supervisedgroup.index',
                'description' => 'Acceso para listar el grupo de supervisados',
                'model' => 'Modules\Payroll\Models\PayrollSupervisedGroup', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.supervisados.listar',
            ],
            [
                'name' => 'Crear el grupo de supervisados',
                'slug' => 'payroll.supervisedgroup.create',
                'description' => 'Acceso para crear el grupo de supervisados',
                'model' => 'Modules\Payroll\Models\PayrollSupervisedGroup', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.supervisados.crear',
            ],
            [
                'name' => 'Editar el grupo de supervisados',
                'slug' => 'payroll.supervisedgroup.edit',
                'description' => 'Acceso para editar el grupo de supervisados',
                'model' => 'Modules\Payroll\Models\PayrollSupervisedGroup', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.supervisados.editar',
            ],
            [
                'name' => 'Eliminar el grupo de supervisados',
                'slug' => 'payroll.supervisedgroup.delete',
                'description' => 'Acceso para eliminar el grupo de supervisados',
                'model' => 'Modules\Payroll\Models\PayrollSupervisedGroup', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.supervisados.eliminar',
            ],
            /* scholarship-types */
            [
                'name' => 'Crear Tipos de beca',
                'slug' => 'payroll.scholarship.types.create',
                'description' => 'Acceso para crear tipos de beca',
                'model' => 'Modules\Payroll\Models\PayrollScholarshipType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.beca.crear',
            ],
            [
                'name' => 'Editar Tipos de beca',
                'slug' => 'payroll.scholarship.types.edit',
                'description' => 'Acceso para editar tipos de beca',
                'model' => 'Modules\Payroll\Models\PayrollScholarshipType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.beca.editar',
            ],
            [
                'name' => 'Eliminar Tipos de beca',
                'slug' => 'payroll.scholarship.types.delete',
                'description' => 'Acceso para eliminar tipos de beca',
                'model' => 'Modules\Payroll\Models\PayrollScholarshipType',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.beca.eliminar',
            ],
            /* Contract types */
            [
                'name' => 'Crear datos de tipos de excepciones',
                'slug' => 'payroll.exception.types.create',
                'description' => 'Acceso para crear datos de tipos de excepciones',
                'model' => 'Modules\Payroll\Models\PayrollExceptionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.excepciones.crear',
            ],
            [
                'name' => 'Editar datos de tipos de excepciones',
                'slug' => 'payroll.exception.types.edit',
                'description' => 'Acceso para editar datos de tipos de excepciones',
                'model' => 'Modules\Payroll\Models\PayrollExceptionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.excepciones.editar',
            ],
            [
                'name' => 'Eliminar datos de tipos de excepciones',
                'slug' => 'payroll.exception.types.delete',
                'description' => 'Acceso para eliminar datos de tipos de excepciones',
                'model' => 'Modules\Payroll\Models\PayrollExceptionType', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'tipo.excepciones.eliminar',
            ],
            /* Permisos de parámetros de hoja de tiempo */
            [
                'name' => 'Listar los parámetros de hoja de tiempo',
                'slug' => 'payroll.timesheetparameter.index',
                'description' => 'Acceso para listar los parámetros de hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetParameter', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.parametros.hoja.tiempo.listar',
            ],
            [
                'name' => 'Crear los parámetros de hoja de tiempo',
                'slug' => 'payroll.timesheetparameter.create',
                'description' => 'Acceso para crear los parámetros de hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetParameter', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.parametros.hoja.tiempo.crear',
            ],
            [
                'name' => 'Editar los parámetros de hoja de tiempo',
                'slug' => 'payroll.timesheetparameter.edit',
                'description' => 'Acceso para editar los parámetros de hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetParameter', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.parametros.hoja.tiempo.editar',
            ],
            [
                'name' => 'Eliminar los parámetros de hoja de tiempo',
                'slug' => 'payroll.timesheetparameter.delete',
                'description' => 'Acceso para eliminar los parámetros de hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetParameter', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'grupo.parametros.hoja.tiempo.eliminar',
            ],
            /* Permisos de datos contables del trabajador */
            [
                'name' => 'Listar los datos contables de los trabajadores',
                'slug' => 'payroll.staffaccount.index',
                'description' => 'Acceso para listar los datos contables de los trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.listar',
            ],
            [
                'name' => 'Crear los datos contables de los trabajadores',
                'slug' => 'payroll.staffaccount.create',
                'description' => 'Acceso para crear los datos contables de los trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.crear',
            ],
            [
                'name' => 'Editar los datos contables de los trabajadores',
                'slug' => 'payroll.staffaccount.edit',
                'description' => 'Acceso para editar los datos contables de los trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.editar',
            ],
            [
                'name' => 'Eliminar los datos contables de los trabajadores',
                'slug' => 'payroll.staffaccount.delete',
                'description' => 'Acceso para eliminar los datos contables de los trabajadores',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.eliminar',
            ],
            [
                'name' => 'Importar datos contables',
                'slug' => 'payroll.staffaccount.import',
                'description' => 'Acceso para importar datos contables',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.importar',
            ],
            [
                'name' => 'Exportar datos contables',
                'slug' => 'payroll.staffaccount.export',
                'description' => 'Acceso para exportar datos contables',
                'model' => 'Modules\Payroll\Models\PayrollStaffAccount', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'cuenta.trabajadores.exportar',
            ],
            /* Permisos archivo txt de nómina */
            [
                'name' => 'Crear archivo txt de nómina',
                'slug' => 'payroll.txt.file.create',
                'description' => 'Acceso para visualizar formulario del archivo txt de nómina',
                'model' => 'Modules\Payroll\Models\PayrollTextFile', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'archivo.txt.crear',
            ],

            /* Permisos para generar reporte presupuestario de nómina */
            [
                'name' => 'Generar reporte presupuestario de nómina',
                'slug' => 'payroll.budget.report.getbudgetaccountingreport',
                'description' => 'Acceso para generar reporte presupuestario de nómina',
                'model' => 'Modules\Payroll\Models\PayrollTextFileController', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'reporte.presupuestario.crear',
            ],

            /* Permisos para solicitar disponibilidad presupuestaria (nómina) */
            [
                'name' => 'Solicitar disponibilidad presupuestaria (nómina)',
                'slug' => 'payroll.availability.request',
                'description' => 'Acceso para solicitar disponibilidad presupuestaria (nómina)',
                'model' => 'Modules\Payroll\Models\PayrollController', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'disponiblidad.presupuestaria.solicitar',
            ],
            /* Permisos de hoja de tiempo */
            [
                'name' => 'Listar hoja de tiempo',
                'slug' => 'payroll.timesheet.index',
                'description' => 'Acceso para listar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.listar',
            ],
            [
                'name' => 'Crear hoja de tiempo',
                'slug' => 'payroll.timesheet.create',
                'description' => 'Acceso para crear hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.crear',
            ],
            [
                'name' => 'Editar hoja de tiempo',
                'slug' => 'payroll.timesheet.edit',
                'description' => 'Acceso para editar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.editar',
            ],
            [
                'name' => 'Eliminar hoja de tiempo',
                'slug' => 'payroll.timesheet.delete',
                'description' => 'Acceso para eliminar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.eliminar',
            ],
            [
                'name' => 'Aprobar hoja de tiempo',
                'slug' => 'payroll.timesheet.approve',
                'description' => 'Acceso para aprobar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.aprobar',
            ],
            [
                'name' => 'Rechazar hoja de tiempo',
                'slug' => 'payroll.timesheet.reject',
                'description' => 'Acceso para rechazar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.rechazar',
            ],
            [
                'name' => 'Confirmar hoja de tiempo',
                'slug' => 'payroll.timesheet.confirm',
                'description' => 'Acceso para confirmar hoja de tiempo',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheet', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.confirmar',
            ],
            /* Permisos de hoja de tiempo pendiente */
            [
                'name' => 'Listar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.index',
                'description' => 'Acceso para listar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.listar',
            ],
            [
                'name' => 'Crear hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.create',
                'description' => 'Acceso para crear hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.crear',
            ],
            [
                'name' => 'Editar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.edit',
                'description' => 'Acceso para editar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.editar',
            ],
            [
                'name' => 'Eliminar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.delete',
                'description' => 'Acceso para eliminar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.eliminar',
            ],
            [
                'name' => 'Aprobar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.approve',
                'description' => 'Acceso para aprobar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.aprobar',
            ],
            [
                'name' => 'Rechazar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.reject',
                'description' => 'Acceso para rechazar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.rechazar',
            ],
            [
                'name' => 'Confirmar hoja de tiempo de pendientes',
                'slug' => 'payroll.timesheetpending.confirm',
                'description' => 'Acceso para confirmar hoja de tiempo de pendientes',
                'model' => 'Modules\Payroll\Models\PayrollTimeSheetPending', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'hoja.tiempo.pendiente.confirmar',
            ],
            /* Permisos de esquemas de guardias */
            [
                'name' => 'Listar esquemas de guardias',
                'slug' => 'payroll.guard.scheme.index',
                'description' => 'Acceso para listar los esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.listar',
            ],
            [
                'name' => 'Crear esquemas de guardias',
                'slug' => 'payroll.guard.scheme.create',
                'description' => 'Acceso para crear esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.crear',
            ],
            [
                'name' => 'Editar esquemas de guardias',
                'slug' => 'payroll.guard.scheme.edit',
                'description' => 'Acceso para editar esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.editar',
            ],
            [
                'name' => 'Eliminar esquemas de guardias',
                'slug' => 'payroll.guard.scheme.delete',
                'description' => 'Acceso para eliminar esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.eliminar',
            ],
            [
                'name' => 'Aprobar esquemas de guardias',
                'slug' => 'payroll.guard.scheme.approve',
                'description' => 'Acceso para aprobar esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.aprobar',
            ],
            [
                'name' => 'Confirmar esquemas de guardias',
                'slug' => 'payroll.guard.scheme.confirm',
                'description' => 'Acceso para confirmar esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.confirmar',
            ],
            [
                'name' => 'Solicitar revisión de esquemas de guardias',
                'slug' => 'payroll.guard.scheme.request.review',
                'description' => 'Acceso para solicitar revisión de esquemas de guardias',
                'model' => 'Modules\Payroll\Models\PayrollGuardScheme', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'esquema.guardia.solicitar.revision',
            ],
            /* Permisos de la planilla ARI */
            [
                'name' => 'Crear registro de la planilla ARI',
                'slug' => 'payroll.ariregister.create',
                'description' => 'Acceso para crear los registros de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.crear',
            ],
            [
                'name' => 'Editar registro de la planilla ARI',
                'slug' => 'payroll.ariregister.edit',
                'description' => 'Acceso para editar los registro de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.editar',
            ],
            [
                'name' => 'Eliminar registro de la planilla ARI',
                'slug' => 'payroll.ariregister.delete',
                'description' => 'Acceso para eliminar los registro de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.eliminar',
            ],
            [
                'name' => 'Visualizar registro de la planilla ARI',
                'slug' => 'payroll.ariregister.list',
                'description' => 'Acceso para visualizar los registro de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.ver',
            ],
            [
                'name' => 'Importar registros de la planilla ARI',
                'slug' => 'payroll.ariregister.import',
                'description' => 'Acceso para importar los registro de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.importar',
            ],
            [
                'name' => 'Exportar registros de la planilla ARI',
                'slug' => 'payroll.ariregister.export',
                'description' => 'Acceso para exportar los registro de la planilla ARI',
                'model' => 'Modules\Payroll\Models\PayrollAriRegister', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'planilla.ari.exportar',
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de talento humano',
                'slug'              => 'payroll.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'Talento Humano',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de talento humano'
            ],
            /* Permisos envio de recibos de pago */
            [
                'name' => 'Enviar recibos de pago',
                'slug' => 'payroll.payment.receipts.send',
                'description' => 'Permiso para enviar los recibos de pago',
                'model' => '',
                'model_prefix' => 'Talento Humano',
                'slug_alt' => 'recibos.enviar',
            ]
        ];

        $additionalPermissions = [
            /* Payroll */
            [
                'name' => 'Omitir momentos presupuestarios al cerrar nómina',
                'slug' => 'payroll.registers.moment.close',
                'description' => 'Acceso para ejecutar los momentos presupuestarios al cerrar nómina',
                'model' => 'Modules\Payroll\Models\Payroll', 'model_prefix' => 'Talento Humano',
                'slug_alt' => 'registers.moment.close',
            ],
        ];

        $removePermissions = [
            /* removePermissions Staff classifications */
            [
                'name' => 'Ver la clasificación del personal',
                'slug' => 'payroll.staff.classifications.list',
            ],
            [
                'name' => 'Crear la clasificación del personal',
                'slug' => 'payroll.staff.classifications.create',
            ],
            [
                'name' => 'Editar la clasificación del personal',
                'slug' => 'payroll.staff.classifications.edit',
            ],
            [
                'name' => 'Eliminar la clasificación del personal',
                'slug' => 'payroll.staff.classifications.delete',
            ],
            /* removePermissions Relationships */
            /*
            [
                'name' => 'Ver los parentescos',
                'slug' => 'payroll.relationships.list',
            ],
            [
                'name' => 'Crear parentescos',
                'slug' => 'payroll.relationships.create',
            ],
            [
                'name' => 'Editar parentescos',
                'slug' => 'payroll.relationships.edit',
            ],
            [
                'name' => 'Eliminar parentescos',
                'slug' => 'payroll.relationships.delete',
            ],*/
            /* removePermissions various permissions */
            [
                'name' => 'Ver la nacionalidad',
                'slug' => 'payroll.nationalities.list',
            ],
            [
                'name' => 'Ver los tipos de concepto',
                'slug' => 'payroll.concept.types.list',
            ],
            [
                'name' => 'Ver los niveles de idioma',
                'slug' => 'payroll.language.levels.list',
            ],
            [
                'name' => 'Ver los idiomas',
                'slug' => 'payroll.languages.list',
            ],
            [
                'name' => 'Ver los géneros',
                'slug' => 'payroll.genders.list',
            ],
            [
                'name' => 'Ver los datos de tipos de inactividad',
                'slug' => 'payroll.inactivity.types.list',
            ],
            [
                'name' => 'Ver las discapacidades',
                'slug' => 'payroll.disabilities.list',
            ],
            [
                'name' => 'Ver cargos',
                'slug' => 'payroll.positions.list',
            ],
            [
                'name' => 'Ver tipos de cargo',
                'slug' => 'payroll.position.types.list',
            ],
            [
                'name' => 'Ver el grado de instrucción',
                'slug' => 'payroll.instruction.degrees.list',
            ],
            [
                'name' => 'Ver el tipo de estudio',
                'slug' => 'payroll.study.types.list',
            ],
            [
                'name' => 'Ver los tipos de liquidación',
                'slug' => 'payroll.settlement.types.list',
            ],
            [
                'name' => 'Ver los datos de tipos de sangre',
                'slug' => 'payroll.blood.types.list',
            ],
            [
                'name' => 'Ver los datos de grados de licencia de conducir',
                'slug' => 'payroll.license.degrees.list',
            ],
            [
                'name' => 'Ver los datos de tipos de sector',
                'slug' => 'payroll.sector.types.list',
            ],
            [
                'name' => 'Ver los datos de tipos de contrato',
                'slug' => 'payroll.contract.types.list',
            ],
            [
                'name' => 'Ver tipos de personal',
                'slug' => 'payroll.staff.types.list',
            ],
            [
                'name' => 'Crear reporte en talento humano',
                'slug' => 'payroll.reports.create',
            ],
            /* removePermissions Settlement types */
            [
                'name' => 'Crear tipos de liquidación',
                'slug' => 'payroll.settlement.types.create',
            ],
            [
                'name' => 'Editar tipos de liquidación',
                'slug' => 'payroll.settlement.types.edit',
            ],
            [
                'name' => 'Eliminar tipos de liquidación',
                'slug' => 'payroll.settlement.types.delete',
            ],
            [
                'name' => 'Editar tipos de pagos abiertos',
                'slug' => 'payroll.payment.types.edit.open',
            ],
        ];

        $payrollRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                ]
            );

            $payrollRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }

        foreach ($additionalPermissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                ]
            );
        }

        /* Proceso para eliminar los permisos agregados usando el campo slug */
        foreach ($removePermissions as $permission) {
            $per = Permission::where('slug', $permission['slug']);
            $per->delete();
        }
    }
}
