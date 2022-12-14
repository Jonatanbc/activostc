<?php

return [

    'update' => [
        'error'                 => 'Er is een fout opgetreden tijdens het updaten. ',
        'success'               => 'Instellingen zijn met succes gewijzigd.',
    ],
    'backup' => [
        'delete_confirm'        => 'Weet je het zeker dat je deze Back-up bestand wilt verwijderen? Dit kan niet meer terug gedraaid worden. ',
        'file_deleted'          => 'De Back-up bestand is met succes verwijderd. ',
        'generated'             => 'Een nieuw Back-up bestand is met succes aangemaakt.',
        'file_not_found'        => 'Die Back-up bestand kon niet gevonden worden op de server.',
        'restore_warning'       => 'Yes, restore it. I acknowledge that this will overwrite any existing data currently in the database. This will also log out all of your existing users (including you).',
        'restore_confirm'       => 'Are you sure you wish to restore your database from :filename?'
    ],
    'purge' => [
        'error'     => 'Er is iets fout gegaan tijdens het opschonen.',
        'validation_failed'     => 'De opschoon bevestiging is niet correct. Typ het woord "DELETE" in het bevestigingsveld.',
        'success'               => 'Verwijderde items succesvol opgeschoond',
    ],
    'mail' => [
        'sending' => 'Sending Test Email...',
        'success' => 'Mail sent!',
        'error' => 'Mail could not be sent.',
        'additional' => 'No additional error message provided. Check your mail settings and your app log.'
    ],
    'ldap' => [
        'testing' => 'Testing LDAP Connection, Binding & Query ...',
        '500' => '500 Server Error. Please check your server logs for more information.',
        'error' => 'Something went wrong :(',
        'sync_success' => 'A sample of 10 users returned from the LDAP server based on your settings:',
        'testing_authentication' => 'Testing LDAP Authentication...',
        'authentication_success' => 'User authenticated against LDAP successfully!'
    ],
    'slack' => [
        'sending' => 'Sending Slack test message...',
        'success_pt1' => 'Success! Check the ',
        'success_pt2' => ' channel for your test message, and be sure to click SAVE below to store your settings.',
        '500' => '500 Server Error.',
        'error' => 'Something went wrong.',
    ]
];
