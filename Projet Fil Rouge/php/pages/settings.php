<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_user_infos.php";
    require_once "../functions/validation/validate_user_input_settings.php";
    require_once "../functions/actions/update_user_profile.php";

    $user_id = $userData->user_id;

    // Get user information
    $user = getUserInfos($pdo, $user_id);

    // Handle form submission
    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $validationResult = validateUserProfile($_POST, $user, $pdo);
        $errors = $validationResult['errors'];
        $validatedData = $validationResult['data'];

        // Update user if no errors
        if (empty($errors)) {
            $pdo->beginTransaction();
            
            try {
                $updateSuccess = updateUserProfile($pdo, $user_id, $validatedData);

                if ($updateSuccess) {
                    $pdo->commit();
                    // Refresh user data with a SELECT query
                    $user = getUserInfos($pdo, $user_id);
                    $success = true;
                } else {
                    throw new PDOException("Update operation failed");
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors['general'] = "An error occurred while updating your infos: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/settings.css">
</head>
<body>
    <button class="sidebar-toggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <?php include "../../views/pages/settings.php" ?>

    <script src="../../assets/js/handle_bio.js"></script>
    <script src="../../assets/js/toggle_sidebar_on_mobile.js"></script>
</body>
</html>