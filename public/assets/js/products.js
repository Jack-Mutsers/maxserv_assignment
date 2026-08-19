currentPage = 1;

function pagination(page) {
    const paginationContainer = document.querySelector('.pagination');
    const totalPages = Number(paginationContainer.dataset.totalPages);

    if (page < 1 || page > totalPages) {
        return;
    }

    fetch("/products", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            page: page,
            perPage: 16
        })
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('products').innerHTML = html;

        document.getElementById('pagination-status').textContent = `Page ${page} of ${totalPages}`;
        document.getElementById('previous-page').disabled = page === 1;
        document.getElementById('next-page').disabled = page === totalPages;

        currentPage = page;
    })
    .catch(error => {
        console.error('Pagination failed:', error);
    });
}

function previousPage() {
    if (currentPage > 1) {
        pagination(currentPage - 1);
    }
}

function nextPage() {
    const totalPages = Number(document.querySelector('.pagination').dataset.totalPages);
    if (currentPage < totalPages) {
        pagination(currentPage + 1);
    }
}