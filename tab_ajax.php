<?php
require 'Tab_translator.php';

if (isset($_POST['input'])) {
	$input = $_POST['input'];
}

$tt = new Tab_translator($input);

$notes = $tt->get_notes();

echo json_encode($notes);
