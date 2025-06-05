<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/validation/validate_csrf_token.php";
    require_once "../functions/queries/addPost/query_post.php";
    require_once "../functions/queries/addPost/query_categories_post.php";
    require_once "../functions/queries/addPost/query_tags.php";
    require_once "../functions/queries/addPost/query_category.php";
    require_once "../functions/queries/addPost/query_status_post.php";
    require_once "../functions/actions/addPost/update_post.php";
    require_once "../functions/actions/addPost/insert_post.php";
    require_once "../functions/actions/addPost/insert_categories_posts.php";
    require_once "../functions/actions/addPost/insert_tag.php";
    // require_once "../functions/actions/addPost/handle_file_upload.php";
    // require_once "../functions/actions/addPost/create_slug.php";

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $user_id = $userData->user_id;

    // Reset post_id if this is a new post
    if (!isset($_GET['post_id'])) {
        $post_data['post_id'] = null;
    }

    $post_data = [
        'post_id' => null,
        'post_title' => '',
        'post_content' => '',
        'post_excerpt' => '',
        'post_img_url' => '',
        'selected_categories' => [],
        'selected_tags' => []
    ];

    if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
        $post_id = filter_var($_GET['post_id'], FILTER_VALIDATE_INT);
        $post = queryPost($pdo, $post_id, $user_id);

        if (!$post) {
            throw new Exception("Post not found or access denied");
        }

        if ($post) {
            $post_data['post_id'] = $post_id;
            $post_data['post_title'] = $post['post_title'];
            $post_data['post_content'] = $post['post_content'];
            $post_data['post_excerpt'] = $post['post_excerpt'];
            $post_data['post_img_url'] = $post['post_img_url'];
            $post_data['post_status'] = $post['post_status'];
    
            $post_data['selected_categories'] = queryCategoriesPost($pdo, $post_id);
    
            $post_data['selected_tags'] = queryTags($pdo, $post_id);
        } else {
            $error_message = "Post not found or you don't have permission to edit it";
        }
    }

    $queryCategories = "SELECT category_name FROM categories";
    $stmt = $pdo->prepare($queryCategories);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST["publish"])) {
        $pdo->beginTransaction();
    
        validateCsrfToken();

        try {
            $post_title = $_POST['post-title'] ?? '';
            $category_names = json_decode($_POST['selected-categories'] ?? '[]', true);
            $post_content = $_POST['content'] ?? '';
            $tags_name = json_decode($_POST['selected-tags'] ?? '[]', true);
            $post_excerpt = $_POST['excerpt'] ?? '';
            $post_status = 'published';
            $user_id = $_SESSION["user"];

            // Validate required fields
            $errors = [];
            
            if (empty(trim($post_title))) {
                $errors['post_title'] = "Post title is required";
            }
            
            if (empty(trim($post_content)) || $post_content === '<p><br></p>') {
                $errors['post_content'] = "Post content is required";
            }
            
            if (empty($category_names)) {
                $errors['categories'] = "At least one category is required";
            }

            if (strlen($post_excerpt) > 300) {
                $errors['excerpt'] = "Excerpt must be 300 characters or less";
            }

            $hasExistingImage = !empty($post_data['post_img_url']);
            $hasNewImage = isset($_FILES['featured-image']) && $_FILES['featured-image']['error'] == UPLOAD_ERR_OK;
            
            if (!$hasExistingImage && !$hasNewImage) {
                $errors['featured-image'] = "Featured image is required";
            }

            if (!is_array($category_names)) {
                $category_names = [];
            }
            if (!is_array($tags_name)) {
                $tags_name = [];
            }

            $post_img_url = $post_data['post_img_url'];
            if (isset($_FILES['featured-image']) && $_FILES['featured-image']['error'] == UPLOAD_ERR_OK) {
                $upload = $_SERVER['DOCUMENT_ROOT'] . '/Projet Fil Rouge/php/uploads/';
                if (!file_exists($upload)) {
                    mkdir($upload, 0777, true);
                }
                $file_name = uniqid() . '-' . basename($_FILES['featured-image']['name']);
                $target_file = $upload . $file_name;
                if (move_uploaded_file($_FILES['featured-image']['tmp_name'], $target_file)) {
                    $post_img_url = '/Projet Fil Rouge/php/uploads/' . $file_name;
                } else {
                    throw new Exception("Failed to move uploaded file.");
                }
            }
            // $post_img_url = handleFileUpload(
            //     $_FILES['featured-image'] ?? null,
            //     '/Projet Fil Rouge/php/uploads/',
            //     $post_data['post_img_url'] ?? null
            // );

            if ($post_data['post_id']) {
                updatePost($pdo, $post_title, $post_content, $post_excerpt, $post_img_url, $post_status, $post_data['post_id'], $user_id);
    
                // Delete existing categories and tags
                $pdo->prepare("DELETE FROM categories_posts WHERE post_id = ?")->execute([$post_data['post_id']]);
                $pdo->prepare("DELETE FROM tags_posts WHERE post_id = ?")->execute([$post_data['post_id']]);
            } else {
                $post_published_current_date = date('Y-m-d H:i:s');
                insertPost($pdo, $post_title, $post_content, $post_excerpt, $post_img_url, $post_status, $post_published_current_date, $user_id);
                $post_data['post_id'] = $pdo->lastInsertId();
            }

            if (!empty($category_names)) {
                foreach ($category_names as $category_name) {
                    $category_id = queryCategory($pdo, $category_name);

                    if ($category_id) {
                        insertCategoryPost($pdo, $post_data['post_id'], $category_id);
                    }
                }
            }

            // createSlug($string);
            function createSlug($string) {
                $slug = strtolower(trim($string));
                $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
                $slug = preg_replace('/-+/', '-', $slug);
                return $slug;
            }

            if (!empty($tags_name)) {
                error_log("Processing tags: " . print_r($tags_name, true));

                foreach ($tags_name as $tag_name) {
                    $queryTag = "SELECT tag_id FROM tags WHERE tag_name = :tag_name";
                    $stmt = $pdo->prepare($queryTag);
                    $stmt->execute([':tag_name' => $tag_name]);
                    $tag_id = $stmt->fetchColumn();

                    if (!$tag_id) {
                        insertTag($pdo, $tag_name);
                        $tag_id = $pdo->lastInsertId();
                        error_log("New tag created with ID: $tag_id");
                    }

                    if ($tag_id) {
                        error_log("Linking tag $tag_id to post {$post_data['post_id']}");
                        $queryInsertTagPost = "INSERT INTO tags_posts (post_id, tag_id) VALUES (:post_id, :tag_id)";
                        $stmt = $pdo->prepare($queryInsertTagPost);
                        $stmt->bindParam(':post_id', $post_data['post_id']);
                        $stmt->bindParam(':tag_id', $tag_id);
                        $stmt->execute();
                    }
                }
            }

            $is_edit = isset($_GET['post_id']) && is_numeric($_GET['post_id']);
            $success_message = $is_edit ? "Post updated successfully!" : "Post published successfully!";

            // Optionally, check the previous post_status for more context
            if ($post_data['post_id']) {
                $previous_status = queryStatusPost($pdo, $post_data);
                if ($previous_status === 'draft') {
                    $success_message = "Post published successfully!";
                }
            }

            // Update $post_data with submitted values
            $post_data['post_title'] = $post_title;
            $post_data['post_content'] = $post_content;
            $post_data['post_excerpt'] = $post_excerpt;
            $post_data['post_img_url'] = $post_img_url;
            $post_data['selected_categories'] = $category_names;
            $post_data['selected_tags'] = $tags_name;

            header("Location: blogsphere.php");

            $pdo->commit();
        } 
        catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Error: " . $e->getMessage();
            // Update $post_data to reflect submitted values
            $post_data['post_title'] = $post_title;
            $post_data['post_content'] = $post_content;
            $post_data['post_excerpt'] = $post_excerpt;
            $post_data['post_img_url'] = $post_img_url;
            $post_data['selected_categories'] = $category_names;
            $post_data['selected_tags'] = $tags_name;
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Blog Post</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/add_blog.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="../../assets/js/main.js" defer></script>
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
    <script src="../../assets/js/handle_hamburger.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php"; ?>

    <div class="container">
        <h1 class="page-title"><?= $post_data['post_id'] ? 'Edit Blog Post' : 'Create New Blog Post'; ?></h1>
        <div id="message" style="margin-bottom: 10px;">
            <?php if (isset($success_message)): ?>
                <span style="color: green;"><?= htmlspecialchars($success_message) ?></span>
            <?php elseif (isset($error_message)): ?>
                <span style="color: red;"><?= htmlspecialchars($error_message) ?></span>
            <?php endif; ?>
        </div>
        
        <form class="blog-form" id="blog-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="title">Blog Title <span class="required-field">*</span></label>
                <input type="text" id="title" name="post-title" placeholder="Enter an eye-catching title" value="<?= htmlspecialchars($post_data['post_title']); ?>">
            </div>
            
            <div class="form-group">
                <label for="category">Category <span class="required-field">*</span></label>
                <select id="category" name="category-name">
                    <option value="" selected disabled>Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="category-container" id="selected-categories"></div>
                <input type="hidden" name="selected-categories" id="selected-categories-input" value="<?= htmlspecialchars(json_encode($post_data['selected_categories'])); ?>">
            </div>
            
            <div class="form-group">
                <label for="featured-image">Featured Image <span class="required-field">*</span></label>
                <div class="image-preview" id="image-preview">
                <?php if ($post_data['post_img_url']) : ?>
                    <img src="<?= htmlspecialchars($post_data['post_img_url']); ?>" alt="Featured Image" style="max-width: 100%; max-height: 200px;">
                <?php else : ?>
                    <span>Preview will appear here</span>
                <?php endif; ?>
                </div>
                <div class="file-input-container">
                    <label for="image-upload" class="file-input-label">Choose Image</label>
                    <input type="file" id="image-upload" class="file-input" name="featured-image" accept="image/*">
                </div>
            </div>
            
            <div class="form-group">
                <label for="content">Content <span class="required-field">*</span></label>
                <div id="editor-container" style="height: 300px;"></div>
                <input type="hidden" name="content" id="content-input" value="<?= htmlspecialchars($post_data['post_content']); ?>">
            </div>
            
            <div class="form-group">
                <label for="tags">Tags (Press Enter to add)</label>
                <input type="text" id="tags" name="tags" placeholder="Add tags to help readers discover your blog">
                <div class="tag-container" id="selected-tags"></div>
                <input type="hidden" name="selected-tags" id="selected-tags-input" value="<?= htmlspecialchars(json_encode($post_data['selected_tags'])); ?>">
            </div>
            
            <div class="form-group">
                <label for="excerpt">Short Excerpt (appears in previews)</label>
                <textarea id="excerpt" name="excerpt" rows="3" placeholder="Write a brief summary of your blog post"><?= htmlspecialchars($post_data['post_excerpt']); ?></textarea>
                <span class="bio-counter">0/300</span>
                <small class="text-muted">Enter a short summary (max 300 characters)</small>
            </div>
            
            <div class="button-group">
                <button type="button" class="btn btn-secondary" id="save-draft">Save as Draft</button>
                <button type="submit" class="btn btn-primary" name="publish">
                    <?= ($post_data['post_id'] && $post_data['post_status'] === 'draft') ? 'Publish' : ($post_data['post_id'] ? 'Update Post' : 'Publish Now') ?>
                </button>
            </div>
        </form>
    </div>

    <?php include "../../views/partials/footer.html"; ?>

    <script src="../../assets/js/handle_excerpt.js"></script>
    <script src="../../assets/js/add_post.js"></script>
    <script>
        // Initialize Quill
        // var quill = new Quill('#editor-container', {
        //     modules: {
        //         toolbar: [
        //             ['bold', 'italic', 'underline'],
        //             ['link', 'blockquote', 'code-block', 'image'],
        //             [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        //             [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        //         ]
        //     },
        //     placeholder: 'Write your blog post here...',
        //     theme: 'snow'
        // });
        var quill = initQuillEditor('#editor-container');

        // Set Quill content for editing, this ensures that the editor displays the stored HTML content when editing a post.
        <?php if ($post_data['post_content']): ?>
            // Decode HTML entities and set content
            const decodedContent = <?php echo json_encode(html_entity_decode($post_data['post_content'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>;
            quill.root.innerHTML = decodedContent;
        <?php endif; ?>

        // Sync Quill content to hidden input before form submission
        const form = document.getElementById('blog-form');
        const contentInput = document.getElementById('content-input');
        
        form.addEventListener('submit', function(e) {
            const content = quill.root.innerHTML;
            console.log('Quill content on submit:', content); // Debug log
            contentInput.value = content;

            // Validate content
            const plainText = quill.getText().trim();
            if (!plainText || content === '<p><br></p>') {
                e.preventDefault();
                showError('editor-container', 'Post content cannot be empty');
            }
        });

        // Category handling
        const categorySelect = document.getElementById('category');
        const categoryContainer = document.getElementById('selected-categories');
        const categoryInput = document.getElementById('selected-categories-input');
        let selectedCategories = <?= json_encode($post_data['selected_categories']); ?>;

        // function updateCategoryDisplay() {
        //     categoryContainer.innerHTML = '';
        //     selectedCategories.forEach(category => {
        //         const tag = document.createElement('div');
        //         tag.className = 'category-tag';
        //         tag.innerHTML = `
        //             ${category}
        //             <span class="category-remove" data-category="${category}">✕</span>
        //         `;
        //         categoryContainer.appendChild(tag);
        //     });
        //     document.querySelectorAll('.category-remove').forEach(btn => {
        //         btn.addEventListener('click', function() {
        //             const categoryToRemove = this.getAttribute('data-category');
        //             selectedCategories = selectedCategories.filter(cat => cat !== categoryToRemove);
        //             updateCategoryDisplay();
        //             updateCategoryInput();
        //         });
        //     });
        // }
        updateCategoryDisplay();

        function updateCategoryInput() {
            categoryInput.value = JSON.stringify(selectedCategories);
        }

        categorySelect.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue && !selectedCategories.includes(selectedValue)) {
                selectedCategories.push(selectedValue);
                updateCategoryDisplay();
                updateCategoryInput();
            }
            this.value = '';
        });

        // Initialize category display
        updateCategoryDisplay();

        // Tag handling - initialization
        const tagInput = document.getElementById('tags');
        const tagContainer = document.getElementById('selected-tags');
        const tagHiddenInput = document.getElementById('selected-tags-input');
        let selectedTags = <?= json_encode($post_data['selected_tags'] ?? []); ?>;

        // function updateTagDisplay() {
        //     tagContainer.innerHTML = '';
        //     selectedTags.forEach(tag => {
        //         const tagElement = document.createElement('div');
        //         tagElement.className = 'tag';
        //         tagElement.innerHTML = `
        //             ${tag}
        //             <span class="tag-remove" data-tag="${tag}">✕</span>
        //         `;
        //         tagContainer.appendChild(tagElement);
        //     });
        //     document.querySelectorAll('.tag-remove').forEach(btn => {
        //         btn.addEventListener('click', function() {
        //             const tagToRemove = this.getAttribute('data-tag');
        //             selectedTags = selectedTags.filter(tag => tag !== tagToRemove);
        //             updateTagDisplay();
        //             updateTagInput();
        //         });
        //     });
        // }
        updateTagDisplay();

        function updateTagInput() {
            tagHiddenInput.value = JSON.stringify(selectedTags);
        }

        // Robust tag input handling
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tagValue = this.value.trim();
                if (tagValue && !selectedTags.includes(tagValue)) {
                    selectedTags.push(tagValue);
                    updateTagDisplay();
                    updateTagInput();
                }
                this.value = '';
            }
        });

        // Initialize tag display
        updateTagDisplay();

        // Form validation
        // form.addEventListener('submit', function(e) {
        //     const postTitle = document.getElementById('title').value.trim();
        //     const postContent = quill.getText().trim();
        //     const categories = JSON.parse(document.getElementById('selected-categories-input').value || '[]');
        //     const imageUpload = document.getElementById('image-upload');
        //     const hasExistingImage = <?php echo !empty($post_data['post_img_url']) ? 'true' : 'false'; ?>;
        //     const hasNewImage = imageUpload.files.length > 0;

        //     // Clear previous error highlights
        //     document.querySelectorAll('.error-highlight').forEach(el => el.classList.remove('error-highlight'));
        //     document.querySelectorAll('.error-message').forEach(el => el.remove());

        //     let isValid = true;

        //     if (!postTitle) {
        //         showError('title', 'Post title is required');
        //         isValid = false;
        //     }

        //     if (!postContent || quill.root.innerHTML === '<p><br></p>') {
        //         showError('editor-container', 'Post content is required');
        //         isValid = false;
        //     }

        //     if (categories.length === 0) {
        //         showError('category', 'At least one category is required');
        //         isValid = false;
        //     }

        //     if (!hasExistingImage && !hasNewImage) {
        //         showError('image-upload', 'Featured image is required');
        //         isValid = false;
        //     }

        //     const excerpt = document.getElementById('excerpt').value.trim();
        //     if (excerpt.length > 300) {
        //         showError('excerpt', 'Excerpt must be 300 characters or less');
        //         isValid = false;
        //     }

        //     if (!isValid) {
        //         e.preventDefault();
        //         const messageDiv = document.getElementById('message');
        //         messageDiv.innerHTML = '<span style="color: red;">Please fix the errors below before publishing</span>';
        //         messageDiv.scrollIntoView({ behavior: 'smooth' });
        //     }
        // });
        validationForm(<?php echo json_encode($post_data['post_img_url'] ?? ''); ?>);

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('error-highlight');
                
                const errorElement = document.createElement('div');
                errorElement.className = 'error-message';
                errorElement.style.color = 'red';
                errorElement.style.marginTop = '5px';
                errorElement.style.fontSize = '0.8em';
                errorElement.textContent = message;
                
                // Insert after the field or its container
                field.parentNode.insertBefore(errorElement, field.nextSibling);
            }
        }
        // showError('title', 'Post title is required');

        // Prevent form submission on Enter in any input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.id !== 'tags') {
                    e.preventDefault();
                }
            });
        });

        // Add event listener to clear image error when a file is selected
        document.getElementById('image-upload').addEventListener('change', function() {
            const errorMessage = this.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
            this.classList.remove('error-highlight');
        });

        // AJAX for Save as Draft
        document.getElementById('save-draft').addEventListener('click', function(e) {
            e.preventDefault();
            // const form = document.getElementById('blog-form');
            // const formData = new FormData(form);

            // // Convert JSON strings to proper format
            // const tags = JSON.parse(document.getElementById('selected-tags-input').value || '[]');
            // const categories = JSON.parse(document.getElementById('selected-categories-input').value || '[]');

            // formData.set('content', quill.root.innerHTML); // Ensure content is included
            // formData.set('selected-tags', JSON.stringify(tags));
            // formData.set('selected-categories', JSON.stringify(categories));

            // <?php if ($post_data['post_id']): ?>
            //     formData.append('post_id', <?php echo $post_data['post_id']; ?>);
            // <?php endif; ?>

            // for (let [key, value] of formData.entries()) {
            //     console.log(key, value);
            // }

            // fetch('../functions/actions/save_draft.php', {
            //     method: 'POST',
            //     headers: {
            //         'X-CSRF-Token': document.querySelector('input[name="csrf_token"]').value
            //     },
            //     body: formData
            // })
            // .then(response => {
            //     return response.json();
            // })
            // .then(data => {
            //     console.log("data: ", data);
                
            // })
            // .catch(error => {
            //     console.error('Error:', error);
            //     const messageDiv = document.getElementById('message');
            //     messageDiv.innerHTML = '<span style="color: red;">An unexpected error occurred while saving</span>';
            //     setTimeout(() => messageDiv.innerHTML = '', 5000);
            // });
            saveBlogDraft(
                'blog-form', 
                quill, 
                'selected-tags-input', 
                'selected-categories-input', 
                '../functions/actions/save_draft.php',
                <?php echo $post_data['post_id'] ?? 'null'; ?>,
                'input[name="csrf_token"]',
                'message'
            );
        });
    </script>
</body>
</html>