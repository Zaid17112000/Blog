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