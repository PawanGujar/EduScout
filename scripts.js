document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('.bookmark-btn');

  buttons.forEach(btn => {
    btn.addEventListener('click', function () {
      const courseId = this.dataset.courseId;
      const isBookmarked = this.classList.contains('bookmarked');

      const action = isBookmarked ? 'remove' : 'add';

      fetch('bookmark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `course_id=${courseId}&action=${action}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (data.status === 'bookmarked') {
              btn.classList.add('bookmarked');
              btn.textContent = 'Bookmarked';
            } else {
              btn.classList.remove('bookmarked');
              btn.textContent = 'Bookmark';
            }
          } else {
            alert('Error: ' + data.error);
          }
        })
        .catch(err => {
          console.error('AJAX error:', err);
        });
    });
  });
});
