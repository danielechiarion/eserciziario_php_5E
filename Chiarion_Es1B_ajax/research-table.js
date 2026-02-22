/**
 * Function to update table rows based on search results
 * @param {Array} results Array of car objects from the server
 */
function updateTableRows(results) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) {
        console.error('Table body not found');
        return;
    }

    if (results.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nessun risultato trovato</td></tr>';
        return;
    }

    let html = '';
    results.forEach(row => {
        html += '<tr>' +
            '<td>' + (row.marca || '') + '</td>' +
            '<td>' + (row.modello || '') + '</td>' +
            '<td>' + (row.cilindrata || '') + 'cc</td>' +
            '<td>' + (row.potenza || '') + 'CV</td>' +
            '<td>' + (row.lunghezza || '') + 'cm</td>' +
            '<td>' + (row.larghezza || '') + 'cm</td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}

/* add event listener to display table input field
* if the button of research has been clicked */
document.querySelectorAll(".table-search-column").forEach(column => {
    const btn = column.querySelector('.table-search-btn');
    if (!btn) {
        console.warn('Search button not found in column');
        return;
    }

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const input = column.querySelector('.table-search-input');
        if (!input) {
            console.warn('Search input not found in column');
            return;
        }

        /* toggle display */
        const isHidden = input.style.display === 'none' || input.style.display === '';
        input.style.display = isHidden ? 'block' : 'none';

        /* focus on the input when displayed */
        if (input.style.display === 'block') {
            input.focus();
        }
    });
});

/* define variables to count time in order
* to avoid debounce method */
let typingTimer;
const TYPINGINTERVAL = 300;

/* add event listener when every input is typed in order
* to make everytime a query to search between the most common cars */
document.querySelectorAll(".table-search-input").forEach(input => {
    input.addEventListener('input', function(){
        /* select all inputs and make the research */
        const allInputs = document.querySelectorAll('.table-search-input');
        let query = {};

        clearTimeout(typingTimer); // clear the timer

        /* get the data from the input in order
        * to populate the query to send into the POST request */
        allInputs.forEach(singleInput => {
           /* get the key and control if the value is not empty,
           * so as to add it to the query */
           const thElement = singleInput.closest('th');
           if (!thElement) return;

           const key = thElement.dataset.field;
           const value = singleInput.value;

           if(value !== '' && value !== null && value !== undefined) {
               query[key] = value;
           }
        });

        /* use setTimeout function to do actions after the
        * typing delay */
        typingTimer = setTimeout(function() {
            /* add action to the query object */
            query['action'] = 'search_car';

            console.log('Sending search query:', query);

            $.ajax({
                url:"dashboard.php",
                method: "POST",
                data: query,
                dataType: "json",
                success: function(response){
                    console.log('Search response:', response);
                    /* update table body with search results */
                    updateTableRows(response);
                },
                error: function(xhr, status, error){
                    console.error("AJAX Error:", status, error);
                    console.error("Response text:", xhr.responseText);
                }
            });
        }, TYPINGINTERVAL);
    });
});