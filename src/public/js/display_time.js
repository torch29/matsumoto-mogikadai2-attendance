let serverTimeOffset = 0;

function updateClock() {
    const now = new Date();
    const serverNow = new Date(now.getTime() + serverTimeOffset);

    const timeSetting = {
        timeZone: 'Asia/Tokyo',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };

    const timeString = serverNow.toLocaleTimeString('ja-JP', timeSetting);
    document.getElementById("clock").textContent = timeString;

    setTimeout(updateClock, 1000);
}

//サーバーの時刻取得する
function fetchServerTime() {
    const request = new XMLHttpRequest();
    request.open('HEAD', window.location.href, true);
    request.onreadystatechange = function () {
        if (this.readyState === 4) {
            const serverTimeString = request.getResponseHeader('Date');
            const clientNow = new Date();
            if (serverTimeString) {
                const serverNow = new Date(serverTimeString);
                serverTimeOffset = serverNow.getTime() - clientNow.getTime();
            }
            updateClock();
        }
    };
    request.send();
}

fetchServerTime();

