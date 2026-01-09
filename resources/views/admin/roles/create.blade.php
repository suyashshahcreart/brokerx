@extends('admin.layouts.vertical', ['title' => 'Create Role', 'subTitle' => 'System'])

@section('content')
@php use Illuminate\Support\Str; @endphp
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create New Role</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.roles.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Role Details</h4>
                    <p class="text-muted mb-0">Specify the role name and assign appropriate permissions</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                            class="form-control @error('name') is-invalid @enderror" 
                            placeholder="e.g, role_name" required minlength="2" maxlength="255" 
                            pattern="[A-Za-z0-9_-]+" title="Role name may contain letters, numbers, underscores or hyphens (e.g., Admin_User, broker-role)">
                        <div class="invalid-feedback">
                            @error('name')
                                {{ $message }}
                            @else
                                Please provide a valid role name (letters, numbers, underscores or hyphens only, minimum 2 characters).
                            @enderror
                        </div>
                        @if(!$errors->has('name'))
                            <div class="valid-feedback">Looks good!</div>
                        @endif
                        <small class="form-text text-muted">Use letters, numbers, underscores or hyphens (e.g., Admin_User, broker-role, role1)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        @if(!empty($canAssignPermissions) && $canAssignPermissions)
                            <div class="card">
                                <div class="card-body">
                                    @php
                                        $selectedPermissions = collect(old('permissions', []));
                                        $chunkSize = max(1, ceil($groupedPermissions->count() / 2));
                                        $groupChunks = $groupedPermissions->isNotEmpty() ? $groupedPermissions->chunk($chunkSize) : collect();
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="select-all-permissions">
                                            <label class="form-check-label fw-semibold" for="select-all-permissions">Select All Permissions</label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-all-groups" data-expanded="true">
                                            <i class="ri-arrow-up-s-line me-1"></i>Collapse All
                                        </button>
                                    </div>
                                    <div class="row g-4">
                                        @foreach($groupChunks as $chunk)
                                            <div class="col-lg-6">
                                                @foreach($chunk as $groupName => $permissions)
                                                    @php
                                                        $groupId = Str::slug($groupName);
                                                    @endphp
                                                    <div class="border rounded mb-3 permission-group">
                                                        <div class="d-flex align-items-center justify-content-between px-3 py-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input group-checkbox" type="checkbox" id="group-{{ $groupId }}" data-group="{{ $groupId }}">
                                                                <label class="form-check-label fw-semibold" for="group-{{ $groupId }}">{{ $groupName }}</label>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary toggle-group" data-bs-toggle="collapse" data-bs-target="#group-list-{{ $groupId }}" aria-expanded="true">
                                                                <i class="ri-subtract-line"></i>
                                                            </button>
                                                        </div>
                                                        <div id="group-list-{{ $groupId }}" class="collapse show px-3 pb-3">
                                                            @foreach($permissions as $permission)
                                                                @php
                                                                    $label = Str::title(str_replace(['_', '-'], ' ', Str::after($permission->name, Str::before($permission->name, '_') . '_')));
                                                                    if ($label === '') {
                                                                        $label = Str::title(str_replace(['_', '-'], ' ', $permission->name));
                                                                    }
                                                                @endphp
                                                                <div class="form-check ms-2">
                                                                    <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                                        data-group="{{ $groupId }}" {{ $selectedPermissions->contains($permission->name) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $label }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0" role="alert">
                                You do not have permission to assign system permissions. The role will be created without changes to permission assignments.
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="ri-check-line me-1"></i> Save Role</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
(function() {
    'use strict';
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all-permissions');
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const toggleAllBtn = document.getElementById('toggle-all-groups');

    function updateGroupState(groupId) {
        const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${groupId}"]`);
        const groupCheckbox = document.querySelector(`.group-checkbox[data-group="${groupId}"]`);
        if (!groupCheckbox) return;
        const allChecked = Array.from(groupPermissions).every(cb => cb.checked);
        const someChecked = Array.from(groupPermissions).some(cb => cb.checked);
        groupCheckbox.checked = allChecked;
        groupCheckbox.indeterminate = !allChecked && someChecked;
    }

    function updateSelectAll() {
        const allPermissions = document.querySelectorAll('.permission-checkbox');
        const allChecked = allPermissions.length > 0 && Array.from(allPermissions).every(cb => cb.checked);
        const someChecked = Array.from(allPermissions).some(cb => cb.checked);
        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && someChecked;
        }
    }

    groupCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const groupId = cb.dataset.group;
            document.querySelectorAll(`.permission-checkbox[data-group="${groupId}"]`).forEach(child => {
                child.checked = cb.checked;
            });
            updateGroupState(groupId);
            updateSelectAll();
        });
    });

    permissionCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updateGroupState(cb.dataset.group);
            updateSelectAll();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            const checked = selectAll.checked;
            permissionCheckboxes.forEach(cb => cb.checked = checked);
            groupCheckboxes.forEach(cb => {
                cb.checked = checked;
                cb.indeterminate = false;
            });
        });
    }

    groupCheckboxes.forEach(cb => updateGroupState(cb.dataset.group));
    updateSelectAll();

    const collapses = document.querySelectorAll('.collapse');
    collapses.forEach(collapseEl => {
        collapseEl.addEventListener('show.bs.collapse', function () {
            const toggleBtn = document.querySelector(`button[data-bs-target="#${this.id}"]`);
            if (toggleBtn) toggleBtn.innerHTML = '<i class="ri-subtract-line"></i>';
        });
        collapseEl.addEventListener('hide.bs.collapse', function () {
            const toggleBtn = document.querySelector(`button[data-bs-target="#${this.id}"]`);
            if (toggleBtn) toggleBtn.innerHTML = '<i class="ri-add-line"></i>';
        });
    });

    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', () => {
            const expanded = toggleAllBtn.getAttribute('data-expanded') === 'true';
            document.querySelectorAll('.permission-group .collapse').forEach(el => {
                const collapseInstance = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                expanded ? collapseInstance.hide() : collapseInstance.show();
            });
            toggleAllBtn.setAttribute('data-expanded', expanded ? 'false' : 'true');
            toggleAllBtn.innerHTML = expanded
                ? '<i class="ri-add-line me-1"></i>Expand All'
                : '<i class="ri-arrow-up-s-line me-1"></i>Collapse All';
        });
    }
});
</script>
@endsection


