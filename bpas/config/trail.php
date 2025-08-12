<?php
// CodeIgniter User Audit Trail

defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Enable Audit Trail
|--------------------------------------------------------------------------
| Set [TRUE/FALSE] to use of audit trail
*/
$config['audit_enable'] = TRUE;
/*
|--------------------------------------------------------------------------
| Not Allowed table for auditing
|--------------------------------------------------------------------------
| The following setting contains a list of the not allowed database tables for auditing.
| You may add those tables that you don't want to perform audit.
|
*/
$config['not_allowed_tables'] = [
    // 'ci_sessions',
    'sessions',
    'user_audit_trails',
    'login_attempts',
    'costing',
    'user_logins',
    'order_ref',
    'users',
    'gl_trans',
    'att_check_in_out',
    'att_dailies',
    'api_logs',
    'api_limits'
];


/*
|--------------------------------------------------------------------------
| Enable Insert Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track insert event.
|
*/
$config['track_insert'] = TRUE;
// $config['track_insert'] = FALSE;

/*
|--------------------------------------------------------------------------
| Enable Update Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track update event
|
*/
$config['track_update'] = TRUE;

/*
|--------------------------------------------------------------------------
| Enable Delete Event Track
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to track delete event
|
*/
$config['track_delete'] = TRUE;
$config['auth'] = '';
