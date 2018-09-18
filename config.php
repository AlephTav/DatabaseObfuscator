<?php

return [
    // The database connection configuration. There must be already created database for the given host.
	'connection' => [
	    'driver' => 'mysql',
        'database' => 'test',
		'username' => 'root',
		'password' => '',
        'host' => '127.0.0.1',
        'port' => 3306,
        'options' => []
	],
    // The schema must have the following structure:
    // [
    //     'table name' => [
    //         'key' => key column or array of key columns (it's optional parameter),
    //         'columns' => [
    //             'column name' => [
    //                 'type' => 'value generator type (valid values: string, email)',
    //                 'min' => min length of generated strings,
    //                 'max' => max length of generated strings,
    //                 'minChar' => min code of string characters,
    //                 'maxChar' => max code of string characters,
    //                 'chars' => 'alphabet of generated strings'
    //             ],
    //             ...
    //         ]
    //     ],
    //     ...
    // ]
	'schema' => [
		'user' => [
		    'key' => 'id',
		    'columns' => [
                'email' => [
                    'type' => 'email',
                    'min' => 8,
                    'max' => 12
                ],
                'name' => [
                    'type' => 'string',
                    'min' => 5,
                    'max' => 15
                ],
                'surname' => [
                    'type' => 'string',
                    'min' => 5,
                    'max' => 15
                ]
            ]
        ]
	]
];