<?php
    function publishDraftPost($pdo, $status) {
        // Collect form data with defaults
        $post_title = $_POST['post-title'] ?? '';
        $category_names = json_decode($_POST['selected-categories'] ?? '[]', true);
        $post_content = $_POST['content'] ?? ''; // Added default for consistency
        $tags_name = json_decode($_POST['selected-tags'] ?? '[]', true);
        $post_excerpt = $_POST['excerpt'] ?? '';
        $post_status = $status;
        $user_id = $_SESSION["user"];
    
        // Ensure arrays
        $category_names = is_array($category_names) ? $category_names : [];
        $tags_name = is_array($tags_name) ? $tags_name : [];
    
        // Handle file upload
        $post_img_url = null;
        if (isset($_FILES['featured-image']) && $_FILES['featured-image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Projet Fil Rouge/php/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = uniqid() . '-' . basename($_FILES['featured-image']['name']);
            $target_file = $upload_dir . $file_name;
            if (!move_uploaded_file($_FILES['featured-image']['tmp_name'], $target_file)) {
                throw new Exception("Failed to move uploaded file.");
            }
            $post_img_url = '/Projet Fil Rouge/php/uploads/' . $file_name;
        }
    
        // Insert post into database
        $queryInsertPost = "INSERT INTO posts(post_title, post_content, post_excerpt, post_img_url, post_status, user_id)
        VALUES(:post_title, :post_content, :post_excerpt, :post_img_url, :post_status, :user_id)";
        $stmt_insert_post = $pdo->prepare($queryInsertPost);
        $stmt_insert_post->execute([
            ':post_title' => $post_title,
            ':post_content' => $post_content,
            ':post_excerpt' => $post_excerpt,
            ':post_img_url' => $post_img_url,
            ':post_status' => $post_status,
            ':user_id' => $user_id
        ]);

        $post_id = $pdo->lastInsertId();
    
        // Insert categories
        if (!empty($category_names)) {
            foreach ($category_names as $category_name) {
                $queryCategory = "SELECT category_id FROM categories WHERE category_name = :category_name";
                $stmt = $pdo->prepare($queryCategory);
                $stmt->execute([':category_name' => $category_name]);
                $category_id = $stmt->fetchColumn();
    
                if ($category_id) {
                    $queryInsertCategoryPost = "INSERT INTO categories_posts (post_id, category_id) VALUES (:post_id, :category_id)";
                    $stmt = $pdo->prepare($queryInsertCategoryPost);
                    $stmt->execute([':post_id' => $post_id, ':category_id' => $category_id]);
                }
            }
        }
    
        // Insert tags
        if (!empty($tags_name)) {
            foreach ($tags_name as $tag_name) {
                $queryTag = "SELECT tag_id FROM tags WHERE tag_name = :tag_name";
                $stmt = $pdo->prepare($queryTag);
                $stmt->execute([':tag_name' => $tag_name]);
                $tag_id = $stmt->fetchColumn();
    
                if ($tag_id) {
                    $queryInsertTagPost = "INSERT INTO tags_posts (post_id, tag_id) VALUES (:post_id, :tag_id)";
                    $stmt = $pdo->prepare($queryInsertTagPost);
                    $stmt->execute([':post_id' => $post_id, ':tag_id' => $tag_id]);
                }
            }
        }
    
        // Return success message
        return "Post " . ($status === 'published' ? "published" : "saved as draft") . " successfully!";
    }

    // Handle form submission
    if (isset($_POST["publish"])) {
        try {
            echo publishDraftPost($pdo, 'published');
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST["draft"])) {
        try {
            echo publishDraftPost($pdo, 'draft');
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }