function ToggleDisplay(strId) {
    var objId = document.getElementById(strId);
    
    if(objId !== undefined) {
        if(objId.style.display == "none") {
            objId.style.display = "block";
        }
        else {
            objId.style.display = "none";
        }
    }
    else {
        alert("No element with ID of " + strId + " exists.");
    }
}

function toggleDisplay(el,elMsg) {
    if(el.style.display == "none") {
        el.style.display = "block";
        elMsg.innerHTML = "click to hide";
    }
    else if(el.style.display == "block") {
        el.style.display = "none";
        elMsg.innerHTML = "click to show";
    }
}