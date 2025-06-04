const categoriesContainer = document.querySelector('.categories');
const leftArrow = document.querySelector('#left-arrow');
const rightArrow = document.querySelector('#right-arrow');

if (categoriesContainer && leftArrow && rightArrow) {
    // Scroll right when right arrow is clicked
    rightArrow.addEventListener('click', () => {
        categoriesContainer.scrollBy({
            left: 200,
            behavior: 'smooth'
        });
    });

    // Scroll left when left arrow is clicked
    leftArrow.addEventListener('click', () => {
        categoriesContainer.scrollBy({
            left: -200,
            behavior: 'smooth'
        });
    });

    // Optional: Disable arrows when there's no more content to scroll
    const updateArrowState = () => {
        const maxScrollLeft = categoriesContainer.scrollWidth - categoriesContainer.clientWidth;
        leftArrow.disabled = categoriesContainer.scrollLeft <= 0;
        rightArrow.disabled = categoriesContainer.scrollLeft >= maxScrollLeft;
    };

    // Update arrow state on scroll
    categoriesContainer.addEventListener('scroll', updateArrowState);

    // Initial check for arrow state
    updateArrowState();
} else {
    console.error("Category container or arrows not found");
}