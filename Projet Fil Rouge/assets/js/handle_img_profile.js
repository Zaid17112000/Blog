document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const inputElement = e.target; // Save reference to the input
    
    // Check file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('File size exceeds 2MB limit');
        e.target.value = '';
        return;
    }
    
    // Check file type
    if (!file.type.match('image.*')) {
        alert('Please select an image file');
        e.target.value = '';
        return;
    }
    
    // Create preview
    const reader = new FileReader();
    reader.onload = function(event) {
        let preview = document.getElementById('image-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.id = 'image-preview';
            preview.className = 'image-preview';
            inputElement.parentNode.appendChild(preview);
        }
        preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview">';
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});