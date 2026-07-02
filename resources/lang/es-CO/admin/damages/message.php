<?php

return [
    'create' => [
        'success' => 'Daño registrado correctamente.',
    ],
    'update' => [
        'success' => 'Daño actualizado correctamente.',
    ],
    'delete' => [
        'success' => 'Daño eliminado correctamente.',
    ],
    'bulk' => [
        'success' => ':count daño(s) actualizado(s) correctamente.',
        'nothing_selected' => 'No se seleccionó ningún daño.',
    ],
    'pr' => [
        'created' => 'Solicitud de compra generada correctamente.',
        'updated' => 'Solicitud de compra actualizada correctamente.',
        'deleted' => 'Solicitud de compra eliminada correctamente.',
        'item_removed' => 'Daño quitado de la solicitud.',
    ],
    'email' => [
        'sent' => 'Reporte enviado por correo correctamente.',
        'scheduled' => 'Envío programado correctamente. Se envió una primera copia ahora.',
        'schedule_deleted' => 'Envío programado eliminado.',
        'failed' => 'No se pudo enviar el correo: :error',
        'log_mode' => 'El correo está en modo prueba (MAIL_MAILER=log): se registró pero NO se entregó. Configura el correo SMTP para envíos reales.',
        'scheduled_log' => 'Envío programado correctamente. Aviso: el correo está en modo prueba (log), así que la copia inmediata no se entregó realmente. Configura SMTP para envíos reales.',
    ],
    'type_create_success' => 'Tipo de daño creado correctamente.',
    'type_update_success' => 'Tipo de daño actualizado correctamente.',
    'type_delete_success' => 'Tipo de daño eliminado correctamente.',
    'type_assoc_damages' => 'Este tipo de daño no se puede eliminar porque tiene daños asociados.',
];
