<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/validation/check_user_like_post.php";
    require_once "../functions/queries/get_likes_count.php";
    require_once "../functions/validation/check_user_save_post.php";

    $user_id = $userData->user_id;

    // Get post_id from URL
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

    $queryPost = "SELECT * FROM posts WHERE post_id = :post_id";
    $stmt = $pdo->prepare($queryPost);
    $stmt->execute(['post_id' => $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        // Handle case where post is not found
        header("Location: blogsphere.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $queryGetComments = "SELECT 
        c.comment_id, 
        c.user_id, 
        c.comment_content, 
        c.comment_created, 
        CONCAT(u.first_name, ' ', u.last_name) AS user_name, 
        c.parent_comment_id, 
        (SELECT COUNT(*) FROM comments r WHERE r.parent_comment_id = c.comment_id) AS reply_count,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id) AS like_count,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id AND cl.user_id = :user_id) AS user_liked
        FROM comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.post_id = :post_id 
        ORDER BY c.comment_created DESC";
    $stmt = $pdo->prepare($queryGetComments);
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize comments into a nested tree structure
    $commentTree = [];
    $topLevelCount = 0;
    $pendingReplies = []; // Store replies whose parents aren't processed yet

    foreach ($comments as $comment) {
        $comment['replies'] = []; // Initialize replies array for all comments
        if ($comment['parent_comment_id'] === null) {
            $topLevelCount++;
            $commentTree[$comment['comment_id']] = $comment;
        } else {
            if (isset($commentTree[$comment['parent_comment_id']])) {
                $commentTree[$comment['parent_comment_id']]['replies'][] = $comment;
            } else {
                // Store reply for later processing
                $pendingReplies[$comment['parent_comment_id']][] = $comment;
            }
        }
    }

    // Process pending replies
    while (!empty($pendingReplies)) {
        $processed = false;
        foreach ($pendingReplies as $parentId => $replies) {
            if (isset($commentTree[$parentId])) {
                foreach ($replies as $reply) {
                    $commentTree[$parentId]['replies'][] = $reply;
                }
                unset($pendingReplies[$parentId]);
                $processed = true;
            }
        }
        // Prevent infinite loop if there are orphaned replies
        if (!$processed) {
            break;
        }
    }

    // Check if the current user liked this post
    $is_liked = checkUserLikePost($pdo, $user_id, $post_id);
    $like_class = $is_liked ? 'liked' : '';

    // Get like count for this post
    $like_count = likesCount($pdo, $post_id);

    // Check if the current user saved this post
    $is_saved = checkUserSavePost($pdo, $user_id, $post['post_id']);
    $save_class = $is_saved ? 'saved' : '';

    // Handle follow/unfollow actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'follow') {
            require_once "../controllers/follow_user.php";
            if (followToUser($pdo, $user_id, $_POST['profile_user_id'])) {
                $_SESSION['message'] = "You've successfully followed!";
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'unfollow') {
            require_once "../controllers/unfollow_user.php";
            if (unfollowFromUser($pdo, $user_id, $_POST['profile_user_id'])) {
                $_SESSION['message'] = "You've unfollowed.";
            }
        }
        // header("Location: ".$_SERVER['PHP_SELF']);
        // exit();
    }

    // Check if current user is followed to this profile
    $isFollowed = false;
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT 1 
            FROM Follow_Users su 
            JOIN Followers s ON su.follower_id = s.follower_id
            WHERE su.user_id = :profile_user_id AND s.user_id = :current_user_id");
        $stmt->execute([
            'profile_user_id' => $user_id,
            'current_user_id' => $userData->user_id
        ]);
        $isFollowed = $stmt->fetchColumn();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post["post_title"]); ?> - BlogSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php" ?>

    <?php print_r($commentTree, true); ?>

    <!-- Blog Posts Section -->
    <main class="container" style="padding-top: 40px;">
        <!-- Post List -->
        <div class="articles-grid" style="margin-top: 40px;">
            <article class="article-card">
                <div class="article-content">
                    <h1 class="title"><?= htmlspecialchars($post["post_title"]); ?></h1>
                    <p class="excerpt"><?= htmlspecialchars($post["post_excerpt"]); ?></p>
                    <div class="author">
                        <div class="author-image">
                            <img src="<?= empty($user['img_profile']) ? 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png' : $user['img_profile']; ?>" alt="Author profile">
                        </div>
                        <div class="author-info">
                            <div class="author-name" style="display: flex; justify-content: center; align-items: center; column-gap: 12px;">
                                <span><?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name']); ?></span> 
                                <form method="post" class="follow-form">
                                    <input type="hidden" name="profile_user_id" value="<?= $user_id ?>">
                                    <?php if ($isFollowed): ?>
                                        <button type="submit" name="action" value="unfollow" class="follow followed">
                                            Following
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="follow" class="follow">
                                            Follow
                                        </button>
                                    <?php endif; ?>
                                </form>
                                <div class="post-date"><?= date('M d, Y', strtotime($post["post_published"])); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="article-card__footer">
                        <!-- Post Likes -->
                        <div class="likes-container <?= $is_liked ? 'liked' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
                            <button class="like-button">
                                <i class="like-svg <?= $is_liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart' ?>"></i>
                            </button>
                            <span class="like-count"><?= $like_count ?></span>
                        </div>
                        
                        <!-- Save Button -->
                        <button class="save <?= $save_class ?>" data-post-id="<?= $post['post_id'] ?>">
                            <?php if ($is_saved) : ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg">
                                    <path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path>
                                </svg>
                            <?php else : ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg">
                                    <path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path>
                                </svg>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="article-image">
                        <img src="<?= htmlspecialchars($post["post_img_url"]); ?>" alt="Article image">
                    </div>
                    <div class="ql-container ql-snow" style="border: none;">
                        <div class="ql-editor" style="white-space: normal;">
                            <!-- Your saved Quill content will be rendered here -->
                            <?= $post["post_content"]; ?>
                        </div>
                    </div>

                </div>
                
                <div class="comments">
                    <form action="../controllers/submit_comment.php" method="POST" class="comments-form" id="comment-form">
                        <input type="hidden" name="post_id" value="<?= $post_id ?>">
                        <div style="margin-bottom: 12px; display: flex; align-items: center;">
                            <div style="display: block; position: relative;">
                                <div style="display: block; position: relative;">
                                    <img alt="Zayd el Khobzi" style="display: block; box-sizing: border-box; border-radius: 50%; background-color: #F2F2F2; vertical-align: middle;" src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                                </div>
                            </div>
                            <div style="margin-left: 12px; display: flex; align-items: flex-start; flex-direction: column; justify-content: center;">
                                <div style="display: flex; align-items: baseline; flex-wrap: wrap;">
                                    <span style="font-weight: 400; font-size: 14px; line-height: 20px; word-break: break-word; padding-right: 4px;">Zayd el Khobzi</span>
                                </div>
                            </div>
                        </div>
                        <div class="write-comment">
                            <div class="write-comment_textarea">
                                <textarea name="comment" placeholder="What are your thoughts ?" cols="4" required></textarea>
                            </div>
                            <div class="control-comments">
                                <div class="btns">
                                    <button class="cancel btn">Cancel</button>
                                    <button type="submit" class="respond btn" style="cursor: not-allowed;">Respond</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="responses">
                    <h2>Responses (<span id="response-count"><?= count($commentTree) ?></span>)</h2>
                    <div id="comments-container">
                        <?php foreach ($commentTree as $comment): ?>
                            <div class="comment" id="comment-<?= $comment['comment_id'] ?>">
                                <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                                    <img alt="<?= htmlspecialchars($comment['user_name']) ?>" style="border-radius: 50%; background-color: #F2F2F2;" 
                                        src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                                    <div style="margin-left: 12px;">
                                        <span style="font-weight: 400; font-size: 14px;"><?= htmlspecialchars($comment['user_name']) ?></span>
                                        <div style="font-size: 12px; color: #6B6B6B;"><?= date("M d, Y", strtotime($comment['comment_created'])) ?></div>
                                    </div>
                                    <!-- Settings menu (only show for comment owner) -->
                                    <?php if ($comment['user_id'] == $user_id): ?>
                                        <div class="settings-comment">
                                            <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                                <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                            <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                                <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                                    <li style="font-size: 14px; padding: 8px 20px;">
                                                        <button class="edit-comment" data-comment-id="<?= $comment['comment_id'] ?>">Edit response</button>
                                                    </li>
                                                    <li style="font-size: 14px; padding: 8px 20px;">
                                                        <button class="delete-comment" data-comment-id="<?= $comment['comment_id'] ?>">Delete response</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Comment content (default view) -->
                                <p class="comment-content" style="font-size: 14px; line-height: 20px;"><?= htmlspecialchars($comment['comment_content']) ?></p>
                                <!-- Edit textarea (hidden by default) -->
                                <div class="edit-area" style="display: none; margin-top: 10px;">
                                    <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;"><?= htmlspecialchars($comment['comment_content']) ?></textarea>
                                    <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                        <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                        <button class="save-edit btn" data-comment-id="<?= $comment['comment_id'] ?>" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                                    </div>
                                </div>
                                <!-- Social interaction bar -->
                                <div style="margin-top: 14px; display: flex; align-items: center; flex-direction: row; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 24px;">
                                        <div class="likes-container">
                                            <button class="like-button" data-comment-id="<?= $comment['comment_id'] ?>">
                                                <i class="like-svg <?= $comment['user_liked'] ? 'fa-solid fa-heart' : 'fa-regular fa-heart' ?>"></i>
                                            </button>
                                            <span class="like-count"><?= $comment['like_count'] ?></span>
                                        </div>
                                        <button class="comments-card">
                                            <svg style="position: relative; top: -1.2px" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-label="responses">
                                                <path d="M18.006 16.803c1.533-1.456 2.234-3.325 2.234-5.321C20.24 7.357 16.709 4 12.191 4S4 7.357 4 11.482c0 4.126 3.674 7.482 8.191 7.482.817 0 1.622-.111 2.393-.327.231.2.48.391.744.559 1.06.693 2.203 1.044 3.399 1.044.224-.008.4-.112.486-.287a.49.49 0 0 0-.042-.518c-.495-.67-.845-1.364-1.04-2.057a4 4 0 0 1-.125-.598zm-3.122 1.055-.067-.223-.315.096a8 8 0 0 1-2.311.338c-4.023 0-7.292-2.955-7.292-6.587 0-3.633 3.269-6.588 7.292-6.588 4.014 0 7.112 2.958 7.112 6.593 0 1.794-.608 3.469-2.027 4.72l-.195.168v.255c0 .056 0 .151.016.295.025.231.081.478.154.733.154.558.398 1.117.722 1.659a5.3 5.3 0 0 1-2.165-.845c-.276-.176-.714-.383-.941-.59z"></path>
                                            </svg>
                                            <p class="reply-count-toggle"><?= count($comment['replies']) ?> replies</p>
                                        </button>
                                        <p class="reply">
                                            <button class="reply-toggle" style="position: relative; top: -1.3px; cursor: pointer; background-color: transparent; text-decoration: underline;">Reply</button>
                                        </p>
                                    </div>
                                </div>
                                <!-- Reply textarea -->
                                <div class="textarea-container reply-area" style="display: none;">
                                    <textarea class="reply-textarea" placeholder="Write your reply..."></textarea>
                                    <div class="textarea-footer">
                                        <button class="cancel btn" style="cursor: pointer; background-color: transparent;">Cancel</button>
                                        <button class="submit-button btn" data-comment-id="<?= $comment['comment_id'] ?>" disabled>Respond</button>
                                    </div>
                                </div>
                                <!-- Replies -->
                                <div class="replies-container" style="display: none; margin-left: 40px; margin-top: 20px;">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="comment" id="comment-<?= $reply['comment_id'] ?>">
                                            <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                                                <img alt="<?= htmlspecialchars($reply['user_name']) ?>" style="border-radius: 50%; background-color: #F2F2F2;" 
                                                    src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                                                <div style="margin-left: 12px;">
                                                    <span style="font-weight: 400; font-size: 14px;"><?= htmlspecialchars($reply['user_name']) ?></span>
                                                    <div style="font-size: 12px; color: #6B6B6B;"><?= date("M d, Y", strtotime($reply['comment_created'])) ?></div>
                                                </div>
                                                <!-- Settings menu for reply (only for owner) -->
                                                <?php if ($reply['user_id'] == $user_id): ?>
                                                    <div class="settings-comment">
                                                        <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                                            <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                                <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                                        <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                                            <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                                                <li style="font-size: 14px; padding: 8px 20px;">
                                                                    <button class="edit-comment" data-comment-id="<?= $reply['comment_id'] ?>">Edit response</button>
                                                                </li>
                                                                <li style="font-size: 14px; padding: 8px 20px;">
                                                                    <button class="delete-comment" data-comment-id="<?= $reply['comment_id'] ?>">Delete response</button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="comment-content" style="font-size: 14px; line-height: 20px;"><?= htmlspecialchars($reply['comment_content']) ?></p>
                                            <div class="edit-area" style="display: none; margin-top: 10px;">
                                                <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;"><?= htmlspecialchars($reply['comment_content']) ?></textarea>
                                                <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                                    <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                                    <button class="save-edit btn" data-comment-id="<?= $reply['comment_id'] ?>" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                                                </div>
                                            </div>
                                            <div class="social-interaction-bar">
                                                <div class="interaction-buttons">
                                                    <div class="likes-container">
                                                        <button class="like-button">
                                                            <i class="like-svg fa-regular fa-heart"></i>
                                                        </button>
                                                        <span class="like-count">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </article>
        </div>
    </main>

    <!-- Footer -->
    <?php include "../../views/partials/footer.html" ?>

    <script>
        const escapeHTML = (str) => {
            if (typeof str !== 'string') return str;
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        };

        // Main comment form handling
        const textareaComment = document.querySelector(".comments-form .write-comment_textarea textarea");
        const controlComments = document.querySelector(".comments-form .write-comment .control-comments");
        const respondComment = document.querySelector(".comments-form .control-comments .respond");
        const cancelComment = document.querySelector(".comments-form .control-comments .cancel");
        const commentForm = document.querySelector("#comment-form");
        const responsesSection = document.querySelector("#comments-container");
        const responseCount = document.querySelector("#response-count");

        // Initialize comment form
        if (textareaComment && respondComment) {
            respondComment.style.opacity = textareaComment.value.trim() === "" ? "0.5" : "1";
            respondComment.disabled = textareaComment.value.trim() === "";

            textareaComment.addEventListener("input", () => {
                respondComment.style.opacity = textareaComment.value.trim() === "" ? "0.5" : "1";
                respondComment.disabled = textareaComment.value.trim() === "";
            });

            textareaComment.addEventListener("click", () => {
                controlComments.style.display = "block";
            });
        }

        if (cancelComment) {
            cancelComment.addEventListener("click", (e) => {
                e.preventDefault();
                controlComments.style.display = "none";
                textareaComment.value = "";
                respondComment.style.opacity = "0.5";
                respondComment.disabled = true;
            });
        }

        if (respondComment) {
            respondComment.style.opacity = textareaComment.value.trim() === "" ? "0.1" : "1";
            respondComment.style.cursor = textareaComment.value.trim() === "" ? "pointer" : "not-allowed";
        }

        if (commentForm) {
            commentForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const formData = new FormData(commentForm);
                try {
                    const response = await fetch("../controllers/submit_comment.php", {
                        method: "POST",
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        const commentDiv = document.createElement("div");
                        commentDiv.classList.add("comment");
                        commentDiv.id = `comment-${result.comment_id}`;
                        commentDiv.innerHTML = `
                            <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                                <img alt="${escapeHTML(result.user_name)}" style="border-radius: 50%; background-color: #F2F2F2;" 
                                    src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                                <div style="margin-left: 12px;">
                                    <span style="font-weight: 400; font-size: 14px;">${escapeHTML(result.user_name)}</span>
                                    <div style="font-size: 12px; color: #6B6B6B;">${escapeHTML(result.created_at)}</div>
                                </div>
                                <div class="settings-comment">
                                    <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                        <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                    <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                        <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                            <li style="font-size: 14px; padding: 8px 20px;">
                                                <button class="edit-comment" data-comment-id="${result.comment_id}">Edit response</button>
                                            </li>
                                            <li style="font-size: 14px; padding: 8px 20px;">
                                                <button class="delete-comment" data-comment-id="${result.comment_id}">Delete response</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <p class="comment-content" style="font-size: 14px; line-height: 20px;">${escapeHTML(result.comment)}</p>
                            <div class="edit-area" style="display: none; margin-top: 10px;">
                                <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;">${escapeHTML(result.comment)}</textarea>
                                <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                    <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                    <button class="save-edit btn" data-comment-id="${result.comment_id}" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                                </div>
                            </div>
                            <div style="margin-top: 14px; display: flex; align-items: center; flex-direction: row; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 24px;">
                                    <div class="likes-container">
                                        <button class="like-button" data-comment-id="${result.comment_id}">
                                            <i class="like-svg fa-regular fa-heart"></i>
                                        </button>
                                        <span class="like-count">0</span>
                                    </div>
                                    <button class="comments-card">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-label="responses">
                                            <path d="M18.006 16.803c1.533-1.456 2.234-3.325 2.234-5.321C20.24 7.357 16.709 4 12.191 4S4 7.357 4 11.482c0 4.126 3.674 7.482 8.191 7.482.817 0 1.622-.111 2.393-.327.231.2.48.391.744.559 1.06.693 2.203 1.044 3.399 1.044.224-.008.4-.112.486-.287a.49.49 0 0 0-.042-.518c-.495-.67-.845-1.364-1.04-2.057a4 4 0 0 1-.125-.598zm-3.122 1.055-.067-.223-.315.096a8 8 0 0 1-2.311.338c-4.023 0-7.292-2.955-7.292-6.587 0-3.633 3.269-6.588 7.292-6.588 4.014 0 7.112 2.958 7.112 6.593 0 1.794-.608 3.469-2.027 4.72l-.195.168v.255c0 .056 0 .151.016.295.025.231.081.478.154.733.154.558.398 1.117.722 1.659a5.3 5.3 0 0 1-2.165-.845c-.276-.176-.714-.383-.941-.59z"></path>
                                        </svg>
                                        <p class="reply-count-toggle">0 replies</p>
                                    </button>
                                    <p class="reply">
                                        <button class="reply-toggle">Reply</button>
                                    </p>
                                </div>
                            </div>
                            <div class="textarea-container reply-area" style="display: none;">
                                <textarea class="reply-textarea" placeholder="Write your reply..."></textarea>
                                <div class="textarea-footer">
                                    <button class="cancel btn">Cancel</button>
                                    <button class="submit-button btn" data-comment-id="${result.comment_id}" disabled>Respond</button>
                                </div>
                            </div>
                        `;
                        responsesSection.prepend(commentDiv);
                        responseCount.textContent = parseInt(responseCount.textContent) + 1;
                        textareaComment.value = "";
                        controlComments.style.display = "none";
                        respondComment.style.opacity = "0.5";
                        respondComment.disabled = true;
                        addCommentEventListeners(commentDiv);
                        attachReplyToggleListeners();
                    } else {
                        alert("Failed to submit comment: " + result.error);
                    }
                } catch (error) {
                    alert("An error occurred: " + error.message);
                }
            });
        }

        // Settings menu toggle
        document.querySelectorAll('.settings-comment button').forEach(button => {
            button.addEventListener('click', () => {
                const settingsMenu = button.closest('.settings-comment').nextElementSibling;
                settingsMenu.style.display = settingsMenu.style.display === 'none' ? 'block' : 'none';
            });
        });

        // Close settings menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.settings-comment') && !e.target.closest('.settings')) {
                document.querySelectorAll('.settings').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });

        // Edit comment handling
        document.querySelectorAll('.edit-comment').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const commentElement = document.getElementById(`comment-${commentId}`);
                const commentContent = commentElement.querySelector('.comment-content');
                const editArea = commentElement.querySelector('.edit-area');
                const settingsMenu = this.closest('.settings');

                commentContent.style.display = 'none';
                editArea.style.display = 'block';
                settingsMenu.style.display = 'none';
            });
        });

        // Save edited comment
        document.querySelectorAll('.save-edit').forEach(button => {
            button.addEventListener('click', async function() {
                const commentId = this.dataset.commentId;
                const commentElement = document.getElementById(`comment-${commentId}`);
                const editTextarea = commentElement.querySelector('.edit-textarea');
                const commentContent = commentElement.querySelector('.comment-content');
                const editArea = commentElement.querySelector('.edit-area');
                const newContent = editTextarea.value.trim();

                if (!newContent) {
                    alert('Comment cannot be empty');
                    return;
                }

                try {
                    const response = await fetch('../controllers/edit_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${encodeURIComponent(commentId)}&comment_content=${encodeURIComponent(newContent)}`
                    });
                    const result = await response.json();

                    if (result.success) {
                        commentContent.textContent = escapeHTML(newContent);
                        commentContent.style.display = 'block';
                        editArea.style.display = 'none';
                    } else {
                        alert('Failed to update comment: ' + result.error);
                    }
                } catch (error) {
                    alert('An error occurred while updating the comment: ' + error.message);
                }
            });
        });

        // Cancel edit
        document.querySelectorAll('.cancel-edit').forEach(button => {
            button.addEventListener('click', function() {
                const commentElement = this.closest('.comment');
                const commentContent = commentElement.querySelector('.comment-content');
                const editArea = commentElement.querySelector('.edit-area');
                const editTextarea = editArea.querySelector('.edit-textarea');

                editTextarea.value = commentContent.textContent;
                commentContent.style.display = 'block';
                editArea.style.display = 'none';
            });
        });

        // Delete comment handling
        document.querySelectorAll('.delete-comment').forEach(button => {
            button.addEventListener('click', async function() {
                const commentId = this.dataset.commentId;
                const commentElement = document.getElementById(`comment-${commentId}`);
                const settingsMenu = this.closest('.settings');

                if (confirm('Are you sure you want to delete this comment ?')) {
                    try {
                        const response = await fetch('../controllers/delete_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `comment_id=${encodeURIComponent(commentId)}`
                        });
                        const result = await response.json();

                        if (result.success) {
                            commentElement.remove();
                            if (!commentElement.closest('.replies-container')) {
                                responseCount.textContent = Math.max(0, parseInt(responseCount.textContent) - 1);
                            } else {
                                const parentComment = commentElement.closest('.comment');
                                const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                                if (replyCountElement) {
                                    const currentCount = parseInt(replyCountElement.textContent) || 0;
                                    replyCountElement.textContent = `${Math.max(0, currentCount - 1)} replies`;
                                }
                            }
                            settingsMenu.style.display = 'none';
                        } else {
                            alert('Failed to delete comment: ' + result.error);
                        }
                    } catch (error) {
                        alert('An error occurred while deleting the comment: ' + error.message);
                    }
                }
            });
        });

        // Reply textarea input
        document.querySelectorAll('.reply-textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const submitButton = this.closest('.reply-area').querySelector('.submit-button');
                submitButton.disabled = this.value.trim() === '';
            });
        });

        // Cancel reply
        document.querySelectorAll('.reply-area .cancel').forEach(button => {
            button.addEventListener('click', function() {
                const replyArea = this.closest('.reply-area');
                replyArea.style.display = 'none';
                replyArea.querySelector('.reply-textarea').value = '';
                replyArea.querySelector('.submit-button').disabled = true;
            });
        });

        // Submit reply
        document.querySelectorAll('.submit-button').forEach(button => {
            button.addEventListener('click', async function() {
                const replyArea = this.closest('.reply-area');
                const textarea = replyArea.querySelector('.reply-textarea');
                const commentId = this.dataset.commentId;
                const commentContent = textarea.value.trim();

                if (!commentContent) {
                    alert('Reply cannot be empty');
                    return;
                }

                const formData = new FormData();
                formData.append('post_id', <?= $post_id ?>);
                formData.append('comment', commentContent);
                formData.append('parent_comment_id', commentId);

                try {
                    const response = await fetch('../controllers/submit_comment.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    // In the submit reply handler
                    if (result.success) {
                        const replyDiv = document.createElement('div');
                        replyDiv.classList.add('comment');
                        replyDiv.id = `comment-${result.comment_id}`;
                        replyDiv.innerHTML = `
                            <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                                <img alt="${escapeHTML(result.user_name)}" style="border-radius: 50%; background-color: #F2F2F2;" 
                                    src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                                <div style="margin-left: 12px;">
                                    <span style="font-weight: 400; font-size: 14px;">${escapeHTML(result.user_name)}</span>
                                    <div style="font-size: 12px; color: #6B6B6B;">${escapeHTML(result.created_at)}</div>
                                </div>
                                <div class="settings-comment">
                                    <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                        <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                    <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                        <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                            <li style="font-size: 14px; padding: 8px 20px;">
                                                <button class="edit-comment" data-comment-id="${result.comment_id}">Edit response</button>
                                            </li>
                                            <li style="font-size: 14px; padding: 8px 20px;">
                                                <button class="delete-comment" data-comment-id="${result.comment_id}">Delete response</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <p class="comment-content" style="font-size: 14px; line-height: 20px;">${escapeHTML(result.comment)}</p>
                            <div class="edit-area" style="display: none; margin-top: 10px;">
                                <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;">${escapeHTML(result.comment)}</textarea>
                                <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                    <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                    <button class="save-edit btn" data-comment-id="${result.comment_id}" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                                </div>
                            </div>
                            <div class="social-interaction-bar">
                                <div class="interaction-buttons">
                                    <div class="likes-container">
                                        <button class="like-button" data-comment-id="${result.comment_id}">
                                            <i class="like-svg fa-regular fa-heart"></i>
                                        </button>
                                        <span class="like-count">0</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        const parentComment = document.getElementById(`comment-${commentId}`);
                        let repliesContainer = parentComment.querySelector('.replies-container');
                        if (!repliesContainer) {
                            repliesContainer = document.createElement('div');
                            repliesContainer.classList.add('replies-container');
                            repliesContainer.style.marginLeft = '40px';
                            repliesContainer.style.marginTop = '20px';
                            parentComment.appendChild(repliesContainer);
                        }
                        repliesContainer.prepend(replyDiv);
                        repliesContainer.style.display = 'block';
                        const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                        if (replyCountElement) {
                            const currentCount = parseInt(replyCountElement.textContent) || 0;
                            replyCountElement.textContent = `${currentCount + 1} replies`;
                        }
                        textarea.value = '';
                        replyArea.style.display = 'none';
                        this.disabled = true;
                        addCommentEventListeners(replyDiv);
                        attachReplyToggleListeners();
                    } else {
                        alert("Failed to submit reply: " + result.error);
                    }
                } catch (error) {
                    alert("An error occurred: " + error.message);
                }
            });
        });

        // Reply count toggle
        function attachReplyToggleListeners() {
            document.querySelectorAll('.reply-count-toggle').forEach(element => {
                if (!element.hasAttribute('data-listener-attached')) {
                    element.setAttribute('data-listener-attached', 'true');
                    element.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const commentContainer = this.closest('.comment');
                        const repliesContainer = commentContainer.querySelector('.replies-container');
                        const replyCount = parseInt(this.textContent) || 0;

                        if (replyCount === 0) {
                            alert('No replies yet.');
                        } else {
                            repliesContainer.style.display = repliesContainer.style.display === 'none' ? 'block' : 'none';
                        }
                    });
                }
            });
        }
        attachReplyToggleListeners();

        // Comment event listeners
        function addCommentEventListeners(commentElement) {
            const deleteButton = commentElement.querySelector('.delete-comment');
            if (deleteButton) {
                deleteButton.addEventListener('click', async function() {
                    const commentId = this.dataset.commentId;
                    const commentElement = document.getElementById(`comment-${commentId}`);
                    if (confirm('Are you sure you want to delete this comment and all its replies?')) {
                        try {
                            const response = await fetch('../controllers/delete_comment.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `comment_id=${encodeURIComponent(commentId)}`
                            });
                            const result = await response.json();
                            if (result.success) {
                                commentElement.remove();
                                if (!commentElement.closest('.replies-container')) {
                                    responseCount.textContent = Math.max(0, parseInt(responseCount.textContent) - 1);
                                } else {
                                    const parentComment = commentElement.closest('.comment');
                                    const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                                    if (replyCountElement) {
                                        const currentCount = parseInt(replyCountElement.textContent) || 0;
                                        replyCountElement.textContent = `${Math.max(0, currentCount - 1)} replies`;
                                    }
                                }
                            } else {
                                alert('Failed to delete comment: ' + result.error);
                            }
                        } catch (error) {
                            alert('An error occurred: ' + error.message);
                        }
                    }
                });
            }

            const editButton = commentElement.querySelector('.edit-comment');
            if (editButton) {
                editButton.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const commentElement = document.getElementById(`comment-${commentId}`);
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    commentContent.style.display = 'none';
                    editArea.style.display = 'block';
                });
            }

            const saveEditButton = commentElement.querySelector('.save-edit');
            if (saveEditButton) {
                saveEditButton.addEventListener('click', async function() {
                    const commentId = this.dataset.commentId;
                    const commentElement = document.getElementById(`comment-${commentId}`);
                    const editTextarea = commentElement.querySelector('.edit-textarea');
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    const newContent = editTextarea.value.trim();

                    if (!newContent) {
                        alert('Comment cannot be empty');
                        return;
                    }

                    try {
                        const response = await fetch('../controllers/edit_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `comment_id=${encodeURIComponent(commentId)}&comment_content=${encodeURIComponent(newContent)}`
                        });
                        const result = await response.json();
                        if (result.success) {
                            commentContent.textContent = escapeHTML(newContent);
                            commentContent.style.display = 'block';
                            editArea.style.display = 'none';
                        } else {
                            alert('Failed to update comment: ' + result.error);
                        }
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                });
            }

            const cancelEditButton = commentElement.querySelector('.cancel-edit');
            if (cancelEditButton) {
                cancelEditButton.addEventListener('click', function() {
                    const commentElement = this.closest('.comment');
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    const editTextarea = editArea.querySelector('.edit-textarea');
                    editTextarea.value = commentContent.textContent;
                    commentContent.style.display = 'block';
                    editArea.style.display = 'none';
                });
            }

            const likeButton = commentElement.querySelector('.like-button');
            if (likeButton) {
                likeButton.addEventListener('click', function() {
                    const likesContainer = this.closest('.likes-container');
                    const likeCountElement = likesContainer.querySelector('.like-count');
                    const currentCount = parseInt(likeCountElement.textContent) || 0;
                    if (likesContainer.classList.contains('liked')) {
                        likeCountElement.textContent = currentCount - 1;
                        likesContainer.classList.remove('liked');
                        this.querySelector('i').classList.remove('fa-solid');
                        this.querySelector('i').classList.add('fa-regular');
                    } else {
                        likeCountElement.textContent = currentCount + 1;
                        likesContainer.classList.add('liked');
                        this.querySelector('i').classList.remove('fa-regular');
                        this.querySelector('i').classList.add('fa-solid');
                    }
                });
            }
        }

        if (textareaComment) {
            textareaComment.addEventListener("click", () => {
                textareaComment.classList.add("display__control-comments");
                controlComments.style.display = "block";
            });
            textareaComment.addEventListener("input", () => {
                respondComment.style.opacity = textareaComment.value.trim() === "" ? "0.1" : "1";
            });
        }

        // Handle delete comment
        document.querySelectorAll('.delete-comment').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const commentId = button.dataset.commentId;
                const commentElement = document.getElementById(`comment-${commentId}`);

                if (!commentId || !commentElement) {
                    console.error('Comment ID or element not found');
                    return;
                }

                if (confirm('Are you sure you want to delete this comment?')) {
                    try {
                        const response = await fetch('../controllers/delete_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `comment_id=${encodeURIComponent(commentId)}`
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Remove comment from UI
                            commentElement.remove();

                            // Update response count (only for top-level comments)
                            if (!commentElement.closest('.replies-container')) { // Top-level comment
                                let currentCount = parseInt(responseCount.textContent) || 0;
                                responseCount.textContent = Math.max(0, currentCount - 1);
                            } else { // Reply
                                // Update reply count of the parent comment
                                const parentComment = commentElement.closest('.comment');
                                const replyCountElement = parentComment.querySelector('.comments-card p');
                                if (replyCountElement) {
                                    const currentCount = parseInt(replyCountElement.textContent) || 0;
                                    replyCountElement.textContent = `${currentCount + 1} replies`;
                                }
                            }
                        } else {
                            console.error('Delete failed:', result.error);
                            alert('Failed to delete comment: ' + result.error);
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        alert('An error occurred while deleting the comment');
                    }
                }
            });
        });

        document.querySelectorAll('.reply-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const comment = this.closest('.comment');
                const replyArea = comment.querySelector('.reply-area');

                replyArea.style.display = replyArea.style.display === 'none' ? 'block' : 'none';

                // Focus textarea if showing
                if (replyArea.style.display === 'block') {
                    const textarea = replyArea.querySelector('.reply-textarea');
                    textarea.focus();

                    // Enable/disable submit button based on content
                    const submitButton = replyArea.querySelector('.submit-button');
                    submitButton.disabled = textarea.value.trim() === '';
                }
            });
        });

        document.querySelectorAll('.reply-textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const submitButton = this.closest('.reply-area').querySelector('.submit-button');
                submitButton.disabled = this.value.trim() === '';
            });
        });

        // Handle cancel buttons for reply forms
        document.querySelectorAll('.reply-area .cancel').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const replyArea = this.closest('.reply-area');
                replyArea.style.display = 'none';
                replyArea.querySelector('.reply-textarea').value = '';
            });
        });
        
        // Like button functionality
        const likeButton = document.querySelector('.like-button');

        if (likeButton) {
            likeButton.addEventListener('click', function() {
                const likeCount = document.querySelector('.like-count');
                const currentCount = parseInt(likeCount.textContent);
                
                // Toggle like state (you'd normally check if already liked)
                // This is just a simple demo
                if (this.classList.contains('liked')) {
                    likeCount.textContent = currentCount - 1;
                    this.classList.remove('liked');
                    this.style.fill = 'rgba(117, 117, 117, 1)';
                } else {
                    likeCount.textContent = currentCount + 1;
                    this.classList.add('liked');
                    this.style.fill = 'rgba(26, 137, 23, 1)';
                }
            });
        }

        // Attach reply count toggle listeners
        function attachReplyToggleListeners() {
            document.querySelectorAll('.reply-count-toggle').forEach(element => {
                if (!element.hasAttribute('data-listener-attached')) {
                    element.setAttribute('data-listener-attached', 'true');
                    element.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const commentContainer = this.closest('.comment');
                        const repliesContainer = commentContainer.querySelector('.replies-container');
                        const replyCount = parseInt(this.textContent) || 0;

                        if (repliesContainer) {
                            if (replyCount === 0) {
                                // Optional: Show a message if no replies exist
                                alert('No replies yet.');
                            } else {
                                repliesContainer.style.display = repliesContainer.style.display === 'none' ? 'block' : 'none';
                            }
                        } else {
                            console.error('Replies container not found for comment:', commentContainer);
                        }
                    });
                }
            });
        }

        // Initial attachment of listeners
        attachReplyToggleListeners();

        // Helper function to handle comment deletion
        async function handleDeleteComment(event) {
            const button = event.target.closest('.delete-comment');
            const commentId = button.dataset.commentId;
            const commentElement = document.getElementById(`comment-${commentId}`);
            const responseCount = document.querySelector("#response-count");

            if (confirm('Are you sure you want to delete this comment and all its replies?')) {
                try {
                    const response = await fetch('../controllers/delete_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${encodeURIComponent(commentId)}`
                    });
                    const result = await response.json();

                    if (result.success) {
                        commentElement.remove();
                        if (!commentElement.closest('.replies-container')) {
                            // Top-level comment
                            responseCount.textContent = Math.max(0, parseInt(responseCount.textContent) - 1);
                        } else {
                            // Reply
                            const parentComment = commentElement.closest('.comment');
                            const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                            if (replyCountElement) {
                                const currentCount = parseInt(replyCountElement.textContent) || 0;
                                replyCountElement.textContent = `${Math.max(0, currentCount - 1)} replies`;
                            }
                        }
                    } else {
                        alert('Failed to delete comment: ' + result.error);
                    }
                } catch (error) {
                    alert('An error occurred while deleting the comment: ' + error.message);
                }
            }
        }

        // Helper function to add event listeners to new comments/replies
        function addCommentEventListeners(commentElement) {
            // Add delete functionality
            const deleteButton = commentElement.querySelector('.delete-comment');
            if (deleteButton) {
                deleteButton.addEventListener('click', handleDeleteComment);
            }

            // Add edit functionality
            const editButton = commentElement.querySelector('.edit-comment');
            if (editButton) {
                editButton.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const commentElement = document.getElementById(`comment-${commentId}`);
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    commentContent.style.display = 'none';
                    editArea.style.display = 'block';
                });
            }

            // Add save edit functionality
            const saveEditButton = commentElement.querySelector('.save-edit');
            if (saveEditButton) {
                saveEditButton.addEventListener('click', async function() {
                    const commentId = this.dataset.commentId;
                    const commentElement = document.getElementById(`comment-${commentId}`);
                    const editTextarea = commentElement.querySelector('.edit-textarea');
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    const newContent = editTextarea.value.trim();

                    if (!newContent) {
                        alert('Comment cannot be empty');
                        return;
                    }

                    try {
                        const response = await fetch('../controllers/edit_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `comment_id=${encodeURIComponent(commentId)}&comment_content=${encodeURIComponent(newContent)}`
                        });
                        const result = await response.json();
                        if (result.success) {
                            commentContent.textContent = escapeHTML(newContent);
                            commentContent.style.display = 'block';
                            editArea.style.display = 'none';
                        } else {
                            alert('Failed to update comment: ' + result.error);
                        }
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                });
            }

            // Add cancel edit functionality
            const cancelEditButton = commentElement.querySelector('.cancel-edit');
            if (cancelEditButton) {
                cancelEditButton.addEventListener('click', function() {
                    const commentElement = this.closest('.comment');
                    const commentContent = commentElement.querySelector('.comment-content');
                    const editArea = commentElement.querySelector('.edit-area');
                    const editTextarea = editArea.querySelector('.edit-textarea');
                    editTextarea.value = commentContent.textContent;
                    commentContent.style.display = 'block';
                    editArea.style.display = 'none';
                });
            }

            // Add textarea input listener for reply submit button
            const replyTextarea = commentElement.querySelector('.reply-textarea');
            if (replyTextarea) {
                replyTextarea.addEventListener('input', function() {
                    const submitButton = this.closest('.reply-area').querySelector('.submit-button');
                    submitButton.disabled = this.value.trim() === '';
                });
            }

            // Add like functionality
            const likeButton = commentElement.querySelector('.like-button');
            if (likeButton) {
                likeButton.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const commentId = this.dataset.commentId;
                    const likesContainer = this.closest('.likes-container');
                    const likeCountElement = likesContainer.querySelector('.like-count');
                    const isLiked = likesContainer.classList.contains('liked');

                    try {
                        const response = await fetch('../controllers/handle_comment_like.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `comment_id=${encodeURIComponent(commentId)}&action=${isLiked ? 'unlike' : 'like'}`
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Update UI based on the response
                            likesContainer.classList.toggle('liked', result.is_liked);
                            likeCountElement.textContent = result.new_like_count;
                            const icon = this.querySelector('.like-svg');
                            icon.classList.toggle('fa-solid', result.is_liked);
                            icon.classList.toggle('fa-regular', !result.is_liked);
                        } else {
                            alert('Failed to update like: ' + result.error);
                        }
                    } catch (error) {
                        alert('An error occurred while updating the like: ' + error.message);
                    }
                });
            }
        }

        // Helper function to add event listeners to new comments/replies
        function addCommentEventListeners(commentElement) {
            // Add delete functionality
            const deleteButton = commentElement.querySelector('.delete-comment');
            if (deleteButton) {
                deleteButton.addEventListener('click', handleDeleteComment);
            }
            
            // Add textarea input listener for reply submit button
            const replyTextarea = commentElement.querySelector('.reply-textarea');
            if (replyTextarea) {
                replyTextarea.addEventListener('input', function() {
                    const submitButton = this.closest('.reply-area').querySelector('.submit-button');
                    submitButton.disabled = this.value.trim() === '';
                });
            }
        }

        // Handle post likes
        document.querySelectorAll('.article-card__footer .like-button').forEach(button => {
            button.addEventListener('click', async function() {
                const container = this.closest('.likes-container');
                const postId = container.dataset.postId;
                const likeCountElement = container.querySelector('.like-count');
                const isLiked = container.classList.contains('liked');

                if (!postId) {
                    console.error('Post ID not found');
                    alert('Error: Post ID not found');
                    return;
                }

                try {
                    const response = await fetch('../controllers/handle_like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `post_id=${encodeURIComponent(postId)}&action=${isLiked ? 'unlike' : 'like'}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        container.classList.toggle('liked');
                        likeCountElement.textContent = result.new_like_count;

                        // Update the icon
                        const icon = this.querySelector('.like-svg');
                        icon.classList.toggle('fa-solid', result.is_liked);
                        icon.classList.toggle('fa-regular', !result.is_liked);
                    } else {
                        console.error('Like failed:', result.error);
                        alert('Failed to update like: ' + result.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the like: ' + error.message);
                }
            });
        });

        // Handle comment likes (already implemented, but ensure it’s separate)
        document.addEventListener('click', async function(e) {
            const likeButton = e.target.closest('.responses .like-button');
            if (likeButton) {
                console.log("hhhh");
                
                e.preventDefault();
                e.stopPropagation(); // Prevent click from bubbling to other handlers

                const commentId = likeButton.dataset.commentId;
                const likesContainer = likeButton.closest('.likes-container');
                const likeCountElement = likesContainer.querySelector('.like-count');
                const isLiked = likesContainer.classList.contains('liked');

                console.log(likeButton);
                

                if (!commentId || isNaN(commentId)) {
                    console.error('Invalid comment ID:', commentId);
                    alert('Error: Invalid comment ID');
                    return;
                }

                // Prevent multiple requests
                if (likeButton.disabled) return;

                try {
                    likeButton.disabled = true;
                    const action = isLiked ? 'unlike' : 'like';
                    const response = await fetch('../controllers/handle_comment_like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${encodeURIComponent(commentId)}&action=${encodeURIComponent(action)}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update UI
                        likesContainer.classList.toggle('liked', result.is_liked);
                        likeCountElement.textContent = result.new_like_count;
                        const icon = likeButton.querySelector('.like-svg');
                        icon.classList.toggle('fa-solid', result.is_liked);
                        icon.classList.toggle('fa-regular', !result.is_liked);
                    } else {
                        console.error('Comment like/unlike failed:', result.error);
                        alert('Failed to update comment like: ' + result.error);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    alert('An error occurred while updating the comment like: ' + error.message);
                } finally {
                    likeButton.disabled = false;
                }
            }

            // Ensure reply button works (example implementation)
            const replyButton = e.target.closest('.comment .reply-button');
            if (replyButton) {
                e.preventDefault();
                const commentId = replyButton.dataset.commentId;
                const replyForm = document.querySelector(`#reply-form-${commentId}`);
                if (replyForm) {
                    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                } else {
                    console.error('Reply form not found for comment ID:', commentId);
                }
            }
        });

        // Handle post saves
        document.querySelectorAll('.save').forEach(button => {
            button.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const isSaved = this.classList.contains('saved');
                console.log(`Clicked save button for post ${postId}, isSaved: ${isSaved}`); // Debug

                try {
                    button.disabled = true; // Prevent multiple clicks
                    const response = await fetch('../controllers/handle_save.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${encodeURIComponent(postId)}&action=${isSaved ? 'unsave' : 'save'}`
                    });

                    const result = await response.json();
                    console.log('Server response:', result); // Debug

                    if (result.success) {
                        this.classList.toggle('saved');
                        this.innerHTML = isSaved
                            ? '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>'
                            : '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>';
                    } else {
                        alert(`Error: ${result.error}`);
                    }
                } catch (error) {
                    console.error('AJAX error:', error);
                    alert('Failed to save/unsave post. Check console for details.');
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
</body>
</html>