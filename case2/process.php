<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

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
if (!isset($_SESSION['case2'])) {
    $_SESSION['case2'] = [
        'hacker_blocked' => false,
        'password_changed' => false,
        'ssh_nat_removed' => false,
        'http_nat_removed' => false,
        'https_nat_removed' => false,
        'dns_nat_removed' => false,
        'mysql_nat_removed' => false,
        'lan_to_internet_removed' => false,
        'brute_force_selected' => false
    ];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'get_sessions') {
    $sessions = [
        [
            'sourceIp' => '192.168.1.10',
            'destination' => '192.168.1.11',
            'port' => 3306
        ],
        [
            'sourceIp' => '192.168.1.20',
            'destination' => '192.168.1.11',
            'port' => 22
        ]
    ];

    if (!$_SESSION['case2']['hacker_blocked']) {
        $sessions[] = [
            'sourceIp' => '203.0.113.5',
            'destination' => '192.168.1.11',
            'port' => 22
        ];
    }

    // Thêm các kết nối khác để làm rối
    $normalIps = [
        '198.51.100.1', '198.51.100.2', '198.51.100.3', '198.51.100.4', '198.51.100.5',
        '203.0.113.2', '203.0.113.3', '203.0.113.4', '203.0.113.6', '203.0.113.7',
        '172.16.254.1', '172.16.254.2', '172.16.254.3', '172.16.254.4', '172.16.254.5'
    ];
    $destinations = ['192.168.1.10', '192.168.1.12', '192.168.1.20', '192.168.1.21'];
    $ports = [80, 443, 53, 21, 445];

    for ($i = 0; $i < 10; $i++) {
        $sessions[] = [
            'sourceIp' => $normalIps[array_rand($normalIps)],
            'destination' => $destinations[array_rand($destinations)],
            'port' => $ports[array_rand($ports)]
        ];
    }

    echo json_encode($sessions);
    exit();
}

if ($action === 'check_hacker_blocked') {
    echo json_encode(['hackerBlocked' => $_SESSION['case2']['hacker_blocked']]);
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

    if ($ip === '203.0.113.5') {
        $_SESSION['case2']['hacker_blocked'] = true;
        if ($reason === 'Brute Force') {
            $_SESSION['case2']['brute_force_selected'] = true;
        }
    }

    updateScore($user_id);
    echo json_encode([
        'message' => 'Rule đã được thêm.',
        'score' => $_SESSION['score']
    ]);
    exit();
}

if ($action === 'delete_rule') {
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    if (isset($_SESSION['rules'][$index])) {
        if ($_SESSION['rules'][$index]['ip'] === '203.0.113.5') {
            $_SESSION['case2']['hacker_blocked'] = false;
            $_SESSION['case2']['brute_force_selected'] = false;
        }
        unset($_SESSION['rules'][$index]);
        $_SESSION['rules'] = array_values($_SESSION['rules']);
        updateScore($user_id);
        echo json_encode(['status' => 'success', 'score' => $_SESSION['score']]);
    } else {
        echo json_encode(['message' => 'Rule không tồn tại']);
    }
    exit();
}

if ($action === 'delete_nat_rule') {
    $service = isset($_POST['service']) ? $_POST['service'] : '';
    $valid_services = ['ssh', 'http', 'https', 'dns', 'mysql'];
    if (in_array($service, $valid_services)) {
        $_SESSION['case2'][$service . '_nat_removed'] = true;
        if ($service === 'ssh') {
            updateScore($user_id);
        }
        echo json_encode(['status' => 'success', 'score' => $_SESSION['score']]);
    } else {
        echo json_encode(['message' => 'Service không hợp lệ']);
    }
    exit();
}

if ($action === 'delete_routing_rule') {
    $rule = isset($_POST['rule']) ? $_POST['rule'] : '';
    if ($rule === 'lan_to_internet') {
        $_SESSION['case2']['lan_to_internet_removed'] = true;
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['message' => 'Rule không hợp lệ']);
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
                        "  ping <target>         - Ping a target IP or hostname\n" .
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
        } elseif ($command === 'passwd') {
            // Kiểm tra mật khẩu có đủ mạnh không
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            if (empty($password)) {
                $response = "passwd: Enter new UNIX password:\n(Please provide a password via the prompt)";
            } else {
                // Kiểm tra độ mạnh của mật khẩu
                $is_strong = preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
                if ($is_strong) {
                    $_SESSION['case2']['password_changed'] = true;
                    updateScore($user_id);
                    $response = "passwd: Password updated successfully";
                } else {
                    $response = "passwd: Password does not meet complexity requirements\n" .
                                "Password must be at least 8 characters long, contain uppercase, lowercase, numbers, and special characters.";
                }
            }
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
                        "  passwd                - Change the password (must be strong: 8+ chars, uppercase, lowercase, numbers, special chars)\n" .
                        "  ping <target>         - Ping a target IP or hostname\n" .
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
                        "  ping <target> - Ping a target IP or hostname\n" .
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
                        "  ping <target> - Ping a target IP or hostname\n" .
                        "  clear         - Clear the terminal screen\n" .
                        "  help          - Show this help message";
        } else {
            $response = 'bash: ' . $command . ': command not found';
        }
    } else {
        $response = 'Server không hợp lệ';
    }

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

if ($action === 'load_server_logs') {
    $server = isset($_POST['server']) ? $_POST['server'] : '';
    $logFile = '';
    if ($server === 'web_server') {
        $logFile = __DIR__ . '/weblog.txt';
    } elseif ($server === 'db_server') {
        $logFile = __DIR__ . '/datalog.txt';
    }

    if ($logFile && file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        echo $logs;
    } else {
        echo "Log file not found.";
    }
    exit();
}

function updateScore($user_id) {
    global $pdo;

    $score = 0;
    if ($_SESSION['case2']['hacker_blocked']) $score += 25; // Chặn IP hacker: 25 điểm
    if ($_SESSION['case2']['password_changed']) $score += 25; // Đổi mật khẩu mạnh: 25 điểm
    if ($_SESSION['case2']['ssh_nat_removed']) $score += 25; // Xóa rule NAT SSH: 25 điểm
    if ($_SESSION['case2']['brute_force_selected']) $score += 25; // Chọn đúng lý do Brute Force: 25 điểm

    $stmt = $pdo->prepare("SELECT score FROM user_case_scores WHERE user_id = ? AND case_id = 2");
    $stmt->execute([$user_id]);
    $current_score = $stmt->fetchColumn() ?: 0;

    if ($score > $current_score) {
        $stmt = $pdo->prepare("INSERT INTO user_case_scores (user_id, case_id, score) VALUES (?, 2, ?) 
                               ON DUPLICATE KEY UPDATE score = ?");
        $stmt->execute([$user_id, $score, $score]);

        $stmt = $pdo->prepare("SELECT SUM(score) as total_score FROM user_case_scores WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_score = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("UPDATE users SET score = ? WHERE id = ?");
        $stmt->execute([$total_score, $user_id]);

        $_SESSION['score'] = $total_score;
    }
}

echo json_encode(['error' => 'Hành động không hợp lệ']);
exit();
?>