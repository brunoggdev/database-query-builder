<?php
defined('ENVIRONMENT') or die('Direct access not allowed');

// Just an example of how the file might be configured
if (ENVIRONMENT === 'PRODUCTION') {

    return [
        'localhost',        //host
        'test',             //dbname
        'tester',           //user
        '2cf24dba5fb0a'     //password
    ];

} else {

    return [
        'localhost',    //host
        'blog',         //dbname
        'root',         //user
        ''              //password
    ];

}