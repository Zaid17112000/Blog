<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/bootstrap.php";
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_categories.php";

    $user = $userData->user_id;

    $categories = getCategories($pdo);

    // Get the category from the URL, if any
    $selected_category = isset($_GET['category']) ? urldecode($_GET['category']) : null;

    // Pagination parameters
    $posts_per_page = 4;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Gets the current page number from URL (?page=X), defaults to 1
    if ($current_page < 1) {
        $current_page = 1;
    } // Validation: Ensures page number is never less than 1
    $offset = ($current_page - 1) * $posts_per_page;

    $featured_id = $pdo->query("SELECT post_id FROM posts ORDER BY post_published DESC LIMIT 1")->fetchColumn();
    
    // Base query for counting total posts (for knowing how many pages to generate)
    $queryCountPosts = "SELECT COUNT(DISTINCT p.post_id) as total FROM posts p 
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
        LEFT JOIN categories c ON cp.category_id = c.category_id
        LEFT JOIN users u ON u.user_id = p.user_id";

    // Base query for getting posts
    $queryGetPosts = "SELECT 
    p.*, 
    CONCAT(u.last_name, ' ', u.first_name) AS username, 
    u.img_profile, 
    p.post_created, 
    p.post_excerpt, 
    GROUP_CONCAT(c.category_name SEPARATOR ' | ') AS category_name, 
    COUNT(l.like_id) as like_count 
        FROM posts p 
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
        LEFT JOIN categories c ON cp.category_id = c.category_id
        LEFT JOIN users u ON u.user_id = p.user_id";

    $conditions = [];
    $params = [];

    if ($featured_id) {
        $conditions[] = "p.post_id != :featured_id";
        $params[':featured_id'] = $featured_id;
    }

    if (!empty($selected_category)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = :category_name");
        $stmt->bindParam(":category_name", $selected_category);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            header("Location: blogsphere.php");
            exit;
        }
        $conditions[] = "c.category_name = :category";
        $params[':category'] = $selected_category;
    }

    $conditions[] = "p.post_status = 'published'";

    if (!empty($conditions)) {
        $queryCountPosts .= " WHERE " . implode(" AND ", $conditions);
        $queryGetPosts .= " WHERE " . implode(" AND ", $conditions);
    }

    $queryGetPosts .= " GROUP BY p.post_id ORDER BY p.post_published DESC LIMIT :limit OFFSET :offset";

    // Get total number of posts for pagination
    $stmt = $pdo->prepare($queryCountPosts);
    $stmt->execute($params);
    $total_posts = $stmt->fetchColumn();

    // Calculate total pages
    $total_pages = ceil($total_posts / $posts_per_page);

    // Prepare and execute the query for posts
    $stmt = $pdo->prepare($queryGetPosts);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':limit', $posts_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts - BlogSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/blogsphere.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .category.active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .likes .fa-solid {
            color: red;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php"; ?>
    
    <!-- Blog Posts Section -->
    <?php include "../../views/pages/blogsphere_main_section.php"; ?>

    <!-- Pagination -->
    <?php include "../../views/pages/pagination.php"; ?>
    
    <!-- Newsletter -->
    <?php include "../../views/partials/newsletter.php"; ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer.html"; ?>

    <script src="../../assets/js/toggle_sidebar.js"></script>
    <script src="../../assets/js/category_scrolling.js"></script>
    <script src="../../assets/js/handle_save_post.js"></script>
    <script src="../../assets/js/handle_like_post.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>

    <script>
        function debounce(func, timeout = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => { func.apply(this, args); }, timeout);
            };
        }

        // Update your search input event listener
        const searchInput = document.getElementById('searchBar');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function(e) {
                handleSearchInput(e.target.value);
            }));
        }

        <?php require_once "../../assets/js/handle_search_input.js"; ?>
        
        <?php require_once "../../assets/js/update_articles_grid.js"; ?>

        function escapeHtml(unsafe) {
            if (unsafe == null) return '';  // Handle null or undefined
            return String(unsafe)  // Convert to string in case it's a number
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        const searchIcon = document.getElementById('searchIcon');
        const searchBar = document.getElementById('searchBar');

        searchIcon.addEventListener('click', () => {
            searchBar.classList.toggle('visible');
            if (searchBar.classList.contains('visible')) {
                searchBar.focus();
            }
        });
    </script>
</body>
</html>