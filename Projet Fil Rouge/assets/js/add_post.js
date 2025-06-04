/**
 * Initializes a Quill editor on the specified container
 * 
 * @param {string} containerId - The ID of the container element for the editor
 * @param {string} placeholder - Placeholder text for the editor
 * @returns {Quill} The Quill editor instance
 */
function initQuillEditor(containerId, placeholder = 'Write your content here...') {
    const quill = new Quill(containerId, {
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['link', 'blockquote', 'code-block', 'image'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ]
        },
        placeholder: placeholder,
        theme: 'snow'
    });
    
    return quill;
}

function updateCategoryDisplay() {
    categoryContainer.innerHTML = '';
    selectedCategories.forEach(category => {
        const tag = document.createElement('div');
        tag.className = 'category-tag';
        tag.innerHTML = `
            ${category}
            <span class="category-remove" data-category="${category}">✕</span>
        `;
        categoryContainer.appendChild(tag);
    });
    document.querySelectorAll('.category-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryToRemove = this.getAttribute('data-category');
            selectedCategories = selectedCategories.filter(cat => cat !== categoryToRemove);
            updateCategoryDisplay();
            updateCategoryInput();
        });
    });
}

function updateTagDisplay() {
    tagContainer.innerHTML = '';
    selectedTags.forEach(tag => {
        const tagElement = document.createElement('div');
        tagElement.className = 'tag';
        tagElement.innerHTML = `
            ${tag}
            <span class="tag-remove" data-tag="${tag}">✕</span>
        `;
        tagContainer.appendChild(tagElement);
    });
    document.querySelectorAll('.tag-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const tagToRemove = this.getAttribute('data-tag');
            selectedTags = selectedTags.filter(tag => tag !== tagToRemove);
            updateTagDisplay();
            updateTagInput();
        });
    });
}

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

function validationForm(post_img_url) {
    form.addEventListener('submit', function(e) {
        const postTitle = document.getElementById('title').value.trim();
        const postContent = quill.getText().trim();
        const categories = JSON.parse(document.getElementById('selected-categories-input').value || '[]');
        const imageUpload = document.getElementById('image-upload');
        const hasExistingImage = post_img_url;
        const hasNewImage = imageUpload.files.length > 0;

        // Clear previous error highlights
        document.querySelectorAll('.error-highlight').forEach(el => el.classList.remove('error-highlight'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());

        let isValid = true;

        if (!postTitle) {
            showError('title', 'Post title is required');
            isValid = false;
        }

        if (!postContent || quill.root.innerHTML === '<p><br></p>') {
            showError('editor-container', 'Post content is required');
            isValid = false;
        }

        if (categories.length === 0) {
            showError('category', 'At least one category is required');
            isValid = false;
        }

        if (!hasExistingImage && !hasNewImage) {
            showError('image-upload', 'Featured image is required');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = '<span style="color: red;">Please fix the errors below before publishing</span>';
            messageDiv.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

/**
 * Saves a blog post as draft
 * @param {string} formId - ID of the form element
 * @param {object} quill - Quill editor instance
 * @param {string} tagsInputId - ID of the hidden tags input
 * @param {string} categoriesInputId - ID of the hidden categories input
 * @param {string} endpoint - API endpoint URL
 * @param {number|null} postId - Existing post ID for updates (optional)
 * @param {string} csrfTokenSelector - Selector for CSRF token input
 * @param {string} messageElementId - ID of the message display element
 */
function saveBlogDraft(
    formId, 
    quill, 
    tagsInputId, 
    categoriesInputId, 
    endpoint, 
    postId = null, 
    csrfTokenSelector = 'input[name="csrf_token"]',
    messageElementId = 'message'
) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);

    // Convert JSON strings to proper format
    const tags = JSON.parse(document.getElementById(tagsInputId).value || '[]');
    const categories = JSON.parse(document.getElementById(categoriesInputId).value || '[]');

    formData.set('content', quill.root.innerHTML);
    formData.set('selected-tags', JSON.stringify(tags));
    formData.set('selected-categories', JSON.stringify(categories));

    if (postId) {
        formData.append('post_id', postId);
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector(csrfTokenSelector).value
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Save successful:", data);
        showMessage(messageElementId, 'Draft saved successfully', 'success');
        return data;
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageElementId, 'An unexpected error occurred while saving', 'error');
        throw error; // Re-throw for further handling if needed
    });
}
/**
 * Helper function to display messages
 * @param {string} elementId - ID of the message element
 * @param {string} message - Message to display
 * @param {string} type - Type of message ('success' or 'error')
 */
function showMessage(elementId, message, type) {
    const messageDiv = document.getElementById(elementId);
    if (!messageDiv) return;
    
    const color = type === 'success' ? 'green' : 'red';
    messageDiv.innerHTML = `<span style="color: ${color};">${message}</span>`;
    setTimeout(() => messageDiv.innerHTML = '', 5000);
}