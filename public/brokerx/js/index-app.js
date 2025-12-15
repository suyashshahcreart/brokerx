const featuredProperties = [
    {
        id: 1,
        image: 'https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Modern Villa in Beverly Hills',
        location: 'Beverly Hills, CA',
        price: '$12,500,000',
        beds: 5,
        baths: 7,
        area: '8,000 sqft'
    },
    {
        id: 2,
        image: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Penthouse with Ocean View',
        location: 'Malibu, CA',
        price: '$9,800,000',
        beds: 3,
        baths: 4,
        area: '4,500 sqft'
    },
    {
        id: 3,
        image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Classic Estate in The Hamptons',
        location: 'The Hamptons, NY',
        price: '$15,000,000',
        beds: 7,
        baths: 9,
        area: '12,000 sqft'
    },
    {
        id: 4,
        image: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Luxury Waterfront Villa',
        location: 'Miami Beach, FL',
        price: '$8,200,000',
        beds: 4,
        baths: 5,
        area: '6,200 sqft'
    },
    {
        id: 5,
        image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Contemporary Downtown Penthouse',
        location: 'Manhattan, NY',
        price: '$11,800,000',
        beds: 3,
        baths: 4,
        area: '3,800 sqft'
    },
    {
        id: 6,
        image: 'https://images.unsplash.com/photo-1566908829077-85d6b503b7e8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Modern Architectural Masterpiece',
        location: 'Hollywood Hills, CA',
        price: '$18,500,000',
        beds: 6,
        baths: 8,
        area: '9,500 sqft'
    },
    {
        id: 7,
        image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2075&q=80',
        title: 'Luxury Urban Residence',
        location: 'Downtown LA, CA',
        price: '$7,300,000',
        beds: 4,
        baths: 5,
        area: '5,200 sqft'
    },
    {
        id: 8,
        image: 'https://images.unsplash.com/photo-1600607687644-aac4c3eac7f4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        title: 'Exclusive Mountain Resort',
        location: 'Aspen, CO',
        price: '$22,000,000',
        beds: 8,
        baths: 10,
        area: '15,000 sqft'
    }
];

// Multi-select location functionality
class MultiSelectLocation {
    constructor(selector) {
        this.container = document.querySelector(selector);
        this.trigger = this.container.querySelector('.multi-select-trigger');
        this.dropdown = this.container.querySelector('.multi-select-dropdown');
        this.options = this.container.querySelectorAll('.multi-select-option');
        this.tagsContainer = document.getElementById('selectedLocationTags');
        this.selectedValues = new Set();
        
        this.init();
    }
    
    init() {
        this.trigger.addEventListener('click', () => this.toggleDropdown());
        this.options.forEach(option => {
            option.addEventListener('click', () => this.selectOption(option));
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }
    
    toggleDropdown() {
        const isOpen = this.dropdown.classList.contains('show');
        if (isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        this.dropdown.classList.add('show');
        this.trigger.classList.add('active');
    }
    
    closeDropdown() {
        this.dropdown.classList.remove('show');
        this.trigger.classList.remove('active');
    }
    
    selectOption(option) {
        const value = option.dataset.value;
        const text = option.textContent;
        
        if (this.selectedValues.has(value)) {
            this.selectedValues.delete(value);
            option.classList.remove('selected');
            this.removeTag(value);
        } else {
            this.selectedValues.add(value);
            option.classList.add('selected');
            this.addTag(value, text);
        }
        
        this.updatePlaceholder();
    }
    
    addTag(value, text) {
        const tag = document.createElement('div');
        tag.className = 'selected-tag';
        tag.dataset.value = value;
        tag.innerHTML = `
            <span>${text}</span>
            <i class="fas fa-times remove-tag"></i>
        `;
        
        // Make the entire tag clickable to unselect
        tag.addEventListener('click', (e) => {
            e.stopPropagation();
            this.unselectTag(value);
        });
        
        const removeBtn = tag.querySelector('.remove-tag');
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.unselectTag(value);
        });
        
        this.tagsContainer.appendChild(tag);
    }
    
    unselectTag(value) {
        // Remove from selected values
        this.selectedValues.delete(value);
        
        // Update dropdown option
        this.options.forEach(opt => {
            if (opt.dataset.value === value) {
                opt.classList.remove('selected');
            }
        });
        
        // Remove tag with animation
        this.removeTag(value);
        
        // Update placeholder
        this.updatePlaceholder();
    }
    
    removeTag(value) {
        const tag = this.tagsContainer.querySelector(`[data-value="${value}"]`);
        if (tag) {
            tag.style.animation = 'tagFadeOut 0.2s ease';
            setTimeout(() => {
                if (tag.parentNode) {
                    tag.parentNode.removeChild(tag);
                }
            }, 200);
        }
    }
    
    updatePlaceholder() {
        const placeholder = this.trigger.querySelector('.placeholder');
        if (this.selectedValues.size === 0) {
            placeholder.textContent = 'Select locations...';
            placeholder.style.color = '#6c757d';
        } else {
            placeholder.textContent = `${this.selectedValues.size} location(s) selected`;
            placeholder.style.color = '#495057';
        }
    }
    
    getSelectedValues() {
        return Array.from(this.selectedValues);
    }
    
    clearAll() {
        this.selectedValues.clear();
        this.tagsContainer.innerHTML = '';
        this.options.forEach(option => option.classList.remove('selected'));
        this.updatePlaceholder();
    }
}

// Multi-select property type functionality
class MultiSelectPropertyType {
    constructor(selector) {
        this.container = document.querySelector(selector);
        this.trigger = this.container.querySelector('.multi-select-trigger');
        this.dropdown = this.container.querySelector('.multi-select-dropdown');
        this.options = this.container.querySelectorAll('.multi-select-option');
        this.tagsContainer = document.getElementById('selectedTypeTags');
        this.selectedValues = new Set();
        
        this.init();
    }
    
    init() {
        this.trigger.addEventListener('click', () => this.toggleDropdown());
        this.options.forEach(option => {
            option.addEventListener('click', () => this.selectOption(option));
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }
    
    toggleDropdown() {
        const isOpen = this.dropdown.classList.contains('show');
        if (isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        this.dropdown.classList.add('show');
        this.trigger.classList.add('active');
    }
    
    closeDropdown() {
        this.dropdown.classList.remove('show');
        this.trigger.classList.remove('active');
    }
    
    selectOption(option) {
        const value = option.dataset.value;
        const text = option.textContent;
        
        if (this.selectedValues.has(value)) {
            this.selectedValues.delete(value);
            option.classList.remove('selected');
            this.removeTag(value);
        } else {
            this.selectedValues.add(value);
            option.classList.add('selected');
            this.addTag(value, text);
        }
        
        this.updatePlaceholder();
    }
    
    addTag(value, text) {
        const tag = document.createElement('div');
        tag.className = 'selected-tag';
        tag.dataset.value = value;
        tag.innerHTML = `
            <span>${text}</span>
            <i class="fas fa-times remove-tag"></i>
        `;
        
        // Make the entire tag clickable to unselect
        tag.addEventListener('click', (e) => {
            e.stopPropagation();
            this.unselectTag(value);
        });
        
        const removeBtn = tag.querySelector('.remove-tag');
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.unselectTag(value);
        });
        
        this.tagsContainer.appendChild(tag);
    }
    
    unselectTag(value) {
        // Remove from selected values
        this.selectedValues.delete(value);
        
        // Update dropdown option
        this.options.forEach(opt => {
            if (opt.dataset.value === value) {
                opt.classList.remove('selected');
            }
        });
        
        // Remove tag with animation
        this.removeTag(value);
        
        // Update placeholder
        this.updatePlaceholder();
    }
    
    removeTag(value) {
        const tag = this.tagsContainer.querySelector(`[data-value="${value}"]`);
        if (tag) {
            tag.style.animation = 'tagFadeOut 0.2s ease';
            setTimeout(() => {
                if (tag.parentNode) {
                    tag.parentNode.removeChild(tag);
                }
            }, 200);
        }
    }
    
    updatePlaceholder() {
        const placeholder = this.trigger.querySelector('.placeholder');
        if (this.selectedValues.size === 0) {
            placeholder.textContent = 'Select property types...';
            placeholder.style.color = '#6c757d';
        } else {
            placeholder.textContent = `${this.selectedValues.size} type(s) selected`;
            placeholder.style.color = '#495057';
        }
    }
    
    getSelectedValues() {
        return Array.from(this.selectedValues);
    }
    
    clearAll() {
        this.selectedValues.clear();
        this.tagsContainer.innerHTML = '';
        this.options.forEach(option => option.classList.remove('selected'));
        this.updatePlaceholder();
    }
}

// Advanced Filter Modal functionality
class AdvancedFilterModal {
    constructor() {
        this.modal = new bootstrap.Modal(document.getElementById('advancedFilterModal'));
        this.filters = {
            bedrooms: '',
            bathrooms: '',
            age: '',
            furnished: '',
            minArea: '',
            maxArea: '',
            amenities: []
        };
        
        this.init();
    }
    
    init() {
        // Advanced filter button
        document.getElementById('advancedFilterBtn').addEventListener('click', () => {
            this.modal.show();
        });
        
        // Apply advanced filters
        document.getElementById('applyAdvancedFilters').addEventListener('click', () => {
            this.applyFilters();
        });
        
        // Clear advanced filters
        document.getElementById('clearAdvancedFilters').addEventListener('click', () => {
            this.clearFilters();
        });
        
        // Track amenity changes
        const amenityCheckboxes = document.querySelectorAll('#advancedFilterModal .form-check-input');
        amenityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateAmenities());
        });
    }
    
    applyFilters() {
        // Get all filter values
        this.filters.bedrooms = document.getElementById('advancedBedrooms').value;
        this.filters.bathrooms = document.getElementById('advancedBathrooms').value;
        this.filters.age = document.getElementById('advancedAge').value;
        this.filters.furnished = document.getElementById('advancedFurnished').value;
        this.filters.minArea = document.getElementById('minArea').value;
        this.filters.maxArea = document.getElementById('maxArea').value;
        
        // Show loading state
        const applyBtn = document.getElementById('applyAdvancedFilters');
        applyBtn.classList.add('btn-loading');
        
        // Simulate filter application
        setTimeout(() => {
            applyBtn.classList.remove('btn-loading');
            this.modal.hide();
            
            // Show success message
            this.showFilterMessage('Advanced filters applied successfully!');
        }, 1000);
    }
    
    updateAmenities() {
        const checkboxes = document.querySelectorAll('#advancedFilterModal .form-check-input:checked');
        this.filters.amenities = Array.from(checkboxes).map(cb => cb.value);
    }
    
    clearFilters() {
        // Reset all form elements
        document.getElementById('advancedBedrooms').value = '';
        document.getElementById('advancedBathrooms').value = '';
        document.getElementById('advancedAge').value = '';
        document.getElementById('advancedFurnished').value = '';
        document.getElementById('minArea').value = '';
        document.getElementById('maxArea').value = '';
        
        // Uncheck all amenities
        const amenityCheckboxes = document.querySelectorAll('#advancedFilterModal .form-check-input');
        amenityCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        this.filters = {
            bedrooms: '',
            bathrooms: '',
            age: '',
            furnished: '',
            minArea: '',
            maxArea: '',
            amenities: []
        };
        
        this.showFilterMessage('All advanced filters cleared!');
    }
    
    showFilterMessage(message) {
        // Create and show a toast message
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
        toast.style.cssText = 'top: 100px; right: 20px; z-index: 1055;';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }
    
    getFilters() {
        return this.filters;
    }
}

// Price range functionality
function initPriceRange() {
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    
    // Add number formatting on input
    if (minPrice) {
        minPrice.addEventListener('input', (e) => {
            const value = e.target.value;
            if (value) {
                // Optional: Add validation to ensure min < max
                const maxValue = maxPrice.value;
                if (maxValue && parseInt(value) > parseInt(maxValue)) {
                    showToastMessage('Minimum price cannot be greater than maximum price', 'warning');
                }
            }
        });
    }
    
    if (maxPrice) {
        maxPrice.addEventListener('input', (e) => {
            const value = e.target.value;
            if (value) {
                // Optional: Add validation to ensure max > min
                const minValue = minPrice.value;
                if (minValue && parseInt(value) < parseInt(minValue)) {
                    showToastMessage('Maximum price cannot be less than minimum price', 'warning');
                }
            }
        });
    }
}

// Main filter application
function applyFilters() {
    const selectedPropertyTypes = multiSelectPropertyType.getSelectedValues();
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    const selectedLocations = multiSelectLocation.getSelectedValues();
    const advancedFilters = advancedFilterModal.getFilters();
    
    // Show loading state
    const applyBtn = document.getElementById('applyFiltersBtn');
    applyBtn.classList.add('btn-loading');
    
    // Simulate filter application
    setTimeout(() => {
        applyBtn.classList.remove('btn-loading');
        
        // Create filter summary
        let filterSummary = [];
        if (selectedPropertyTypes.length > 0) filterSummary.push(`Types: ${selectedPropertyTypes.length} selected`);
        if (selectedLocations.length > 0) filterSummary.push(`Locations: ${selectedLocations.length} selected`);
        if (minPrice) filterSummary.push(`Min Price: $${parseInt(minPrice).toLocaleString()}`);
        if (maxPrice) filterSummary.push(`Max Price: $${parseInt(maxPrice).toLocaleString()}`);
        
        const message = filterSummary.length > 0 
            ? `Filters applied: ${filterSummary.join(', ')}`
            : 'All filters cleared!';
            
        showToastMessage(message, 'success');
    }, 800);
}

function showToastMessage(message, type = 'success') {
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${bgClass} border-0 position-fixed`;
    toast.style.cssText = 'top: 100px; right: 20px; z-index: 1055;';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}

// Mobile Filter Modal functionality
class MobileFilterModal {
    constructor() {
        this.modal = new bootstrap.Modal(document.getElementById('mobileFilterModal'));
        this.mobileLocationSelect = null;
        this.mobileTypeSelect = null;
        this.filters = {
            propertyTypes: [],
            priceRange: '5000000',
            locations: [],
            bedrooms: '',
            bathrooms: '',
            age: '',
            furnished: '',
            minArea: '',
            maxArea: '',
            amenities: []
        };
        
        this.init();
    }
    
    init() {
        // Initialize mobile location multi-select
        this.initMobileLocationSelect();
        
        // Initialize mobile property type multi-select
        this.initMobileTypeSelect();
        
        // Initialize mobile price range
        this.initMobilePriceRange();
        
        // Mobile filter button
        document.getElementById('mobileFilterBtn').addEventListener('click', () => {
            this.syncFiltersFromDesktop();
            this.modal.show();
        });
        
        // Apply mobile filters
        document.getElementById('applyMobileFilters').addEventListener('click', () => {
            this.applyFilters();
        });
        
        // Clear mobile filters
        document.getElementById('clearMobileFilters').addEventListener('click', () => {
            this.clearFilters();
        });
        
        // Track amenity changes
        const amenityCheckboxes = document.querySelectorAll('#mobileFilterModal .form-check-input');
        amenityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateAmenities());
        });
    }
    
    initMobileLocationSelect() {
        const container = document.getElementById('mobileLocationSelect');
        if (!container) return;
        
        this.mobileLocationSelect = new MultiSelectLocation('#mobileLocationSelect');
        
        // Update the container reference for mobile
        this.mobileLocationSelect.tagsContainer = document.getElementById('mobileSelectedLocationTags');
    }
    
    initMobileTypeSelect() {
        const container = document.getElementById('mobileTypeSelect');
        if (!container) return;
        
        this.mobileTypeSelect = new MultiSelectPropertyType('#mobileTypeSelect');
        
        // Update the container reference for mobile
        this.mobileTypeSelect.tagsContainer = document.getElementById('mobileSelectedTypeTags');
    }
    
    initMobilePriceRange() {
        const priceRange = document.getElementById('mobilePriceRange');
        const priceValue = document.getElementById('mobilePriceValue');
        
        if (priceRange && priceValue) {
            priceRange.addEventListener('input', (e) => {
                const value = parseInt(e.target.value);
                priceValue.textContent = `$${value.toLocaleString()}`;
                this.filters.priceRange = value;
            });
        }
    }
    
    syncFiltersFromDesktop() {
        // Sync property types
        if (multiSelectPropertyType && this.mobileTypeSelect) {
            const selectedTypes = multiSelectPropertyType.getSelectedValues();
            // Clear mobile selections first
            this.mobileTypeSelect.clearAll();
            // Apply desktop selections to mobile
            selectedTypes.forEach(value => {
                const option = this.mobileTypeSelect.container.querySelector(`[data-value="${value}"]`);
                if (option) {
                    this.mobileTypeSelect.selectOption(option);
                }
            });
        }
        
        // Sync price range - now using min/max inputs
        const desktopMinPrice = document.getElementById('minPrice').value;
        const desktopMaxPrice = document.getElementById('maxPrice').value;
        
        // For now, keep mobile as range slider but sync with desktop values
        // You can update mobile to min/max inputs later if needed
        if (desktopMaxPrice) {
            document.getElementById('mobilePriceRange').value = desktopMaxPrice;
            document.getElementById('mobilePriceValue').textContent = `$${parseInt(desktopMaxPrice).toLocaleString()}`;
        }
        
        // Sync locations
        if (multiSelectLocation && this.mobileLocationSelect) {
            const selectedLocations = multiSelectLocation.getSelectedValues();
            // Clear mobile selections first
            this.mobileLocationSelect.clearAll();
            // Apply desktop selections to mobile
            selectedLocations.forEach(value => {
                const option = this.mobileLocationSelect.container.querySelector(`[data-value="${value}"]`);
                if (option) {
                    this.mobileLocationSelect.selectOption(option);
                }
            });
        }
    }
    
    applyFilters() {
        // Get all filter values
        this.filters.propertyTypes = this.mobileTypeSelect ? this.mobileTypeSelect.getSelectedValues() : [];
        this.filters.priceRange = document.getElementById('mobilePriceRange').value;
        this.filters.bedrooms = document.getElementById('mobileBedrooms').value;
        this.filters.bathrooms = document.getElementById('mobileBathrooms').value;
        this.filters.age = document.getElementById('mobileAge').value;
        this.filters.furnished = document.getElementById('mobileFurnished').value;
        this.filters.minArea = document.getElementById('mobileMinArea').value;
        this.filters.maxArea = document.getElementById('mobileMaxArea').value;
        
        if (this.mobileLocationSelect) {
            this.filters.locations = this.mobileLocationSelect.getSelectedValues();
        }
        
        // Show loading state
        const applyBtn = document.getElementById('applyMobileFilters');
        applyBtn.classList.add('btn-loading');
        applyBtn.textContent = 'Applying...';
        
        // Simulate filter application
        setTimeout(() => {
            applyBtn.classList.remove('btn-loading');
            applyBtn.textContent = 'Apply Filters';
            this.modal.hide();
            
            // Sync filters back to desktop
            this.syncFiltersToDesktop();
            
            // Show success message
            this.showFilterMessage('Mobile filters applied successfully!');
        }, 1000);
    }
    
    syncFiltersToDesktop() {
        // Sync to desktop filters
        document.getElementById('typeFilter').value = this.filters.type;
        document.getElementById('priceRange').value = this.filters.priceRange;
        document.getElementById('priceValue').textContent = `$${parseInt(this.filters.priceRange).toLocaleString()}`;
        
        // Sync locations to desktop
        if (multiSelectLocation && this.mobileLocationSelect) {
            multiSelectLocation.clearAll();
            this.filters.locations.forEach(value => {
                const option = multiSelectLocation.container.querySelector(`[data-value="${value}"]`);
                if (option) {
                    multiSelectLocation.selectOption(option);
                }
            });
        }
    }
    
    updateAmenities() {
        const checkboxes = document.querySelectorAll('#mobileFilterModal .form-check-input:checked');
        this.filters.amenities = Array.from(checkboxes).map(cb => cb.value);
    }
    
    clearFilters() {
        // Reset price range
        document.getElementById('mobilePriceRange').value = '5000000';
        document.getElementById('mobilePriceValue').textContent = '$5,000,000';
        document.getElementById('mobileBedrooms').value = '';
        document.getElementById('mobileBathrooms').value = '';
        document.getElementById('mobileAge').value = '';
        document.getElementById('mobileFurnished').value = '';
        document.getElementById('mobileMinArea').value = '';
        document.getElementById('mobileMaxArea').value = '';
        
        // Clear mobile location selection
        if (this.mobileLocationSelect) {
            this.mobileLocationSelect.clearAll();
        }
        
        // Clear mobile property type selection
        if (this.mobileTypeSelect) {
            this.mobileTypeSelect.clearAll();
        }
        
        // Uncheck all amenities
        const amenityCheckboxes = document.querySelectorAll('#mobileFilterModal .form-check-input');
        amenityCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset filters object
        this.filters = {
            propertyTypes: [],
            priceRange: '5000000',
            locations: [],
            bedrooms: '',
            bathrooms: '',
            age: '',
            furnished: '',
            minArea: '',
            maxArea: '',
            amenities: []
        };
        
        this.showFilterMessage('All mobile filters cleared!');
    }
    
    showFilterMessage(message) {
        showToastMessage(message, 'success');
    }
    
    getFilters() {
        return this.filters;
    }
}

// Load and display featured properties
function loadFeaturedProperties() {
    const propertiesGrid = document.getElementById('propertiesGrid');
    if (!propertiesGrid) return;
    
    propertiesGrid.innerHTML = '';
    
    featuredProperties.forEach(property => {
        const propertyCard = createPropertyCard(property);
        propertiesGrid.appendChild(propertyCard);
    });
}

// Create property card element
function createPropertyCard(property) {
    const colDiv = document.createElement('div');
    colDiv.className = 'col-lg-4 col-md-6';
    
    colDiv.innerHTML = `
        <div class="property-card h-100">
            <div class="property-image-container">
                <div class="image-placeholder loading" id="property-placeholder-${property.id}">
                    <i class="fas fa-home image-placeholder-icon"></i>
                    <div class="image-placeholder-text">Loading image...</div>
                </div>
                <img src="${property.image}" 
                     alt="${property.title}" 
                     class="property-image loading"
                     data-property-id="${property.id}"
                     onload="handlePropertyImageLoad(this)"
                     onerror="handlePropertyImageError(this)">
                <div class="property-badge">For Sale</div>
                <button class="favorite-btn" onclick="toggleFavorite(${property.id})">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            <div class="property-details">
                <h5 class="property-title">${property.title}</h5>
                <p class="property-location">
                    <i class="fas fa-map-marker-alt"></i>
                    ${property.location}
                </p>
                <div class="property-price">${property.price}</div>
                <div class="property-specs">
                    <span><i class="fas fa-bed"></i> ${property.beds}</span>
                    <span><i class="fas fa-bath"></i> ${property.baths}</span>
                    <span><i class="fas fa-ruler-combined"></i> ${property.area}</span>
                </div>
            </div>
        </div>
    `;
    
    return colDiv;
}

// Property image loading handlers
window.handlePropertyImageLoad = function(img) {
    const propertyId = img.getAttribute('data-property-id');
    const placeholder = document.getElementById(`property-placeholder-${propertyId}`);
    
    // Remove loading classes and show image
    img.classList.remove('loading');
    img.classList.add('loaded');
    
    // Hide placeholder
    if (placeholder) {
        placeholder.style.display = 'none';
    }
};

window.handlePropertyImageError = function(img) {
    const propertyId = img.getAttribute('data-property-id');
    const placeholder = document.getElementById(`property-placeholder-${propertyId}`);
    const originalSrc = img.src;
    
    // Try a fallback image first
    if (!img.hasAttribute('data-fallback-tried')) {
        img.setAttribute('data-fallback-tried', 'true');
        // Use a more reliable fallback image
        img.src = 'https://via.placeholder.com/800x600/0EA5E9/FFFFFF?text=Luxury+Property';
        return;
    }
    
    // Hide failed image
    img.classList.add('error');
    
    // Show error placeholder with retry option
    if (placeholder) {
        placeholder.classList.remove('loading');
        placeholder.classList.add('clickable');
        placeholder.innerHTML = `
            <i class="fas fa-image image-placeholder-icon"></i>
            <div class="image-placeholder-text">Image not available</div>
            <button class="image-placeholder-retry" onclick="retryPropertyImageLoad('${propertyId}', '${originalSrc}')">
                Try Again
            </button>
        `;
    }
};

// Retry property image loading
window.retryPropertyImageLoad = function(propertyId, imageSrc) {
    const img = document.querySelector(`[data-property-id="${propertyId}"]`);
    const placeholder = document.getElementById(`property-placeholder-${propertyId}`);
    
    if (img && placeholder) {
        // Reset placeholder to loading state
        placeholder.classList.remove('clickable');
        placeholder.classList.add('loading');
        placeholder.innerHTML = `
            <i class="fas fa-home image-placeholder-icon"></i>
            <div class="image-placeholder-text">Loading image...</div>
        `;
        
        // Reset image and fallback flag
        img.classList.remove('error', 'loaded');
        img.classList.add('loading');
        img.removeAttribute('data-fallback-tried');
        
        // Reload image with cache busting
        img.src = imageSrc + (imageSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
    }
};

// Toggle favorite status
window.toggleFavorite = function(propertyId) {
    const btn = document.querySelector(`button[onclick="toggleFavorite(${propertyId})"] i`);
    if (btn.classList.contains('far')) {
        btn.classList.remove('far');
        btn.classList.add('fas');
        showToastMessage('Property added to favorites!', 'success');
    } else {
        btn.classList.remove('fas');
        btn.classList.add('far');
        showToastMessage('Property removed from favorites!', 'info');
    }
};

// Show toast message
function showToastMessage(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.textContent = message;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show with animation
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Global variables for multi-select components
let multiSelectLocation;
let multiSelectPropertyType;
let advancedFilterModal;
let mobileFilterModal;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize multi-select components
    multiSelectLocation = new MultiSelectLocation('#customLocationSelect');
    multiSelectPropertyType = new MultiSelectPropertyType('#customTypeSelect');
    
    // Initialize modals
    advancedFilterModal = new AdvancedFilterModal();
    mobileFilterModal = new MobileFilterModal();
    
    // Initialize price range inputs
    initPriceRange();
    
    // Initialize apply filters button
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    
    // Load featured properties
    loadFeaturedProperties();
    
    // Initialize Google Reviews Section
    initGoogleReviews();
    
    // Initialize audio showcase buttons
    initAudioShowcase();

    console.log('Multi-select filters initialized successfully');
});

// ================================
// GOOGLE REVIEWS SECTION FUNCTIONALITY
// ================================

function initGoogleReviews() {
    initReviewsSlider();
    initAnimatedCounters();
    initGoogleReviewButton();
    
    console.log('Google Reviews section initialized');
}

// Reviews Slider Class
class ReviewsSlider {
    constructor() {
        this.track = document.getElementById('reviewsTrack');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.indicators = document.querySelectorAll('.indicator');
        this.reviews = document.querySelectorAll('.review-card');
        
        this.currentSlide = 0;
        this.totalSlides = this.reviews.length;
        this.isAutoPlaying = true;
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        if (!this.track) return;
        
        // Event listeners
        this.prevBtn?.addEventListener('click', () => this.prevSlide());
        this.nextBtn?.addEventListener('click', () => this.nextSlide());
        
        // Indicator clicks
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Auto-play
        this.startAutoPlay();
        
        // Pause auto-play on hover
        this.track.addEventListener('mouseenter', () => this.pauseAutoPlay());
        this.track.addEventListener('mouseleave', () => this.resumeAutoPlay());
        
        // Touch/swipe support
        this.initTouchSupport();
    }
    
    updateSlider() {
        // Update track position
        const translateX = -this.currentSlide * 100;
        this.track.style.transform = `translateX(${translateX}%)`;
        
        // Update indicators
        this.indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentSlide);
        });
        
        // Update review card states
        this.reviews.forEach((review, index) => {
            review.classList.toggle('active', index === this.currentSlide);
        });
    }
    
    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.updateSlider();
    }
    
    prevSlide() {
        this.currentSlide = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
        this.updateSlider();
    }
    
    goToSlide(index) {
        this.currentSlide = index;
        this.updateSlider();
    }
    
    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => {
            if (this.isAutoPlaying) {
                this.nextSlide();
            }
        }, 6000); // Change slide every 6 seconds
    }
    
    pauseAutoPlay() {
        this.isAutoPlaying = false;
    }
    
    resumeAutoPlay() {
        this.isAutoPlaying = true;
    }
    
    initTouchSupport() {
        let startX = 0;
        let endX = 0;
        
        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        this.track.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        });
    }
}

function initReviewsSlider() {
    new ReviewsSlider();
}

// Animated Counters for Stats
function initAnimatedCounters() {
    const counters = document.querySelectorAll('[data-count]');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element) {
    const target = parseFloat(element.getAttribute('data-count'));
    if (Number.isNaN(target)) return;

    const decimalsAttr = element.getAttribute('data-decimals');
    const decimals = decimalsAttr !== null ? Math.max(0, parseInt(decimalsAttr, 10)) : (target % 1 !== 0 ? 1 : 0);
    const prefix = element.getAttribute('data-prefix') || '';
    const suffix = element.getAttribute('data-suffix') || '';
    const duration = parseInt(element.getAttribute('data-duration'), 10) || 2000;
    const frameTime = 16;
    const steps = Math.max(1, Math.round(duration / frameTime));
    const increment = target / steps;
    let current = 0;

    const formatValue = (value, forceFinal = false) => {
        let formatted;
        if (decimals > 0) {
            formatted = (forceFinal ? target : value).toFixed(decimals);
        } else {
            formatted = forceFinal ? Math.round(target).toString() : Math.floor(value).toString();
        }
        return `${prefix}${formatted}${suffix}`;
    };

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = formatValue(target, true);
            clearInterval(timer);
        } else {
            element.textContent = formatValue(current);
        }
    }, frameTime);
}

// Google Review Button
function initGoogleReviewButton() {
    const googleBtn = document.getElementById('googleReviewBtn');
    
    if (googleBtn) {
        googleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Simulate opening Google Reviews (replace with actual Google Reviews URL)
            console.log('Opening Google Reviews...');
            // window.open('https://g.page/r/YOUR_GOOGLE_BUSINESS_ID/review', '_blank');
            
            // Show feedback message
            showNotification('Thank you for your interest! Google Reviews will open shortly.', 'success');
        });
    }
}

// Utility function for notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="material-symbols-outlined">
                ${type === 'success' ? 'check_circle' : 'info'}
            </span>
            <span class="notification-text">${message}</span>
        </div>
    `;
    
    // Add notification styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        font-family: 'Roboto', sans-serif;
        font-size: 14px;
        max-width: 350px;
    `;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 4000);
}

// Add notification animations to CSS
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .notification-content .material-symbols-outlined {
        font-size: 20px;
    }
`;
document.head.appendChild(notificationStyles);

// ================================
// AUDIO SHOWCASE
// ================================

function initAudioShowcase() {
    const buttons = document.querySelectorAll('.audio-play-btn');
    if (!buttons.length) {
        return;
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetUrl = btn.getAttribute('data-yt');
            if (targetUrl) {
                window.open(targetUrl, '_blank', 'noopener');
            }
        });
    });
}