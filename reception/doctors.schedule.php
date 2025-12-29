<?php
include 'include/config.php'; // your MySQLi connection


// Handle AJAX roster submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_roster'){
    $month = $_POST['month']; // YYYY-MM
    $roster = json_decode($_POST['roster'], true); // decoded roster array

    foreach($roster as $doctor_id => $shifts){
        foreach($shifts as $day => $shift_type){
            if($shift_type != ''){
                $shift_date = "$month-$day"; // YYYY-MM-DD
                // Insert or update using ON DUPLICATE KEY
                $stmt = $con->prepare("INSERT INTO duty_roster (doctor_id, shift_date, shift_type) 
                                       VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE shift_type=?");
                $stmt->bind_param("isss", $doctor_id, $shift_date, $shift_type, $shift_type);
                $stmt->execute();
            }
        }
    }
    echo json_encode(['status'=>'success']);
    exit;
}

// Fetch active doctors
$doctors = [];
$result = mysqli_query($con, "SELECT id, first_name, last_name FROM doctor WHERE status='Active'");
while($row = mysqli_fetch_assoc($result)){
    $doctors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctors Duty Roster</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<style>
body { font-family: Arial; margin:10px; background:#f7f9fc; font-size:13px; }
h1 { text-align:center; color:#007bff; font-size:20px; margin-bottom:10px; }
#controls { text-align:center; margin-bottom:10px; }
#controls label, #controls input, #controls button { font-size:13px; }
#rosterContainer { overflow-x:auto; }
table { width:100%; border-collapse:collapse; min-width:600px; }
th, td { border:1px solid #ddd; text-align:center; padding:2px 3px; min-width:50px; }
th { background:#007bff; color:#fff; position:sticky; top:0; z-index:1; }
td select { width:100%; padding:2px; font-size:12px; border-radius:3px; border:1px solid #ccc; box-sizing:border-box; height:25px; }
.shift-morning { background:#d1e7dd; }
.shift-evening { background:#fff3cd; }
.shift-night { background:#f8d7da; }
button.save-btn { margin-top:10px; padding:5px 10px; background:#007bff; color:white; border:none; border-radius:3px; cursor:pointer; font-size:13px; }
@media(max-width:768px){ table, thead, tbody, th, td, tr{ display:block; } th{ text-align:left; } td{ margin-bottom:5px; } }
@media print {
  body { font-size:10px; }
  table { page-break-inside:auto; }
  tr { page-break-inside:avoid; page-break-after:auto; }
}

</style>
</head>
<body>
<h1>Doctor Duty Roster</h1>
<div id="controls">
<label for="monthPicker">Select Month:</label>
<input type="month" id="monthPicker">
<button onclick="generateRoster()">Generate Roster</button>
</div>

<div id="rosterContainer">
<table id="rosterTable"><thead></thead><tbody></tbody></table>
</div>
<div style="text-align:center;">
<button class="save-btn" onclick="saveRoster()">Save Roster</button>
</div>
<button class="save-btn" onclick="printRoster()">Print Roster</button>


<script>
const doctors = <?php echo json_encode($doctors); ?>;
let selectedMonthYear = "";
const weekdayNames = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];

function generateRoster(){
    const monthInput = document.getElementById('monthPicker').value;
    if(!monthInput){ alert("Please select a month!"); return; }
    selectedMonthYear = monthInput;
    const [year, month] = monthInput.split('-').map(Number);
    const daysInMonth = new Date(year, month, 0).getDate();

    const table = document.getElementById('rosterTable');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    thead.innerHTML = '';
    tbody.innerHTML = '';

    // Header
    const headerRow = document.createElement('tr');
    let th = document.createElement('th');
    th.innerText = 'Doctor Name';
    headerRow.appendChild(th);
    for(let d=1; d<=daysInMonth; d++){
        const dateObj = new Date(year, month-1, d);
        let th = document.createElement('th');
        th.innerText = `${weekdayNames[dateObj.getDay()]} ${d}`;
        headerRow.appendChild(th);
    }
    thead.appendChild(headerRow);

    // Rows
    doctors.forEach(doc=>{
        let row = document.createElement('tr');
        let tdName = document.createElement('td');
        tdName.innerText = doc.first_name + " " + doc.last_name;
        tdName.dataset.id = doc.id;
        row.appendChild(tdName);

        for(let d=1; d<=daysInMonth; d++){
            let td = document.createElement('td');
            let select = document.createElement('select');
            select.innerHTML = `<option value="">--</option>
                                <option value="Morning">Morning</option>
                                <option value="Evening">Evening</option>
                                <option value="Night">Night</option>
                                <option value="Off">Off</option>`;
            select.dataset.day = d;
            select.addEventListener('change', function(){ 
                td.className = 'shift-' + select.value.toLowerCase(); 
            });
            td.appendChild(select);
            row.appendChild(td);
        }
        tbody.appendChild(row);
    });
}

function saveRoster(){
    if(!selectedMonthYear){ alert("Generate roster first!"); return; }
    const table = document.getElementById('rosterTable');
    const tbody = table.querySelector('tbody');
    const roster = {};

    for(let i=0;i<tbody.rows.length;i++){
        const row = tbody.rows[i];
        const doctor_id = row.cells[0].dataset.id;
        roster[doctor_id] = {};
        for(let j=1;j<row.cells.length;j++){
            const select = row.cells[j].firstChild;
            roster[doctor_id][select.dataset.day] = select.value;
        }
    }

    const formData = new FormData();
    formData.append('action','save_roster');
    formData.append('month', selectedMonthYear);
    formData.append('roster', JSON.stringify(roster));

    fetch('', {method:'POST', body:formData})
    .then(res=>res.json())
    .then(data=>alert(data.status))
    .catch(err=>alert('Error saving roster'));
}
function printRoster() {
    if(!selectedMonthYear){ alert("Generate roster first!"); return; }

    const table = document.getElementById('rosterTable').outerHTML;
    const style = `
        <style>
        body { font-family: Arial; font-size:12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border:1px solid #000; padding:4px; text-align:center; }
        th { background: #007bff; color: #fff; }
        .shift-morning { background:#d1e7dd; }
        .shift-evening { background:#fff3cd; }
        .shift-night { background:#f8d7da; }
        </style>
    `;
    
    const printWindow = window.open('', '', 'height=800,width=1200');
    printWindow.document.write('<html><head><title>Print Doctor Duty Roster</title>');
    printWindow.document.write(style);
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Doctor Duty Roster - ' + selectedMonthYear + '</h2>');
    printWindow.document.write(table);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

</script>
</body>
</html>
