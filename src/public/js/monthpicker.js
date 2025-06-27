document.addEventListener('DOMContentLoaded', function () {
    const monthPicker = document.getElementById('monthPicker');
    if (!monthPicker) return;

    function getUrlParameter(name) {
        return new URLSearchParams(window.location.search).get(name);
    }

    function navigateToMonth(date) {
        const url = new URL(window.location);
        url.searchParams.set('date', date); //ex. 2025-06
        window.location.href = url.toString();
    }

    function getDefaultDate() {
        const param = getUrlParameter('date');
        return param ? `${param}-01` : new Date(); //"2025-06-01"形式にする
    }
    flatpickr("#monthPicker", {
        locale: "ja",
        dateFormat: "Y-m", //URLに渡す
        defaultDate: getDefaultDate(),
        plugins: [
            new monthSelectPlugin({
                shorthand: false,
                dateFormat: "Y/m" //表示形式
            })
        ],
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                const selected = flatpickr.formatDate(selectedDates[0], "Y-m");
                navigateToMonth(selected);
            }
        }
    });
});