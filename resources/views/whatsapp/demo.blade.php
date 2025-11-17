<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Cloud API Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="mb-4">WhatsApp Cloud API – Laravel Demo (No Webhook)</h1>

        @if(session('response'))
            <div class="alert alert-info">
                <strong>API Response:</strong>
                <pre class="mt-2 bg-dark text-white p-3 rounded" style="max-height: 300px; overflow:auto;">{{ json_encode(session('response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif

        @if(session('templates'))
            <div class="alert alert-secondary">
                <strong>Templates List:</strong>
                <pre class="mt-2 bg-dark text-white p-3 rounded" style="max-height: 300px; overflow:auto;">{{ json_encode(session('templates'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif

        <div class="row g-4">
            {{-- SEND TEXT --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        Send Custom Text Message
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('whatsapp.sendText') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Phone (with country code)</label>
                                <input type="text" name="phone" class="form-control" placeholder="91XXXXXXXXXX">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="3">Hello from Laravel WhatsApp Demo!</textarea>
                            </div>
                            <button class="btn btn-primary">Send Text</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- SEND TEMPLATE --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        Send Template Message
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('whatsapp.sendTemplate') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="91XXXXXXXXXX">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="template_name" class="form-control" placeholder="your_template_name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Language Code</label>
                                <input type="text" name="language" class="form-control" value="en_US">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Body Variable 1</label>
                                <input type="text" name="body_var_1" class="form-control" placeholder="e.g. John">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Body Variable 2</label>
                                <input type="text" name="body_var_2" class="form-control" placeholder="e.g. #1234">
                            </div>
                            <button class="btn btn-success">Send Template</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- INTERACTIVE BUTTONS --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        Send Interactive Buttons
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('whatsapp.sendButtons') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="91XXXXXXXXXX">
                            </div>
                            <button class="btn btn-warning">Send Buttons Message</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- INTERACTIVE LIST --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        Send Interactive List
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('whatsapp.sendList') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="91XXXXXXXXXX">
                            </div>
                            <button class="btn btn-info text-white">Send List Message</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TEMPLATE MGMT --}}
            <div class="col-md-12">
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-dark text-white">
                        Template Management (List / Create / Delete)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <form method="POST" action="{{ route('whatsapp.templates.list') }}">
                                    @csrf
                                    <button class="btn btn-secondary w-100">List Templates</button>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <h6>Create Template (basic BODY only)</h6>
                                <form method="POST" action="{{ route('whatsapp.templates.create') }}">
                                    @csrf
                                    <div class="mb-2">
                                        <input type="text" name="name" class="form-control" placeholder="template_name">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="language" class="form-control" value="en_US">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="category" class="form-control" value="TRANSACTIONAL">
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="body_text" class="form-control" rows="2" placeholder="Hi {{1}}, your order {{2}} is confirmed."></textarea>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm">Create Template</button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <h6>Delete Template</h6>
                                <form method="POST" action="{{ route('whatsapp.templates.delete') }}">
                                    @csrf
                                    <div class="mb-2">
                                        <input type="text" name="name" class="form-control" placeholder="template_name">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="language" class="form-control" value="en_US">
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm">Delete Template</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row -->
    </div>
</body>
</html>

