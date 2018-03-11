let searchTable = () => {
    let searchInput = document.getElementById('search-input');
    let searchText = searchInput.value.toLowerCase();
    let tr = document.getElementsByTagName('tr');
    for(let cnt = 0, numTr = tr.length; cnt < numTr; cnt++) {
        locationTd = tr[cnt].getElementsByTagName('td')[0];
        if(locationTd) {
            if(locationTd.innerHTML.toLowerCase().replace(/[ \"\'.,\/#!$%\^&\*;:{}=\-_`~()]/g, '').indexOf(searchText) > -1) {
                tr[cnt].style.display = '';
            } else {
                tr[cnt].style.display = 'none';
            }
        }
    }
}