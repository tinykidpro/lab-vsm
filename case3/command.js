document.getElementById('terminalInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const command = this.value.trim();
        if (command) {
            if (currentServer === 'email_server' && command === 'update-exim') {
                // Hiển thị quá trình cập nhật Exim
                const terminalOutput = document.getElementById('terminalOutput');
                const updateProcess = [
                    'Checking for updates...',
                    'Found Exim 4.96 update available',
                    'Downloading package files...',
                    'Verifying package integrity...',
                    'Installing updates...',
                    'Stopping Exim service...',
                    'Updating configuration...',
                    'Starting Exim service...',
                    'Update completed successfully.',
                    'Exim has been updated to version 4.96'
                ];

                let index = 0;
                const interval = setInterval(() => {
                    if (index < updateProcess.length) {
                        terminalOutput.innerHTML += `${updateProcess[index]}<br>`;
                        terminalOutput.scrollTop = terminalOutput.scrollHeight;
                        index++;
                    } else {
                        clearInterval(interval);
                        sendCommand(command);
                    }
                }, 500);
            } else {
                sendCommand(command);
            }
        }
        this.value = '';
    }
});

function sendCommand(command) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case3/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const terminalContent = document.getElementById('terminalContent');
            const formattedOutput = response.response.replace(/\n/g, '<br>');
            terminalContent.innerHTML += `admin@${currentServer}:~$ ${command}<br>${formattedOutput}<br>`;
            const terminalOutput = document.getElementById('terminalOutput');
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }
    };
    xhr.send(`action=command&server=${currentServer}&command=${encodeURIComponent(command)}`);
}