const savePost = document.querySelectorAll(".save");

savePost.forEach(btn => {
    btn.addEventListener("click", async (e) => {
        e.preventDefault();

        const saveButton = btn;
        const postId = btn.dataset.postId;
        const saveSvg = saveButton.querySelector('.save-svg');

        try {
            const response = await fetch("../functions/actions/handle_save_posts.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const result = await response.json();
    
            if (result.success) {
                if (result.action === 'saved') {
                    saveButton.classList.add('saved');
                    saveSvg.outerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>`;
                    // alert('Post saved successfully');
                } else if (result.action === 'unsaved') {
                    saveButton.classList.remove('saved');
                    saveSvg.outerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>`;
                    // alert('Post unsaved successfully');
                }
            } else {
                alert('Error saving post: ' + result.message);
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the post');
        }
    })
})