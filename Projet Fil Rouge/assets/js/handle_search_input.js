async function handleSearchInput(value) {
    if (!value.trim()) {
        window.location.href = 'blogsphere.php';
        return;
    }

    try {
        const response = await fetch("../functions/actions/handle_search.php", {
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
        }
    } catch (error) {
        console.error('Error:', error);
    }
}