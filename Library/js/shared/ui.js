/**
 * Shows a custom popup modal with a specific message.
 * @param {string} message The text to display in the popup body.
 */
window.showPopup = function(message) {
    const modal = document.getElementById('popup-modal');
    const messageEl = document.getElementById('popup-message');
    
    if (modal && messageEl) {
        messageEl.innerText = message;
        modal.style.display = 'flex';
    } else {
        console.error("Popup modal elements not found!");
        // Fallback to a basic alert if the modal is missing
        alert(message);
    }
}

/**
 * Initializes the close listeners for the global popup modal.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('popup-modal');
    const closeBtn = document.getElementById('popup-close-btn');

    if (modal && closeBtn) {
        const closeModal = () => {
            modal.style.display = 'none';
        };
        
        // Close when the 'OK' button is clicked
        closeBtn.addEventListener('click', closeModal);
        
        // Close when the user clicks on the dark overlay
        modal.addEventListener('click', (e) => {
            if (e.target === modal) { 
                closeModal();
            }
        });
    }
});