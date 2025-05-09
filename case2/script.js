function updateCharts() {
    const now = new Date().toLocaleTimeString();
    labels.push(now);
    if (labels.length > 20) labels.shift();

    // Giữ CPU và Network Usage luôn dưới 40%
    const cpuValue = Math.random() * 20 + 10; // 10-30%
    const networkValue = Math.random() * 20 + 10; // 10-30%

    cpuData.push(cpuValue);
    networkData.push(networkValue);

    if (cpuData.length > 20) cpuData.shift();
    if (networkData.length > 20) networkData.shift();

    cpuChart.data.labels = labels;
    cpuChart.data.datasets[0].data = cpuData;
    cpuChart.update();

    networkChart.data.labels = labels;
    networkChart.data.datasets[0].data = networkData;
    networkChart.update();
}