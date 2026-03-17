/* function to pass to edit mode */
function changeToEditMode(row){
    /* first hide the button for the change,
    * because it has already been selected */
    row.querySelector(".btn .change-vehicle").classList.add("d-none");
    /* then delete also the one to delete the vehicles,
    * because we don't need it */
    row.querySelector(".btn .delete-vehicle").classList.add("d-none");

    /* then make the confirmation and cancel buttons
    for the change appear, because they're needed
    in this part of the operation */
    row.querySelector(".btn .confirm-change-vehicle").classList.remove("d-none");
    row.querySelector(".btn .cancel-change-vehicle").classList.remove("d-none");

    /* hide the view fields and show
    * the editable fields */
    row.querySelector(".view-fields").forEach(
        singleField => singleField.classList.add("d-none")
    );
    row.querySelector(".editable-fields").forEach(
        singleField => singleField.classList.remove("d-none")
    );
}

/* function to change to edit mode */
function changeToViewMode(row){
    /* make the change and delete button
    * appear again */
    row.querySelector(".btn .change-vehicle").classList.remove("d-none");
    row.querySelector(".btn .delete-vehicle").classList.remove("d-none");

    /* make the confirmation and the cancel button
    * of the change disappear because we have just exited
    * the change mode */
    row.querySelector(".btn .confirm-change-vehicle").classList.add("d-none");
    row.querySelector(".btn .cancel-change-vehicle").classList.add("d-none");

    /* hide the editable fields and bring back
    * the viewer fields */
    row.querySelector(".view-fields").forEach(
        singleField => singleField.classList.remove("d-none")
    );
    row.querySelector(".editable-fields").forEach(
        singleField => singleField.classList.add("d-none")
    );
}

/**
 * Function to use while the button for change car is pressed
 * This allows the user to have the right buttons at the right time
 * @param index {int} positional index of the car inside the table
 */
function changeCarPress(index){
    /* select the precise section of the table */
    const row = document.querySelectorAll("table tbody .tr")[index];

    changeToEditMode(row); // just change to edit mode
}

/**
 * Function to cancel the changes
 * made by the user editing the car,
 * coming back to the viewer mode
 * @param index {int} positional index of the car inside the table
 */
function changeCarCancel(index){
    /* select the precise section of the table */
    const row = document.querySelectorAll("table tbody .tr")[index];

    changeToViewMode(row); // just change to view mode
}

/**
 * Function of the car to send the POST
 * request after the change of the roe
 * @param index {int} position of the car inside the table
 */
async function changeCarConfirm(index){
    /* select the precise section of the table */
    const row = document.querySelectorAll("table tbody .tr")[index];
    /* upload all the data to make the request */
    const payload = new URLSearchParams();
    payload.append('action', 'change_car');
    payload.append('index', index);
    payload.append('marca', row.querySelector('input[name="marca"]').value)
    payload.append('marca', row.querySelector('input[name="modello"]').value)
    payload.append('marca', parseInt(row.querySelector('input[name="cilidrata"]').value))
    payload.append('marca', parseInt(row.querySelector('input[name="poteza"]').value))
    payload.append('marca', parseInt(row.querySelector('input[name="lunghezza"]').value))
    payload.append('marca', parseInt(row.querySelector('input[name="larghezza"]').value))

    try{
        const response = await fetch(
            window.location.href,
            {
                method:'POST',
                body:payload
            }
        );
    }catch(e){
        console.error(e);
    }

    /* then come back to the viewer mode */
    changeToViewMode(row);
}

/**
 * Function to send the request to delete the car
 * @param index position of the car inside the table
 */
async function deleteCar(index){
    /* make the data which will
    * contain the action and the index of the car */
    const payload = new URLSearchParams();
    payload.append('action', 'change_car');
    payload.append('index', index);

    try{
        const response = await fetch(
            window.location.href,
            {
                method:'POST',
                body:payload
            }
        );
    }catch(e){
        console.error(e);
    }

    /* then turn back to the viewer mode */
    const row = document.querySelectorAll("table tbody .tr")[index];
    changeToViewMode(row);
}

/* add final the event listeners
* for each button */
document.querySelectorAll(".btn .confirm-change-vehicle").forEach(
    (singleButton, index) => singleButton.addEventListener("click", async function(){
        await changeCarConfirm(index);
    })
);
document.querySelectorAll(".btn .change-vehicle").forEach(
    (singleButton, index) => singleButton.addEventListener("click", function(){
        changeCarPress(index);
    })
);
document.querySelectorAll(".btn .cancel-change-vehicle").forEach(
    (singleButton, index) => singleButton.addEventListener("click", function(){
        changeCarCancel(index);
    })
);
document.querySelectorAll(".btn .delete-vehicle").forEach(
    (singleButton, index) => singleButton.addEventListener("click", async function(){
        await deleteCar(index);
    })
);