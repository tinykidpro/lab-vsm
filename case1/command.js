document.getElementById('terminalInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const command = this.value.trim();
        if (command === '') return;

        const terminalContent = document.getElementById('terminalContent');
        const prompt = document.querySelector('.prompt').innerText;

        // Hiển thị lệnh người dùng nhập
        terminalContent.innerHTML += `${prompt}${command}<br>`;

        if (command === 'clear') {
            terminalContent.innerHTML = '';
            this.value = '';
            // Gửi yêu cầu để xóa lịch sử trên server
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/case1/process.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(`action=command&server=${currentServer}&command=${command}`);
            return;
        }

        // Gửi lệnh đến server
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/case1/process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                // Thay thế \n bằng <br> để hiển thị đúng định dạng xuống dòng
                const formattedResponse = response.response.replace(/\n/g, '<br>');
                terminalContent.innerHTML += `${formattedResponse}<br>`;
                // Cuộn xuống dưới cùng
                const terminalOutput = document.getElementById('terminalOutput');
                terminalOutput.scrollTop = terminalOutput.scrollHeight;
            }
        };
        xhr.send(`action=command&server=${currentServer}&command=${command}`);

        this.value = ''; // Xóa input sau khi gửi lệnh
    }
});