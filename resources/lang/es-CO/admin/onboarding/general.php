<?php

return [
    'module_title' => 'Gestión de ingreso del personal',
    'module_menu' => 'Ingreso de personal',
    'management_title' => 'Solicitudes de ingreso de personal',
    'management_menu' => 'Solicitudes de ingreso',

    'wizard_intro' => 'Complete los datos del nuevo funcionario. Las opciones irán apareciendo a medida que avanza.',

    'position' => 'Cargo del funcionario',
    'position_ph' => 'Ej. Analista de sistemas',
    'employee_name' => 'Nombre del nuevo funcionario',
    'employee_name_ph' => 'Nombres y apellidos',

    'entry_type' => '¿Es un nuevo puesto o reemplaza a un funcionario?',
    'entry_new' => 'Nuevo puesto',
    'entry_replacement' => 'Reemplaza a un funcionario',

    'replaces_user' => 'Funcionario al que reemplaza',
    'replaces_help' => 'Se reasignará al nuevo funcionario el equipo actualmente asignado a esta persona.',
    'assigned_device' => 'Equipo asignado a reasignar',
    'no_assigned_device' => 'Este funcionario no tiene equipos asignados.',
    'loading_devices' => 'Consultando equipos asignados…',

    'choose_device' => 'Equipo disponible a asignar',
    'choose_device_help' => 'Seleccione uno de los equipos disponibles en inventario.',
    'choose_device_ph' => 'Buscar equipo disponible…',
    'no_available_assets' => 'No hay equipos disponibles en inventario en este momento.',

    'components' => 'Componentes / accesorios que requiere',
    'components_help' => 'Solo se muestran los accesorios con unidades disponibles.',
    'available_units' => 'disponibles',
    'no_components' => 'No hay accesorios con stock disponible.',

    'platforms' => 'Plataformas que requiere el usuario',
    'platforms_help' => 'Marque las plataformas a las que necesita acceso.',

    'notes' => 'Observaciones',
    'notes_ph' => 'Cualquier detalle adicional para TI…',
    'submit' => 'Enviar solicitud de ingreso',

    'created' => 'Solicitud de ingreso registrada correctamente.',
    'updated' => 'Solicitud de ingreso actualizada.',
    'deleted' => 'Solicitud de ingreso eliminada.',

    // IT management list
    'col_employee' => 'Funcionario',
    'col_position' => 'Cargo',
    'col_type' => 'Tipo',
    'col_device' => 'Equipo',
    'col_components' => 'Componentes',
    'col_platforms' => 'Plataformas',
    'col_requested_by' => 'Solicitado por',
    'col_status' => 'Estado',
    'col_date' => 'Fecha',
    'replaces_short' => 'Reemplaza a',

    'status_pending' => 'Pendiente',
    'status_approved' => 'Aprobada',
    'status_rejected' => 'Rechazada',
    'status_fulfilled' => 'Completada',

    'action_approve' => 'Aprobar',
    'action_reject' => 'Rechazar',
    'action_fulfill' => 'Marcar completada',
    'action_delete' => 'Eliminar',
    'confirm_delete' => '¿Eliminar esta solicitud de ingreso?',
    'filter_all' => 'Todas',
    'none' => 'Ninguno',
    'no_requests' => 'No hay solicitudes de ingreso.',
    'new_request' => 'Registrar ingreso',
];
