@php
    function datetime_diff($date1) {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime();
        $difference = $datetime1->diff($datetime2);
        return $difference->y;
    }

    /**
    * Método que cambia el formato de visualización de la fecha a
    * dd/mm/yyyy.
    *
    * @author Natanael Rojo <rojonatanael99@gmail.com>
    *
    * @param {dateString} dateString fecha ha ser fornateada
    */
    function convertDate($dateString) {
        if (empty($dateString)) {
            // Devuelve una cadena vacía si $dateString es nulo o vacío.
            return "";
        }

        $dateParts = explode("-", $dateString);
        $year = $dateParts[0];
        $month = $dateParts[1];
        $day = $dateParts[2];

        return "{$day}/{$month}/{$year}";
    }
@endphp
<table cellspacing="0" cellpadding="1" border="0">
    <tr>
        <th>
            <h4>
                <b>Información Personal</b>
            </h4>
        </th>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Trabajador</th>
        <th width="30%">Cédula</th>
        <th width="20%">¿Activo?</th>
    </tr>
    <tr>
        <td width="50%">
            {{
                $field->first_name
                    ? $field->first_name . ' ' . $field->last_name
                    : 'No aplica'
            }}
        </td>
        <td width="30%">
            {{
                $field->id_number
                    ? $field->id_number
                    : 'No aplica'
            }}
        </td>
        <td width="20%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->active
                        ? ($field->payrollEmployment->active ==  'true')
                            ? 'Si'
                            : 'No'
                        : 'No'
                    : 'No'
            }}
        </td>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="25%">Teléfono</th>
        <th width="25%">Correo Personal</th>
        <th width="25%">Dirección</th>
        <th width="25%">Licencia</th>
    </tr>
    <tr>
        <td width="25%">
            @foreach ($field->phones as $phones)
                {{
                    $phones ?
                    $phones->area_code . ' ' . $phones->number :
                    'No tiene teléfono'
                }}
                <br>
            @endforeach
        </td>
        <td width="25%">
            {{
                $field->email
                    ? $field->email
                    : 'No tiene correo'
            }}
        </td>
        <td width="25%">
            {{
                $field->address
                    ? $field->address
                    : 'No'
            }}
        </td>
        <td width="25%">
            {{
                $field->payrollLicensedegree
                    ? $field->payrollLicensedegree->name
                    : 'No tiene'
            }}
        </td>
    </tr>
    <tr style="background-color: #BDBDBD;">
        <th width="33%">Edad</th>
        <th width="33%">Tipo de sangre</th>
        <th width="33%">Discapacidad</th>
    </tr>
    <tr>
        <td width="33%">
            {{
                $field->birthdate ?
                age($field->birthdate) :
                'No aplica'
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollBloodType ?
                $field->payrollBloodType->name :
                'No aplica'
            }}
        </td>
        <td width="20%">
            {{
                $field->payrollDisability
                    ? $field->payrollDisability->name
                    : 'No posee'
            }}
        </td>
    </tr>
    <tr>
        <th>
            <h4>
                <b>Información Socio-económica</b>
            </h4>
        </th>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="33%">Estado civil</th>
        <th width="33%">Hijos</th>
        <th width="33%">Pareja</th>
    </tr>
    <tr>
        <td width="33%">
            {{
                $field->payrollSocioeconomic
                    ?  $field->payrollSocioeconomic->MaritalStatus
                        ?  $field->payrollSocioEconomic->MaritalStatus->name
                        : 'No aplica'
                    : 'No aplica'
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollSocioeconomic
                    ? $field->payrollSocioeconomic->payrollChildrens
                        ? 'Si tiene'
                        : 'No tiene'
                    : 'No tiene hijos'
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollSocioeconomic
                    ?  $field->payrollSocioeconomic->full_name_twosome
                    : 'No aplica'
            }}
        </td>
    </tr>
    <tr>
        <th>
            <h4>
                <b>Información profesional</b>
            </h4>
        </th>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Profesión</th>
        <th width="50%">Grado de instrucción</th>
    </tr>
    <tr>
        <td width="50%">
            @foreach($field->payrollProfessional->payrollStudies ?? [] as $prof)
                {{
                    $prof->professions
                        ? $prof->professions->name
                            : 'No aplica'
                }}
            @endforeach
        </td>
        <td width="50%">
            {{
                $field->payrollProfessional->payrollInstructionDegree?->name
                    ?? 'No aplica'
            }}
        </td>
    </tr>
    <tr>
        <th>
            <h4>
                <b>Información Laboral</b>
            </h4>
        </th>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="33%">Cargo</th>
        <th width="33%">Tipo de personal</th>
        <th width="33%">Tipo de contrato</th>
    </tr>
    <tr>
        <td width="33%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->payrollPosition
                        ? $field->payrollEmployment->payrollPosition->name
            : 'No aplica'
                    : 'No aplica'
            }}
        </td>
        <td width="33%">
            {{
                    $field->payrollEmployment
                    ? $field->payrollEmployment->payrollStaffType
            ? $field->payrollEmployment->payrollStaffType->name
            : 'No aplica'
                    : 'No aplica'
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->payrollContractType
                        ? $field->payrollEmployment->payrollContractType->name
                        : 'No aplica'
                    : 'No aplica'
            }}
        </td>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="33%">Departamento</th>
        <th width="33%">Nombre de organización anterior</th>
        <th width="33%">Años en otras instituciones públicas</th>
    </tr>
    <tr>
        <td width="33%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->Department
                        ? $field->payrollEmployment->Department->name
                        : 'No aplica'
                    : 'No aplica'
            }}
        </td>
        <td width="33%">
            @if ($field->payrollEmployment->payrollPreviousJob)
                @foreach ($field->payrollEmployment->payrollPreviousJob as $previousJob)
                    {{
                        $field->payrollEmployment
                            ? $field->payrollEmployment->payrollPreviousJob
                                ? $previousJob->organization_name
                                : 'No aplica'
                            : 'No aplica'
                    }}
                @endforeach
            @endif
        </td>
        <td width="33%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->years_apn
                    : 'No aplica'
            }}
        </td>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Fecha de ingreso a la institución</th>
        <th width="50%">Fecha de egreso de la institución</th>
    </tr>
    <tr>
        <td width="50%">
            {{
                $field->payrollEmployment
                ? convertDate($field->payrollEmployment->start_date)
                : ''
            }}
        </td>
        <td width="50%">
            {{
                $field->payrollEmployment
                ? convertDate($field->payrollEmployment->end_date)
                : ''
            }}
        </td>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Años de servicio</th>
        <th width="50%">Tipo de Cargo</th>
    </tr>
    <tr>
        <td width="50%">
        {{
            intval(datetime_diff($field->payrollEmployment->start_date)) +
            intval($field->payrollEmployment->years_apn)
        }}
        </td>
        <td width="50%">
            {{
                $field->payrollEmployment
                    ? $field->payrollEmployment->payrollPositionType
                        ? $field->payrollEmployment->payrollPositionType->name
                    : 'No aplica'
                : 'No aplica'
            }}
        </td>
    </tr>
    <tr><th></th></tr>
    <tr style="background-color: #BDBDBD;">
        <th width="33%">Tipo de inactividad</th>
        <th width="33%">Coordinación</th>
        <th width="33%">Ficha de expediente</th>
    </tr>
    <tr>
        <td width="33%">
            {{
                $field->payrollEmployment->payrollInactivityType
                ? $field->payrollEmployment->payrollInactivityType->name
                : ''
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollEmployment->payrollCoordination
                ? $field->payrollEmployment->payrollCoordination->name
                : ''
            }}
        </td>
        <td width="33%">
            {{
                $field->payrollEmployment->worksheet_code
                ? $field->payrollEmployment->worksheet_code
                : ''
            }}
        </td>
    </tr>
    <br>
    <tr style="background-color: #BDBDBD;">
        <th width="100%">Descripción de funciones</th>
    </tr>
    <tr>
        <td width="100%">
            {{
                $field->payrollEmployment->function_description
                ? strip_tags($field->payrollEmployment->function_description)
                : ''
            }}
        </td>
    </tr>
</table>
