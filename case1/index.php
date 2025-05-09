<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];

// Lấy điểm của người dùng cho Case 1 (case_id = 1)
$stmt = $pdo->prepare("SELECT score FROM user_case_scores WHERE user_id = ? AND case_id = 1");
$stmt->execute([$user_id]);
$case_score = $stmt->fetchColumn() ?: 0; // Nếu không có điểm, mặc định là 0

// Khởi tạo session cho rules và terminal_output nếu chưa có
if (!isset($_SESSION['rules'])) {
    $_SESSION['rules'] = [];
}
if (!isset($_SESSION['terminal_output'])) {
    $_SESSION['terminal_output'] = [
        'web_server' => [],
        'db_server' => [],
        'workstation_1' => [],
        'workstation_2' => []
    ];
}

// Xử lý reset
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    $_SESSION['rules'] = [];
    $_SESSION['terminal_output'] = [
        'web_server' => [],
        'db_server' => [],
        'workstation_1' => [],
        'workstation_2' => []
    ];
    header("Location: /case1/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
    <title>Mô phỏng PBQ - SIM LAB</title>
    <!-- Meta tags mô tả website và case -->
    <meta name="description" content="Mô phỏng PBQ SIM LAB - Case 1: Xử lý sự cố mạng. Website không truy cập được từ bên ngoài do Hacker tấn công. Hãy chặn IP của Hacker và khôi phục truy cập. Được phát triển bởi Vệ Sĩ Mạng.">
    <meta name="keywords" content="PBQ, SIM LAB, xử lý sự cố mạng, firewall, DNS, hacker, Vệ Sĩ Mạng">
    <meta name="author" content="Vệ Sĩ Mạng">
    <!-- Thẻ Open Graph cho mạng xã hội -->
    <meta property="og:title" content="Mô phỏng PBQ SIM LAB - Case 1: Xử lý sự cố mạng">
    <meta property="og:description" content="Website không truy cập được từ bên ngoài do Hacker tấn công. Hãy chặn IP của Hacker và khôi phục truy cập. Được phát triển bởi Vệ Sĩ Mạng.">
    <meta property="og:image" content="/case1/images/thumbnail.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://fake.jx2ngulong.com/case1/index.php">
    <!-- Thêm Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/case1/styles.css">
</head>
<body>
    <!-- Tiêu đề -->
    <h1>Network Diagram for VSM Sim Lab</h1>
    <!-- Nút Show Question và Reset All -->
    <div class="buttons">
        <button onclick="showQuestion()">Show Question</button>
        <button onclick="resetAll()">Reset All</button>
        <button onclick="window.location.href='/dashboard.php'">Quay lại Dashboard</button>
    </div>
    <!-- Hiển thị điểm -->
    <div class="scoreboard">Điểm Case 1: <span id="scoreDisplay"><?php echo $case_score; ?></span></div>

    <!-- Vùng hiển thị sơ đồ mạng -->
    <div id="diagram">
        <!-- Sơ đồ mạng: Hacker -> Internet -> Firewall -> DMZ -> Floor 2 -->
        <div class="network-row">
            <!-- Hacker -->
            <div class="device" id="hacker">
                <img src="/case1/icons/hacker.png" alt="Hacker">
                <p>Hacker</p>
                <p class="ip-address">132.123.132.123</p>
                <button class="check-website-hacker-btn" onclick="checkWebsite('hacker')">Kiểm tra Trạng Thái</button>
            </div>
            <!-- External User (Người dùng bên ngoài) -->
            <div class="device">
                <img src="/case1/icons/pc.png" alt="External User">
                <p>External User</p>
                <p class="ip-address">203.0.113.6</p>
                <button class="check-website-btn" onclick="checkWebsite('external')">Kiểm tra Website</button>
            </div>
            <div class="arrow-box">
                <img src="/case1/icons/arrow_right.png" class="arrow" alt="->">
            </div>
            <!-- Internet -->
            <div class="device" id="cloud">
                <img src="/case1/icons/cloud.png" alt="Cloud">
                <p>Internet</p>
            </div>
            <div class="arrow-box">
                <img src="/case1/icons/arrow_right.png" class="arrow" alt="->">
            </div>
            <!-- Firewall: có thể click mở modal -->
<!-- Firewall -->
        <div class="device clickable" id="firewallDevice">
            <img src="/case1/icons/firewall.png" alt="Firewall">
            <p>Firewall</p>
        </div>
        <!-- Mũi tên phân nhánh -->
        <div class="arrow-split">
            <div class="arrow-box arrow-up">
                <img src="/case1/icons/arrow_right.png" class="arrow" alt="->">
            </div>
            <div class="arrow-box arrow-down">
                <img src="/case1/icons/arrow_right.png" class="arrow" alt="->">
            </div>
        </div>
        <!-- Hai vùng song song: DMZ và Floor 2 -->
        <div class="parallel-zones">
            <!-- Vùng DMZ -->
            <div class="network-column dmz">
                <p><strong>DMZ - Server Room</strong></p>
                <!-- Hàng trên -->
                <div class="dmz-row">
                    <div class="device">
                        <img src="/case1/icons/switch.png" alt="Switch">
                        <p>Switch</p>
                    </div>
                    <div class="device clickable" id="dnsServerDevice">
                        <img src="/case1/icons/server.png" alt="DNS Server">
                        <p>DNS Server</p>
                        <p class="ip-address">192.168.1.12</p>
                    </div>
                    <div class="device">
                        <img src="/case1/icons/server.png" alt="File Server">
                        <p>File Server</p>
                    </div>
                </div>
                <!-- Hàng dưới -->
                <div class="dmz-row">
                    <div class="device">
                        <img src="/case1/icons/server.png" alt="Email Server">
                        <p>Email Server</p>
                    </div>
                    <div class="device clickable" id="webServerDevice">
                        <img src="/case1/icons/server.png" alt="Web Server">
                        <p>Web Server</p>
                        <p class="ip-address">192.168.1.10</p>
                    </div>
                    <div class="device clickable" id="dbServerDevice">
                        <img src="/case1/icons/server.png" alt="Database Server">
                        <p>Database Server</p>
                        <p class="ip-address">192.168.1.11</p>
                    </div>
                </div>
            </div>
            <!-- Vùng Floor 2 - Executive Offices -->
            <div class="network-column floor-2">
                <p><strong>LAN - Floor 2 - Executive Offices</strong></p>
                <div class="floor-row">
                    <div class="device">
                        <img src="/case1/icons/switch.png" alt="Switch">
                        <p>Switch</p>
                    </div>
                    <div class="device">
                        <img src="/case1/icons/printer.png" alt="Printer">
                        <p>Printer</p>
                    </div>
                    <div class="device clickable" id="workstation1Device">
                        <img src="/case1/icons/pc.png" alt="PC">
                        <p>Workstation 1</p>
                        <p class="ip-address">192.168.1.20</p>
                        <button class="check-website-btn" onclick="checkWebsite('internal')">Kiểm tra website</button>
                    </div>
                    <div class="device clickable" id="workstation2Device">
                        <img src="/case1/icons/pc.png" alt="PC">
                        <p>Workstation 2</p>
                        <p class="ip-address">192.168.1.21</p>
                        <button class="check-website-btn" onclick="checkWebsite('internal')">Kiểm tra website</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Modal Câu hỏi -->
    <div class="modal" id="questionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Bài kiểm tra và Hướng dẫn</h2>
                <span class="close" id="questionCloseBtn">×</span>
            </div>
            <div class="modal-body">
                <h3>Đề bài</h3>
                <p>Website trên máy chủ web hiện không thể truy cập từ bên ngoài (External User), nhưng các IP nội bộ (Workstation 1 và Workstation 2) vẫn có thể truy cập bình thường. Hãy kiểm tra nguyên nhân gây ra sự cố này bằng cách xem log trên Firewall để xác định IP của máy Hacker đang gây ảnh hưởng. Sau đó, thêm rule vào Firewall để chặn IP của Hacker và khôi phục truy cập từ bên ngoài.</p>
                
                <h3>Hướng dẫn</h3>
                <p><strong>1. Kiểm tra phân giải tên miền trên DNS Server:</strong></p>
                <ul>
                    <li>Nhấn vào "DNS Server" trong sơ đồ mạng để mở bảng phân giải tên miền.</li>
                    <li>Xem thông tin trong Zone Public (phân giải ra IP WAN của Firewall: 203.0.113.1) và Zone Private (phân giải ra IP nội bộ của Web Server: 192.168.1.10).</li>
                </ul>

                <p><strong>2. Sử dụng các lệnh trong máy chủ:</strong></p>
                <ul>
                    <li>Nhấn vào "Web Server" hoặc "Database Server" để mở terminal.</li>
                    <li>Các lệnh có sẵn:
                        <ul>
                            <li><code>ifconfig</code>: Hiển thị cấu hình mạng của máy (IP, netmask, v.v.).</li>
                            <li><code>systemctl status httpd</code> (trên Web Server): Kiểm tra trạng thái dịch vụ Apache HTTP Server.</li>
                            <li><code>systemctl status mysql</code> (trên Database Server): Kiểm tra trạng thái dịch vụ MySQL.</li>
                            <li><code>ping &lt;IP&gt;</code>: Kiểm tra kết nối đến một IP bất kỳ (ví dụ: <code>ping 8.8.8.8</code>).</li>
                            <li><code>help</code>: Hiển thị danh sách các lệnh có sẵn.</li>
                            <li><code>clear</code>: Xóa màn hình terminal.</li>
                        </ul>
                    </li>
                </ul>

                <p><strong>3. Kiểm tra website đã truy cập được hay chưa:</strong></p>
                <ul>
                    <li>Nhấn nút "Kiểm tra website" trên "External User" để kiểm tra truy cập từ bên ngoài.</li>
                    <li>Nhấn nút "Kiểm tra website" trên "Workstation 1" hoặc "Workstation 2" để kiểm tra truy cập từ mạng nội bộ.</li>
                    <li>Nếu website không truy cập được từ External User, cần kiểm tra và chặn IP của Hacker trên Firewall.</li>
                </ul>

                <p><strong>4. Thao tác trên Firewall:</strong></p>
                <ul>
                    <li>Nhấn vào "Firewall" để mở bảng điều khiển.</li>
                    <li>Chuyển sang tab "Firewall Logs" để xem log, tìm IP của Hacker (203.0.113.5) đang gửi các gói SYN (SYN Flood).</li>
                    <li>Chuyển sang tab "Firewall Rules", thêm rule chặn IP của Hacker:
                        <ul>
                            <li>Nhập IP: 203.0.113.5</li>
                            <li>Chọn lý do: Loại tấn công</li>
                            <li>Nhấn "Thêm Rule"</li>
                        </ul>
                    </li>
                    <li>Chuyển sang tab "Interface" để xem cấu hình IP của các card mạng (WAN, LAN, DMZ).</li>
                    <li>Chuyển sang tab "Dashboard" để xem biểu đồ CPU và Network Usage, kiểm tra xem sau khi chặn Hacker thì tải có giảm không.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Firewall -->
    <div class="modal" id="firewallModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Firewall</h2>
                <span class="close" id="firewallCloseBtn">×</span>
            </div>
            <div class="modal-tabs">
                <button class="tab-btn" data-target="dashboardTab">Dashboard</button>
                <button class="tab-btn" data-target="interfaceTab">Interface</button>
                <button class="tab-btn" data-target="logsTab">Firewall Logs</button>
                <button class="tab-btn" data-target="rulesTab">Firewall Rules</button>
            </div>
            <!-- Tab Dashboard -->
            <div class="tab-content" id="dashboardTab">
                <h3>Firewall Summary Dashboard</h3>
                <div class="chart-container">
                    <canvas id="cpuChart"></canvas>
                    <canvas id="networkChart"></canvas>
                </div>
                <h4>Các phiên kết nối hiện tại:</h4>
                <div class="session-list">
                    <table class="session-table" id="sessionTable">
                        <thead>
                            <tr>
                                <th>Source IP</th>
                                <th>Destination</th>
                                <th>Port</th>
                            </tr>
                        </thead>
                        <tbody id="sessionList"></tbody>
                    </table>
                </div>
            </div>
            <!-- Tab Logs -->
            <div class="tab-content" id="logsTab">
                <h3>Real Time Logs</h3>
                <div class="log-area">
                    <table class="log-table" id="logTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Source IP</th>
                                <th>Destination</th>
                                <th>Port</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody id="logArea"></tbody>
                    </table>
                </div>
            </div>
            <!-- Tab Rules -->
            <div class="tab-content" id="rulesTab">
                <h3>Firewall Rules</h3>
                <p><strong>NAT Rules:</strong></p>
                <table class="rules-table">
                    <thead>
                        <tr>
                            <th>Public IP</th>
                            <th>Private IP</th>
                            <th>Destination</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>203.0.113.100</td>
                            <td>192.168.1.10</td>
                            <td>Web Server</td>
                        </tr>
                        <tr>
                            <td>203.0.113.101</td>
                            <td>192.168.1.11</td>
                            <td>Database Server</td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>Thêm rules chặn mới:</strong></p>
                <form id="ruleForm" class="rule-form">
                    <div class="form-group">
                        <label for="blockIP">IP:</label>
                        <input type="text" id="blockIP" required>
                    </div>
                    <div class="form-group">
                        <label for="blockReason">Lý do:</label>
                        <select id="blockReason" required>
                            <option value="">--Chọn--</option>
                            <option value="UDP DoS">UDP Flood</option>
                            <option value="SYN DoS">TCP SYN Flood</option>
                            <option value="Port Scan">Port Scan</option>
                            <option value="Brute Force">Brute Force</option>
                            <option value="SQL Injection">SQL Injection</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <button type="submit">Thêm Rule</button>
                </form>
                <div id="ruleMessage"></div>
                <p><strong>Các rules chặn hiện tại:</strong></p>
                <?php if (empty($_SESSION['rules'])): ?>
                    <p class="placeholder">Chưa có rules chặn nào</p>
                <?php else: ?>
                    <table class="rules-table">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Lý do</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['rules'] as $index => $rule): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rule['ip']); ?></td>
                                    <td><?php echo htmlspecialchars($rule['reason']); ?></td>
                                    <td>
                                        <button onclick="deleteRule(<?php echo $index; ?>)">Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <!-- Tab Interface -->
            <div class="tab-content" id="interfaceTab">
                <h3>Network Interfaces</h3>
                <table class="rules-table">
                    <thead>
                        <tr>
                            <th>Interface</th>
                            <th>IP Address</th>
                            <th>Netmask</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>WAN</td>
                            <td>203.0.113.150</td>
                            <td>255.255.255.0</td>
                        </tr>
                        <tr>
                            <td>LAN</td>
                            <td>192.168.1.1</td>
                            <td>255.255.255.0</td>
                        </tr>
                        <tr>
                            <td>DMZ</td>
                            <td>192.168.2.1</td>
                            <td>255.255.255.0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Terminal -->
    <div class="modal" id="terminalModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="terminalTitle">Terminal</h2>
                <span class="close" id="terminalCloseBtn">×</span>
            </div>
            <div class="modal-body terminal-body">
                <div id="terminalOutput" class="terminal-output">
                    <span id="terminalContent"></span>
                </div>
                <div class="terminal-input-wrapper">
                    <span class="prompt">admin@server:~$</span>
                    <input type="text" id="terminalInput" class="terminal-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kiểm tra Website -->
    <div class="modal" id="websiteCheckModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Kết quả kiểm tra website</h2>
                <span class="close" id="websiteCheckCloseBtn">×</span>
            </div>
            <div class="modal-body">
                <div id="websiteCheckResult"></div>
                <p id="websiteCheckMessage"></p>
            </div>
        </div>
    </div>

    <!-- Modal DNS Server -->
    <div class="modal" id="dnsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>DNS Server - Name Resolution</h2>
                <span class="close" id="dnsCloseBtn">×</span>
            </div>
            <div class="modal-body">
                <h3>Zone: Public</h3>
                <table class="rules-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>IP Address</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>vesimang.org</td>
                            <td>203.0.113.150</td>
                            <td>A</td>
                        </tr>
                        <tr>
                            <td>www.vesimang.org</td>
                            <td>203.0.113.150</td>
                            <td>A</td>
                        </tr>
                    </tbody>
                </table>
                <h3>Zone: Private</h3>
                <table class="rules-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>IP Address</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>vesimang.local</td>
                            <td>192.168.1.10</td>
                            <td>A</td>
                        </tr>
                        <tr>
                            <td>www.vesimang.local</td>
                            <td>192.168.1.10</td>
                            <td>A</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- File JS -->
    <script src="/case1/script.js"></script>
    <script src="/case1/command.js"></script>
    <script src="/case1/logs.js"></script>

    <!-- Footer bản quyền -->
    <footer class="footer">
        <p>&copy; 2025 Công ty Vệ Sĩ Mạng. Tất cả quyền được bảo lưu.</p>
        <p>Phát triển bởi <a href="https://vesimang.org" target="_blank">VSM TEAM</a> | Liên hệ: <a href="mailto:support@vesimang.org">support@vesimang.org</a></p>
    </footer>

    <!-- File JS -->
    <script src="/case1/script.js"></script>
    <script src="/case1/command.js"></script>
    <script src="/case1/logs.js"></script>
</body>
</html>