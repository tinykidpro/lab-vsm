const logArea = document.getElementById('logArea');
const hackerIp = '198.51.100.72';
const normalIps = [
    '198.51.100.1', '198.51.100.2', '198.51.100.3', '198.51.100.4', '198.51.100.5',
    '198.51.100.6', '198.51.100.7', '198.51.100.8', '198.51.100.9', '198.51.100.10',
    '203.0.113.2', '203.0.113.3', '203.0.113.4', '203.0.113.6', '203.0.113.7',
    '203.0.113.8', '203.0.113.9', '203.0.113.10', '203.0.113.11', '203.0.113.12',
    '172.16.254.1', '172.16.254.2', '172.16.254.3', '172.16.254.4', '172.16.254.5',
    '172.16.254.6', '172.16.254.7', '172.16.254.8', '172.16.254.9', '172.16.254.10'
];

const logTypes = [
    // Log HTTP 200 OK (20%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: Math.random() > 0.5 ? 80 : 443,
        message: 'HTTP 200 OK'
    }),
    // Log HTTPS Connection (15%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.10',
        port: 443,
        message: 'HTTPS connection established'
    }),
    // Log DNS Query (15%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.12',
        port: 53,
        message: 'DNS query processed'
    }),
    // Log NTP Sync (15%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.12',
        port: 123,
        message: 'NTP time synchronization'
    }),
    // Log FTP Connection (15%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.21',
        port: 21,
        message: 'FTP connection established'
    }),
    // Log SMB Connection (8%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.20',
        port: 445,
        message: 'SMB connection established'
    }),
    // Log ICMP Echo Reply (3.5%)
    () => ({
        sourceIp: normalIps[Math.floor(Math.random() * normalIps.length)],
        destination: '192.168.1.20',
        port: 0,
        message: 'ICMP echo reply'
    }),
    // SMTP connection from hacker (2.5%)
    () => ({
        sourceIp: hackerIp,
        destination: '192.168.1.13',
        port: 25,
        message: 'SMTP connection established'
    }),
    // Email sent to Workstation 1 (1%)
    () => ({
        sourceIp: '192.168.1.13',
        destination: '192.168.1.20',
        port: 25,
        message: 'Email sent to 192.168.1.20'
    }),
    // Email sent to Workstation 2 (1%)
    () => ({
        sourceIp: '192.168.1.13',
        destination: '192.168.1.21',
        port: 25,
        message: 'Email sent to 192.168.1.21'
    }),
    // HTTP request to phishing site from Workstation 1 (2%)
    () => ({
        sourceIp: '192.168.1.20',
        destination: hackerIp,
        port: 80,
        message: 'HTTP request to fake.vesimang.local'
    }),
    // HTTP request to phishing site from Workstation 2 (2%)
    () => ({
        sourceIp: '192.168.1.21',
        destination: hackerIp,
        port: 80,
        message: 'HTTP request to fake.vesimang.local'
    })
];

function startLogs() {
    setInterval(() => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/case3/process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const hackerBlocked = response.hackerBlocked;
                let logEntry;

                if (!hackerBlocked) {
                    const logType = Math.random();
                    if (logType < 0.20) {
                        logEntry = logTypes[0](); // HTTP 200 OK (20%)
                    } else if (logType < 0.35) {
                        logEntry = logTypes[1](); // HTTPS Connection (15%)
                    } else if (logType < 0.50) {
                        logEntry = logTypes[2](); // DNS Query (15%)
                    } else if (logType < 0.65) {
                        logEntry = logTypes[3](); // NTP Sync (15%)
                    } else if (logType < 0.80) {
                        logEntry = logTypes[4](); // FTP Connection (15%)
                    } else if (logType < 0.88) {
                        logEntry = logTypes[5](); // SMB Connection (8%)
                    } else if (logType < 0.915) {
                        logEntry = logTypes[6](); // ICMP Echo Reply (3.5%)
                    } else if (logType < 0.94) {
                        logEntry = logTypes[7](); // SMTP from hacker (2.5%)
                    } else if (logType < 0.95) {
                        logEntry = logTypes[8](); // Email to WS1 (1%)
                    } else if (logType < 0.96) {
                        logEntry = logTypes[9](); // Email to WS2 (1%)
                    } else if (logType < 0.98) {
                        logEntry = logTypes[10](); // HTTP from WS1 (2%)
                    } else {
                        logEntry = logTypes[11](); // HTTP from WS2 (2%)
                    }
                } else {
                    const logType = Math.random();
                    if (logType < 0.25) {
                        logEntry = logTypes[0](); // HTTP 200 OK (25%)
                    } else if (logType < 0.45) {
                        logEntry = logTypes[1](); // HTTPS Connection (20%)
                    } else if (logType < 0.65) {
                        logEntry = logTypes[2](); // DNS Query (20%)
                    } else if (logType < 0.80) {
                        logEntry = logTypes[3](); // NTP Sync (15%)
                    } else if (logType < 0.90) {
                        logEntry = logTypes[4](); // FTP Connection (10%)
                    } else if (logType < 0.95) {
                        logEntry = logTypes[5](); // SMB Connection (5%)
                    } else {
                        logEntry = logTypes[6](); // ICMP Echo Reply (5%)
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