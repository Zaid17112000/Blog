const leftArrow = document.getElementById("left-arrow");
const rightArrow = document.getElementById("right-arrow");
const categories = document.querySelector(".categories");

const scrollAmount = 150; // Amount to scroll

/** What does the number 150 represent ?
 * 150 is the number of pixels the element should move horizontally.
 * If left: 150, it scrolls right.
 * If left: -150, it scrolls left.
*/

/** How he know that the last category is reached ? because it's stop scroll when the last category is reached
 * The reason it stops scrolling when the last category is reached is not because of any special condition in the code—it's actually handled automatically by the scrollBy() method and the browser’s scrolling behavior.
 1. How Does scrollBy() Know When to Stop ?
    ** The .categories element has a maximum scrollable width, determined by:
        o Its total content width (all categories inside).
        o The width of the visible container (.categories itself).
    ** When .categories reaches the maximum scroll position, calling scrollBy() won't scroll any further. The browser automatically prevents scrolling past the end.
    ** You can check this in the console by logging: console.log(categories.scrollLeft, categories.scrollWidth, categories.clientWidth);
        o categories.scrollLeft: Current scroll position.
        o categories.scrollWidth: Total width of the scrollable content.
        o categories.clientWidth: Visible width of .categories (the part shown without scrolling).
 2. When Does Scrolling Stop?
    ** The scrolling stops when:
        categories.scrollLeft + categories.clientWidth >= categories.scrollWidth
    ** This means:
        o scrollLeft (current position) + clientWidth (visible width) reaches or exceeds the scrollWidth (total content width).
        o There's no more content left to scroll.

 * .categories should have overflow-x: scroll; in CSS to allow horizontal scrolling.
 * overflow-x: auto; also works because it enables scrolling only when needed.
 */

// Scroll left
if (leftArrow) {
    leftArrow.addEventListener("click", () => {
        categories.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });
}

// Scroll right
if (rightArrow) {
    rightArrow.addEventListener("click", () => {
        categories.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });
}

/*********** */
import Quill from 'quill';
import 'quill/dist/quill.snow.css'; // Import Quill CSS

// Initialize Quill
var quill = new Quill('#editor-container', {
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            ['link', 'blockquote', 'code-block', 'image'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }]
        ]
    },
    placeholder: 'Write your blog post here...',
    theme: 'snow'
});

// Category selection handling
const categorySelect = document.getElementById('category');
const categoryContainer = document.getElementById('selected-categories');
const categoryInput = document.getElementById('selected-categories-input');
let selectedCategories = [];

categorySelect.addEventListener('change', function() {
    const selectedValue = this.value;
    
    if (selectedValue && !selectedCategories.includes(selectedValue)) {
        selectedCategories.push(selectedValue);
        updateCategoryDisplay();
        updateCategoryInput();
    }
    this.value = ''; // Reset dropdown
});

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

    // Add remove functionality
    document.querySelectorAll('.category-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryToRemove = this.getAttribute('data-category');
            selectedCategories = selectedCategories.filter(cat => cat !== categoryToRemove);
            updateCategoryDisplay();
            updateCategoryInput();
        });
    });
}

function updateCategoryInput() {
    categoryInput.value = JSON.stringify(selectedCategories);
}

/** 
 * In the updateCategoryInput() function, the input (specifically categoryInput.value) is being set to hold an array of categories, but it’s stored as a JSON string.
 * While the underlying data structure (selectedCategories) is an array of categories, the categoryInput field itself holds that array as a stringified JSON representation, not as a raw JavaScript array. This is done to facilitate sending the data to the server via a form submission.
 * How It Works in Context
    1. Client-Side (JavaScript):
        o When the user selects a category from the dropdown, the change event listener on categorySelect adds the selected category to the selectedCategories array.
        o updateCategoryInput() is called, converting selectedCategories to a JSON string and storing it in the hidden input field categoryInput.
    2. Server-Side (PHP):
        o When the form is submitted, the value of the hidden input (selected-categories) is sent to the server as part of the $_POST data.
        o In the PHP code, json_decode($_POST['selected-categories'] ?? '[]', true) decodes this JSON string back into a PHP array ($category_names).
        o The true parameter in json_decode ensures the result is a PHP array rather than an object.
 * Example Flow
    ** User selects "Technology" and "Science" from the dropdown.
    ** JavaScript:
        o selectedCategories = ["Technology", "Science"].
        o updateCategoryInput() sets categoryInput.value = '["Technology", "Science"]'.
    ** Form submission sends selected-categories as ["Technology", "Science"] (a string).
    ** PHP:
        o $_POST['selected-categories'] = '["Technology", "Science"]'.
        o json_decode($_POST['selected-categories'], true) results in $category_names = ["Technology", "Science"].
 */

// Tag handling
const tagInput = document.getElementById('tags');
const tagContainer = document.getElementById('selected-tags');
const tagHiddenInput = document.getElementById('selected-tags-input');
let selectedTags = [];

tagInput.addEventListener('keypress', function(e) {
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

function updateTagInput() {
    tagHiddenInput.value = JSON.stringify(selectedTags);
}