document.addEventListener('DOMContentLoaded', function () {
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    function navigateToDate(date) {
        const url = new URL(window.location);
        url.searchParams.set('date', date);
        window.location.href = url.toString();
    }

    const datePicker = flatpickr("#datepicker", {
        locale: "ja",
        defaultDate: getUrlParameter('date') || "today",
        dateFormat: "Y/m/d",

        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                navigateToDate(dateStr);
            }
        },

        onClose: function (selectedDates, dateStr, instance) {
            const currentDate = getUrlParameter('date') || new Date().toISOString().split('T')[0].replace(/-/g, '/');
            if (dateStr && dateStr !== currentDate) {
                navigateToDate(dateStr);
            }
        }
    });

    const dateInput = document.getElementById('datepicker');
    if (dateInput) {
        dateInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const inputDate = e.target.value;
                if (inputDate) {
                    navigateToDate(inputDate);
                }
            }
        })
    }

});
