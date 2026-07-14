<?php

function logActivity($pdo, $userId, $action)
{
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs(user_id, action)
        VALUES (?, ?)
    ");

    $stmt->execute([$userId, $action]);
}