// Function to handle search input
function handleSearchInput() {
    const input = document.getElementById('search-input').value;
    const tbody = document.getElementById('table-body');
    const table = document.getElementById('search-table');

    if (input === '') {
        table.style.display = 'none';
    }

    // Create AJAX request
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if ( xhr.responseText.length > 4 ) {

                data = JSON.parse(xhr.responseText);

                if ( data.count > 0 )
                    table.style.display = 'table';
                else
                    table.style.display = 'none';

                let html = '';
                for (let i = 0; i < data.count; i++) {
                    html += `<tr><td>${data[i].givenname[0]}</td><td>${data[i].sn[0]}</td><td>${data[i].uid[0]}</td><td>${data[i].mail[0]}</td><td><a href="?module=users&action=edit&object=${data[i].uid[0]}">edit</a></td></tr>`;
                }
                tbody.innerHTML = html;
            }
        }
    };
    
    // Send search query to backend
    xhr.open('GET', `search.php?q=${input}`, true);
    xhr.send();
  }
  
  // Add event listener to the search input
  document.getElementById('search-input').addEventListener('input', handleSearchInput);
  