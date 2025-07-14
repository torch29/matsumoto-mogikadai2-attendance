document.addEventListener('DOMContentLoaded', function () {
    function getUrlParameter(name) {
        // 現在のURLからパラメータを取得する関数
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // URLを更新してページを遷移する関数
    function navigateToDate(date) {
        const url = new URL(window.location);
        url.searchParams.set('date', date);
        window.location.href = url.toString();
    }

    // flatpickrの設定
    const datePicker = flatpickr("#datepicker", {
        locale: "ja",
        defaultDate: getUrlParameter('date') || "today",
        dateFormat: "Y/m/d",
        // 日付が選択されたときの処理
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                // 選択された日付でページを更新する
                navigateToDate(dateStr);
            }
        },
    });
});

