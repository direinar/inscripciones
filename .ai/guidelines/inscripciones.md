# Reglas de negocio - Sistema de Inscripciones

Estas reglas describen el comportamiento funcional y las reglas de negocio del sistema de inscripciones.

## Instrucciones generales

- Estas reglas representan los requerimientos funcionales del sistema y deben respetarse al modificar o crear funcionalidades relacionadas con inscripciones.
- Antes de implementar una funcionalidad, revisar primero las estructuras existentes del proyecto, modelos, migraciones, rutas, componentes Livewire, controladores, políticas y vistas.
- No crear tablas, modelos, columnas, relaciones, rutas o componentes duplicados si ya existe una implementación equivalente.
- Mantener la arquitectura y convenciones existentes del proyecto.
- No eliminar funcionalidades existentes sin confirmar explícitamente que forman parte del requerimiento.
- Cuando una modificación afecte permisos o estados del estudiante, revisar siempre las relaciones entre Prospectos, Mercadeo, Ficha Integral e Inscripciones.
- Las operaciones financieras deben estar asociadas al programa/inscripción correspondiente y nunca afectar accidentalmente otros programas del mismo estudiante.
- Mantener separación clara entre las capacidades del asesor comercial y las del usuario administrativo.
- Cuando una regla de negocio no esté suficientemente definida en este documento o en el código existente, analizar primero el proyecto y solicitar aclaración antes de inventar comportamiento.

# 1. Dashboard

El Dashboard debe estar orientado principalmente a la búsqueda, filtrado y consulta general de información.

## Filtros obligatorios

Debe permitir consultar por:

- Sede.
- Periodo.
- Programa.
- Jornada.

## Reglas

- Retirar del Dashboard los elementos o ítems actuales que no sean necesarios para búsquedas, filtros y consultas generales.
- No agregar métricas, tarjetas, gráficos o indicadores que no estén relacionados con la necesidad de consulta general, salvo requerimiento explícito.
- Los filtros deben poder combinarse para obtener consultas más específicas.
- Al modificar el Dashboard, conservar únicamente la información necesaria para facilitar la consulta de los registros.

# 2. Prospectos

El módulo Prospectos recibe automáticamente la información de los estudiantes que realizan una preinscripción mediante el formulario web.

## Información

Cada prospecto debe mostrar la información registrada en el formulario de preinscripción.

## Asignación de asesor

Cada prospecto pendiente debe disponer de una acción:

**Asignar asesor**

Al utilizarla:

1. Mostrar los asesores registrados y disponibles en la plataforma.
2. Permitir seleccionar un asesor.
3. Registrar el asesor responsable del prospecto.
4. Guardar la asignación de forma persistente.
5. Retirar automáticamente el prospecto del listado de Prospectos pendientes.
6. Permitir que el prospecto continúe su proceso en el módulo correspondiente al asesor.

## Estados

Debe existir una diferenciación clara entre:

- Prospectos pendientes de asignación.
- Prospectos asignados.

Un prospecto que ya tenga un asesor responsable no debe continuar apareciendo como pendiente de asignación.

# 3. Mercadeo

El módulo Mercadeo está destinado principalmente a los asesores comerciales.

## Visibilidad

Cuando un asesor ingrese con su usuario:

- Debe visualizar los estudiantes que le fueron asignados.
- No debe visualizar como propios los prospectos asignados a otros asesores, salvo que sus permisos le permitan explícitamente consultar esa información.
- La información debe estar asociada al asesor responsable.

## Seguimiento comercial

El asesor debe poder:

- Consultar la información del estudiante.
- Crear seguimientos.
- Guardar seguimientos.
- Registrar observaciones.
- Registrar avances.
- Consultar el historial de seguimientos.
- Identificar el estado actual del estudiante.

Cada seguimiento debe conservar su historial y no debe sobrescribir los seguimientos anteriores.

## Inscripción

Cuando el estudiante confirme que desea realizar el programa, debe existir la acción:

**Inscribir**

Al utilizar esta acción:

- El estudiante debe pasar a la Ficha Integral del Estudiante.
- Debe conservarse la información personal existente.
- Debe registrarse la inscripción al programa correspondiente.
- No se debe crear una segunda ficha personal para el mismo estudiante.

# 4. Ficha Integral del Estudiante

La Ficha Integral es el espacio central para la información general, académica y administrativa del estudiante.

## Permisos

Debe existir una separación clara entre asesor y usuario administrativo.

### Asesor

El asesor puede:

- Consultar la información necesaria del estudiante.
- Consultar el estado del estudiante.
- Consultar información relacionada con sus procesos comerciales.

El asesor no debe poder ejecutar procesos administrativos dentro de la Ficha Integral.

### Usuario administrativo

El usuario administrativo puede:

- Consultar información completa del estudiante.
- Editar y actualizar los datos permitidos.
- Consultar información general.
- Consultar y actualizar información de contacto.
- Consultar información de inscripciones.
- Consultar y gestionar la información financiera asociada a cada programa.
- Gestionar los procesos administrativos permitidos.

Los permisos deben implementarse mediante los mecanismos de autorización existentes en el proyecto y no solamente ocultando botones en la interfaz.

# 5. Inscripciones del estudiante

Dentro de la Ficha Integral debe existir una sección:

**Inscripciones del estudiante**

Esta sección debe mostrar todos los programas en los que el estudiante se encuentre inscrito.

## Regla fundamental

Cada inscripción representa un proceso independiente.

Un estudiante puede tener múltiples programas inscritos.

Los procesos de cada programa deben mantenerse independientes:

- Matrícula.
- Crédito.
- Abonos.
- Pagos.
- Plan de estudios.
- Estado de la inscripción.

Una operación realizada sobre un programa no debe modificar accidentalmente la información de otro programa del mismo estudiante.

## Acciones

Cada inscripción debe disponer de las acciones correspondientes.

### Crear crédito

Permite crear un crédito o financiación para el programa seleccionado.

Cuando el crédito sea creado correctamente:

**Inscrito → Matriculado**

El cambio de estado debe realizarse automáticamente de acuerdo con las reglas de negocio y debe afectar únicamente la inscripción correspondiente.

### Abono a crédito

Permite registrar los pagos o abonos realizados sobre el crédito del programa seleccionado.

Cada abono debe:

- Quedar registrado.
- Mantener su fecha.
- Mantener su valor.
- Mantener la información necesaria para identificar la operación.
- Formar parte del historial financiero de la inscripción correspondiente.

Un abono de un programa nunca debe afectar el crédito de otro programa.

### Pago individual

Permite registrar un pago realizado directamente por el estudiante sin asociarlo a un crédito.

El pago debe quedar asociado a la inscripción/programa correspondiente.

No debe registrarse como abono de crédito cuando el pago sea individual.

### Plan de estudios

Permite consultar el plan de estudios correspondiente al programa en el que se encuentra matriculado el estudiante.

El plan mostrado debe corresponder al programa seleccionado y no a otro programa del estudiante.

# 6. Inscribir otro programa

La Ficha Integral debe disponer en la parte superior derecha de la acción:

**Inscribir programa**

Esta acción permite que un estudiante existente se inscriba en un nuevo programa.

## Reglas

Al inscribir un nuevo programa:

- No crear nuevamente la ficha personal del estudiante.
- Conservar la información general existente.
- Seleccionar el nuevo programa.
- Crear una nueva inscripción.
- Asociar la nueva inscripción al mismo estudiante.
- Mostrar el nuevo programa dentro de "Inscripciones del estudiante".

## Independencia de programas

Cada programa debe manejar independientemente:

- Inscripción.
- Estado académico.
- Matrícula.
- Crédito.
- Abonos.
- Pagos.
- Plan de estudios.

Modificar la información financiera o académica de un programa no debe alterar los demás programas del estudiante.

# 7. Integridad de la información

Antes de modificar información relacionada con un estudiante:

- Identificar si se está modificando la ficha personal o una inscripción específica.
- Si la información pertenece al estudiante, actualizar el registro general del estudiante.
- Si la información pertenece a un programa, actualizar únicamente la inscripción correspondiente.
- Las operaciones financieras siempre deben estar vinculadas a la inscripción/programa correspondiente.
- Evitar duplicar estudiantes cuando el estudiante ya existe.
- Evitar duplicar inscripciones cuando la operación ya fue registrada.
- Mantener la trazabilidad de las operaciones financieras y de seguimiento.

# 8. Estados del proceso

Los estados deben representar correctamente la etapa del estudiante.

Como mínimo debe contemplarse la transición:

**Inscrito → Matriculado**

cuando se cree correctamente el crédito correspondiente.

No cambiar estados automáticamente en otras situaciones si la regla de negocio no lo establece.

# 9. Desarrollo de funcionalidades

Cuando se implemente cualquiera de estos módulos:

1. Revisar primero el modelo de datos existente.
2. Revisar las relaciones Eloquent existentes.
3. Revisar las migraciones existentes.
4. Revisar las políticas/autorizaciones.
5. Revisar componentes Livewire existentes si la funcionalidad utiliza Livewire.
6. Revisar las rutas y permisos existentes.
7. Reutilizar componentes existentes cuando sea apropiado.
8. Crear pruebas para las reglas de negocio afectadas.
9. No modificar datos de otros estudiantes o programas accidentalmente.
10. Validar autorización en backend además de las restricciones de interfaz.

# 10. Regla para el agente de IA

Antes de implementar una funcionalidad relacionada con estos módulos:

- Inspeccionar el código existente.
- Utilizar Laravel Boost/MCP para consultar modelos, rutas, configuración, migraciones y estructura de la aplicación cuando sea necesario.
- Buscar primero implementaciones existentes antes de crear nuevas.
- No asumir nombres de tablas, columnas, modelos o relaciones sin comprobar el proyecto.
- No inventar reglas de negocio que no estén documentadas.
- Si existe conflicto entre estas reglas y el comportamiento actual del código, identificar el conflicto antes de modificarlo.
- Proponer la modificación mínima necesaria para cumplir el requerimiento.
- Después de implementar, verificar las pruebas y el comportamiento afectado.
