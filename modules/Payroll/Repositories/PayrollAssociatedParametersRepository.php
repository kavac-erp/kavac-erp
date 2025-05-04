<?php

namespace Modules\Payroll\Repositories;

/**
 * @class      PayrollAssociatedParametersRepository
 * @brief      Gestiona los parámetros asociados a la generación de nómina
 *
 * Clase que gestiona los parámetros asociados a la generación de nómina
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAssociatedParametersRepository
{
    /**
     * Arreglo con los registros asociados a la configuración de vacaciones
     *
     * @var array $associatedVacation
     */
    protected $associatedVacation;

    /**
     * Arreglo con los registros asociados al expediente del trabajador
     *
     * @var array $associatedWorkerFile
     */
    protected $associatedWorkerFile;

    /**
     * Arreglo con los registros asociados a la configuración de prestaciones sociales
     *
     * @var array $associatedBenefit
     */
    protected $associatedBenefit;

    /**
     * Arreglo con los registros de opciones a asignar el concepto
     *
     * @var array $assignTo
     */
    protected $assignTo;

    /**
     * Crea una nueva instancia de la clase
     */
    public function __construct()
    {
        /* Define los campos de la configuración de vacaciones a emplear en el formulario */
        $this->associatedVacation = [
            [
                'id'       => 'VACATION_DAYS',
                'name'     => 'Días a otorgar para el disfrute de vacaciones',
                'model'    => 'Modules\Payroll\Models\PayrollVacationPolicy',
                'required' => ['vacation_days'],
            ],
            [
                'id'       => 'ADDITIONAL_DAYS_PER_YEAR',
                'name'     => 'Días de disfrute adicionales por año de servicio',
                'model'    => 'Modules\Payroll\Models\PayrollVacationPolicy',
                'required' => ['additional_days_per_year'],
            ],
            [
                'id'       => 'DAYS_REQUESTED',
                'name'     => 'Días a otogar para el pago de vacaciones',
                'model'    => 'Modules\Payroll\Models\PayrollVacationRequests',
                'required' => ['days_requested'],
            ]
        ];

        /* Define los campos del expediente del trabajador a emplear en el formulario */
        $this->associatedWorkerFile = [
            [
                'id'       => 'STAFF',
                'name'     => 'Datos Personales',
                'model'    => 'Modules\Payroll\Models\PayrollStaff',
                'required' => [],
                'children' =>  [
                    [
                        'id'        => 'NATIONALITY',
                        'name'      => 'Nacionalidad',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\PayrollNationality',
                        'required'  => ['payroll_nationality_id']
                    ],
                    [
                        'id'        => 'GENDER',
                        'name'      => 'Género',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\PayrollGender',
                        'required'  => ['payroll_gender_id']

                    ],
                    [
                        'id'        => 'DISABLE',
                        'name'      => 'Estatus Discapacitado',
                        'type'      => 'boolean',
                        'model'     => '',
                        'required'  => ['has_disability']
                    ],
                    [
                        'id'        => 'BLOOD_TYPE',
                        'name'      => 'Tipo de sangre',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\PayrollBloodType',
                        'required'  => ['payroll_blood_type_id']
                    ],
                    [
                        'id'        => 'LICENSE_DEGREE',
                        'name'      => 'Grado de licencia de conducir',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\PayrollLicenseDegree',
                        'required'  => ['payroll_license_degree_id']
                    ]
                ]
            ],
            [
                'id'       => 'PROFESIONAL',
                'name'     => 'Datos Profesionales',
                'model'    => 'Modules\Payroll\Models\PayrollProfessional',
                'required' => ['payrollProfessional'],
                'children' =>
                [
                    [
                        'id'        => 'INSTRUCTION_DEGREE',
                        'name'      => 'Grado de instrucción',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\PayrollInstructionDegree',
                        'required'  => ['payroll_instruction_degree_id']
                    ],
                    [
                        'id'        => 'PROFESSION',
                        'name'      => 'Profesión',
                        'type'      => 'list',
                        'model'     => 'Modules\Payroll\Models\Profession',
                        'required'  => ['profession_id']
                    ],
                    [
                        'id'        => 'STUDENT',
                        'name'      => 'Estatus Estudiante',
                        'type'      => 'boolean',
                        'model'     => '',
                        'required'  => ['is_student']
                    ],
                    [
                        'id'        => 'NUMBER_LANG',
                        'name'      => 'Número de idiomas',
                        'type'      => 'number',
                        'model'     => '',
                        'required'  => ['payrollLanguages']
                    ]
                ]
            ],
            [
                'id'       => 'SOCIOECONOMIC_INFORMATION',
                'name'     => 'Datos Socioeconómicos',
                'model'    => 'Modules\Payroll\Models\PayrollSocioeconomic',
                'required' => ['payrollSocioecomicInformation'],
                'children' =>
                [
                    [
                        'id'       => 'MARITAL_STATUS',
                        'name'     => 'Estado Civil',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\MaritalStatus',
                        'required' => ['marital_status_id']
                    ],
                    [
                        'id'       => 'NUMBER_CHILDREN',
                        'name'     => 'Número de hijos',
                        'type'     => 'number',
                        'model'    => '',
                        'required' => ['payrollChildrens']
                    ]
                ]
            ],
            [
                'id'       => 'EMPLOYMENT_INFORMATION',
                'name'     => 'Datos Laborales',
                'model'    => 'Modules\Payroll\Models\PayrollEmployment',
                'required' => ['payrollEmployment'],
                'children' =>
                [
                    [
                        'id'       => 'START_APN',
                        'name'     => 'Años en la administración pública',
                        'type'     => 'date',
                        'model'    => '',
                        'required' => ['startDateApn']
                    ],
                    [
                        'id'       => 'START_DATE',
                        'name'     => 'Años en la institución',
                        'type'     => 'date',
                        'model'    => '',
                        'required' => ['start_date']
                    ],
                    [
                        'id'       => 'POSITION_TYPE',
                        'name'     => 'Tipo de cargo',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\PayrollPositionType',
                        'required' => ['payroll_position_type_id']
                    ],
                    [
                        'id'       => 'POSITION',
                        'name'     => 'Cargo',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\PayrollPosition',
                        'required' => ['payroll_position_id']
                    ],
                    [
                        'id'       => 'DEPARTMENT',
                        'name'     => 'Departamento',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\Department',
                        'required' => ['department_id']
                    ],
                    [
                        'id'       => 'STAFF_TYPE',
                        'name'     => 'Tipo de personal',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\PayrollStaffType',
                        'required' => ['payroll_staff_type_id']
                    ],
                    [
                        'id'       => 'CONTRACT_TYPE',
                        'name'     => 'Tipo de contrato',
                        'type'     => 'list',
                        'model'    => 'Modules\Payroll\Models\PayrollContractType',
                        'required' => ['payroll_contract_type_id']
                    ]
                ]
            ]
        ];

        /* Define los campos de la configuración de prestaciones sociales a emplear en el formulario */
        $this->associatedBenefit = [
            [
                'id'       => 'BENEFIT_DAYS',
                'name'     => 'Días a cancelar por garantías de prestaciones sociales',
                'model'    => 'Modules\Payroll\Models\PayrollBenefitsPolicy',
                'required' => ['benefit_days'],
            ],
            [
                'id'       => 'BENEFIT_ADDITIONAL_DAYS_PER_YEAR',
                'name'     => 'Días adicionales a otorgar por año de servicio',
                'model'    => 'Modules\Payroll\Models\PayrollBenefitsPolicy',
                'required' => ['additional_days_per_year'],
                'minimum' => ['minimum_number_years', 'minimum_number_months'],
            ],
            [
                'id'       => 'WORK_INTERRUPTION_DAYS',
                'name'     => 'Días a cancelar por interrupción de relación laboral',
                'model'    => 'Modules\Payroll\Models\PayrollBenefitsPolicy',
                'required' => ['work_interruption_days'],
            ],
            [
                'id'       => 'MONTH_WORKED_DAYS',
                'name'     => 'Días a cancelar por mes trabajado',
                'model'    => 'Modules\Payroll\Models\PayrollBenefitsPolicy',
                'required' => ['month_worked_days'],
            ]
        ];

        /* Define las opciones del campo "asignar a" a emplear en el formulario */
        $this->assignTo = [
            [
                'id'          => 'all',
                'name'        => 'Todos los trabajadores',
                'model'       => 'Modules\Payroll\Models\PayrollStaff',
                'type'        => '',
                'whereHas'    => null,
                'where'       => null
            ],
            [
                'id'       => 'all_active_staff',
                'name'     => 'Todos los trabajadores activos',
                'model'    => 'Modules\Payroll\Models\PayrollStaff',
                'type'     => '',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'where' => ['active', true]
                ],
                'where'    => null
            ],
            [
                'id'    => 'all_except_disabled_staff',
                'name'  => 'Todos excepto trabajadores discapacitados',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => '',
                'whereHas' => null,
                'where'    => ['has_disability', false]
            ],
            [
                'id'    => 'all_disabled_staff',
                'name'  => 'Todos los trabajadores con discapacidad',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => '',
                'whereHas' => null,
                'where'    => ['has_disability', true]
            ],
            [
                'id'    => 'all_studying_staff',
                'name'  => 'Todos los trabajadores que cursen estudios',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => '',
                'whereHas' => [
                    'field' => 'payrollProfessional',
                    'where' => ['is_student', true]
                ],
                'where'    => null
            ],
            [
                'id'    => 'all_staff_with_sons',
                'name'  => 'Todos los trabajadores con hijos',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => 'range',
                'whereHas' => [
                    'field' => 'payrollSocioeconomic',
                    'whereHas' => [
                        'field' => 'payrollChildrens',
                        'whereYear' => 'birthdate'
                    ],
                    'where' => null,
                ],
            ],
            [
                'id'    => 'all_staff_with_sons_studying',
                'name'  => 'Todos los trabajadores con hijos que cursen estudios',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => '',
                'whereHas' => [
                    'field' => 'payrollSocioeconomic',
                    'whereHas' => [
                        'field' => 'payrollChildrens',
                        'where' => ['is_student',true ],
                    ],
                ],
            ],
            [
                'id'    => 'staff_with_sons_has_scholarships',
                'name'  => 'Todos los trabajdores con hijos que poseen becas',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => 'list',
                'optionModel'   => 'Modules\Payroll\Models\PayrollScholarshipType',
                'optionField'   => ['name'],
                'whereHas' => null,

            ],
            [
                'id'      => 'staff',
                'name'    => 'Trabajadores',
                'model'   => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel'   => 'Modules\Payroll\Models\PayrollStaff',
                'optionField'   => ['first_name', ' ','last_name'],
                'field'   => ['first_name', ' ','last_name'],
                'type'    => 'list',
                'whereIn' => ['id', ['ids']]
            ],
            [
                'id'    => 'staff_master_the_languages',
                'name'  => 'Trabajadores que dominen más de un idioma',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => '',
                'whereHas' => [
                    'field' => 'payrollProfessional',
                    'withCount' => 'payrollLanguages',
                ],
            ],
            [
                'id'          => 'staff_except_specified',
                'name'        => 'Todos los trabajadores excepto los especificados',
                'model'       => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel'   => 'Modules\Payroll\Models\PayrollStaff',
                'optionField'   => ['first_name', ' ','last_name'],
                'field'       => ['first_name', ' ','last_name'],
                'type'        => 'list',
                'whereNotIn'  => ['id', ['ids']]
            ],
            [
                'id'    => 'staff_according_contract_type',
                'name'  => 'Trabajadores de acuerdo al tipo de contrato al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollContractType',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'whereIn' => ['payroll_contract_type_id', ['ids']],
                ],
            ],
            [
                'id'    => 'staff_according_department',
                'name'  => 'Trabajadores de acuerdo al departamento al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\Department',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'whereIn' => ['department_id', ['ids']],
                ],
            ],
            [
                'id'    => 'staff_according_position',
                'name'  => 'Trabajadores de acuerdo al cargo al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollPosition',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollEmployment.payrollPositions',
                    'whereIn' => ['payroll_positions.id', ['ids']],
                ],
            ],
            [
                'id'    => 'staff_according_position_type',
                'name'  => 'Trabajadores de acuerdo al tipo de cargo al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollPositionType',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'whereIn' => ['payroll_position_type_id', ['ids']],
                ],
            ],
            [
                'id'    => 'staff_according_staff_type',
                'name'  => 'Trabajadores de acuerdo al tipo de personal al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollStaffType',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'whereIn' => ['payroll_staff_type_id', ['ids']],
                ],
            ],
            [
                'id'    => 'all_staff_according_start_date',
                'name'  => 'Todos los trabajadores según fecha de ingreso',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'type'  => 'range',
                'whereHas' => [
                    'field' => 'payrollEmployment',
                    'whereDate' => 'start_date',
                ],
            ],
            [
                'id'    => 'staff_according_instruction_degree',
                'name'  => 'Trabajadores de acuerdo a su nivel de instrucción',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollInstructionDegree',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereHas' => [
                    'field' => 'payrollProfessional',
                    'whereIn' => ['payroll_instruction_degree_id', ['ids']],
                ],
            ],
            [
                'id'    => 'staff_according_gender',
                'name'  => 'Trabajadores de acuerdo al género al que pertenece',
                'model' => 'Modules\Payroll\Models\PayrollStaff',
                'optionModel' => 'Modules\Payroll\Models\PayrollGender',
                'optionField' => ['name'],
                'type'  => 'list',
                'whereIn' => ['payroll_gender_id', ['ids']],
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
    public function loadData($type)
    {
        return $this->$type;
    }
}
