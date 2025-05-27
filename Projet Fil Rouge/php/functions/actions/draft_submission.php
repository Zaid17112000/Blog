<?php
    /**
     * Processes draft submission (create or update).
     * @param PDO $pdo Database connection
     * @param int $user_id User ID
     * @return array Response data
     * @throws Exception
     */

    function createSlug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }

    function processDraftSubmission($pdo, $user_id) {
        $post_title = trim(filter_input(INPUT_POST, 'post-title', FILTER_SANITIZE_SPECIAL_CHARS));
        $category_names = json_decode($_POST['selected-categories'] ?? '[]', true);
        $post_content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS));
        $tags_name = json_decode($_POST['selected-tags'] ?? '[]', true);
        $post_excerpt = trim(filter_input(INPUT_POST, 'excerpt', FILTER_SANITIZE_SPECIAL_CHARS));
        $post_status = 'draft';
        // $user_id = $_SESSION["user"];
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT) ?: null;

        if (!is_array($category_names)) {
            $category_names = [];
        }
        if (!is_array($tags_name)) {
            $tags_name = [];
        }

        $post_img_url = null;
        $hasNewImage = isset($_FILES['featured-image']) && $_FILES['featured-image']['error'] == UPLOAD_ERR_OK;

        if ($hasNewImage) {
            $post_img_url = handleFileUpload($_FILES['featured-image']);
        }

        $pdo->beginTransaction();

        if ($post_id) {
            // Fetch existing post to preserve post_img_url if no new image is uploaded
            $stmt = $pdo->prepare("SELECT post_img_url FROM posts WHERE post_id = :post_id AND user_id = :user_id");
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            $existing_post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing_post) {
                throw new Exception("Post not found or unauthorized.");
            }

            $post_img_url = $hasNewImage ? $post_img_url : $existing_post['post_img_url'];

            // Update existing post
            $queryUpdatePost = "UPDATE posts SET 
                post_title = :post_title, 
                post_content = :post_content, 
                post_excerpt = :post_excerpt, 
                post_img_url = :post_img_url, 
                post_status = :post_status, 
                post_updated = NOW()
            WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $pdo->prepare($queryUpdatePost);
            $stmt->execute([
                ':post_title' => $post_title,
                ':post_content' => $post_content,
                ':post_excerpt' => $post_excerpt,
                ':post_img_url' => $post_img_url,
                ':post_status' => $post_status,
                ':post_id' => $post_id,
                ':user_id' => $user_id
            ]);

            // Delete existing relations(categories and tags)
            $pdo->prepare("DELETE FROM categories_posts WHERE post_id = ?")->execute([$post_id]);
            $pdo->prepare("DELETE FROM tags_posts WHERE post_id = ?")->execute([$post_id]);
        }
        else {
            $queryInsertPost = "INSERT INTO posts(
                post_title, 
                post_content, 
                post_excerpt, 
                post_img_url, 
                post_status, 
                user_id,
                post_created
            )
            VALUES(
                :post_title, 
                :post_content, 
                :post_excerpt, 
                :post_img_url, 
                :post_status, 
                :user_id, 
                NOW()
            )";
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
        }

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

        if (!empty($tags_name)) {
            error_log("Starting tag processing with tags: " . print_r($tags_name, true));
            // Ensure tags_name is an array
            if (!is_array($tags_name)) {
                error_log("Invalid tags format: " . print_r($_POST['selected-tags'], true));
                $tags_name = [];
            }
            foreach ($tags_name as $tag_name) {
                $tag_name = trim($tag_name);
                if (empty($tag_name)) {
                    continue;
                }

                error_log("Processing tag: " . $tag_name);

                $queryTag = "SELECT tag_id FROM tags WHERE tag_name = :tag_name";
                $stmt = $pdo->prepare($queryTag);
                $stmt->execute([':tag_name' => $tag_name]);
                $tag_id = $stmt->fetchColumn();

                // If tag doesn't exist, create it
                if (!$tag_id) {
                    try {
                        $queryInsertTag = "INSERT INTO tags (tag_name, tag_slug) VALUES (:tag_name, :tag_slug)";
                        $stmt = $pdo->prepare($queryInsertTag);
                        $slug = createSlug($tag_name); // Implement this function
                        $stmt->execute([
                            ':tag_name' => $tag_name,
                            ':tag_slug' => $slug
                        ]);
                        $tag_id = $pdo->lastInsertId();
                    } catch (PDOException $e) {
                        // If duplicate, try to fetch the tag again
                        if ($e->errorInfo[1] == 1062) { // Duplicate entry error code
                            $stmt = $pdo->prepare("SELECT tag_id FROM tags WHERE tag_slug = :slug");
                            $stmt->execute([':slug' => $slug]);
                            $tag_id = $stmt->fetchColumn();
                        } else {
                            throw $e;
                        }
                    }
                }

                if ($tag_id) {
                    error_log("Linking tag $tag_id to post $post_id");
                    $queryInsertTagPost = "INSERT INTO tags_posts (post_id, tag_id) VALUES (:post_id, :tag_id)";
                    $stmt = $pdo->prepare($queryInsertTagPost);
                    $stmt->execute([':post_id' => $post_id, ':tag_id' => $tag_id]);
                }
            }
        }

        $pdo->commit();
    
        return [
            'success' => true,
            'message' => 'Draft saved successfully!',
            'post_id' => $post_id,
            'image_url' => $post_img_url,
            'category_names' => $category_names,
            'tags' => $tags_name
        ];
    }

    /**
     * Handles file upload and validation.
     * @param array $file Uploaded file data
     * @return string|null File URL or null
     * @throws Exception
     */
    function handleFileUpload($file) {
        // Validate image file
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
            throw new Exception("Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.");
        }

        // Validate file size (max 5MB)
        if ($file['size'] > MAX_IMAGE_SIZE) {
            throw new Exception("Image size too large. Maximum 5MB allowed.");
        }

        $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/Projet Fil Rouge/php/uploads/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file_name = uniqid() . '-' . basename($file['name']);
        $target_file = $upload_path . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return '/Projet Fil Rouge/php/uploads/' . $file_name;
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    }

    /**
     * Sends a JSON response and exits.
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode($data);
        exit;
    }