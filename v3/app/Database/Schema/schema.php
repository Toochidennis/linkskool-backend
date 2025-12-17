<?php

return [
    'account_chart' => [
        'typeid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'account_id' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account_type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'inactive' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'account_type' => [
        'typeid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'aid' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'admission_criteria' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'courses' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'lowest1' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'lowest2' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'others' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'pass_mark' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'success_msg' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'failure_msg' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_venue' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_comment' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'admission_form_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'start_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'process' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'form' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'free' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'end_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'courses' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_time' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_venue' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'comment' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'success_msg' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'failure_msg' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_pass_mark' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'min_avg' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'min_num_pass' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course1' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course2' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'min_score_course1' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'min_score_course2' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'admission_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'exam_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_no' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'payment_pin' => [
            'type' => 'bigint(20) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'payment_amount' => [
            'type' => 'double UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'allowance' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'include' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'percent' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'dummy' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'allowance_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'alumni_record' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'picture_ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'title' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'surname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sex' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'birthdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'city' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'website' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'phone' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_no' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'religion' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_email' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_phone_no' => [
            'type' => 'varchar(55)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'local_government_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'nationality' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_status' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_admitted' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'past_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'occupation' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'graduation_year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'assessment_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'assesment_name' => [
            'type' => 'char(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'max_score' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'attendance' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'staff_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'count' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'register' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'card_data' => [
        'id' => [
            'type' => 'bigint(20)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'pin' => [
            'type' => 'varchar(17)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'serial' => [
            'type' => 'varchar(20)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'barcode' => [
            'type' => 'varchar(70)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'bigint(2)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'datetime' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'category' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'chatroom_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'chatroom' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'class_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'class_name' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'form_teacher' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result_template' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'monday_timetable' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'tuesday_timetable' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'wednesday_timetable' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'thursday_timetable' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'friday_timetable' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'comment_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reg_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'pass' => [
            'type' => 'varchar(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'form_teacher' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'principal' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'no_absent' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'no_present' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'no_school_opened' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'country' => [
        'countryId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'course_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'course_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_code' => [
            'type' => 'char(5)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'customer' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'customerid' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'customername' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'contact' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address2' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'city' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'zipcode' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'telephone' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'telephone1' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'website' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fax' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'customer_since' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'customer_type' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'inactive' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'prospect' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picture' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'referal' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'current_balance' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_by' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'clientid' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'default_account_settings' => [
        'sid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'account_receivable' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account_payable' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'cash' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sales' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'cost_of_sales' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fixed_assets' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'other_assets' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'expenses' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'current_assets' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'current_liabilities' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'long_term_liabilities' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'equity_open' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'equity_closed' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'equity_retained' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'inventory' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'accumulated_depreciation' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'payroll' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'discount' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'pur_discount' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'production' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'department' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(120)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'department_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'designation' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'designation_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'employee' => [
        'EmpID' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => true
        ],
        'LastName' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'FirstName' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'Country' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'entrance_form' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'number' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'admission_status' => [
            'type' => 'tinyint(1)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_status' => [
            'type' => 'tinyint(1)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col1' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col2' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col3' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col4' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col5' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col6' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col7' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col8' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col9' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col10' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col11' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col12' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col13' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col14' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col15' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col16' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col17' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col18' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col19' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col20' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col21' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col22' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col23' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col24' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col25' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col26' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col27' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col28' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col29' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'col30' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'fees_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'mandatory' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'file_count' => [
        'countid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'type' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'number' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'gender' => [
        'genderId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'grade_allowance' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'grade' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'allowance' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'grade_level' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'hall_of_fame' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'room' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'alumni' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'title' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'biography' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'hall_of_fame_room' => [
        'roomId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'hostel_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'capacity' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'insurable_earning' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'allowance' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'item' => [
        'tid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'transcid' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'itemID' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sales_desc' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'purchase_desc' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'upc' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'item_type' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'location' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'unit' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'weight' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'unit_cost' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'taxable' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'price' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'inactive' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'commission' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'costing_method' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'gl_sales_account' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'gl_inventory_account' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'gl_cos_account' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'minimum_stock' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'quantity_on_hand' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_created' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'mandatory' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_by' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'leave_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'staff_ref' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'startdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'enddate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'leave_type' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'level_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'level_name' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'school_type' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'rank' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result_template' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'admit' => [
            'type' => 'int(2)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'lga' => [
        'lgaId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'State' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'link' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'title' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'category' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'rank' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'parent' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'outline' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'url' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'body' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'start_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'end_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picref' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'no_of_views' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'author_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'publish' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'author_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'path_label' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'upload_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'marital_status' => [
        'statusId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'monthly_assessment_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reg_no' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'month' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_modified' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'nametitle' => [
        'titleId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'titleName' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'new_student_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'phone' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'next_term_fees' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fee' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fee_name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float(8, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'next_term_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'term_name' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term_start' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term_end' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'paid_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'payment_ref' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fees_ref' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'payment_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'bank_ref' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'fees_ref' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'teller_no' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount_used' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'teller_date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_used' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'productline' => [
        'productlineid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'transcid' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'shortcode' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'Name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'parent' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'promoted_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reg_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'promoted' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'prospective_staff_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'surname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sex' => [
            'type' => 'char(6)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'birthdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ref_no' => [
            'type' => 'varchar(15)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picture_ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'local_government_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'nationality' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'religion' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'qualification_primary' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'qualification_secondary' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'qualification_tertiary' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'qualification_postgraduate' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'referees' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'marital_status' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_status' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'phone_no' => [
            'type' => 'varchar(55)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'employment_status' => [
            'type' => 'tinyint(1)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'prospective_students_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'surname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sex' => [
            'type' => 'char(6)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'birthdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_no' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_email' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_phone_no' => [
            'type' => 'varchar(55)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picture_ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'local_government_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'nationality' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'religion' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'past_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'admission_status' => [
            'type' => 'tinyint(1)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_status' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_admitted' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_applied' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level_applied' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'question_table' => [
        'question_id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'parent' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'content' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'explanation' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'explanation_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'passage' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'passage_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'topic' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'topic_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'topic_status' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'instruction' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'instruction_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'title' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'answer' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'correct' => [
            'type' => 'longtext',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'relief' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'parameter1' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'parameter2' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'responses' => [
        'response_id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'student' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'student_name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'response' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'decimal(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'unmarked' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'marking' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'marking_score' => [
            'type' => 'decimal(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'total_score' => [
            'type' => 'decimal(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_total' => [
            'type' => 'decimal(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_count' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'attempted' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'converted' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class_name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'response_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'reference' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picture_ref' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'form_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'response' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_number' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'admit' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'register' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'new_student_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_start' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_end' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount_paid' => [
            'type' => 'float(12, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'result_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reg_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'new_result' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'total' => [
            'type' => 'decimal(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'grade' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'remark' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'comment' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'passed' => [
            'type' => 'tinyint(1)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_modified' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'modified_by' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'approved' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_approved' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'approved_by' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'saved_report' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'title' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'itype' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'itable' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'idcol' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'datecol' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'dateTitle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'others' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'group_others' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'graph' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'param' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'isource' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'idummy' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'igroup' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'datacol' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'datacol_label' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'icondition' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'idetail' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ifilter' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'icombine' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_alumni_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'username' => [
            'type' => 'varchar(75)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_calender' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'subject' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'timeFrom' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'timeTo' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'venue' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reminder' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reminderTime' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ReminderAudience' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ReminderSelect' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'personWith' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'confirmed' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_chat_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'message' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(75)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'chatroom' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_forum_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'subject' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'content' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'parent' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(75)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_posted' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'number_replies' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'number_views' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_news_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'subject' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'content' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_posted' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'publish' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'audience' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'views' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'pic_ref' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_pictures' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'pic' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_prefix' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'student' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'staff' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'alumni' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'regstart' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_settings_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'remote_server' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'website' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'short_name' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(155)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'initialize' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'conform' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'formdetails' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'dummy1' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'city' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'phone' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'student_prefix' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'staff_prefix' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'alumni_prefix' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'last_reg' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'school_logo' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'coverImage' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result_template' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_timetable' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'school_start_time' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'school_end_time' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'brake_start_time' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'brake_end_time' => [
            'type' => 'time',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'duration_course_period' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'score_grade_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'grade_symbol' => [
            'type' => 'char(2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'start' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'remark' => [
            'type' => 'varchar(15)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'section' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'department' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'section_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'sent_messages' => [
        'sent_messageId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'type' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sent_to' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'message' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'userid' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'Date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'skill_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'skill_name' => [
            'type' => 'char(45)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_access_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'username' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'access_level' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_account_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'staff' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'bank' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account_no' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'branch' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_allowance' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'staff_ref' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'allowance' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_anual_leave_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'begining_date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ending_date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_bank_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_course_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_grade_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'grade_name' => [
            'type' => 'varchar(15)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'grade_number' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'basic_salary' => [
            'type' => 'double UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_payroll' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'staff_ref' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'allowance' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'month' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_query_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'query_subject' => [
            'type' => 'varchar(150)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'query_body' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_issued' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'comment' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_record' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'picture_ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'surname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sex' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'birthdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'city' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'phone' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'staff_no' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'religion' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'marital_status' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'local_government_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'nationality' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'town' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_status' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'past_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'past_record2' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'p_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'work_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'referees' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'additional' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registrationtime' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'kin_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'kin_address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'kin_email' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'kin_phone_no' => [
            'type' => 'varchar(55)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_employed' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_appraisal' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'appraisal' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'grade' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'department' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'section' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'designation' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'access_level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_relief' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'staff_ref' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'relief' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_sack_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_sacked' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reason' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'staff_suspension_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_suspended' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'recall_date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reason' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'state' => [
        'stateId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'statutory_allowance' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'allowance' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_drop_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_dropped' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reason' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_expulsion_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_expelled' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reason' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_hostel_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'hostel' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_payment_record' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'payment_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_promotion_criteria_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'courses_min' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'no_passed' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'strict' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'pass_mark' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_record' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'picture_ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'surname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sex' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'birthdate' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'city' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'country' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'registration_no' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'religion' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_name' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_address' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_email' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'guardian_phone_no' => [
            'type' => 'varchar(55)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'local_government_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'state_origin' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'nationality' => [
            'type' => 'varchar(25)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'health_status' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_admitted' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'past_record' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'result' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'student_class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'student_level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_registration_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_signature' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_skill_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'tinyint(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reg_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'skill' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_suspension_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_suspended' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'recall_date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reason' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'students_transfer_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'registration_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_transfered' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'school' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'student_access_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'username' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(30)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ref_no' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'sync_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'query' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'tax_rates' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'rate' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'start_limit' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'transactions' => [
        'tid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'memo' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'ref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'method' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'date',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'year' => [
            'type' => 'year(4)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'term' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'c_type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'cid' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'cref' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'class' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'quantity' => [
            'type' => 'float(12, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'it_id' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'it_type' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'unit_price' => [
            'type' => 'float(12, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount_due' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'net_due' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'account_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'glaccount' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'discount_account' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'discount_amount' => [
            'type' => 'float(10, 2)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'trans_no' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        's_no' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'trans_type' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'sub' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'approved' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'voucher_bonus_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'percentage' => [
            'type' => 'double(6, 3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'double',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'voucher_deduction_table' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'percentage' => [
            'type' => 'double(6, 3) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'double',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'ward' => [
        'wardId' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'Name' => [
            'type' => 'varchar(20)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'lga' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'school_data' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true,
        ],
        'token' => [
            'type' => 'int(5)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false,
        ],
        'school_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'address' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'state' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'country' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'latlng' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'website' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'picture' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'logo' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'proprietors_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'proprietors_email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'proprietors_phone' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'principals_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'principals_email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'principals_phone' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'admin_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'admin_email' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'admin_phone' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'school_url' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'database_name' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'folder_name' => [
            'type' => 'varchar(200)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'active' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'size' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'satisfaction' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'partners_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'partners_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ],
    ],
    'cbt_users' => [
        'id' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'username' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'name' => [
            'type' => 'varchar(100)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'email' => [
            'type' => 'varchar(100)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
            'unique' => true
        ],
        'profile_picture' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'attempt' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => '0',
            'auto_increment' => false,
            'primary' => false
        ],
        'subscribed' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => '0',
            'auto_increment' => false,
            'primary' => false
        ],
        'date_subscribed' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'expires_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'last_renewed' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'subscription_type' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'reference' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'amount' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'exam_type' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'title' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'shortname' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'picref' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'level' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_ids' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'is_active' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'display_order' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'exam_attempts' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'exam_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'average_score' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'completion_rate' => [
            'type' => 'float',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'test_taken' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'history' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date_taken' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'audit_logs' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'user_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'action' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'action_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'action_type' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'details' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'users' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'picture_ref' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'first_name' => [
            'type' => 'varchar(255)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'middle_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'last_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(50)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'phone' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'email' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'state' => [
            'type' => 'varchar(70)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'password' => [
            'type' => 'varchar(255)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'bio' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'roleId' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'accessLevel' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'address' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'website' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'other' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'date' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'last_active' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'challenge' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'title' => [
            'type' => 'varchar(250)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'description' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'start_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'end_date' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'time_limit' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'details' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'count_per_exam' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'question_ids' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'exam_type_id' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'status' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => 0,
            'auto_increment' => false,
            'primary' => false
        ],
        'author_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'author_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'is_active' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => 1,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'updated_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'published_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'challenge_participants' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'challenge_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'joined_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'cbt_leaderboard' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'challenge_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'user_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'username' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'score' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'correct_answers' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'total_questions' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'time_taken' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'attempts_count' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => 1,
            'auto_increment' => false,
            'primary' => false
        ],
        'submitted_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'position' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'location' => [
            'type' => 'varchar(100)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'device_id' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'platform' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'extra' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'updated_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'syllabi' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(255)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'normalized_name' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'updated_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'topics' => [
        'id' => [
            'type' => 'int(11) UNSIGNED',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(255)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'normalized_name' => [
            'type' => 'text',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'course_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'syllabus_id' => [
            'type' => 'int(11)',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        'created_at' => [
            'type' => 'datetime',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'auto_increment' => false,
            'primary' => false
        ],
        'updated_at' => [
            'type' => 'datetime',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'primary' => false
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]
    ],
    'exam' => [],
    'videosTable' => [],
    'categoryTable' => [],
];
