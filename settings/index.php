<?php

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;

if ( ! defined('COOKIE_SESSION') ) define('COOKIE_SESSION', true);
require_once("../config.php");
require_once("settings_util.php");
session_start();

if ( ! U::get($_SESSION,'id') ) {
    die('Must be logged in.');
}

LTIX::getConnection();

$key_count = settings_key_count();

$sql = "SELECT count(C.context_id)
        FROM {$CFG->dbprefix}lti_context AS C
        LEFT JOIN {$CFG->dbprefix}lti_membership AS M ON C.context_id = M.context_id
        WHERE C.key_id IN (select key_id from {$CFG->dbprefix}lti_key where user_id = :UID ) 
         OR C.user_id = :UID";

$course_count = 0;
if ( U::get($_SESSION, 'id') ) {
    $row = $PDOX->rowDie($sql, array(':UID' => $_SESSION['id']));
    $course_count = U::get($row, 'count', 0);
}

$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();
?>
<div id="iframe-dialog" title="Read Only Dialog" style="display: none;">
   <iframe name="iframe-frame" style="height:600px" id="iframe-frame" 
    src="<?= $OUTPUT->getSpinnerUrl() ?>"></iframe>
</div>
<h1>My Settings</h1>
<p>This page is for instructors to manage their courses and the use of these
applications in their courses.
</p>
<ul>
<?php if ( $CFG->providekeys ) { ?>
<li><p><a href="key">Manage LMS Access Keys</a>
(<?= $key_count ?>)<br/>
These tools can be integrated into Learning Management Systems
that support the IMS Learning Tools Interoperability specification.
</p>
</li>
<?php } ?>
<?php if ( isset($CFG->google_classroom_secret) ) { ?>
<li><p><a href="../gclass/login">Connect to Google Classroom</a>
<?php
if ( isset($_SESSION['gc_courses']) ) {
    echo('(Connected to '.count(U::get($_SESSION,'gc_courses')).' classroom(s))');
} else {
    echo('(Not connected)');
}
?>
<p>
These 
<?php
if ( isset($_SESSION['gc_courses']) ) {
    echo('<a href="../store">tools</a>');
} else {
    echo('tools');
}
?>
 can be used in Google Classroom courses.
</p>
</li>
<?php } ?>
<!--
<li>
  <a href="recent" title="Recent Logins" target="iframe-frame"
  onclick="showModalIframe(this.title, 'iframe-dialog', 'iframe-frame', _TSUGI.spinnerUrl);" >
  Recent Logins 
  </a></li>
-->
<li><p><a href="context/">View My Contexts (Courses)</a>
(<?= $course_count ?>)
</p>
</li>
</ul>
<p>If you are an administrator for the overall site, you
can visit the administrator dashboard.
</p>
<?php

$OUTPUT->footer();

