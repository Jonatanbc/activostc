<?php

return [
    'create' => [
        'success' => 'Damage registered successfully.',
    ],
    'update' => [
        'success' => 'Damage updated successfully.',
    ],
    'delete' => [
        'success' => 'Damage deleted successfully.',
    ],
    'bulk' => [
        'success' => ':count damage(s) updated successfully.',
        'nothing_selected' => 'No damages were selected.',
    ],
    'pr' => [
        'created' => 'Purchase request generated successfully.',
        'updated' => 'Purchase request updated successfully.',
        'deleted' => 'Purchase request deleted successfully.',
        'item_removed' => 'Damage removed from the request.',
    ],
    'email' => [
        'sent' => 'Report emailed successfully.',
        'scheduled' => 'Sending scheduled successfully. A first copy was sent now.',
        'schedule_deleted' => 'Scheduled send deleted.',
        'failed' => 'The email could not be sent: :error',
        'log_mode' => 'Mail is in test mode (MAIL_MAILER=log): it was logged but NOT delivered. Configure SMTP for real delivery.',
        'scheduled_log' => 'Sending scheduled. Note: mail is in test mode (log), so the immediate copy was not actually delivered. Configure SMTP for real delivery.',
    ],
    'type_create_success' => 'Damage type created successfully.',
    'type_update_success' => 'Damage type updated successfully.',
    'type_delete_success' => 'Damage type deleted successfully.',
    'type_assoc_damages' => 'This damage type cannot be deleted because it has associated damages.',
];
