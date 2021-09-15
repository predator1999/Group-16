<!DOCTYPE html>
<html lang="en" id="id1">

<head>
    <title>Donation Report</title>
    <link rel="stylesheet" href="/public/assets/newstyles.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/c119b7fc61.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
</head>

<style>
canvas {
    display: -webkit-inline-box;
    width: 100%;
}

a,
b {
    color: #16c79a;
}

h1,
h3 {
    text-align: center;
}

table {
    width: 100%;
    background: #e2dfdf;
    text-align: center;
    margin: 20px;
}

table,
th,
td {

    border-collapse: collapse;

}

th,
td {
    border-bottom: 1px solid #fff;
    padding: 8px;
}

.container {
    width: 80%;
    max-width: none;
}

.date-time-container {
    display: flex;
    font-size: 12px;
}
</style>

<?php 
if(!isset($moderator)) $moderator= false;
if(!isset($treasurer)) $treasurer= false;
$organization = $admin =$registered_user = $guest_user = false;

if(isset($_SESSION ["user"] ["user_type"])){
    if($_SESSION ["user"] ["user_type"] == "organization"){
        $organization = true;
    }
    
    if($_SESSION ["user"] ["user_type"] == "admin"){
        $admin = true;
    }

    if($_SESSION ["user"] ["user_type"] == "registered_user"){
        $registered_user = true;
    }
    
}else{
    $guest_user= true;
}
?>


<body>
    <header class="header">
        <div style="width: 20%;"><a class=" logo ">
                <img src="/Public/assets/visal logo.png ">
            </a>
        </div>

        <div style="width: 60%; text-align:center">
            <p style="color: #16C79A;">Report generated by <a href="/user/home"><b>Community
                        Retreat</b> </a></p>
        </div>

        <div class="date-time-container" style="width: 20%;">
            <p>Date and Time: <span id='date-time'></span>.</p>
            <br><br>
        </div>
    </header>
    <div class="container">
        <div class="container">
            <h1><?= $event_name ?></h1>
            <h3>Donation Report</h3>
            <div class="center" style="text-align: center;">
                <canvas id="myChart"></canvas>
            </div>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Donor</th>
                    <th>Amount</th>
                    <th>Contact no</th>
                </tr>
                <?php foreach($donations as $donation) { ?>
                <tr>
                    <td><?= $donation["date"] ?></td>
                    <td><?= $donation["username"] ?></td>
                    <td><?= $donation["amount"] ?></td>
                    <td><?= $donation["contact_no"] ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</body>

<script>
const data = <?= $donations_graph ?>;
console.log(data);
const backgroundColor = ['#6F69AC', '#FEC260', '#93B5C6', '#FA8072']
const borderColor = ['#6F69AC80', '#FEC26080', '#93B5C680', '#FA807280']

let keys = [];
let amounts = [];
for (const event in data) {
    keys.push(data[event]["day"]);
    amounts.push(data[event]["donation_sum"]);
}


var myLineChart = new Chart('myChart', {
    type: 'line',
    data: {
        labels: keys,
        datasets: [{
            label: 'Donations',
            data: amounts,
            backgroundColor: backgroundColor[0],
            borderColor: borderColor[0],
            fill: false
        }]

    },
    options: {
        responsive: true,
        scales: {
            yAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: 'Amount',
                },
            }],
            xAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: 'Date',
                },
            }]
        }
    }
});
</script>
<script>
var dt = new Date();
document.getElementById('date-time').innerHTML = dt;
</script>