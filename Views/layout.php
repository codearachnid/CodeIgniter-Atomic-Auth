<!doctype html>
<html>
<head>
    <title>AtomicAuth</title>
</head>
<body>
    <?= $this->include('AtomicAuth\Views\navigation') ?>
    <?= $this->include('AtomicAuth\Views\message') ?>
    <?= $this->renderSection('app') ?>
    <?= $this->include('AtomicAuth\Views\footer') ?>
</body>
</html>
