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