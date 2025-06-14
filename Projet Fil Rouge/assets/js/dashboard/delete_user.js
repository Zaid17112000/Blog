function deleteUser() {
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const userRow = this.closest('tr');
            
            // Confirm deletion
            const isConfirmed = confirm('Are you sure you want to delete this user? This action cannot be undone.');
            
            if (!isConfirmed) return;

            try {
                const response = await fetch('../functions/actions/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                });

                const data = await response.json();

                if (response.ok) {
                    // Remove the user row from the table
                    userRow.remove();
                    
                    // Show success toast
                    showToast('User deleted successfully', 'success');
                } else {
                    throw new Error(data.error || 'Failed to delete user');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message, 'error');
            }
        });
    });
}

deleteUser();