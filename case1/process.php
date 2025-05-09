<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Khởi tạo session nếu chưa có
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

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'get_sessions') {
    $ips = [
        '203.0.113.5', // IP của hacker
        '198.51.100.1', '198.51.100.2', '198.51.100.3', '198.51.100.4', '198.51.100.5',
        '198.51.100.6', '198.51.100.7', '198.51.100.8', '198.51.100.9', '198.51.100.10',
        '203.0.113.2', '203.0.113.3', '203.0.113.4', '203.0.113.6', '203.0.113.7',
        '203.0.113.8', '203.0.113.9', '203.0.113.10', '203.0.113.11', '203.0.113.12',
        '172.16.254.1', '172.16.254.2', '172.16.254.3', '172.16.254.4', '172.16.254.5',
        '172.16.254.6', '172.16.254.7', '172.16.254.8', '172.16.254.9', '172.16.254.10'
    ];

    $sessions = [];
    $hackerBlocked = false;
    foreach ($_SESSION['rules'] as $rule) {
        if ($rule['ip'] === '203.0.113.5' && $rule['reason'] === 'SYN DoS') {
            $hackerBlocked = true;
            break;
        }
    }

    $numSessions = $hackerBlocked ? rand(5, 10) : rand(15, 20);
    for ($i = 0; $i < $numSessions; $i++) {
        $sourceIp = $ips[array_rand($ips)];
        if (!$hackerBlocked || $sourceIp !== '203.0.113.5') {
            $sessions[] = [
                'sourceIp' => $sourceIp,
                'destination' => '192.168.1.10',
                'port' => $sourceIp === '203.0.113.5' ? 80 : (rand(0, 1) ? 80 : 443)
            ];
        }
    }

    echo json_encode($sessions);
    exit();
}

if ($action === 'check_hacker_blocked') {
    $hackerBlocked = false;
    foreach ($_SESSION['rules'] as $rule) {
        if ($rule['ip'] === '203.0.113.5' && $rule['reason'] === 'SYN DoS') {
            $hackerBlocked = true;
            break;
        }
    }
    echo json_encode(['hackerBlocked' => $hackerBlocked]);
    exit();
}

if ($action === 'block') {
    $ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if (empty($ip) || empty($reason)) {
        echo json_encode(['message' => 'Vui lòng nhập đầy đủ IP và lý do']);
        exit();
    }

    $_SESSION['rules'][] = ['ip' => $ip, 'reason' => $reason];

    // Kiểm tra nếu chặn IP của hacker
    if ($ip === '203.0.113.5') {
        // Kiểm tra điểm hiện tại của người dùng cho Case 1
        $stmt = $pdo->prepare("SELECT score FROM user_case_scores WHERE user_id = ? AND case_id = 1");
        $stmt->execute([$user_id]);
        $current_score = $stmt->fetchColumn() ?: 0;

        // Tính điểm mới
        $new_score = 0;
        $message = '';
        if ($reason === 'SYN DoS') {
            $new_score = 100;
            $message = 'Chính xác! Bạn đã chặn đúng cuộc tấn công SYN DoS. Bạn được thưởng 100 điểm!';
        } else {
            $new_score = 50;
            $message = 'Bạn đã chặn đúng IP của hacker nhưng lý do không chính xác. Bạn được 50 điểm.';
        }

        // Chỉ cập nhật nếu điểm mới cao hơn điểm hiện tại
        if ($new_score > $current_score) {
            // Cập nhật điểm cho Case 1
            $stmt = $pdo->prepare("INSERT INTO user_case_scores (user_id, case_id, score) VALUES (?, 1, ?) 
                                   ON DUPLICATE KEY UPDATE score = ?");
            $stmt->execute([$user_id, $new_score, $new_score]);

            // Cập nhật tổng điểm trong bảng users
            $stmt = $pdo->prepare("SELECT SUM(score) as total_score FROM user_case_scores WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $total_score = $stmt->fetchColumn() ?: 0;

            $stmt = $pdo->prepare("UPDATE users SET score = ? WHERE id = ?");
            $stmt->execute([$total_score, $user_id]);

            // Cập nhật session score
            $_SESSION['score'] = $total_score;
        } else {
            $message .= ' (Điểm không thay đổi vì bạn đã đạt điểm cao hơn trước đó)';
        }

        echo json_encode([
            'message' => $message,
            'score' => $_SESSION['score']
        ]);
    } else {
        // Lấy tổng điểm hiện tại từ cơ sở dữ liệu
        $stmt = $pdo->prepare("SELECT score FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['score'] = $user['score'];

        echo json_encode([
            'message' => 'Rule đã được thêm, nhưng IP không đúng.',
            'score' => $_SESSION['score']
        ]);
    }
    exit();
}

if ($action === 'delete_rule') {
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    if (isset($_SESSION['rules'][$index])) {
        unset($_SESSION['rules'][$index]);
        $_SESSION['rules'] = array_values($_SESSION['rules']);

        // Reset điểm cho Case 1
        $stmt = $pdo->prepare("UPDATE user_case_scores SET score = 0 WHERE user_id = ? AND case_id = 1");
        $stmt->execute([$user_id]);

        // Cập nhật tổng điểm trong bảng users
        $stmt = $pdo->prepare("SELECT SUM(score) as total_score FROM user_case_scores WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_score = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("UPDATE users SET score = ? WHERE id = ?");
        $stmt->execute([$total_score, $user_id]);

        // Cập nhật session score
        $_SESSION['score'] = $total_score;

        echo json_encode(['status' => 'success', 'score' => $_SESSION['score']]);
    } else {
        echo json_encode(['message' => 'Rule không tồn tại']);
    }
    exit();
}

if ($action === 'command') {
    $server = isset($_POST['server']) ? $_POST['server'] : '';
    $command = isset($_POST['command']) ? trim($_POST['command']) : '';
    $response = '';

    if (empty($server) || empty($command)) {
        echo json_encode(['response' => 'Lỗi: Thiếu tham số']);
        exit();
    }

    if ($server === 'web_server') {
        if ($command === 'ifconfig') {
            $response = "eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500\n" .
                        "        inet 192.168.1.10  netmask 255.255.255.0  broadcast 192.168.1.255\n" .
                        "        inet6 fe80::a00:27ff:fe4e:66ea  prefixlen 64  scopeid 0x20<link>\n" .
                        "        ether 08:00:27:4e:66:ea  txqueuelen 1000  (Ethernet)\n" .
                        "        RX packets 12345  bytes 9876543 (9.8 MB)\n" .
                        "        RX errors 0  dropped 0  overruns 0  frame 0\n" .
                        "        TX packets 6789  bytes 1234567 (1.2 MB)\n" .
                        "        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0\n";
        } elseif ($command === 'systemctl status httpd') {
            $response = "● httpd.service - The Apache HTTP Server\n" .
                        "   Loaded: loaded (/usr/lib/systemd/system/httpd.service; enabled; vendor preset: disabled)\n" .
                        "   Active: active (running) since Mon 2025-03-22 10:00:00 UTC; 1h ago\n" .
                        " Main PID: 1234 (httpd)\n" .
                        "   CGroup: /system.slice/httpd.service\n" .
                        "           ├─1234 /usr/sbin/httpd -DFOREGROUND\n" .
                        "           ├─1235 /usr/sbin/httpd -DFOREGROUND\n" .
                        "           └─1236 /usr/sbin/httpd -DFOREGROUND\n";
        } elseif (preg_match('/^ping\s+(.+)/', $command, $matches)) {
            $target = $matches[1];
            $response = "PING $target ($target) 56(84) bytes of data.\n" .
                        "64 bytes from $target: icmp_seq=1 ttl=64 time=10.0 ms\n" .
                        "64 bytes from $target: icmp_seq=2 ttl=64 time=10.1 ms\n" .
                        "64 bytes from $target: icmp_seq=3 ttl=64 time=10.2 ms\n" .
                        "64 bytes from $target: icmp_seq=4 ttl=64 time=10.3 ms\n" .
                        "\n--- $target ping statistics ---\n" .
                        "4 packets transmitted, 4 received, 0% packet loss, time 3000ms\n" .
                        "rtt min/avg/max/mdev = 10.0/10.15/10.3/0.1 ms";
        } elseif ($command === 'help') {
            $response = "Available commands:\n" .
                        "  ifconfig              - Display network interface configuration\n" .
                        "  systemctl status httpd - Check the status of the Apache HTTP Server\n" .
                        "  ping <target>            - Ping a target IP or hostname\n" .
                        "  clear                 - Clear the terminal screen\n" .
                        "  help                  - Show this help message";
        } else {
            $response = 'bash: ' . $command . ': command not found';
        }
    } elseif ($server === 'db_server') {
        if ($command === 'ifconfig') {
            $response = "eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500\n" .
                        "        inet 192.168.1.11  netmask 255.255.255.0  broadcast 192.168.1.255\n" .
                        "        inet6 fe80::a00:27ff:fe4e:66eb  prefixlen 64  scopeid 0x20<link>\n" .
                        "        ether 08:00:27:4e:66:eb  txqueuelen 1000  (Ethernet)\n" .
                        "        RX packets 54321  bytes 3456789 (3.4 MB)\n" .
                        "        RX errors 0  dropped 0  overruns 0  frame 0\n" .
                        "        TX packets 9876  bytes 2345678 (2.3 MB)\n" .
                        "        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0\n";
        } elseif ($command === 'systemctl status mysql') {
            $response = "● mysql.service - MySQL Community Server\n" .
                        "   Loaded: loaded (/usr/lib/systemd/system/mysql.service; enabled; vendor preset: disabled)\n" .
                        "   Active: active (running) since Mon 2025-03-22 10:00:00 UTC; 1h ago\n" .
                        " Main PID: 5678 (mysqld)\n" .
                        "   CGroup: /system.slice/mysql.service\n" .
                        "           └─5678 /usr/sbin/mysqld --daemonize --pid-file=/var/run/mysqld/mysqld.pid\n";
        } elseif (preg_match('/^ping\s+(.+)/', $command, $matches)) {
            $target = $matches[1];
            $response = "PING $target ($target) 56(84) bytes of data.\n" .
                        "64 bytes from $target: icmp_seq=1 ttl=64 time=10.0 ms\n" .
                        "64 bytes from $target: icmp_seq=2 ttl=64 time=10.1 ms\n" .
                        "64 bytes from $target: icmp_seq=3 ttl=64 time=10.2 ms\n" .
                        "64 bytes from $target: icmp_seq=4 ttl=64 time=10.3 ms\n" .
                        "\n--- $target ping statistics ---\n" .
                        "4 packets transmitted, 4 received, 0% packet loss, time 3000ms\n" .
                        "rtt min/avg/max/mdev = 10.0/10.15/10.3/0.1 ms";
        } elseif ($command === 'help') {
            $response = "Available commands:\n" .
                        "  ifconfig              - Display network interface configuration\n" .
                        "  systemctl status mysql - Check the status of the MySQL Server\n" .
                        "  ping <target>            - Ping a target IP or hostname\n" .
                        "  clear                 - Clear the terminal screen\n" .
                        "  help                  - Show this help message";
        } else {
            $response = 'bash: ' . $command . ': command not found';
        }
    } elseif ($server === 'workstation_1') {
        if ($command === 'ifconfig') {
            $response = "eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500\n" .
                        "        inet 192.168.1.20  netmask 255.255.255.0  broadcast 192.168.1.255\n" .
                        "        inet6 fe80::a00:27ff:fe4e:66ec  prefixlen 64  scopeid 0x20<link>\n" .
                        "        ether 08:00:27:4e:66:ec  txqueuelen 1000  (Ethernet)\n" .
                        "        RX packets 23456  bytes 4567890 (4.5 MB)\n" .
                        "        RX errors 0  dropped 0  overruns 0  frame 0\n" .
                        "        TX packets 12345  bytes 3456789 (3.4 MB)\n" .
                        "        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0\n";
        } elseif (preg_match('/^ping\s+(.+)/', $command, $matches)) {
            $target = $matches[1];
            $response = "PING $target ($target) 56(84) bytes of data.\n" .
                        "64 bytes from $target: icmp_seq=1 ttl=64 time=10.0 ms\n" .
                        "64 bytes from $target: icmp_seq=2 ttl=64 time=10.1 ms\n" .
                        "64 bytes from $target: icmp_seq=3 ttl=64 time=10.2 ms\n" .
                        "64 bytes from $target: icmp_seq=4 ttl=64 time=10.3 ms\n" .
                        "\n--- $target ping statistics ---\n" .
                        "4 packets transmitted, 4 received, 0% packet loss, time 3000ms\n" .
                        "rtt min/avg/max/mdev = 10.0/10.15/10.3/0.1 ms";
        } elseif ($command === 'help') {
            $response = "Available commands:\n" .
                        "  ifconfig      - Display network interface configuration\n" .
                        "  ping <target>    - Ping a target IP or hostname\n" .
                        "  clear         - Clear the terminal screen\n" .
                        "  help          - Show this help message";
        } else {
            $response = 'bash: ' . $command . ': command not found';
        }
    } elseif ($server === 'workstation_2') {
        if ($command === 'ifconfig') {
            $response = "eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500\n" .
                        "        inet 192.168.1.21  netmask 255.255.255.0  broadcast 192.168.1.255\n" .
                        "        inet6 fe80::a00:27ff:fe4e:66ed  prefixlen 64  scopeid 0x20<link>\n" .
                        "        ether 08:00:27:4e:66:ed  txqueuelen 1000  (Ethernet)\n" .
                        "        RX packets 34567  bytes 5678901 (5.6 MB)\n" .
                        "        RX errors 0  dropped 0  overruns 0  frame 0\n" .
                        "        TX packets 23456  bytes 4567890 (4.5 MB)\n" .
                        "        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0\n";
        } elseif (preg_match('/^ping\s+(.+)/', $command, $matches)) {
            $target = $matches[1];
            $response = "PING $target ($target) 56(84) bytes of data.\n" .
                        "64 bytes from $target: icmp_seq=1 ttl=64 time=10.0 ms\n" .
                        "64 bytes from $target: icmp_seq=2 ttl=64 time=10.1 ms\n" .
                        "64 bytes from $target: icmp_seq=3 ttl=64 time=10.2 ms\n" .
                        "64 bytes from $target: icmp_seq=4 ttl=64 time=10.3 ms\n" .
                        "\n--- $target ping statistics ---\n" .
                        "4 packets transmitted, 4 received, 0% packet loss, time 3000ms\n" .
                        "rtt min/avg/max/mdev = 10.0/10.15/10.3/0.1 ms";
        } elseif ($command === 'help') {
            $response = "Available commands:\n" .
                        "  ifconfig      - Display network interface configuration\n" .
                        "  ping <target>    - Ping a target IP or hostname\n" .
                        "  clear         - Clear the terminal screen\n" .
                        "  help          - Show this help message";
        } else {
            $response = 'bash: ' . $command . ': command not found';
        }
    } else {
        $response = 'Server không hợp lệ';
    }

    // Lưu lịch sử lệnh
    if ($command !== 'clear') {
        $_SESSION['terminal_output'][$server][] = "admin@$server:~$ $command\n$response";
    } else {
        $_SESSION['terminal_output'][$server] = [];
    }

    echo json_encode(['response' => $response]);
    exit();
}

if ($action === 'load_terminal') {
    $server = isset($_POST['server']) ? $_POST['server'] : '';
    if (isset($_SESSION['terminal_output'][$server])) {
        $output = implode("\n", $_SESSION['terminal_output'][$server]);
        echo $output;
    } else {
        echo '';
    }
    exit();
}

echo json_encode(['error' => 'Hành động không hợp lệ']);
exit();
?>