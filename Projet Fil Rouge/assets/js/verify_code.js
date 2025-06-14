document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const form = document.querySelector('.verification-form');
    const submitButton = document.querySelector('.verification-button');
    
    // Auto-format the code input
    codeInput.addEventListener('input', function(e) {
        // Remove any non-digit characters
        let value = e.target.value.replace(/\D/g, '');
        
        // Limit to 6 digits
        if (value.length > 6) {
            value = value.slice(0, 6);
        }
        
        e.target.value = value;
        
        // Auto-submit when 6 digits are entered
        if (value.length === 6) {
            setTimeout(() => {
                submitButton.click();
            }, 500);
        }
    });
    
    // Add loading state to button on form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        submitButton.classList.add('loading');
        submitButton.disabled = true;

        // Get the input value
        const codeInput = document.getElementById('code');
        const enteredCode = codeInput.value.trim();
        
        // Basic validation - check if it's 6 digits
        if (!/^\d{6}$/.test(enteredCode)) {
            // Show error message
            alert('Please enter a valid 6-digit code');
            
            // Remove loading state
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            
            // Prevent form submission
            e.preventDefault();
            
            // Focus back on the input
            codeInput.focus();
            return;
        }
        else {
            window.location.href = 'reset_password.php';

            // Remove loading state
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
        }
    });
    
    // Focus the input on page load
    codeInput.focus();
    
    // Paste handling for verification codes
    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const code = paste.replace(/\D/g, '').slice(0, 6);
        this.value = code;
        
        if (code.length === 6) {
            setTimeout(() => {
                submitButton.click();
            }, 500);
        }
    });
});