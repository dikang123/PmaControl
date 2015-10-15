<?php

/*
 * Test all configuration to be sure everything correctly installed
 * developement => true
 * production => false
 * default : true
 */

if (!defined('AUTH_SESSION_TIME')) {
    define("AUTH_SESSION_TIME",86400);
}


if (! defined('AUTH_ACTIVE'))
{
   define("AUTH_ACTIVE", true);
}

