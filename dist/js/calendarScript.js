const daysTag = document.querySelector(".days"),
    currentDate = document.querySelector(".current-date"),
    prevNextIcon = document.querySelectorAll(".icons span");

const isFromUser = window.location.href.includes('user');
var url = new URL(window.location.href);
var court_id = url.searchParams.get("court_id");
url.pathname = url.pathname.substring(0, url.pathname.lastIndexOf('/'));
var reservations = [];
var resDatesSched = [];
var dateAvailabilityArray = [];

// getting new date, current year and month
let date = new Date(),
    currYear = date.getFullYear(),
    currMonth = date.getMonth();
var completeRows = [];

// storing full name of all months in array
const months = ["January", "February", "March", "April", "May", "June", "July",
    "August", "September", "October", "November", "December"
];

const renderCalendar = () => {
    if (isFromUser) {
        getReservations();
    }
    //check if a get data court_id is set
    let firstDayofMonth = new Date(currYear, currMonth, 1).getDay(), // getting first day of month
        lastDateofMonth = new Date(currYear, currMonth + 1, 0).getDate(), // getting last date of month
        lastDayofMonth = new Date(currYear, currMonth, lastDateofMonth).getDay(), // getting last day of month
        lastDateofLastMonth = new Date(currYear, currMonth, 0).getDate(); // getting last date of previous month
    let liTag = "";

    for (let i = firstDayofMonth; i > 0; i--) { // creating li of previous month last days
        liTag += `<li class="calendar-date inactive">${lastDateofLastMonth - i + 1}</li>`;
    }

    for (let i = 1; i <= lastDateofMonth; i++) { // creating li of all days of current month
        // adding active class to li if the current day, month, and year matched
        if (i === new Date(date).getDate() && currMonth === new Date(date).getMonth() &&
            currYear === new Date(date).getFullYear()) {
            // liTag += `<li class="calendar-date active">${i}</li>`;
            continue;
        } else {
            liTag += `<li class="calendar-date">${i}</li>`;
        }

    }

    for (let i = lastDayofMonth; i < 6; i++) { // creating li of next month first days
        liTag += `<li class="calendar-date inactive">${i - lastDayofMonth + 1}</li>`
    }
    currentDate.innerText = `${months[currMonth]} ${currYear}`; // passing current mon and yr as currentDate text
    daysTag.innerHTML = liTag;
    document.querySelectorAll('.calendar-date').forEach(function(dateElement) {
        dateElement.addEventListener('click', function() {
            if (this.classList.contains('inactive')) {
                return;
            }

            // Remove 'active' class from all elements
            document.querySelectorAll('.calendar-date').forEach(function(dateElement) {
                dateElement.classList.remove('active');
            });

            // Add 'active' class to the clicked element

            this.classList.add('active');

            var selectedDay = this.textContent; // Get the selected day
            var selectedDate = new Date(currYear, currMonth, selectedDay); // Construct the full date

            // Format the date as a string
            var dateString = selectedDate.toLocaleDateString();

            date = dateString;

            // Alert the full date
            filterTableByDate();


        });
    });
    completeRows = document.querySelectorAll('#list tbody tr');
}

function formatDate() {
    var d = new Date(date),
        month = '' + d.toLocaleString('en-US', { month: 'short' }),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (day.length < 2)
        day = '0' + day;
    return month + ' ' + day + ', ' + year;
}

function filterTableByDate() {
    var formattedDate = formatDate();

    // Get the table body
    var tbody = document.querySelector('#list tbody');

    // Empty out the table
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }

    // Supply the table with completeRows
    completeRows.forEach(function(row) {
        tbody.appendChild(row);
    });

    // Get all the rows in the table
    var rows = Array.from(tbody.children);

    // Filter out rows that do not contain the court variable in the court name
    var filteredRows = rows.filter(function(row) {
        var courtName = row.cells[2].innerText.trim(); // Assuming the court name is in the second column
        return courtName.toLowerCase().includes(formattedDate.toLowerCase());
    });

    // Remove non-matching rows from the DOM
    rows.forEach(function(row) {
        if (!filteredRows.includes(row)) {
            row.remove();
        }
    });
}

function fetchReservations(date) {
    alert("asd");
    // Send an AJAX request to the server
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'calendar/index.php?fetch_reservations=true&date=' + date, true);
    xhr.onload = function() {
        if (this.status == 200) {
            // Update the table with the new data
            document.querySelector('#test').innerHTML = date;
        }
    };
    xhr.send();
}

renderCalendar();

prevNextIcon.forEach(icon => { // getting prev and next icons
    icon.addEventListener("click", () => { // adding click event on both icons
        // if clicked icon is previous icon then decrement current month by 1 else increment it by 1
        alert("clicked");
        currMonth = icon.id === "prev" ? currMonth - 1 : currMonth + 1;

        if (currMonth < 0 || currMonth > 11) { // if current month is less than 0 or greater than 11
            // creating a new date of current year & month and pass it as date value
            date = new Date(currYear, currMonth, new Date().getDate());
            currYear = date.getFullYear(); // updating current year with new date year
            currMonth = date.getMonth(); // updating current month with new date month
        } else {
            date = new Date(); // pass the current date as date value
        }
        renderCalendar(); // calling renderCalendar function
    });
});

function getReservations() {
    $.ajax({
        url: 'add/getReservations.php',
        type: 'get',
        data: { court_id: court_id },
        success: function(response) {
            reservations = JSON.parse(response);
            console.log("reservations");
            console.log(reservations);

            reservations.forEach(function(reservation) {
                var datetime_start = new Date(reservation.datetime_start);
                var date = datetime_start.toISOString().split('T')[0];

                if (!resDatesSched.hasOwnProperty(date)) {
                    resDatesSched[date] = reservation.hours;
                    dateAvailabilityArray[date] = true;
                } else {
                    resDatesSched[date] += reservation.hours;
                }
                //check if the date is a weekend
                var day = datetime_start.getDay();
                if (day === 0 || day === 6) {
                    if (resDatesSched[date] >= 5) {
                        dateAvailabilityArray[date] = false;
                    }
                } else {
                    if (resDatesSched[date] >= 12) {
                        dateAvailabilityArray[date] = false;
                    }
                }
            });

        }
    });

}