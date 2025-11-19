import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

// Holiday management
const holidayManager = {
    holidays: [],
    settingName: 'holidays',
    
    init() {
        this.loadHolidays();
        this.bindEvents();
        this.render();
        this.initCalendar();
    },

    initCalendar() {
        const calendarInput = document.getElementById('holidayCalendar');
        if (!calendarInput) return;
        flatpickr(calendarInput, {
            mode: "multiple",
            dateFormat: "Y-m-d",
            onChange: (selectedDates, dateStrArr) => {
                // Add all selected dates as holidays (no title by default)
                selectedDates.forEach(date => {
                    const dateStr = date.toISOString().slice(0, 10);
                    if (!this.holidays.some(h => h.date === dateStr)) {
                        this.holidays.push({
                            id: Date.now() + Math.random(),
                            title: "",
                            date: dateStr,
                            event: 'holiday'
                        });
                    }
                });
                this.saveHolidays();
                this.render();
            }
        });
    },

    // ...existing code...
};

document.addEventListener('DOMContentLoaded', () => {
    holidayManager.init();
});
