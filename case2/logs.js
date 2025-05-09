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
    // Log HTTP 200 OK
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: Math.random() > 0.5 ? 80 : 443,
        message: 'HTTP 200 OK'
    }),
    // Log HTTPS Connection
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: 443,
        message: 'HTTPS connection established'
    }),
    // Log DNS Query
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.12',
        port: 53,
        message: 'DNS query processed'
    }),
    // Log NTP Sync
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.12',
        port: 123,
        message: 'NTP time synchronization'
    }),
    // Log FTP Connection
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.21',
        port: 21,
        message: 'FTP connection established'
    }),
    // Log SMB Connection
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.20',
        port: 445,
        message: 'SMB connection established'
    }),
    // Log ICMP Echo Reply
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.20',
        port: 0,
        message: 'ICMP echo reply'
    }),
    // Log SSH từ IP nội bộ
    () => ({
        sourceIp: '192.168.1.20',
        destination: '192.168.1.11',
        port: 22,
        message: 'SSH connection established'
    }),
    // Log SSH từ hacker
    () => ({
        sourceIp: hackerIp,
        destination: '192.168.1.11',
        port: 22,
        message: 'SSH connection established'
    })
];

function startLogs() {
    setInterval(() => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/case2/process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const hackerBlocked = response.hackerBlocked;
                let logEntry;

                    if (!hackerBlocked) {
                        const logType = Math.random();
                        if (logType < 0.2) {
                            // 20% khả năng là log HTTP 200 OK
                            logEntry = logTypes[0]();
                        } else if (logType < 0.35) {
                            // 15% khả năng là log HTTPS Connection
                            logEntry = logTypes[1]();
                        } else if (logType < 0.5) {
                            // 15% khả năng là log DNS Query
                            logEntry = logTypes[2]();
                        } else if (logType < 0.65) {
                            // 15% khả năng là log NTP Sync
                            logEntry = logTypes[3]();
                        } else if (logType < 0.8) {
                            // 15% khả năng là log FTP Connection
                            logEntry = logTypes[4]();
                        } else if (logType < 0.9) {
                            // 10% khả năng là log SMB Connection
                            logEntry = logTypes[5]();
                        } else if (logType < 0.95) {
                            // 5% khả năng là log ICMP Echo Reply
                            logEntry = logTypes[6]();
                        } else if (logType < 0.975) {
                            // 2.5% khả năng là SSH từ IP nội bộ
                            logEntry = logTypes[7]();
                        } else {
                            // 2.5% khả năng là SSH từ hacker
                            logEntry = logTypes[8]();
                        }
                    } else {
                        const logType = Math.random();
                        if (logType < 0.25) {
                            // 25% khả năng là log HTTP 200 OK
                            logEntry = logTypes[0]();
                        } else if (logType < 0.45) {
                            // 20% khả năng là log HTTPS Connection
                            logEntry = logTypes[1]();
                        } else if (logType < 0.6) {
                            // 15% khả năng là log DNS Query
                            logEntry = logTypes[2]();
                        } else if (logType < 0.75) {
                            // 15% khả năng là log NTP Sync
                            logEntry = logTypes[3]();
                        } else if (logType < 0.9) {
                            // 15% khả năng là log FTP Connection
                            logEntry = logTypes[4]();
                        } else if (logType < 0.95) {
                            // 5% khả năng là log SMB Connection
                            logEntry = logTypes[5]();
                        } else if (logType < 0.975) {
                            // 2.5% khả năng là log ICMP Echo Reply
                            logEntry = logTypes[6]();
                        } else {
                            // 2.5% khả năng là SSH từ IP nội bộ
                            logEntry = logTypes[7]();
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

                while (logArea.children.length > 50) {
                    logArea.removeChild(logArea.lastChild);
                }
            }
        };
        xhr.send('action=check_hacker_blocked');
    }, 1000);
}