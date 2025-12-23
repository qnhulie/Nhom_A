<?php
require_once __DIR__ . '/../../controllers/PersonaController.php';
$controller = new PersonaController();

if (isset($_GET['id'])) {
    $controller->delete($_GET['id']);
    header('Location: index.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>