<?php
$_POST['nickname'] = 'TestUser_' . rand(1000,9999);
$_POST['email'] = 'test@example.com';
$_POST['phone_number'] = '1234567890';
for ($i=0; $i<10; $i++) {
    $_POST['finger_' . $i] = 'SKIP';
}
$_POST['finger_0'] = 'SOME_FAKE_HASH_DATA'; // Give it at least one valid finger to pass validation
include 'api/enroll.php';
?>
