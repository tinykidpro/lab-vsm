// Biến toàn cục
let currentServer = '';
let cpuChart, networkChart;
let cpuData = [];
let networkData = [];
let labels = [];
let hackerBlocked = false;

// Hiển thị câu hỏi
function showQuestion() {
    const modal = document.getElementById('questionModal');
    modal.style.display = 'flex';
}

// Đóng modal câu hỏi
document.getElementById('questionCloseBtn').addEventListener('click', () => {
    document.getElementById('questionModal').style.display = 'none';
});

// Reset toàn bộ
function resetAll() {
    window.location.href = '/case1/index.php?reset=true';
}

// Hiển thị modal Firewall
document.getElementById('firewallDevice').addEventListener('click', () => {
    const modal = document.getElementById('firewallModal');
    modal.style.display = 'flex';
    showTab('dashboardTab');
    initializeCharts();
    loadSessions();
    startLogs(); // Gọi từ logs.js
});

// Đóng modal Firewall
document.getElementById('firewallCloseBtn').addEventListener('click', () => {
    document.getElementById('firewallModal').style.display = 'none';
});

// Hiển thị modal Terminal cho Web Server
document.getElementById('webServerDevice').addEventListener('click', () => {
    currentServer = 'web_server';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Web Server Terminal';
    updatePrompt();
    loadTerminalHistory();
});

// Hiển thị modal Terminal cho Database Server
document.getElementById('dbServerDevice').addEventListener('click', () => {
    currentServer = 'db_server';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Database Server Terminal';
    updatePrompt();
    loadTerminalHistory();
});

// Hiển thị modal Terminal cho Workstation 1
document.getElementById('workstation1Device').addEventListener('click', () => {
    currentServer = 'workstation_1';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Workstation 1 Terminal';
    updatePrompt();
    loadTerminalHistory();
});

// Hiển thị modal Terminal cho Workstation 2
document.getElementById('workstation2Device').addEventListener('click', () => {
    currentServer = 'workstation_2';
    const modal = document.getElementById('terminalModal');
    modal.style.display = 'flex';
    document.getElementById('terminalTitle').innerText = 'Workstation 2 Terminal';
    updatePrompt();
    loadTerminalHistory();
});

// Hiển thị modal DNS Server
document.getElementById('dnsServerDevice').addEventListener('click', () => {
    const modal = document.getElementById('dnsModal');
    modal.style.display = 'flex';
});

// Đóng modal DNS Server
document.getElementById('dnsCloseBtn').addEventListener('click', () => {
    document.getElementById('dnsModal').style.display = 'none';
});

// Đóng modal Terminal
document.getElementById('terminalCloseBtn').addEventListener('click', () => {
    document.getElementById('terminalModal').style.display = 'none';
});

// Đóng modal Kiểm tra Website
document.getElementById('websiteCheckCloseBtn').addEventListener('click', () => {
    document.getElementById('websiteCheckModal').style.display = 'none';
});

// Xử lý tab trong modal Firewall
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.tab-btn[data-target="${tabId}"]`).classList.add('active');
}

// Gán sự kiện click cho các tab
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.getAttribute('data-target');
        showTab(tabId);
    });
});

// Khởi tạo biểu đồ
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

    // Cập nhật biểu đồ mỗi giây
    setInterval(updateCharts, 1000);
}

// Cập nhật biểu đồ
function updateCharts() {
    // Kiểm tra xem hacker đã bị chặn chưa
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case1/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            hackerBlocked = response.hackerBlocked;

            // Cập nhật dữ liệu
            const now = new Date().toLocaleTimeString();
            labels.push(now);
            if (labels.length > 20) labels.shift(); // Giữ tối đa 20 điểm dữ liệu

            // Trước khi chặn: CPU và Network > 90%
            // Sau khi chặn: CPU và Network < 30%
            const cpuValue = hackerBlocked ? Math.random() * 10 + 20 : Math.random() * 10 + 90;
            const networkValue = hackerBlocked ? Math.random() * 10 + 20 : Math.random() * 10 + 90;

            cpuData.push(cpuValue);
            networkData.push(networkValue);

            if (cpuData.length > 20) cpuData.shift();
            if (networkData.length > 20) networkData.shift();

            // Cập nhật biểu đồ
            cpuChart.data.labels = labels;
            cpuChart.data.datasets[0].data = cpuData;
            cpuChart.update();

            networkChart.data.labels = labels;
            networkChart.data.datasets[0].data = networkData;
            networkChart.update();
        }
    };
    xhr.send('action=check_hacker_blocked');
}

// Load danh sách session
function loadSessions() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case1/process.php', true);
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

// Xử lý form thêm luật chặn
document.getElementById('ruleForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const ip = document.getElementById('blockIP').value;
    const reason = document.getElementById('blockReason').value;

    // Gửi yêu cầu AJAX để thêm rule
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case1/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById('ruleMessage').innerText = response.message;
            document.getElementById('scoreDisplay').innerText = response.score;
            location.reload(); // Tải lại trang để cập nhật danh sách rules
        } else {
            console.error('Error adding rule:', xhr.status, xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send(`action=block&ip=${ip}&reason=${reason}`);
});

// Xóa luật chặn
function deleteRule(index) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case1/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            location.reload(); // Tải lại trang để cập nhật danh sách rules
        } else {
            console.error('Error deleting rule:', xhr.status, xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send(`action=delete_rule&index=${index}`);
}

// Cập nhật prompt động dựa trên máy
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

// Load lịch sử terminal
function loadTerminalHistory() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case1/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const terminalContent = document.getElementById('terminalContent');
            // Thay thế \n bằng <br> để hiển thị đúng định dạng xuống dòng
            const formattedOutput = xhr.responseText.replace(/\n/g, '<br>');
            terminalContent.innerHTML = formattedOutput;
            // Cuộn xuống dưới cùng
            const terminalOutput = document.getElementById('terminalOutput');
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }
    };
    xhr.send(`action=load_terminal&server=${currentServer}`);
}

// Kiểm tra website
function checkWebsite(type) {
    const modal = document.getElementById('websiteCheckModal');
    const resultDiv = document.getElementById('websiteCheckResult');
    const messageDiv = document.getElementById('websiteCheckMessage');

    if (type === 'internal') {
        // Workstation 1 và 2 (IP nội bộ) luôn truy cập được
        resultDiv.innerHTML = '<img src="/case1/icons/website.png" alt="Website">';
        messageDiv.innerText = 'Tải website thành công';
        messageDiv.className = 'success';
        modal.style.display = 'flex';
    } else if (type === 'external' || type === 'hacker') {
        // External User và Hacker: Kiểm tra xem hacker đã bị chặn chưa
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/case1/process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.hackerBlocked) {
                    // Hacker đã bị chặn, hiển thị website
                    if (type === 'external') {
                        resultDiv.innerHTML = '<img src="/case1/icons/website.png" alt="Website">';
                        messageDiv.innerText = 'Tải website thành công';
                    } else {
                        resultDiv.innerHTML = '<img src="/case1/icons/check1.png" alt="Website">';
                        messageDiv.innerText = 'Website đã được phục hồi';
                    }
                    
                    messageDiv.className = 'success';
                } else {
                    // Hacker chưa bị chặn, hiển thị lỗi
                    if (type === 'external') {
                        resultDiv.innerHTML = '<img src="/case1/icons/website-e.png" alt="Website Error">';
                        messageDiv.innerText = 'Tải website thất bại';
                    } else {
                        resultDiv.innerHTML = '<img src="/case1/icons/check2.png" alt="Website Error">';
                        messageDiv.innerText = 'Đánh sập Website thành công';
                    }
                    
                    messageDiv.className = 'error';
                }
                modal.style.display = 'flex';
            }
        };
        xhr.send('action=check_hacker_blocked');
    }
}