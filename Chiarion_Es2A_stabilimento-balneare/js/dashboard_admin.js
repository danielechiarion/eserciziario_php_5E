/**
 * Function to display a form for adding
 * a new season
 */
function displayAddSeasonForm(){
    /* just bring visibility to the form */
    document.getElementById("add-season_form").classList.remove("d-none");
}

/**
 * Function to change the mode to the edit one
 */
function changeToEditMode(){
    /* get the row and then change the
    * visibility of the buttons */
    const row = document.querySelector("table tbody tr");

    row.querySelector(".view-report").classList.add("d-none");
    row.querySelector(".change-season").classList.add("d-none");
    row.querySelectorAll(".view-fields").forEach((singleElement) => singleElement.classList.add("d-none"));

    row.querySelector(".confirm-change-season").classList.remove("d-none");
    row.querySelector(".cancel-change-season").classList.remove("d-none");
    row.querySelectorAll(".input-fields").forEach((singleElement) => singleElement.classList.remove("d-none"));
}

/**
 * Function to change the mode to the view one
 */
function changeToViewMode(){
    /* get the row and then change the
    * visibility of the buttons */
    const row = document.querySelector("table tbody tr");

    row.querySelector(".view-report").classList.remove("d-none");
    row.querySelector(".change-season").classList.remove("d-none");
    row.querySelectorAll(".view-fields").forEach((singleElement) => singleElement.classList.remove("d-none"));

    row.querySelector(".confirm-change-season").classList.add("d-none");
    row.querySelector(".cancel-change-season").classList.add("d-none");
    row.querySelectorAll(".input-fields").forEach((singleElement) => singleElement.classList.add("d-none"));
}

async function sendSeasonChange(){
    /* get the row and then change the
    * visibility of the buttons */
    const row = document.querySelector("table tbody tr");

    /* create the payload and get the fields */
    const payload = new URLSearchParams();
    payload.append('action', 'change_season');
    payload.append('year', parseInt(row.querySelector(".season-year").innerText))
    payload.append('quantity_towels', parseInt(row.querySelector("input[name='quantity_towels']").value));
    payload.append('price_towels', parseFloat(row.querySelector("input[name='price_towels']").value));
    payload.append('price_umbrella', parseFloat(row.querySelector("input[name='price_umbrella']").value));

    /* create POST request
    * to send to the same page */
    try{
        const response = await fetch(
            window.location.href,
            {
                method: 'POST',
                body: payload
            }
        )
    }catch(e){
        console.error(e);
    }
}

/* add the event listener to display
* form for adding a new season when it
* is requested */
document.getElementById("add_season").addEventListener("click", displayAddSeasonForm);

/* add event listeners for the buttons that are
* allowed to change the season */
document.querySelector(".change-season").addEventListener("click", changeToEditMode);
document.querySelector(".cancel-change-season").addEventListener("click", changeToViewMode);
