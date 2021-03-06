<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Return token using a shared secret to verify request source.
 * @package     local_ws_pocketcampus
 * @copyright   2021 Amer Chamseddine <amer@pocketcampus.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/moodlelib.php');

$pcid = required_param('pcsecret', PARAM_RAW);
$userid = required_param('user_id', PARAM_RAW);
$serviceshortname  = required_param('service',  PARAM_ALPHANUMEXT);
$config = get_config('local_ws_pocketcampus');

echo $OUTPUT->header();

// Check if the IP the request comes from is valid.
if (isset($config->subnet) AND (trim($config->subnet) != '')) {
    if (!(address_in_subnet(getremoteaddr(), $config->subnet))) {
        throw new moodle_exception('invalidsubnet', 'local_ws_pocketcampus');
    }
}

// Check if the service exists and is enabled.
$service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
if (empty($service)) {
    if ($serviceshortname === 'moodle_mobile_app') {
        throw new moodle_exception('enablewsdescription', 'webservice');
    } else {
        throw new moodle_exception('servicenotavailable', 'webservice');
    }
}

if ($pcid === $config->secret) {
    $user = get_complete_user_data('username', $userid, null, true);
} else {
    throw new moodle_exception('invalidsecret', 'local_ws_pocketcampus');
}

$systemcontext = context_system::instance();
if (!empty($user)) {

    // Cannot authenticate unless maintenance access is granted.
    $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
    if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }
    if (isguestuser($user)) {
        throw new moodle_exception('noguest');
    }
    if (empty($user->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
    }
    // Check credential expiry.
    $userauth = get_auth_plugin($user->auth);
    if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
        $days2expire = $userauth->password_expire($user->username);
        if (intval($days2expire) < 0 ) {
            throw new moodle_exception('passwordisexpired', 'webservice');
        }
    }
    // Setup user session to check capability.
    \core\session\manager::set_user($user);
    // Get an existing token or create a new one.
    $token = external_generate_token_for_current_user($service);
    external_log_token_request($token);
    $usertoken = new stdClass;
    $usertoken->token = $token->token;
    // Private token, only transmitted to https sites and non-admin users.
    $siteadmin = has_capability('moodle/site:config', $systemcontext, $user->id);
    if (is_https() and !$siteadmin) {
        $usertoken->privatetoken = $token->privatetoken;
    } else {
        $usertoken->privatetoken = null;
    }
    echo json_encode($usertoken);
} else {
    throw new moodle_exception('usernamenotfound', 'moodle');
}
