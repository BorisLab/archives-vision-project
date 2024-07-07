$(document).ready(function() {
    const messages = document.querySelectorAll('.flash-msg');
    let index = 0;

    function showNextMessage() {
        if (index < messages.length) {
            messages[index].classList.add('show');
            setTimeout(() => {
                messages[index].classList.remove('show');
                setTimeout(() => {
                    messages[index].style.display = 'none';
                    index++;
                    showNextMessage();
                }, 500); // Adjust the delay for hiding message
            }, 3000); // Adjust the delay for displaying message
        }
    }

    showNextMessage();
});