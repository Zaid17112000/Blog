<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Post Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .post-title { color: #333; font-size: 24px; }
        .excerpt { margin: 20px 0; padding: 15px; background: #f5f5f5; }
        .button { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #0066cc; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="post-title"><?= htmlspecialchars($post['post_title']) ?></h1>
        <p>Hello, a new post has been published by <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>!</p>
        
        <?php if ($post['post_excerpt']): ?>
        <div class="excerpt">
            <?= nl2br(htmlspecialchars($post['post_excerpt'])) ?>
        </div>
        <?php endif; ?>
        
        <a href="localhost/Projet%20Fil%20Rouge%20v2%20-%20Copie/php/pages/post.php?post_id=<?= $post['post_id']; ?>" class="button">
            Read the full post
        </a>
    </div>
</body>
</html>