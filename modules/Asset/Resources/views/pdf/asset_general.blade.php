<table cellspacing="0" cellpadding="1" border="1">
    <tr>
        <th style="background-color: #BDBDBD;"  width="33.33%" align="center" valign="middle">Código sigecof</th>
        <th style="background-color: #BDBDBD;"  width="33.33%" align="center" valign="middle">Descripción</th>
        <th style="background-color: #BDBDBD;"  width="33.33%" align="center" valign="middle">Cantidad</th>
    </tr>
    @php
        $codes = [];
        foreach($assets as $fields) {
            if($fields->code_sigecof) {
                if (!array_key_exists($fields->code_sigecof, $codes)) {
                    $codes[$fields->code_sigecof] = 1;
                } else {
                    $codes[$fields->code_sigecof] += 1;
                }
            }
        }

        $sigecof_codes = [];
        foreach($assets as $fields) {
            if($fields->code_sigecof) {
                if (!array_key_exists($fields->code_sigecof, $sigecof_codes)) {
                    $sigecof_codes[$fields->code_sigecof] = $fields;
                }
            }
        }
    @endphp


    @foreach($sigecof_codes as $fields)
    <tr align="C">
            <td width="33.33%" align="center" valign="middle"> {{ $fields->code_sigecof ? $fields->code_sigecof : '' }} </td>
            <td width="33.33%" align="center" valign="middle"> {{ $fields->assetSpecificCategory ? $fields->assetSpecificCategory['name'] : '' }} </td>
            <td width="33.33%" align="center" valign="middle"> {{ $codes[$fields->code_sigecof] }} </td>
        </tr>
    @endforeach
</table>