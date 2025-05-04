<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollProfessional;
use Modules\Payroll\Models\Profession;
use Modules\Payroll\Models\PayrollLanguage;
use Modules\Payroll\Models\PayrollLanguageLevel;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Modules\Payroll\Models\Document;
use Modules\Payroll\Models\PayrollClassSchedule;
use Modules\Payroll\Rules\PayrollLangProfUnique;
use Modules\Payroll\Models\PayrollCourse;
use Modules\Payroll\Models\PayrollCourseFile;
use Modules\Payroll\Models\PayrollAcknowledgment;
use Modules\Payroll\Models\PayrollAcknowledgmentFile;
use Modules\Payroll\Models\PayrollStudy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Modules\Payroll\Jobs\PayrollExportNotification;

/**
 * @class PayrollProfessionalController
 * @brief Controlador de información profesional del trabajador
 *
 * Clase que gestiona los datos de información profesional
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollProfessionalController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Lista de atributos para los campos personalizados
     *
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.professionals.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.professionals.create', ['only' => 'store']);
        $this->middleware('permission:payroll.professionals.edit', ['only' => ['create', 'update']]);
        $this->middleware('permission:payroll.professionals.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.professionals.import', ['only' => 'import']);
        $this->middleware('permission:payroll.professionals.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            'payroll_instruction_degree_id' => ['required'],
        ];

        /* Define los atributos para los campos personalizados */
        $this->attributes = [
            'payroll_staff_id' => 'trabajador',
            'payroll_instruction_degree_id' => 'grado de instrucción',
        ];
    }

    /**
     * Muestra todos los registros de información profesional del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View    Muestra los datos organizados en una tabla
     */
    public function index()
    {
        return view('payroll::professionals.index');
    }

    /**
     * Muestra el formulario de registro de información profesional del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View    Vista con el formulario
     */
    public function create()
    {
        return view('payroll::professionals.create-edit');
    }

    /**
     * Valida y registra una nueva información profesional del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: result en verdadero y redirect con la url a donde ir
     */
    public function store(Request $request)
    {
        $this->rules['payroll_staff_id'] = ['required', 'unique:payroll_professionals,payroll_staff_id'];
        $this->validate($request, $this->rules, [], $this->attributes);
        $payrollInstructionDegree1 = PayrollInstructionDegree::where('name', 'TSU Universitario')->first();
        $payrollInstructionDegree1 = isset($payrollInstructionDegree1) ? $payrollInstructionDegree1->id : '';
        $payrollInstructionDegree2 = PayrollInstructionDegree::where('name', 'Universitario Pregrado')->first();
        $payrollInstructionDegree2 = isset($payrollInstructionDegree2) ? $payrollInstructionDegree2->id : '';
        if (
            $request->payroll_instruction_degree_id == $payrollInstructionDegree1 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree2
        ) {
            $this->validate(
                $request,
                [
                    // 'professions' => ['required', 'array', 'min:1'],
                ],
                [],
                [
                    'professions' => 'profesiones',
                ],
            );
        }
        $payrollInstructionDegree3 = PayrollInstructionDegree::where('name', 'Especialización')->first();
        $payrollInstructionDegree3 = isset($payrollInstructionDegree3) ? $payrollInstructionDegree3->id : '';
        $payrollInstructionDegree4 = PayrollInstructionDegree::where('name', 'Maestría')->first();
        $payrollInstructionDegree4 = isset($payrollInstructionDegree4) ? $payrollInstructionDegree4->id : '';
        $payrollInstructionDegree5 = PayrollInstructionDegree::where('name', 'Doctorado')->first();
        $payrollInstructionDegree5 = isset($payrollInstructionDegree5) ? $payrollInstructionDegree5->id : '';
        if (
            $request->payroll_instruction_degree_id == $payrollInstructionDegree3 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree4 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree5
        ) {
            $this->validate(
                $request,
                [
                    // 'instruction_degree_name' => ['required', 'max:100'],
                ],
                [],
                [
                    'instruction_degree_name' => 'nombre de especialización, maestría o doctorado',
                ],
            );
        }
        $i = 0;
        foreach ($request->payroll_studies as $payrollStudy) {
            $this->validate(
                $request,
                [
                    'payroll_studies.' . $i . '.university_name' => ['required', 'max:200'],
                    'payroll_studies.' . $i . '.graduation_year' => ['required', 'date'],
                    'payroll_studies.' . $i . '.payroll_study_type_id' => ['required'],
                    'payroll_studies.' . $i . '.profession_id' => ['required'],
                ],
                [],
                [
                    'payroll_studies.' . $i . '.university_name' => 'nombre de la universidad n°' . ($i + 1),
                    'payroll_studies.' . $i . '.graduation_year' => 'año de graduación n°' . ($i + 1),
                    'payroll_studies.' . $i . '.payroll_study_type_id' => 'tipo de estudio n°' . ($i + 1),
                    'payroll_studies.' . $i . '.profession_id' => 'profesión n°' . ($i + 1),
                ]
            );
            $i++;
        }
        if ($request->is_student) {
            $this->validate(
                $request,
                [
                    'payroll_study_type_id' => ['required'],
                    'study_program_name' => ['required'],
                    'class_schedule' => ['nullable'],
                ],
                [],
                [
                    'payroll_study_type_id' => 'tipo de estudio',
                    'study_program_name' => 'nombre del programa de estudio',
                ],
            );
        }
        $i = 0;
        foreach ($request->payroll_cou_ack_files as $payrollCouAckFile) {
            $this->validate(
                $request,
                [
                    'payroll_cou_ack_files.' . $i . '.course_name' => ['required', 'max:200'],
                    'payroll_cou_ack_files.' . $i . '.course.id' => ['required'],
                    'payroll_cou_ack_files.' . $i . '.ack_name' => ['max:200'],
                ],
                [],
                [
                    'payroll_cou_ack_files.' . $i . '.course_name' => 'nombre del curso #' . ($i + 1),
                    'payroll_cou_ack_files.' . $i . '.course.id' => 'archivo del curso #' . ($i + 1),
                    'payroll_cou_ack_files.' . $i . '.ack_name' => 'nombre del reconocimiento #' . ($i + 1),
                ]
            );
            $i++;
        }
        DB::transaction(function () use ($request) {
            $payrollProfessional = PayrollProfessional::create([
                'payroll_staff_id' => $request->payroll_staff_id,
                'payroll_instruction_degree_id' => $request->payroll_instruction_degree_id,
                'instruction_degree_name' => $request->instruction_degree_name,
                'is_student' => ($request->is_student) ? true : false,
                'payroll_study_type_id' => ($request->is_student) ? $request->payroll_study_type_id : null,
                'study_program_name' => ($request->is_student) ? $request->study_program_name : null,
                'class_schedule' => ($request->is_student) ? $request->class_schedule : null,
            ]);
            foreach ($request->payroll_studies as $payrollStudy) {
                $payrollProfessional->payrollStudies()->save(new PayrollStudy([
                    'university_name' => $payrollStudy['university_name'],
                    'graduation_year' => $payrollStudy['graduation_year'],
                    'payroll_study_type_id' => $payrollStudy['payroll_study_type_id'],
                    'profession_id' => $payrollStudy['profession_id'],
                ]));
            }
            $payrollClassSchedule = PayrollClassSchedule::create([
                'payroll_professional_id' => $payrollProfessional->id
            ]);
            if ($request->class_schedule_ids && !empty($request->class_schedule_ids)) {
                foreach ($request->class_schedule_ids as $classScheduleId) {
                    $document = Document::find($classScheduleId['id']);
                    $document->documentable_type = PayrollClassSchedule::class;
                    $document->documentable_id = $payrollClassSchedule->id;
                    $document->save();
                }
            }
            $i = 0;
            foreach ($request->payroll_languages as $payrollLanguage) {
                $this->validate(
                    $request,
                    [
                        'payroll_languages.' . $i . '.payroll_lang_id' => [
                            'required',
                            new PayrollLangProfUnique($payrollProfessional->id, $payrollLanguage['payroll_lang_id'])
                        ],
                        'payroll_languages.' . $i . '.payroll_language_level_id' => ['required'],
                    ],
                    [],
                    [
                        'payroll_languages.' . $i . '.payroll_lang_id' => 'idioma #' . ($i + 1),
                        'payroll_languages.' . $i . '.payroll_language_level_id' => 'nivel de idioma #' . ($i + 1),
                    ],
                );
                $payrollLang = PayrollLanguage::find($payrollLanguage['payroll_lang_id']);
                $payrollLanguageLevel = PayrollLanguageLevel::find($payrollLanguage['payroll_language_level_id']);
                $payrollProfessional->payrollLanguages()->attach(
                    $payrollLang->id,
                    ['payroll_language_level_id' => $payrollLanguageLevel->id]
                );
                $i++;
            }
            foreach ($request->professions as $profession) {
                $prof = Profession::find($profession['id']);
                $payrollProfessional->professions()->attach($prof);
            }
            $payrollCourse = PayrollCourse::create([
                'payroll_professional_id' => $payrollProfessional->id
            ]);
            $payrollAcknowledgment = PayrollAcknowledgment::create([
                'payroll_professional_id' => $payrollProfessional->id
            ]);
            if ($request->payroll_cou_ack_files && !empty($request->payroll_cou_ack_files)) {
                foreach ($request->payroll_cou_ack_files as $payrollCouAckFile) {
                    if ($payrollCouAckFile['course']['file_type'] === 'img') {
                        $payrollCourseFile = PayrollCourseFile::create([
                            'name' => $payrollCouAckFile['course_name'],
                            'payroll_course_id' => $payrollCourse->id,
                            'image_id' => $payrollCouAckFile['course']['id'],
                        ]);
                    } else {
                        $payrollCourseFile = PayrollCourseFile::create([
                            'name' => $payrollCouAckFile['course_name'],
                            'payroll_course_id' => $payrollCourse->id,
                        ]);
                        $document = Document::find($payrollCouAckFile['course']['id']);
                        $document->documentable_type = PayrollCourseFile::class;
                        $document->documentable_id = $payrollCourseFile->id;
                        $document->save();
                    }
                    if ($payrollCouAckFile['ack']['id'] != '') {
                        if ($payrollCouAckFile['ack']['file_type'] === 'img') {
                            $payrollAcknowledgmentFile = PayrollAcknowledgmentFile::create([
                            'name' => $payrollCouAckFile['ack_name'],
                            'payroll_acknowledgment_id' => $payrollAcknowledgment->id,
                            'image_id' => $payrollCouAckFile['ack']['id'],
                            ]);
                        } else {
                            $payrollAcknowledgmentFile = PayrollAcknowledgmentFile::create([
                                'name' => $payrollCouAckFile['ack_name'],
                                'payroll_acknowledgment_id' => $payrollAcknowledgment->id,
                            ]);
                            $document = Document::find($payrollCouAckFile['ack']['id']);
                            $document->documentable_type = PayrollAcknowledgmentFile::class;
                            $document->documentable_id = $payrollAcknowledgmentFile->id;
                            $document->save();
                        }
                    }
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.professionals.index')], 200);
    }

    /**
     * Muestra los datos de la información profesional del trabajador en específico
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                          Identificador del dato a mostrar
     *
     * @return \Illuminate\Http\JsonResponse        Json con el dato de la información profesional del trabajador
     */
    public function show($id)
    {
        $payrollProfessional = PayrollProfessional::where('id', $id)->with([
            'payrollStaff', 'payrollInstructionDegree', 'professions', 'payrollStudyType',
            'payrollLanguages', 'payrollStudies',
            'payrollClassSchedule' => function ($query) {
                $query->with('documents');
            },
            'payrollCourse' => function ($query) {
                $query->with(['payrollCourseFiles' => function ($query) {
                    $query->with(['image', 'documents']);
                }]);
            },
            'payrollAcknowledgment' => function ($query) {
                $query->with(['payrollAcknowledgmentFiles' => function ($query) {
                    $query->with(['image', 'documents']);
                }]);
            },
        ])->first();
        return response()->json(['record' => $payrollProfessional], 200);
    }

    /**
     * Muestra el formulario de actualización de información profesional del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id              Identificador con el dato a actualizar
     *
     * @return \Illuminate\View\View    Vista con el formulario y el objeto con el dato a actualizar
     */
    public function edit($id)
    {
        $payrollProfessional = PayrollProfessional::find($id);
        return view('payroll::professionals.create-edit', compact('payrollProfessional'));
    }

    /**
     * Actualiza la información profesional del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del dato a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con la redirección y mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollProfessional = PayrollProfessional::where('id', $id)->with([
            'payrollStaff', 'payrollInstructionDegree', 'professions', 'payrollStudyType',
            'payrollLanguages', 'payrollClassSchedule', 'payrollStudies',
            'payrollCourse' => function ($query) {
                $query->with('payrollCourseFiles');
            },
            'payrollAcknowledgment' => function ($query) {
                $query->with('payrollAcknowledgmentFiles');
            },
        ])->first();
        $this->rules['payroll_staff_id'] = [
            'required',
            'unique:payroll_professionals,payroll_staff_id,' . $payrollProfessional->id,
        ];
        $this->validate($request, $this->rules, [], $this->attributes);
        $payrollInstructionDegree1 = PayrollInstructionDegree::where('name', 'TSU Universitario')->first();
        $payrollInstructionDegree1 = isset($payrollInstructionDegree1) ? $payrollInstructionDegree1->id : '';
        $payrollInstructionDegree2 = PayrollInstructionDegree::where('name', 'Universitario Pregrado')->first();
        $payrollInstructionDegree2 = isset($payrollInstructionDegree2) ? $payrollInstructionDegree2->id : '';
        if (
            $request->payroll_instruction_degree_id == $payrollInstructionDegree1 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree2
        ) {
            $this->validate(
                $request,
                [
                    // 'professions' => ['required', 'array', 'min:1'],
                ],
                [],
                [
                    'professions' => 'profesiones',
                ],
            );
        }

        $payrollInstructionDegree3 = PayrollInstructionDegree::where('name', 'Especialización')->first();
        $payrollInstructionDegree3 = isset($payrollInstructionDegree3) ? $payrollInstructionDegree3->id : '';
        $payrollInstructionDegree4 = PayrollInstructionDegree::where('name', 'Maestría')->first();
        $payrollInstructionDegree4 = isset($payrollInstructionDegree4) ? $payrollInstructionDegree4->id : '';
        $payrollInstructionDegree5 = PayrollInstructionDegree::where('name', 'Doctorado')->first();
        $payrollInstructionDegree5 = isset($payrollInstructionDegree5) ? $payrollInstructionDegree5->id : '';
        if (
            $request->payroll_instruction_degree_id == $payrollInstructionDegree3 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree4 ||
            $request->payroll_instruction_degree_id == $payrollInstructionDegree5
        ) {
            $this->validate(
                $request,
                [
                    // 'instruction_degree_name' => ['required', 'max:100'],
                ],
                [],
                [
                    'instruction_degree_name' => 'nombre de especialización, maestría o doctorado',
                ],
            );
        }
        $i = 0;
        foreach ($request->payroll_studies as $payrollStudy) {
            $this->validate(
                $request,
                [
                    'payroll_studies.' . $i . '.university_name' => ['required', 'max:200'],
                    'payroll_studies.' . $i . '.graduation_year' => ['required', 'date'],
                    'payroll_studies.' . $i . '.payroll_study_type_id' => ['required'],
                    'payroll_studies.' . $i . '.profession_id' => ['required'],
                ],
                [],
                [
                    'payroll_studies.' . $i . '.university_name' => 'nombre de la universidad n°' . ($i + 1),
                    'payroll_studies.' . $i . '.graduation_year' => 'año de graduación n°' . ($i + 1),
                    'payroll_studies.' . $i . '.payroll_study_type_id' => 'tipo de estudio n°' . ($i + 1),
                    'payroll_studies.' . $i . '.profession_id' => 'profesión n°' . ($i + 1),
                ]
            );
            $i++;
        }
        if ($request->is_student) {
            $this->validate(
                $request,
                [
                    'payroll_study_type_id' => ['required'],
                    'study_program_name' => ['required'],
                    'class_schedule' => ['nullable'],
                ],
                [],
                [
                    'payroll_study_type_id' => 'tipo de estudio',
                    'study_program_name' => 'nombre del programa de estudio',
                ],
            );
        }
        $i = 0;
        foreach ($request->payroll_cou_ack_files as $payrollCouAckFile) {
            $this->validate(
                $request,
                [
                    'payroll_cou_ack_files.' . $i . '.course_name' => ['required'],
                    'payroll_cou_ack_files.' . $i . '.course.id' => ['required'],
                ],
                [],
                [
                    'payroll_cou_ack_files.' . $i . '.course_name' => 'nombre del curso #' . ($i + 1),
                    'payroll_cou_ack_files.' . $i . '.course.id' => 'archivo del curso #' . ($i + 1),
                ]
            );
            $i++;
        }
        DB::transaction(function () use ($payrollProfessional, $request) {
            $payrollProfessional->payroll_staff_id = $request->payroll_staff_id;
            $payrollProfessional->payroll_instruction_degree_id = $request->payroll_instruction_degree_id;
            $payrollProfessional->is_student = ($request->is_student) ? true : false;
            $payrollProfessional->payroll_study_type_id = ($request->is_student) ? $request->payroll_study_type_id : null;
            $payrollProfessional->study_program_name = ($request->is_student) ? $request->study_program_name : null;
            $payrollProfessional->class_schedule = ($request->is_student) ? $request->class_schedule : null;
            $payrollProfessional->save();
            $payrollProfessional->payrollStudies()->delete();
            foreach ($request->payroll_studies as $payrollStudy) {
                $payrollProfessional->payrollStudies()->save(new PayrollStudy([
                    'university_name' => $payrollStudy['university_name'],
                    'graduation_year' => $payrollStudy['graduation_year'],
                    'payroll_study_type_id' => $payrollStudy['payroll_study_type_id'],
                    'profession_id' => $payrollStudy['profession_id'],
                ]));
            }
            if (!$request->is_student) {
                Document::where('documentable_id', $payrollProfessional->payrollClassSchedule->id)->delete();
            }
            if ($request->class_schedule_ids && !empty($request->class_schedule_ids)) {
                foreach ($request->class_schedule_ids as $classScheduleId) {
                    $document = Document::find($classScheduleId['id']);
                    $document->documentable_type = PayrollClassSchedule::class;
                    $document->documentable_id = $payrollProfessional->payrollClassSchedule->id;
                    $document->save();
                }
            }
            foreach ($payrollProfessional->payrollLanguages as $payrollLanguage) {
                $payrollLang = PayrollLanguage::find($payrollLanguage['id']);
                $payrollProfessional->payrollLanguages()->detach($payrollLang->id);
            }
            $i = 0;
            foreach ($request->payroll_languages as $payrollLanguage) {
                $this->validate(
                    $request,
                    [
                        'payroll_languages.' . $i . '.payroll_lang_id' => [
                            'required',
                            new PayrollLangProfUnique($payrollProfessional->id, $payrollLanguage['payroll_lang_id'])
                        ],
                        'payroll_languages.' . $i . '.payroll_language_level_id' => ['required'],
                    ],
                    [],
                    [
                        'payroll_languages.' . $i . '.payroll_lang_id' => 'idioma #' . ($i + 1),
                        'payroll_languages.' . $i . '.payroll_language_level_id' => 'nivel de idioma #' . ($i + 1),
                    ],
                );
                $payrollLang = PayrollLanguage::find($payrollLanguage['payroll_lang_id']);
                $payrollLanguageLevel = PayrollLanguageLevel::find($payrollLanguage['payroll_language_level_id']);
                $payrollProfessional->payrollLanguages()->attach(
                    $payrollLang->id,
                    ['payroll_language_level_id' => $payrollLanguageLevel->id]
                );
                $i++;
            }
            foreach ($payrollProfessional->professions as $profession) {
                $prof = Profession::find($profession['id']);
                $payrollProfessional->professions()->detach($prof);
            }
            foreach ($request->professions as $profession) {
                $prof = Profession::find($profession['id']);
                $payrollProfessional->professions()->attach($prof);
            }
            PayrollCourseFile::where(
                'payroll_course_id',
                $payrollProfessional->payrollCourse->id,
            )->delete();
            PayrollAcknowledgmentFile::where(
                'payroll_acknowledgment_id',
                $payrollProfessional->payrollAcknowledgment->id,
            )->delete();
            if ($request->payroll_cou_ack_files && !empty($request->payroll_cou_ack_files)) {
                foreach ($request->payroll_cou_ack_files as $payrollCouAckFile) {
                    if ($payrollCouAckFile['course']['file_type'] === 'img') {
                        $payrollCourseFile = PayrollCourseFile::create([
                            'name' => $payrollCouAckFile['course_name'],
                            'payroll_course_id' => $payrollProfessional->payrollCourse->id,
                            'image_id' => $payrollCouAckFile['course']['id'],
                        ]);
                    } else {
                        $payrollCourseFile = PayrollCourseFile::create([
                            'name' => $payrollCouAckFile['course_name'],
                            'payroll_course_id' => $payrollProfessional->payrollCourse->id,
                        ]);
                        $document = Document::find($payrollCouAckFile['course']['id']);
                        $document->documentable_type = PayrollCourseFile::class;
                        $document->documentable_id = $payrollCourseFile->id;
                        $document->save();
                    }
                    if ($payrollCouAckFile['ack']['file_type'] != null) {
                        if ($payrollCouAckFile['ack']['file_type'] === 'img') {
                            $payrollAcknowledgmentFile = PayrollAcknowledgmentFile::create([
                                'name' => $payrollCouAckFile['ack_name'],
                                'payroll_acknowledgment_id' => $payrollProfessional->payrollAcknowledgment->id,
                                'image_id' => $payrollCouAckFile['ack']['id'],
                            ]);
                        } else {
                            $payrollAcknowledgmentFile = PayrollAcknowledgmentFile::create([
                                'name' => $payrollCouAckFile['ack_name'],
                                'payroll_acknowledgment_id' => $payrollProfessional->payrollAcknowledgment->id,
                            ]);
                            $document = Document::find($payrollCouAckFile['ack']['id']);
                            $document->documentable_type = PayrollAcknowledgmentFile::class;
                            $document->documentable_id = $payrollAcknowledgmentFile->id;
                            $document->save();
                        }
                    }
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.professionals.index')], 200);
    }

    /**
     * Realiza la acción necesaria para importar los datos Profesionales
     *
     * @author    Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function import(Request $request)
    {
        $filePath = $request->file('file')->store('', 'temporary');
        $fileErrorsPath = 'import' . uniqid() . '.errors';
        Storage::disk('temporary')->put($fileErrorsPath, '');
        $import = new RegisterStaffImport($filePath, 'temporary', auth()->user()->id, $fileErrorsPath);

        $import->import();

        return response()->json(['result' => true], 200);
    }

    /**
     * Exportar registros
     *
     * @author  Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Profesionales',
        );

        request()->session()->flash('message', ['type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
            'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.professionals.index');
    }

    /**
     * Elimina la información profesional del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del dato a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollProfessional = PayrollProfessional::find($id);
        $payrollProfessional->delete();
        return response()->json(['record' => $payrollProfessional, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información profesional del trabajador registrada
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información profesional
     */
    public function vueList(Request $request)
    {
        $records = PayrollProfessional::select(
            'id',
            'instruction_degree_name',
            'is_student',
            'study_program_name',
            'class_schedule',
            'payroll_staff_id',
            'payroll_instruction_degree_id',
            'payroll_study_type_id'
        )->with([
            'payrollStaff' => function ($query) {
                $query->without(
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollEmployment',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional'
                );
            },
            'payrollInstructionDegree',
            'professions',
            'payrollStudyType',
            'payrollLanguages',
            'payrollStudies',
            'payrollCourse' => function ($query) {
                $query->with(['payrollCourseFiles' => function ($query) {
                    $query->with(['documents', 'image']);
                }]);
            },
            'payrollAcknowledgment' => function ($query) {
                $query->with(['payrollAcknowledgmentFiles' => function ($query) {
                    $query->with(['documents', 'image']);
                }]);
            }
        ])
        ->search($request->get('query'))
        ->paginate($request->get('limit'));

        return response()->json(
            [
                'data' => $records->items(),
                'count' => $records->total(),
            ],
            200
        );
    }

    /**
     * Obtiene las profesiones sin usar template_choices
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de las profesiones
     */
    public function getJsonProfessions()
    {
        return response()->json(['jsonProfessions' => Profession::all()], 200);
    }
}
