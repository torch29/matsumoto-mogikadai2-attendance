function nowTime() {
    const timeSetting = {
        timeZone: 'Asia/Tokyo',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }
    const timeString = new Date().toLocaleTimeString('ja-JP', timeSetting);

    document.getElementById("clock").textContent = timeString;
}

nowTime(); // 初回表示
setInterval(nowTime, 1000); // 毎秒更新