document.getElementById('terminalInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const command = this.value.trim();
        if (command) {
            if (currentServer === 'db_server' && command === 'passwd' && !document.getElementById('passwordPrompt')) {
                // Hiển thị prompt nhập mật khẩu
                const terminalOutput = document.getElementById('terminalOutput');
                const promptDiv = document.createElement('div');
                promptDiv.id = 'passwordPrompt';
                promptDiv.innerHTML = 'Enter new UNIX password: <input type="password" id="passwordInput">';
                terminalOutput.appendChild(promptDiv);
                terminalOutput.scrollTop = terminalOutput.scrollHeight;

                const passwordInput = document.getElementById('passwordInput');
                passwordInput.focus();
                passwordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const password = this.value.trim();
                        sendCommand(command, password);
                        promptDiv.remove();
                    }
                });
            } else {
                sendCommand(command);
            }
        }
        this.value = '';
    }
});

function sendCommand(command, password = '') {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/case2/process.php', true);
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
    const data = `action=command&server=${currentServer}&command=${encodeURIComponent(command)}${password ? `&password=${encodeURIComponent(password)}` : ''}`;
    xhr.send(data);
}