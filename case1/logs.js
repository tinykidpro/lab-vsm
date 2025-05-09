const logArea = document.getElementById('logArea');
const hackerIp = '203.0.113.5';
const normalIps = [
    '198.51.100.1', '198.51.100.2', '198.51.100.3', '198.51.100.4', '198.51.100.5',
    '198.51.100.6', '198.51.100.7', '198.51.100.8', '198.51.100.9', '198.51.100.10',
    '203.0.113.2', '203.0.113.3', '203.0.113.4', '203.0.113.6', '203.0.113.7',
    '203.0.113.8', '203.0.113.9', '203.0.113.10', '203.0.113.11', '203.0.113.12',
    '172.16.254.1', '172.16.254.2', '172.16.254.3', '172.16.254.4', '172.16.254.5',
    '172.16.254.6', '172.16.254.7', '172.16.254.8', '172.16.254.9', '172.16.254.10'
];

const logTypes = [
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: Math.random() > 0.5 ? 80 : 443,
        message: 'Connection established'
    }),
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: 80,
        message: 'Error 404: Requested /nonexistent'
    }),
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: 80,
        message: 'Error 403: Access denied to /admin'
    }),
    () => ({
        sourceIp: '192.168.1.10',
        destination: '192.168.1.11',
        port: 3306,
        message: 'MySQL connection established'
    }),
    () => ({
        sourceIp: hackerIp,
        destination: '192.168.1.10',
        port: 80,
        message: 'Sending SYN'
    })
];

function startLogs() {
    setInterval(() => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/case1/process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const hackerBlocked = response.hackerBlocked;
                let logEntry;

                if (!hackerBlocked) {
                    const logType = Math.random();
                    if (logType < 0.4) {
                        // 40% khả năng là log bình thường hoặc lỗi 404/403
                        const normalLogType = Math.random();
                        if (normalLogType < 0.3) {
                            logEntry = logTypes[0]();
                        } else if (normalLogType < 0.6) {
                            logEntry = logTypes[1]();
                        } else if (normalLogType < 0.9) {
                            logEntry = logTypes[2]();
                        } else {
                            logEntry = logTypes[3](); // MySQL connection
                        }
                    } else {
                        // 60% khả năng là SYN flood từ hacker
                        logEntry = logTypes[4]();
                    }
                } else {
                    const normalLogType = Math.random();
                    if (normalLogType < 0.4) {
                        logEntry = logTypes[0]();
                    } else if (normalLogType < 0.6) {
                        logEntry = logTypes[1]();
                    } else if (normalLogType < 0.8) {
                        logEntry = logTypes[2]();
                    } else {
                        logEntry = logTypes[3](); // MySQL connection
                    }
                }

                const timestamp = new Date().toLocaleTimeString();
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${timestamp}</td>
                    <td>${logEntry.sourceIp}</td>
                    <td>${logEntry.destination}</td>
                    <td>${logEntry.port}</td>
                    <td>${logEntry.message}</td>
                `;
                logArea.insertBefore(row, logArea.firstChild);

                // Giữ tối đa 50 dòng log
                while (logArea.children.length > 50) {
                    logArea.removeChild(logArea.lastChild);
                }
            }
        };
        xhr.send('action=check_hacker_blocked');
    }, 1000);
}