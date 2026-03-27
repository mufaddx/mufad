<?php
require_once __DIR__ . '/includes/functions.php';
logoutUser();
setFlash('success', 'You have been logged out successfully.');
redirect('/login.php');
