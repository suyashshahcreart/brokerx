// Holiday management
const holidayManager = {
    holidays: [],
    settingName: 'holidays',
    
    init() {
        this.loadHolidays();
        this.bindEvents();
        this.render();
    },
    
    async loadHolidays() {
        // Try to load from server first
        try {
            const response = await fetch(`/api/settings/${this.settingName}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data.value) {
                    this.holidays = Array.isArray(result.data.value) 
                        ? result.data.value 
                        : JSON.parse(result.data.value || '[]');
                    this.render();
                    return;
                }
            }
        } catch (error) {
            console.warn('Failed to load holidays from server:', error);
        }
        
        // Fallback to localStorage
        const stored = localStorage.getItem('holidays');
        this.holidays = stored ? JSON.parse(stored) : [];
        this.render();
    },
    
    async saveHolidays() {
        // Save to localStorage as backup
        localStorage.setItem('holidays', JSON.stringify(this.holidays));
        
        // Save to server
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            const response = await fetch('/api/settings/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: this.settingName,
                    value: this.holidays
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                console.error('Failed to save holidays:', result.message);
            }
        } catch (error) {
            console.error('Error saving holidays to server:', error);
        }
    },
    
    addHoliday(title, date) {
        if (!title || !date) {
            alert('Please provide both title and date');
            return false;
        }
        
        const holiday = {
            id: Date.now(),
            title: title.trim(),
            date: date,
            event: 'holiday'
        };
        
        this.holidays.push(holiday);
        this.saveHolidays();
        this.render();
        return true;
    },
    
    removeHoliday(id) {
        this.holidays = this.holidays.filter(h => h.id !== id);
        this.saveHolidays();
        this.render();
    },
    
    render() {
        const container = document.getElementById('holidayContainer');
        if (!container) return;
        
        if (this.holidays.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">No holidays added yet</p>';
            return;
        }
        
        container.innerHTML = this.holidays.map(holiday => `
            <div class="holiday-card card mb-2 border">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">${this.escapeHtml(holiday.title)}</h6>
                        <small class="text-muted">${this.formatDate(holiday.date)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-holiday" data-id="${holiday.id}">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        // Bind remove events
        container.querySelectorAll('.remove-holiday').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id);
                if (confirm('Are you sure you want to remove this holiday?')) {
                    this.removeHoliday(id);
                }
            });
        });
    },
    
    formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    bindEvents() {
        const addButton = document.getElementById('addHolidayBtn');
        const titleInput = document.getElementById('holidayTitle');
        const dateInput = document.getElementById('holidayDate');
        
        if (addButton && titleInput && dateInput) {
            addButton.addEventListener('click', (e) => {
                e.preventDefault();
                
                const title = titleInput.value;
                const date = dateInput.value;
                
                if (this.addHoliday(title, date)) {
                    titleInput.value = '';
                    dateInput.value = '';
                    titleInput.focus();
                }
            });
            
            // Allow Enter key to add holiday
            [titleInput, dateInput].forEach(input => {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addButton.click();
                    }
                });
            });
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    holidayManager.init();
});