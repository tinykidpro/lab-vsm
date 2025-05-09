let currentServer = '';
let cpuChart, networkChart;
let cpuData = [];
let networkData = [];
let labels = [];
let hackerBlocked = false;

function showQuestion() {
    const modal = document.getElementById('questionModal');
    modal.style.display = 'flex';
}

document.getElementById('questionCloseBtn').addEventListener('click', () => {
    document.getElementById('questionModal').style.display = 'none';
});

function resetAll() {
    window.location.href = '/case2/index.php?reset=true';
}

document.getElementById('firewallDevice').addEventListener('click', () => {
    const modal = document.getElementById('firewallModal');
    modal.style.display = 'flex';
    showTab('dashboardTab');
    initializeCharts();
    loadSessions();
    startLogs();
});

document.getElementById('firewallCloseBtn').addEventListener('click', () => {
    document.getElementById('firewallModal').style.display = 'none';
});

document.getElementById('webServerDevice').addEventListener('click', () => {
    currentServer = 'web_server';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Web Server Terminal';
    showTab('terminalTab');
    updatePrompt();
    loadTerminalHistory();
});

document.getElementById('dbServerDevice').addEventListener('click', () => {
    currentServer = 'db_server';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Database Server Terminal';
    showTab('terminalTab');
    updatePrompt();
    loadTerminalHistory();
});

document.getElementById('workstation1Device').addEventListener('click', () => {
    currentServer = 'workstation_1';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Workstation 1 Terminal';
    showTab('terminalTab');
    updatePrompt();
    loadTerminalHistory();
});

document.getElementById('workstation2Device').addEventListener('click', () => {
    currentServer = 'workstation_2';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Workstation 2 Terminal';
    showTab('terminalTab');
    updatePrompt();
    loadTerminalHistory();
});

document.getElementById('dnsServerDevice').addEventListener('click', () => {
    const modal = document.getElementById('dnsModal');
    modal.style.display = 'flex';
});

document.getElementById('dnsCloseBtn').addEventListener('click', () => {
    document.getElementById('dnsModal').style.display = 'none';
});

document.getElementById('terminalCloseBtn').addEventListener('click', () => {
    document.getElementById('terminalModal').style.display = 'none';
});

document.getElementById('websiteCheckCloseBtn').addEventListener('click', () => {
    document.getElementById('websiteCheckModal').style.display = 'none';
});

function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.tab-btn[data-target="${tabId}"]`).classList.add('active');

    if (tabId === 'logsTab' && (currentServer === 'web_server' || currentServer === 'db_server')) {
        loadServerLogs();
    }
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.getAttribute('data-target');
        showTab(tabId);
    });
});

function initializeCharts() {
    const cpuCtx = document.getElementById('cpuChart').getContext('2d');
    const networkCtx = document.getElementById('networkChart').getContext('2d');

    cpuChart = new Chart(cpuCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'CPU Usage (%)',
                data: cpuData,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    networkChart = new Chart(networkCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Network Usage (%)',
                data: networkData,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    setInterval(updateCharts, 1000);
}

function updateCharts() {
    const now = new Date().toLocaleTimeString();
    labels.push(now);
    if (labels.length > 20) labels.shift();

    const cpuValue = Math.random() * 20 + 20; // Luôn dưới 40%
    const networkValue = Math.random() * 20 + 20; // Luôn dưới 40%

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

function loadSessions() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const sessions = JSON.parse(xhr.responseText);
            const sessionList = document.getElementById('sessionList');
            sessionList.innerHTML = '';
            sessions.forEach(session => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${session.sourceIp}</td>
                    <td>${session.destination}</td>
                    <td>${session.port}</td>
                `;
                sessionList.appendChild(row);
            });
        }
    };
    xhr.send('action=get_sessions');
}

document.getElementById('ruleForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const ip = document.getElementById('blockIP').value;
    const reason = document.getElementById('blockReason').value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById('ruleMessage').innerText = response.message;
            document.getElementById('scoreDisplay').innerText = response.score;
            location.reload();
        }
    };
    xhr.send(`action=block&ip=${ip}&reason=${reason}`);
});

function deleteRule(index) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            location.reload();
        }
    };
    xhr.send(`action=delete_rule&index=${index}`);
}

function deleteNatRule(service) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById(service + 'NatRule').remove();
            const response = JSON.parse(xhr.responseText);
            document.getElementById('scoreDisplay').innerText = response.score;
        }
    };
    xhr.send(`action=delete_nat_rule&service=${service}`);
}

function deleteRoutingRule(rule) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById(rule + 'Rule').remove();
        }
    };
    xhr.send(`action=delete_routing_rule&rule=${rule}`);
}

function updatePrompt() {
    let host = '';
    switch (currentServer) {
        case 'web_server':
            host = 'webserver';
            break;
        case 'db_server':
            host = 'dbserver';
            break;
        case 'workstation_1':
            host = 'workstation1';
            break;
        case 'workstation_2':
            host = 'workstation2';
            break;
        default:
            host = 'server';
    }
    const promptElements = document.querySelectorAll('.prompt');
    promptElements.forEach(element => {
        element.innerText = `admin@${host}:~$ `;
    });
}

function loadTerminalHistory() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const terminalContent = document.getElementById('terminalContent');
            const formattedOutput = xhr.responseText.replace(/\n/g, '<br>');
            terminalContent.innerHTML = formattedOutput;
            const terminalOutput = document.getElementById('terminalOutput');
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }
    };
    xhr.send(`action=load_terminal&server=${currentServer}`);
}

function loadServerLogs() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const serverLogs = document.getElementById('serverLogs');
            const formattedOutput = xhr.responseText.replace(/\n/g, '<br>');
            serverLogs.innerHTML = formattedOutput;
            serverLogs.scrollTop = serverLogs.scrollHeight;
        }
    };
    xhr.send(`action=load_server_logs&server=${currentServer}`);
}

function checkWebsite(type) {
    const modal = document.getElementById('websiteCheckModal');
    const resultDiv = document.getElementById('websiteCheckResult');
    const messageDiv = document.getElementById('websiteCheckMessage');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.hackerBlocked) {
                if (type === 'external' || type === 'internal') {
                    resultDiv.innerHTML = '<img src="/case2/icons/website.png" alt="Website">';
                    messageDiv.innerText = 'Tải website thành công';
                } else {
                    resultDiv.innerHTML = '<img src="/case2/icons/check1.png" alt="Website">';
                    messageDiv.innerText = 'Website đã được phục hồi';
                }
                messageDiv.className = 'success';
            } else {
                if (type === 'external' || type === 'internal') {
                    resultDiv.innerHTML = '<img src="/case2/icons/website-e.png" alt="Website Error">';
                    messageDiv.innerText = 'Error establishing a database connection';
                } else {
                    resultDiv.innerHTML = '<img src="/case2/icons/check2.png" alt="Website Error">';
                    messageDiv.innerText = 'Tấn công thành công';
                }
                messageDiv.className = 'error';
            }
            modal.style.display = 'flex';
        }
    };
    xhr.send('action=check_hacker_blocked');
}