<?php

$deploymentFile = __DIR__ . '/deployment-check.txt';

$deploymentStatus = file_exists($deploymentFile)
    ? file_get_contents($deploymentFile)
    : 'Deployment script has not run yet.';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerAvatar Git Test</title>
</head>
<body>

    <h1>Git Integration Test - Version 3!</h1>

    <p>My PHP application is running on ServerAvatar.</p>

    <h3>Deployment Script Status</h3>

    <p>
        <?= htmlspecialchars(
            trim($deploymentStatus),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>

</body>
</html>