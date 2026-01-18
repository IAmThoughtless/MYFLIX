document.addEventListener('DOMContentLoaded', () => {

    // Sticky Header Logic
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (header) { 
            if (window.scrollY > 0) {
                header.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
            } else {
                header.style.backgroundColor = '#141414';
            }
        }
    });
});

// Function to toggle star status
function toggleStar(event, tmdbId, type, title, posterPath) {
    // Prevent the click from triggering the link to the detail page
    event.preventDefault(); 
    event.stopPropagation();

    const btn = event.currentTarget;
    
    fetch('api/toggle_star.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tmdb_id: tmdbId,
            type: type,
            title: title,
            poster_path: posterPath
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Toggle the visual class
            if (data.action === 'added') {
                btn.classList.add('active');
                btn.innerHTML = '★'; // Solid star
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '☆'; // Hollow star
            }
        } else {
            console.error('Error toggling star:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}