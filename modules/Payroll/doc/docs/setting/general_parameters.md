# Configuración Módulo de Talento Humano
****************************************
![Screenshot](../img/logokavac.png#imagen)

El usuario selecciona el módulo de Talento Humano en el menú lateral de los módulos del sistema, ahí visualizara las opciones **Configuración**, **Ajustes en Tablas salariales**, **Expediente**, **Registros de nómina**, **Solicitudes** y  **Reportes**. Luego pulsa la opción **Configuración** y el sistema muestra las secciones correspondientes a los parámetros globales de nómina.

![Screenshot](../img/menu_configuracion.png)<div style="text-align: center;font-weight: bold">Figura 1: Menú del Módulo de Talento Humano</div>

## Tipo de excepción de jornada laboral

Esta funcionalidad permite definir el tipo de excepción que aplica a la jornada laboral, tales como ausencias y horas extras. 

![Screenshot](../img/tipos_excepcion.png#imagen)<div style="text-align: center;font-weight: bold">Figura 2: Opción de Tipo de excepción de jornada laboral</div>

Para definir un tipo de excepción:
- Escriba el **Nombre** y la **Descripción**.
- Indique sobre cuál excepción incide. 
- En caso de que se seleccione una excepción en el campo **Incide sobre**, indique el signo de la incidencia.

![Screenshot](../img/nomina_excepcion.png#imagen)<div style="text-align: center;font-weight: bold">Figura 3: Formulario de Tipo de excepción de jornada laboral</div>

#### Gestión de registros:

- Complete el formulario de registro, de acuerdo al tipo de variable seleccionado .
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 

## Parámetros generales de nómina

### Parámetros globales

Mediante esta funcionalidad el usuario puede registrar tres tipos de variables auxiliares asociadas a la nómina, en caso de ser necesario, todo depende de las características y estructura que posea la nómina de cada organización. Los tipos de variables que el sistema permite registrar son las que se indican a continuación:

-**Valor global:** permite registrar valores fijos o permanentes, como por ejemplo: sueldo mínimo nacional, unidad tributaria, días de bono vacacional, días de evaluación, porcentaje de recargo por jornada nocturna, entre otros.

-**Variable procesada:** permite registrar variables que se generan a partir de una formula, tales como: días fraccionados de utilidades, días fraccionados de vacaciones, días fraccionados de bono de evaluación, entre otras.

-**Variable reiniciable a cero:** permite registrar aquellas variables cambian su valor en cada ejecución de la nómina, como por ejemplo: días de sueldo pendiente, días de jornada nocturna, horas extras, entre otras.

-**Parámetro de tiempo:** permite definir los diferentes parámetros que conformarán la hoja de tiempo, como las excepciones por turnos, ausencias y horas extras.

Para crear un parámetro global de nómina:

- Dirigirse a la **Configuración** del módulo de **Talento Humano**.
- Ingrese a **Parámetros Globales** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/parametro_global.png#imagen)<div style="text-align: center;font-weight: bold">Figura 4: Opción Parámetros Globales</div>

El formulario de registro de **Parámetro Global de Nómina** incluye cuatro (4) tipos de parámetros: 

![Screenshot](../img/image27.png#imagen)<div style="text-align: center;font-weight: bold">Figura 5: Registro de Parámetro Global de Nómina</div>


- Valor global: Se debe indicar **Nombre**, y el **Valor** (se hace uso del botón de selección para indicar un valor porcentual).

![Screenshot](../img/nomina_valor_global.png#imagen)<div style="text-align: center;font-weight: bold">Figura 6: Registro de Valor global</div>


- Variable reiniciable a cero por período de nómina: Se indica **Nombre**.

![Screenshot](../img/nomina_variable_cero.png#imagen)<div style="text-align: center;font-weight: bold">Figura 7: Registro de Variable reiniciable a cero por período de nómina</div>


- Variable procesada: Se debe indicar **Nombre**, y se establece una **Fórmula** mediante la calculadora habilitada para esta opción. 

![Screenshot](../img/nomina_variable_procesada.png#imagen)<div style="text-align: center;font-weight: bold">Figura 8: Registro de Variable procesada</div>

**Nota**: Para el caso de la variable procesada, el sistema permite seleccionar variables del expediente del trabajador o parámetros que se han registrado previamente. Los parámetros o variables establecidos se incluyen como un nuevo botón dentro de la calculadora, haciendo uso de estos botónes se genera una sentencia dentro del campo **Fórmula**. 

- Parámetro de tiempo: Se debe indicar **Nombre**, y se establece el tipo de excepción (turnos, ausencias, horas extras). 

![Screenshot](../img/nomina_parametro_tiempo.png#imagen)<div style="text-align: center;font-weight: bold">Figura 9: Registro de Parámetro de tiempo</div>

Una vez se realice un registro, el sistema los lista en una tabla que cuenta con los campos **Tipo de parámetro**, **Código**, **Acrónimo**, **Nombre**, **Descripción** y **Acción**.

![Screenshot](../img/parametro_tabla.png#imagen)<div style="text-align: center;font-weight: bold">Figura 10: Tabla de registro de Parámetros globales</div>


#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 


### Escalafones salariales 

Esta funcionalidad le permite al usuario registrar las diferentes clasificaciones que maneje la organización, como por ejemplo clasificaciones asociadas al tipo de personal, por cargos, por grado de instrucción, por años de servicio, entre otras.

Para crear un escalafón salarial:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Escalafones Salariales** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/escalas.png#imagen)<div style="text-align: center;font-weight: bold">Figura 11: Opción Escalafones Salariales</div>


-   Indique el **Nombre** de la escala, **Estado** (Activo/Inactivo) e **Institución**.
-   Indique el tipo de agrupación, ejemplo: Cargo, Departamento, Tipo de personal, entre otros.
-   Agregue una o más niveles  a la escala.      
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

![Screenshot](../img/image28.png#imagen)<div style="text-align: center;font-weight: bold">Figura 12: Registro de Escalafón Salarial</div>

**Nota**: En la sección **Nueva Escala** es posible agregar una o más escalas, para ello es necesario indicar un **Nombre** y el **Valor** por cada escala, las opciones del campo **Valor** son definidas a partir del tipo de VARIABLE seleccionado en el campo anterior **Agrupar por**. 

![Screenshot](../img/image29.png#imagen)


#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 



### Tabulador de nómina

Una vez se registren los diferentes escalafones salariales que maneja la organización se procede a realizar el registro de tabuladores.

Esta funcionalidad permite registrar los valores monetarios que corresponden a los diferentes tabuladores de la organización.  Se divide en dos secciones: **Definir Tabulador** y **Cargar Tabulador**.

En la sección **Definir Tabulador** se establecen los parámetros que definen las escalas a considerar dentro del tabulador.   

En la sección **Cargar Tabulador** se establecen valores monetarios para el tabulador salarial. 

Definir tabulador de nómina:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Tabulador de Nómina** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/tabulador.png#imagen)<div style="text-align: center;font-weight: bold">Figura 13: Opción Tabuladores de Nómina</div>


-   Complete campos de la sección **Definir Tabulador**.
-   Complete campos de la sección **Cargar Tabulador**.      
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

**Nota**: Para definir un tabulador de nómina es necesario contar con registros previos de **Escalafones Salariales**. 

![Screenshot](../img/image30.png#imagen)<div style="text-align: center;font-weight: bold">Figura 14: Registro de Tabulador de Nómina: Sección Definir Tabulador</div>

![Screenshot](../img/image31.png#imagen)<div style="text-align: center;font-weight: bold">Figura 15: Registro de Tabulador de Nómina: Sección Cargar Tabulador</div>

 
#### Gestión de registros:

Para **Descargar** tabulador, **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_3.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 



### Tipos de conceptos

Esta funcionalidad le permite a los usuarios registrar los tipos de conceptos que gestiona la organización. 

Si los conceptos están asociados remuneraciones que se le realizan a los trabajadores, se pueden registrar como asignaciones y se les indica el sigo “+”. Si los conceptos corresponden a montos que se le descuentan a los trabajadores se pueden registrar, como deducciones y se les indica el signo “-”. Si los conceptos corresponden a cálculos que se requiere procesar para la ejecución de la nómina se les indica la opción “No aplica”.

Para crear un tipo de concepto:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Tipos de Concepto** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/tipos_conceptos.png#imagen)<div style="text-align: center;font-weight: bold">Figura 16: Opción Tipos de Concepto</div>



-   Complete el formulario indicando **Nombre**, **Signo** y **Descripción** del concepto.
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

![Screenshot](../img/image32.png#imagen)<div style="text-align: center;font-weight: bold">Figura 17: Registro de Tipo de Concepto</div>

#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 



### Conceptos

Esta funcionalidad le permite a los usuarios registrar los diferentes cálculos asociados a la estructura de la nómina que gestiona cada organización; tales como: sueldo base, prima de antigüedad, prima de profesionalización, prima por hijos, cesta ticket, bono vacacional, utilidades, I.V.S.S, F.A.O.V, R.P.E, Fondo de jubilaciones, Caja de ahorros, entre otros pagos o descuentos permanentes o eventuales que maneje cada organización.

Esta sección requiere de registros previos como tipo de concepto, cuentas contables, presupuestarias y tabulador de nómina.   

Para crear un concepto:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Conceptos** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/conceptos.png#imagen)<div style="text-align: center;font-weight: bold">Figura 18: Registro de Conceptos</div>


- Complete el formulario de **Concepto**.
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

Este formulario se muestra en dos partes. La primera pestaña muestra los campos correspondientes a  **Conceptos**:

![Screenshot](../img/image33.png#imagen)<div style="text-align: center;font-weight: bold">Figura 19: Registro de Concepto</div>

La segunda pestaña muestra los campos correspondientes a **Datos presupuestarios/contables**:

![Screenshot](../img/image33_1.png#imagen)<div style="text-align: center;font-weight: bold">Figura 20: Registro de Datos presupuestarios/contables</div>


#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 

### Tipos de pago 

Una vez se registren los diferentes conceptos que gestiona cada organización, el usuario puede  registrar los tipos de pago asociados a la estructura de las nóminas.

Esta funcionalidad permite establecer un código de identificación único, programar períodos de pago y asociar los conceptos que corresponden a cada estructura de nómina que gestiona la organización.   

Definir un tipo de pago:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Tipos de Pago** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/tipo_pago.png#imagen)<div style="text-align: center;font-weight: bold">Figura 21: Opción Tipos de Pago</div>


-   Complete el formulario de **Tipos de Pago Nómina**.
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

**Nota**: Para completar el formulario se requiere al menos un registro previo de **Concepto**.

![Screenshot](../img/image34.png#imagen)<div style="text-align: center;font-weight: bold">Figura 22: Registro de Tipo de Pago</div>

![Screenshot](../img/image35.png#imagen)<div style="text-align: center;font-weight: bold">Figura 23: Período de Pago Definido</div>

#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 






### Política vacacional 

1. El usuario pulsa la opción “Configuración”, “Parámetros Generales de Nómina”, luego “Políticas vacacionales”.
#### 
![Screenshot](../img/politica_vacaciones.png#imagen)<div style="text-align: center;font-weight: bold">Figura 24: Opción Políticas Vacacionales</div>
#### 
El sistema muestra un formulario con los siguientes pestañas: **Política vacacional**, donde solicita se ingrese los campos: **Nombre, Desde, Hasta, ¿Activo?, Organización, Tipo de vacaciones (lista las opciones: Colectivas y Salidas individuales) y Asignar a**. 
#### 
En caso de vacaciones **Colectivas**, el sistema activa la sección **Salidas colectivas**, junto con el botón (+), que al pulsarlo muestra los campos:  **Fecha de inicio, Fecha de finalización,  Días a otorgar para el disfrute de vacaciones (campo generado por el sistema una vez se indiquen la fecha de inicio y la fecha de finalización) y el botón ¿Son días hábiles?**. 
#### 
![Screenshot](../img/PV_3.png#imagen)<div style="text-align: center;font-weight: bold">Figura 25: Formulario Políticas Vacacionales: Sección Política vacacional (vacaciones Colectivas)</div>
#### 
En caso de **Salidas individuales**, el sistema solicita se ingresen los siguientes campos: 
**Días a otorgar para el disfrute de vacaciones** (permite determinar los días base de disfrute de vacaciones)
#### 
**Períodos vacacionales permitidos por año** (permite determinar la cantidad de periodos que pueden disfrutar los trabajadores por año fiscal)
#### 
**Días de disfrute adicionales a otorgar según años de servicios** (permite determinar la cantidad de días adicionales de disfrute de vacaciones que se le deben otorgar a los trabajadores por cada año de servicio. Este valor el sistema lo debe ir sumando a los días disfrute base.)
#### 
**A partir de que año de servicio se otorgan días adicionales** (permite determinar el año de servicio a partir del cual se otorga el primer aumento asociado a los días adicionales de disfrute)
#### 
**Intervalo en años de servicios para el aumento de días de disfrute** (permite determinar por cuantos años de servicios se mantiene el aumento de los días adicionales de disfrute)
#### 
**Días de disfrute de vacaciones máximo por año de servicio** (permite determinar el límite de los días de disfrute por trabajador)
#### 
**¿Toma en cuenta los años de servicios en otras instituciones públicas?** (SI/NO. En caso de **SÍ**, el sistema calcula los días de disfrute en base al total de años de servicios que posee el trabajador. En caso de **NO**, el sistema calcula los días de disfrute en base a los años de servicios que ha laborado el trabajador en la organización), **¿Son días hábiles?** (SI/NO. En caso de **SI**, en los días de disfrute se descuentan los días feriados. En caso de **NO**, son días continuos). 
#### 
![Screenshot](../img/PV_4.png#imagen)<div style="text-align: center;font-weight: bold">Figura 26: Formulario Políticas Vacacionales: Sección Política vacacional (Salidas individuales)</div>
#### 
Seguidamente, el sistema activa el botón **Siguiente** y la pestaña **Solicitud de vacaciones** en donde solicita se ingrese información en el campo **Días de anticipación (mínimo) para realizar la solicitud de vacaciones. Finalmente,  el sistema muestra  los botones: “Cerrar”, “Cancelar” y “Guardar”.
#### 
![Screenshot](../img/PV_6.png#imagen)<div style="text-align: center;font-weight: bold">Figura 27: Ficha de Solicitud de vacaciones</div>
#### 
El usuario ingresa los datos solicitados y pulsa **Guardar**: El sistema muestra el mensaje: **Registro almacenado con éxito**. Seguidamente, el sistema lista el registro en la tabla denominada **Registros**, la cual contiene las columnas: **Nombre, Fecha de aplicación, Tipo de vacaciones, Activa y Acción, que a su vez cuenta con las opciones:  Modificar registro y Eliminar registro**.
#### 
El usuario pulsa el botón  **Cerrar**: El sistema cierra el formulario.
#### 
El usuario pulsa el botón **Cancelar**: El sistema no ejecuta ninguna acción.
#### 
Si el usuario omite un campo obligatorio (*): El sistema  muestra el mensaje: “El  campo  x es obligatorio”.
#### 
**Importante**
#### 
1. El campo nombre es un campo  alfanumérico y debe ser único.
#### 
2. Los campos: **Nombre, Desde, Organización, Tipo de vacaciones, Asignar a, Fecha de inicio, Fecha de finalización, Días a otorgar para el disfrute de vacaciones, Períodos vacacionales permitidos por año, Intervalo en años de servicios para el aumento de días de disfrute, Días de disfrute adicionales a otorgar por año de servicio, A partir del año, Días de disfrute de vacaciones máximo por año de servicio**. Son campos obligatorios (*).
#### 
3. El campo **Días de anticipación mínimos para la realizar la solicitud de vacaciones** es obligatorio, solo en el caso de salidas individuales.
#### 
4. El campo **Asignar a**, es un filtro de asignación que permite determinar los trabajadores para los cuales aplica la política vacacional que se registra. Este campo muestra las opciones: **Todos los trabajadores, Todos los trabajadores activos, Todos excepto trabajadores discapacitados, Todos los trabajadores con discapacidad, Todos los trabajadores que cursen estudios, Todos los trabajadores con hijos, Todos los trabajadores con hijos que cursen estudios, Trabajadores, Trabajadores que dominen más de un idioma, Todos los trabajadores excepto los especificados,Trabajadores de acuerdo al tipo de contrato al que pertenece, Trabajadores de acuerdo al departamento al que pertenece, Trabajadores de acuerdo al cargo al que pertenece, Trabajadores de acuerdo al tipo de cargo al que pertenece, Trabajadores de acuerdo al tipo de personal al que pertenece, Todos los trabajadores según fecha de ingreso, Trabajadores de acuerdo a su nivel de instrucción y Trabajadores de acuerdo al género al que pertenece**.
#### 
5. El formato de los campos: **Desde – Hasta** debe ser dd/mm/aaaa.
#### 
6. Los parámetros establecidos en el formulario de políticas vacaciones, son los que rigen las solicitudes de vacaciones, en caso de salidas individuales.
#### 
#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 



### Políticas de prestaciones 

Esta funcionalidad permite establecer parámetros (disposiciones legales) que rigen los cálculos asociados a las prestaciones sociales que gestiona la organización.
    
Definir políticas de prestaciones:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Políticas de Prestaciones** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/prestaciones.png#imagen)<div style="text-align: center;font-weight: bold">Figura 28: Opción Política de Prestaciones Sociales</div>

-   Complete el formulario de la sección **Definir Política de Prestaciones Sociales**.
-   Complete el formulario de la sección **Definir Pago de Prestaciones Sociales**.
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

**Nota**: 
Para completar el formulario se requiere al menos un registro previo de **Tipo de Pago**.

![Screenshot](../img/image38.png#imagen)<div style="text-align: center;font-weight: bold">Figura 29: Formulario Política de Prestaciones Sociales: Sección Definir política de prestaciones sociales</div>



#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 



### Políticas de permisos

Esta funcionalidad permite definir los parámetros asociados a los tipos de permisos que gestiona la organización.

Definir políticas de permisos:

-   Dirigirse a la **Configuración** del módulo de **Talento Humano**.
-   Ingrese a **Políticas de Permisos** en la sección **Parámetros Generales de Nómina**.

![Screenshot](../img/politica_permisos.png#imagen)<div style="text-align: center;font-weight: bold">Figura 30: Opción Políticas de Permisos</div>


- Complete el formulario de la sección **Políticas de Permisos** .
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.

![Screenshot](../img/image40.png#imagen)<div style="text-align: center;font-weight: bold">Figura 31: Formulario Política de Permisos</div>

#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/manage_1.png#imagen) 

#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.


#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 

### Grupo de supervisados 
Esta funcionalidad permite definir el personal que está a cargo de un determinado supervisor. El usuario pulsa la opción “Configuración” y se dirige a la sección “Parámetros generales de nómina” y luego pulsa “Grupos de Supervisados”.
###
![Screenshot](../img/GS_1.png#imagen)<div style="text-align: center;font-weight: bold">Figura 32: Grupo de supervisados</div> 
###

### Definir el Grupo de supervisados 

El sistema detalla un formulario que contiene un conjunto de campos específicos, que se enuncian a continuación: Código, Supervisor, Aprobador y Supervisados, junto con los botones: “Cerrar”, “Cancelar” y “Guardar”.
###

1. **Campo Código:** Es un campo único, alfanumérico y obligatorio.

2. **Campo Supervisor:** Se listan los trabajadores, permitiendo seleccionar un solo trabajador. Es un campo obligatorio.

3. **Campo Aprobador:** Se listan los trabajadores, permitiendo seleccionar un solo trabajador. Es un campo obligatorio.

4. **Campo Supervisados:** Se listan los trabajadores, permitiendo seleccionar más de un trabajador. Es un campo obligatorio. La lista de supervisados seleccionados es única para cada grupo de supervisados. El sistema permite eliminar trabajadores del grupo de supervisados, y este trabajador queda disponible y puede ser agregado en un nuevo grupo de de supervisados. El sistema filtra a los trabajadores por departamento para realizar la selección de trabajadores de forma más rápida.

###
![Screenshot](../img/GS_2.png#imagen)<div style="text-align: center;font-weight: bold">Figura 33: Opción Grupo de supervisados</div>
###

- Complete el formulario de la sección **Grupo de supervisados** .
- Presione el botón ![Screenshot](../img/save_3.png) **Guardar** para registrar los cambios efectuados.
- Presione el botón ![Screenshot](../img/cancel_1.png) **Cancelar** para limpiar datos del formulario.
- Presione el botón ![Screenshot](../img/close.png) **Cerrar** para cerrar el formulario.
###
![Screenshot](../img/GS_3.png#imagen)<div style="text-align: center;font-weight: bold">Figura 34: Grupo de supervisados</div>

#### Gestión de registros:

Para **Editar** o **Eliminar** un registro se debe hacer uso de los botones ubicados en la columna titulada **Acción** de la tabla de Registros.

![Screenshot](../img/GS_5.png#imagen) 



#### Editar registros

- Presione el botón **Editar registro** ![Screenshot](../img/edit.png) para un registro de interés.
- Luego, el sistema muestra el formulario en forma de edición.
- Modifique la información que requiera.
- Presione el botón **Guardar**  ![Screenshot](../img/save_1.png) para registrar los cambios efectuados.

#### Eliminar registros

- Presione el botón **Eliminar** ![Screenshot](../img/delete.png)  para un registro de interés.
- Seguidamente, el sistema presenta un modal con un mensaje de confirmación de si está seguro de eliminar el ingreso de almacén, y muestra los botones Confirmar y Cancelar.
- Pulse el botón **Confirmar** si está seguro de eliminar el registro seleccionado.
- El sistema elimina el registro.
- Si pulsa el botón **Cancelar**, el sistema no ejecuta ninguna acción. 
####
**Nota:** El sistema no debe permitir eliminar un grupo de supervisados para el
cual se ha ingresado información en el registro de hoja de tiempo periodo
activo/pendiente.

