<?php
require "config.php";
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $token = $_POST['csrf_token'] ?? null;

    if (!csrf_check($token)) {
        flash('error', 'Invalid security token.');
        header("Location: my_certificate.php");
        exit;
    }

    if ($id > 0) {
        $stmt = $conn->prepare(
            "SELECT certificate_file FROM certificates WHERE certificate_id = ? AND user_id = ?"
        );
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $file = UPLOAD_DIR . $row['certificate_file'];
            if (file_exists($file)) {
                unlink($file);
            }

            $stmt = $conn->prepare(
                "DELETE FROM certificates WHERE certificate_id = ? AND user_id = ?"
            );
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();

            flash('success', 'Certificate deleted successfully.');
        } else {
            flash('error', 'Certificate not found or unauthorized access.');
        }
    } else {
        flash('error', 'Invalid certificate selection.');
    }
}

header("Location: my_certificate.php");
exit;