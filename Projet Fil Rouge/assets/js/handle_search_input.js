async function handleSearchInput(value) {
    if (!value.trim()) {
        // If search is empty, reload the page to show all posts
        window.location.href = 'blogsphere.php';
        return;
    }

    try {
        const response = await fetch("../controllers/handle_search.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `search-by-title=${encodeURIComponent(value)}`
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            updateArticlesGrid(result.posts, result.searchTerm);
        } else {
            console.error('Search failed:', result.message);
            // Show error message to user
        }
    } catch (error) {
        console.error('Error:', error);
        // Show error message to user
    }
}