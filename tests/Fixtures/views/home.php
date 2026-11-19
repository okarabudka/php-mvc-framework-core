<?php
/** @var Okarabudka\PhpMvcCore\View $this */
/** @var string $name */
$name ??= 'guest';
$this->title = 'Home';
?>
<h1>Hello <?= htmlspecialchars($name) ?></h1>
