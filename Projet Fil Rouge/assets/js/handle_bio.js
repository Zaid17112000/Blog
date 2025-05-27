document.addEventListener('DOMContentLoaded', function() {
    const bioTextarea = document.getElementById('bio');
    const bioCounter = document.querySelector('.bio-counter');
    const maxLength = 500;
    
    // Initial count display
    updateCounter();
    
    // Update counter on input
    bioTextarea.addEventListener('input', updateCounter);
    
    function updateCounter() {
        const currentLength = bioTextarea.value.length;
        bioCounter.textContent = `${currentLength}/${maxLength}`;
        
        // Update counter styling based on remaining characters
        if (currentLength >= maxLength) {
            bioCounter.classList.add('limit-reached');
            bioCounter.classList.remove('limit-warning');
        } else if (currentLength >= maxLength * 0.8) {
            bioCounter.classList.add('limit-warning');
            bioCounter.classList.remove('limit-reached');
        } else {
            bioCounter.classList.remove('limit-warning', 'limit-reached');
        }
    }
});