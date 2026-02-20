/* add event listener to display table input field
* if the button of research has been clicked */
document.querySelectorAll(".table-search-column").forEach(column => {
    btn = column.querySelector('.table-search-btn');
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const input = column.querySelector('.table-search-input'); // get the following input field
        input.style.display = input.style.display === 'none' ? 'block' : 'none';
        /* focus on the input when displayed */
        if (input.style.display === 'block')
            input.focus();
    });
});

/* add event listener when every input is typed in order
* to make everytime a query to search between the most common cars */
document.querySelectorAll(".table-search-input").forEach(input => {
    input.addEventListener('input', function(){
         
    });
});