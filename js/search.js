let searchTable = () => {
    let searchOpenOnlyInput = document.getElementById('search-open-only');
    let openOnly = searchOpenOnlyInput.checked;

    let includeClosingSoonInput = document.getElementById('search-include-closing-soon');
    let includeClosingSoon = includeClosingSoonInput.checked;

    let searchInput = document.getElementById('search-input');
    let searchText = searchInput.value.toLowerCase().replace(/[ \"\'.,\/#!$%\^&\*;:{}=\-_`~()]/g, '');
    let tr = document.getElementsByTagName('tr');
    for(let cnt = 0, numTr = tr.length; cnt < numTr; cnt++) {
        locationTd = tr[cnt].getElementsByTagName('td')[0];
        if(locationTd) {
            let locationOpen = (tr[cnt].className.indexOf('table-success') > -1) || (tr[cnt].className.indexOf('table-warning') > -1);
            let locationClosingSoon = tr[cnt].className.indexOf('table-warning') > -1;
            let searchTextInTitle = (locationTd.innerHTML.toLowerCase().replace(/[ \"\'.,\/#!$%\^&\*;:{}=\-_`~()]/g, '').indexOf(searchText) > -1);
            let searchTextInTag = (locationTd.dataset.alt.toLowerCase().replace(' ','').indexOf(searchText) > -1);

            let displayValue = '';
            if((openOnly && !locationOpen) || (!includeClosingSoon && locationClosingSoon) || (!searchTextInTitle && !searchTextInTag)) {
                displayValue = 'none';
            }
            tr[cnt].style.display = displayValue;
        }
    }
}

let toggleChildInput = (e) => {
    let childInput = e.getElementsByTagName('input')[0];
    if(childInput.dataset.locked == "true") {
        childInput.dataset.locked = "false";
    } else {
        childInput.checked = !childInput.checked;
    }
    searchTable();
}

let ensureOneToggle = (e) => {
    e.dataset.locked = "true";
}


let makeTypingSearch = () => {
    window.onkeydown = function(e) {
        if(document.activeElement.id != 'search-input') {
            let searchInput = document.getElementById('search-input');
            searchInput.value = "";
            searchInput.focus();
        }
    }
}

let initialize = () => {
    searchTable();
    makeTypingSearch();
}
