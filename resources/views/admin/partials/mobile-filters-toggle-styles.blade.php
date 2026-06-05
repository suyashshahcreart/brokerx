    .booking-filters-mobile-bar {
        display: none;
    }

    .booking-filters-mobile-bar .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        min-height: 34px;
    }

    .booking-filters-mobile-bar .filter-toggle-icon {
        transition: transform 0.2s ease;
        font-size: 1rem;
    }

    .booking-filters-panel.filters-expanded .filter-toggle-icon {
        transform: rotate(180deg);
    }

    .booking-filters-body {
        position: relative;
    }

    @media (max-width: 767.98px) {
        .booking-filters-mobile-bar {
            display: block;
        }

        .booking-filters-panel:not(.filters-expanded) .booking-filters-body {
            display: none;
        }

        .booking-filters-panel.filters-expanded .booking-filters-body {
            display: block;
            margin-top: 0.5rem;
            padding-top: 1.75rem;
        }

        .booking-filters-panel:not(.filters-expanded) {
            padding-bottom: 0.35rem;
        }
    }

    @media (min-width: 768px) {
        .booking-filters-panel .booking-filters-body {
            display: block !important;
        }
    }
