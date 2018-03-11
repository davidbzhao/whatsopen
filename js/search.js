let searchTable = () => {
    let searchOpenOnlyInput = document.getElementById('search-open-only');
    let openOnly = searchOpenOnlyInput.checked;

    let includeClosingSoonInput = document.getElementById('search-include-closing-soon');
    let includeClosingSoon = includeClosingSoonInput.checked;

    let searchInput = document.getElementById('search-input');
    let searchText = searchInput.value.toLowerCase();
    let tr = document.getElementsByTagName('tr');
    for(let cnt = 0, numTr = tr.length; cnt < numTr; cnt++) {
        locationTd = tr[cnt].getElementsByTagName('td')[0];
        if(locationTd) {
            if(locationTd.innerHTML.toLowerCase().replace(/[ \"\'.,\/#!$%\^&\*;:{}=\-_`~()]/g, '').indexOf(searchText) > -1 &&
                    ((!openOnly) || (openOnly && tr[cnt].className.indexOf('table-success') > -1)) && 
                    ((includeClosingSoon) || (!includeClosingSoon && tr[cnt].className.indexOf('table-warning') == -1))) {
                tr[cnt].style.display = '';
            } else {
                tr[cnt].style.display = 'none';
            }
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


searchTable();
