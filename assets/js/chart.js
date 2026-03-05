function renderChart(miskin, rentan, sejahtera){
    new Chart(document.getElementById('chartKemiskinan'), {
        type: 'pie',
        data: {
            labels: ['Miskin','Rentan','Sejahtera'],
            datasets: [{
                data: [miskin, rentan, sejahtera]
            }]
        }
    });
}