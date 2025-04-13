function calculateProgress() {
    const totalPages = document.getElementById('totalPages').value;
    const readPages = document.getElementById('readPages').value;
    const progressOutput = document.getElementById('progressOutput');
    const progressBar = document.getElementById('progressBar');

    if (totalPages > 0) {
        const progress = (readPages / totalPages) * 100;
        progressOutput.innerText = `Your reading progress: ${Math.min(progress, 100).toFixed(2)}%`;
        progressBar.style.width = `${Math.min(progress, 100)}%`;
    } else {
        progressOutput.innerText = 'Please enter a valid total number of pages.';
        progressBar.style.width = '0%';
    }
}