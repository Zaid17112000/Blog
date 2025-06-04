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
                            <!-- <span style="margin: 0px 8px;">
                                <span class="ar b bu am bx">·</span>
                            </span> -->
                            <form method="post" class="follow-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                <!-- <div class="responses">
                    <h2>Responses (23)</h2>
                </div> -->
                <form method="POST" class="comments-form" id="comment-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                            <textarea name="comment" placeholder="What are your thoughts ?" cols="4" style="font-family: 'Merriweather Sans', sans-serif;" required></textarea>
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
                                <textarea class="reply-textarea" placeholder="Write your reply..." style="font-family: 'Merriweather Sans', sans-serif;"></textarea>
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
                                        <div class="likes-container <?= $reply['user_liked'] ? 'liked' : '' ?>">
                                            <button class="like-button" data-comment-id="<?= $reply['comment_id'] ?>">
                                                <i class="like-svg <?= $reply['user_liked'] ? 'fa-solid fa-heart' : 'fa-regular fa-heart' ?>"></i>
                                            </button>
                                            <span class="like-count"><?= $reply['like_count'] ?: 0 ?></span>
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