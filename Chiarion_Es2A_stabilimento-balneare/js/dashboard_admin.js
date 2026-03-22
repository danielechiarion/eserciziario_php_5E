/**
 * Function to display a form for adding
 * a new season
 */
function displayAddSeasonForm(){
    /* just bring visibility to the form */
    document.getElementById("add-season_form").classList.remove("d-none");
}

/* add the event listener to display
* form for adding a new season when it
* is requested */
document.getElementById("add_season").addEventListener("click", displayAddSeasonForm);
