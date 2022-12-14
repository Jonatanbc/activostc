<?php

return [
    'custom_fields'		        => 'Egyéni mezők',
    'manage'                    => 'Manage',
    'field'		                => 'Mező',
    'about_fieldsets_title'		=> 'A mezőcsoportokról',
    'about_fieldsets_text'		=> 'Fieldsets allow you to create groups of custom fields that are frequently re-used for specific asset model types.',
    'custom_format'             => 'Custom Regex format...',
    'encrypt_field'      	        => 'A mező értékének titkosítása az adatbázisban',
    'encrypt_field_help'      => 'Figyelmeztetés: egy mező titkosítása kereshetetlenné teszi azt.',
    'encrypted'      	        => 'Titkosított',
    'fieldset'      	        => 'Mezőcsoportok',
    'qty_fields'      	      => 'Mennyiségi mezők',
    'fieldsets'      	        => 'Mezőcsoportok',
    'fieldset_name'           => 'Mezőcsoport neve',
    'field_name'              => 'Mező neve',
    'field_values'            => 'Mező értékei',
    'field_values_help'       => 'Adjon hozzá választási lehetőségeket, soronként egyet. Az első soron kívüli üres sorokat figyelmen kívül hagyjuk.',
    'field_element'           => 'Ürlap elem',
    'field_element_short'     => 'Elem',
    'field_format'            => 'Formátum',
    'field_custom_format'     => 'Egyéni formátum',
    'field_custom_format_help'     => 'Ez a mező lehetővé teszi a regex kifejezések használatát az érvényesítéshez. A kifejezésnek "regex:" -el kell kezdődnie - például, hogy ellenőrizze, hogy egy egyéni mezőérték érvényes IMEI-t tartalmaz-e ( ami 15 numerikus számjegy), akkor ezt használja <code>regex: / ^[0-9]{15}$ /</code>.',
    'required'   		          => 'Kötelező',
    'req'   		              => 'Kötelező.',
    'used_by_models'   		    => 'Modellek szerint ',
    'order'   		            => 'Rendelés',
    'create_fieldset'         => 'Új mezőcsoportok',
    'create_fieldset_title' => 'Create a new fieldset',
    'create_field'            => 'Új egyéni mező',
    'create_field_title' => 'Create a new custom field',
    'value_encrypted'      	        => 'A mező értéke titkosítva van az adatbázisban. Csak az adminisztrátor felhasználók láthatják a dekódolt értéket',
    'show_in_email'     => 'Szerepeljen ez a mező az eszköz kiadásakor a felhasználónak küldött emailben? A titkosított mezők nem szerepelhetnek az emailekben.',
    'help_text' => 'Help Text',
    'help_text_description' => 'This is optional text that will appear below the form elements while editing an asset to provide context on the field.',
    'about_custom_fields_title' => 'About Custom Fields',
    'about_custom_fields_text' => 'Custom fields allow you to add arbitrary attributes to assets.',
    'add_field_to_fieldset' => 'Add Field to Fieldset',
    'make_optional' => 'Required - click to make optional',
    'make_required' => 'Optional - click to make required',
    'reorder' => 'Reorder',
    'db_field' => 'DB Field',
    'db_convert_warning' => 'WARNING. This field is in the custom fields table as <code> :db_column </code> but should be :expected </code>.'
];
