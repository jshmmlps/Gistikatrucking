$(document).ready(function () {
    console.log('Profile Page Loaded');
    // Add any interactive JavaScript functionality here.
});

// Pie Chart For Dashbaord
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: ['Good Condition', 'Requires Maintenance', 'Critical Condition'],
        datasets: [{
            data: [55.3, 23.1, 21.6],
            backgroundColor: ['#4caf50', '#ffc107', '#f44336']
        }]
    }
});

// Bar Chart
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: ['NBD 1234', 'ABM 9876', 'TQR 4567', 'GXL 3210', 'JPA 8542', 'LYN 5087', 'KWD 7485'],
        datasets: [{
            label: 'Days Until Maintenance',
            data: [3, 7, 2, 10, 4, 5, 8],
            backgroundColor: '#007bff'
        }]
    }
});
