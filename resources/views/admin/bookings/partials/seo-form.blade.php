<form method="POST" id="seoForm" action="{{ route('admin.tours.updateSeo', $tour) }}" class="needs-validation" novalidate >
    @csrf
    @method('PUT')
    <!-- SEO Meta Tags -->
    <div class="card mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <h5 class="card-title mb-0"> <i class="ri-bookmark-line"></i>  SEO Meta Tags</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="tour_meta_title" class="form-control"
                        placeholder="e.g, PROP PIK virtual Tour."
                            value="{{ $tour->meta_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_meta_keywords">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="tour_meta_keywords" class="form-control"
                        placeholder="e.g, Prop Pik virtua tour,India"
                            value="{{ $tour->meta_keywords }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label" for="tour_meta_description">Meta Description</label>
                    <textarea name="meta_description" id="tour_meta_description" class="form-control"
                    placeholder="e.g, Explore next-gen Web Virtual Reality powered by AI with PROP PiK. Create,view and share interactive virtual property tours instantly and professionally."
                    rows="2">{{ $tour->meta_description }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_canonical_url">Canonical URL</label>
                        <input type="url" name="canonical_url" id="tour_canonical_url" class="form-control"
                        placeholder="e.g, https://www.example.com/your-tour"
                            value="{{ $tour->canonical_url }}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-0">
                        <label class="form-label" for="tour_meta_robots">Meta Robots</label>
                        <input type="text" name="meta_robots" id="tour_meta_robots" class="form-control"
                            value="{{ $tour->meta_robots }}" placeholder="e.g, index, follow">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="gtm_tag">GTM tag</label>
                    <input type="text" name="gtm_tag" id="gtm_tag" class="form-control"
                    placeholder="e.g, GTM-Tag-7458945"
                        value="{{ $tour->gtm_tag }}">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Open Graph / Social Media -->
    <div class="d-none card mb-3">
        <div class="card-header bg-secondary-subtle border-secondary">
            <h5 class="card-title mb-0"> <i class="ri-twitter-line"></i>  Open Graph / Social Media</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_og_title">OG Title</label>
                        <input type="text" name="og_title" id="tour_og_title" class="form-control"
                            value="{{ $tour->og_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_og_image">OG Image URL</label>
                        <input type="text" name="og_image" id="tour_og_image" class="form-control"
                            value="{{ $tour->og_image }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="tour_og_description">OG Description</label>
                <textarea name="og_description" id="tour_og_description" class="form-control"
                    rows="2">{{ $tour->og_description }}</textarea>
            </div>

            <h6 class="mt-3 mb-2">Twitter Card</h6>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_twitter_title">Twitter Title</label>
                        <input type="text" name="twitter_title" id="tour_twitter_title" class="form-control"
                            value="{{ $tour->twitter_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_twitter_image">Twitter Image URL</label>
                        <input type="text" name="twitter_image" id="tour_twitter_image" class="form-control"
                            value="{{ $tour->twitter_image }}">
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_twitter_description">Twitter Description</label>
                <textarea name="twitter_description" id="tour_twitter_description" class="form-control"
                    rows="2">{{ $tour->twitter_description }}</textarea>
            </div>
        </div>
    </div>

    <!-- Structured Data -->
    <div class="card mb-3 d-none">
        <div class="card-header bg-success-subtle border-success">
            <h5 class="card-title mb-0"> <i class="ri-file-code-line"></i> Structured Data (JSON-LD)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_structured_data_type">Structured Data Type</label>
                        <select name="structured_data_type" id="tour_structured_data_type" class="form-select">
                            <option value="">Select type</option>
                            <option value="Article" @selected($tour->structured_data_type == 'Article')>Article</option>
                            <option value="Place" @selected($tour->structured_data_type == 'Place')>Place</option>
                            <option value="Event" @selected($tour->structured_data_type == 'Event')>Event</option>
                            <option value="Product" @selected($tour->structured_data_type == 'Product')>Product</option>
                            <option value="TouristAttraction"
                                @selected($tour->structured_data_type == 'TouristAttraction')>TouristAttraction</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_structured_data">Structured Data JSON</label>
                <textarea name="structured_data" id="tour_structured_data" class="form-control font-monospace" rows="5"
                    placeholder='{"@context": "https://schema.org", "@type": "TouristAttraction"}'>{!! is_array($tour->structured_data) ? json_encode($tour->structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $tour->structured_data !!}</textarea>
                <small class="text-muted">Enter valid JSON-LD structured data</small>
            </div>
        </div>
    </div>

    <!-- Custom Code -->
    <div class="card mb-3">
        <div class="card-header bg-warning-subtle border-warning">
            <h5 class="card-title mb-0"> <i class="ri-code-s-slash-line"></i> Custom Code Injection</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="tour_header_code">Header Code (before &lt;/head&gt;)</label>
                <textarea name="header_code" id="tour_header_code" class="form-control font-monospace"
                placeholder="e.g, &lt;meta name='custom' content='value' /&gt;"
                    rows="4">{{ $tour->header_code }}</textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the header</small>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_footer_code">Footer Code (before &lt;/body&gt;)</label>
                <textarea name="footer_code" id="tour_footer_code" class="form-control font-monospace"
                placeholder="e.g, console.log('Footer scripts loaded');"
                    rows="4">{{ $tour->footer_code }}</textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the footer</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Update SEO</button>
    </div>
</form>