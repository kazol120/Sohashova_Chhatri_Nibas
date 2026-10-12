@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css" />
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Layout -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <!-- Title -->
                        <h5 class="card-title mb-0 text-capitalize">{{$page_title}}</h5>
                        <!-- Button Group -->
                        <div class="dt-action-buttons">
                            <div class="dt-buttons btn-group">
                                <!-- Create Button -->
                                <a href="{{ url()->previous() }}" class="btn btn-primary create-new waves-effect waves-light">
                                    <span>
                                        <i class="ti ti-arrow-left me-1"></i>
                                        <span class="d-none d-sm-inline-block">Back</span>

                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <form action="{{route('setting.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="slug" value="{{$slug}}">
                        <div class="card-body">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-warning" role="alert">{{ $error }}</div>
                                @endforeach
                            @endif
                            
                            <div class="row">
                                @foreach($fields as $key => $filed)
                                    @php
                                        $fieldCol = $filed['col'] ?? 'col-12';
                                        $fieldLabel = \Illuminate\Support\Str::of($key)->replace('_', ' ');
                                        if ($slug === 'meal_setting') {
                                            if ($key === 'full') $fieldLabel = 'Full Meal Rate (ফুল মিল রেট ৳)';
                                            elseif ($key === 'half') $fieldLabel = 'Half Meal Rate (হাফ মিল রেট ৳)';
                                        }
                                    @endphp
                                    <div class="{{ $fieldCol }} mb-3">
                                        <label for="{{$key}}" class="form-label text-capitalize fw-bold text-dark">
                                            {{ $fieldLabel }}
                                            <code>{{ !empty($filed['required']) ? "*" : "" }}</code>
                                        </label>

                                        @if($filed['type'] == 'file')

                                            @if(!empty($editData->$key))
                                                <div class="mb-2">
                                                    <img src="{{ asset($editData->$key) }}"
                                                         alt="{{$key}}"
                                                         style="max-width:160px; max-height:100px; border:1px solid #ddd; padding:5px; border-radius:6px;">
                                                </div>
                                            @endif

                                            <input
                                                type="file"
                                                name="{{$key}}"
                                                class="form-control @error($key) is-invalid @enderror"
                                                id="{{$key}}"
                                                accept="image/*"
                                                {{ !empty($filed['required']) ? 'required' : '' }}
                                            >

                                        @elseif($filed['type'] == 'toggle')

                                            <div class="mt-1">
                                                <input type="hidden" name="{{$key}}" value="0">
                                                <input
                                                    data-toggle="toggle"
                                                    data-onstyle="success"
                                                    data-offstyle="danger"
                                                    data-on="Active"
                                                    data-off="Deactivated"
                                                    data-width="100%"
                                                    type="checkbox"
                                                    name="{{$key}}"
                                                    value="1"
                                                    id="{{$key}}"
                                                    {{ (string) old($key, $editData->$key ?? '1') === '1' ? 'checked' : '' }}
                                                >
                                            </div>

                                        @elseif($filed['type'] == 'select')

                                            <select
                                                name="{{$key}}"
                                                class="form-select @error($key) is-invalid @enderror"
                                                id="{{$key}}"
                                                {{ !empty($filed['required']) ? 'required' : '' }}
                                            >
                                                @foreach(($filed['options'] ?? []) as $optVal => $optLabel)
                                                    <option value="{{$optVal}}" {{ (string) old($key, $editData->$key ?? '') === (string) $optVal ? 'selected' : '' }}>
                                                        {{$optLabel}}
                                                    </option>
                                                @endforeach
                                            </select>

                                        @else

                                            <div class="input-group">
                                                @if($slug === 'meal_setting')
                                                    <span class="input-group-text fw-bold text-primary">৳</span>
                                                @endif
                                                <input
                                                    type="{{$filed['type']}}"
                                                    step="0.01"
                                                    name="{{$key}}"
                                                    value="{{ old($key, $editData->$key ?? '') }}"
                                                    class="form-control font-monospace fw-bold fs-5 text-primary @error($key) is-invalid @enderror"
                                                    id="{{$key}}"
                                                    placeholder="{{$filed['placeholder'] ?? ''}}"
                                                    {{ !empty($filed['required']) ? 'required' : '' }}
                                                >
                                            </div>

                                        @endif

                                        @error($key)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="row mt-3">
                                <div class="d-grid gap-2 col-lg-4 mx-auto">
                                    <button class="btn btn-primary btn-lg waves-effect waves-light fw-bold shadow-sm" type="submit">
                                        <i class="ti ti-device-floppy me-1"></i> Save Meal Setting
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                @if($slug === 'meal_setting')
                    <!-- ═══════════════ Special Meal Management Card ═══════════════ -->
                    <div class="card mb-4 shadow-sm border-0 mt-4">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-light py-3 border-bottom gap-2">
                            <div>
                                <h5 class="card-title mb-1 fw-bold text-dark d-flex align-items-center">
                                    <i class="ti ti-star me-2 text-warning fs-3"></i>Special Meal Schedule (স্পেশাল মিল শিডিউল)
                                </h5>
                                <p class="text-muted small mb-0">
                                    আগামী যে তারিখে স্পেশাল মিল হবে, সেই তারিখ ও স্পেশাল রেট এখানে সেট করুন। ঐ নির্দিষ্ট দিনে নিয়মিত রেটের বদলে এই স্পেশাল রেট স্বয়ংক্রিয়ভাবে হিসাব হবে।
                                </p>
                            </div>
                            <button type="button" class="btn btn-primary shadow-sm fw-bold" onclick="openSpecialMealModal()">
                                <i class="ti ti-plus me-1"></i>+ Add Special Meal
                            </button>
                        </div>
                        <div class="card-body pt-4">
                            <div class="table-responsive border rounded">
                                <table class="table table-hover table-striped align-middle mb-0" id="specialMealsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="fw-bold text-secondary text-uppercase">SL</th>
                                            <th class="fw-bold text-secondary text-uppercase">DATE</th>
                                            <th class="fw-bold text-secondary text-uppercase">EVENT / TITLE</th>
                                            <th class="fw-bold text-secondary text-uppercase text-center">FULL MEAL RATE</th>
                                            <th class="fw-bold text-secondary text-uppercase text-center">HALF MEAL RATE</th>
                                            <th class="fw-bold text-secondary text-uppercase text-center">STATUS</th>
                                            <th class="fw-bold text-secondary text-uppercase text-center" style="width: 140px;">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody id="specialMealsBody">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Loading special meals...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Special Meal Create/Edit Modal -->
                    <div class="modal fade" id="specialMealModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-primary text-white py-3">
                                    <h5 class="modal-title text-white fw-bold" id="specialMealModalTitle">
                                        <i class="ti ti-star me-2"></i>Add Special Meal
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="specialMealForm" onsubmit="saveSpecialMeal(event)">
                                    @csrf
                                    <input type="hidden" id="sp_meal_id" name="id">
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">Select Date (তারিখ) <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="sp_date" name="date" required>
                                            <small class="text-muted">যে নির্দিষ্ট তারিখে স্পেশাল মিল কার্যকর হবে</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">Event / Title (বিবরণ)</label>
                                            <input type="text" class="form-control" id="sp_title" name="title" placeholder="যেমন: শুক্রবার স্পেশাল বিরিয়ানি ফিস্ট">
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark">Full Meal Rate (৳) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text fw-bold">৳</span>
                                                    <input type="number" step="0.01" class="form-control font-monospace fw-bold fs-5 text-primary" id="sp_full_rate" name="full_rate" required placeholder="80">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark">Half Meal Rate (৳)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text fw-bold">৳</span>
                                                    <input type="number" step="0.01" class="form-control font-monospace fw-bold fs-5 text-info" id="sp_half_rate" name="half_rate" placeholder="40">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" id="sp_status" name="status" value="1" checked>
                                            <label class="form-check-label fw-semibold text-dark" for="sp_status">Active (কার্যকর)</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary fw-bold" id="sp_save_btn">
                                            <i class="ti ti-device-floppy me-1"></i>Save Special Meal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script src="{{asset('backend')}}/vendor/libs/select2/select2.js"></script>
    <script src="{{asset('backend')}}/js/forms-selects.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rolesSelect').select2({
                placeholder: 'Select a role',
                allowClear: true
            });

            $('#permissionsSelect').select2({
                placeholder: 'Select permissions',
                allowClear: true
            });
        });

        @if($slug === 'meal_setting')
        let specialMealsData = [];

        function loadSpecialMeals() {
            $.ajax({
                url: '{{ route("special-meals.index") }}',
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        specialMealsData = response.data;
                        renderSpecialMealsTable(response.data);
                    }
                },
                error: function() {
                    $('#specialMealsBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Failed to load special meals.</td></tr>');
                }
            });
        }

        function renderSpecialMealsTable(data) {
            const tbody = $('#specialMealsBody');
            tbody.empty();

            if (!data || data.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">কোনো স্পেশাল মিল শিডিউল সেট করা নেই। নতুন শিডিউল যোগ করতে <strong>"+ Add Special Meal"</strong> বাটনে ক্লিক করুন।</td></tr>');
                return;
            }

            data.forEach((item, index) => {
                const sl = String(index + 1).padStart(2, '0');
                const fullRate = '৳ ' + Number(item.full_rate).toFixed(2);
                const halfRate = item.half_rate ? ('৳ ' + Number(item.half_rate).toFixed(2)) : '-';
                const displayDate = item.date ? (item.date.includes('T') ? item.date.split('T')[0] : item.date) : '';
                const statusBadge = item.status 
                    ? '<span class="badge bg-label-success cursor-pointer" onclick="toggleSpecialMealStatus(' + item.id + ')"><i class="ti ti-check me-1"></i>Active</span>'
                    : '<span class="badge bg-label-danger cursor-pointer" onclick="toggleSpecialMealStatus(' + item.id + ')"><i class="ti ti-x me-1"></i>Inactive</span>';

                const tr = $('<tr></tr>');
                tr.html(`
                    <td><span class="text-muted font-monospace">${sl}</span></td>
                    <td><span class="badge bg-label-primary fs-6 fw-bold font-monospace"><i class="ti ti-calendar me-1"></i>${displayDate}</span></td>
                    <td><div class="fw-bold text-dark">${item.title || '<span class="text-muted font-italic">Special Meal</span>'}</div></td>
                    <td class="text-center"><span class="badge bg-label-warning fs-6 fw-bold font-monospace px-3 py-1 text-dark">${fullRate}</span></td>
                    <td class="text-center"><span class="badge bg-label-info fs-6 fw-bold font-monospace px-3 py-1 text-dark">${halfRate}</span></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-label-info" onclick="editSpecialMeal(${item.id})" title="Edit">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-label-danger" onclick="deleteSpecialMeal(${item.id})" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                `);
                tbody.append(tr);
            });
        }

        function getSpecialMealModal() {
            const modalEl = document.getElementById('specialMealModal');
            if (window.bootstrap && bootstrap.Modal) {
                return bootstrap.Modal.getOrCreateInstance(modalEl);
            }
            return null;
        }

        function openSpecialMealModal() {
            $('#specialMealModalTitle').html('<i class="ti ti-star me-2"></i>Add Special Meal');
            $('#sp_meal_id').val('');
            $('#sp_date').val('').prop('readonly', false);
            $('#sp_title').val('');
            $('#sp_full_rate').val('');
            $('#sp_half_rate').val('');
            $('#sp_status').prop('checked', true);

            const modal = getSpecialMealModal();
            if (modal) {
                modal.show();
            } else {
                $('#specialMealModal').modal('show');
            }
        }

        function editSpecialMeal(id) {
            const item = specialMealsData.find(m => m.id === id);
            if (!item) return;

            const cleanDate = item.date ? (item.date.includes('T') ? item.date.split('T')[0] : item.date) : '';
            $('#specialMealModalTitle').html('<i class="ti ti-edit me-2"></i>Edit Special Meal');
            $('#sp_meal_id').val(item.id);
            $('#sp_date').val(cleanDate).prop('readonly', false);
            $('#sp_title').val(item.title || '');
            $('#sp_full_rate').val(item.full_rate);
            $('#sp_half_rate').val(item.half_rate || '');
            $('#sp_status').prop('checked', item.status == 1);

            const modal = getSpecialMealModal();
            if (modal) {
                modal.show();
            } else {
                $('#specialMealModal').modal('show');
            }
        }

        function saveSpecialMeal(e) {
            e.preventDefault();
            const btn = $('#sp_save_btn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

            const formData = {
                _token: '{{ csrf_token() }}',
                date: $('#sp_date').val(),
                title: $('#sp_title').val(),
                full_rate: $('#sp_full_rate').val(),
                half_rate: $('#sp_half_rate').val(),
                status: $('#sp_status').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: '{{ route("special-meals.store") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Save Special Meal');
                    const modal = getSpecialMealModal();
                    if (modal) {
                        modal.hide();
                    } else {
                        $('#specialMealModal').modal('hide');
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Special Meal saved successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadSpecialMeals();
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Save Special Meal');
                    let msg = 'Failed to save special meal.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg
                    });
                }
            });
        }

        function deleteSpecialMeal(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'এই স্পেশাল মিল শিডিউলটি মুছে ফেলতে চান?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("special-meals") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Special Meal deleted successfully!',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadSpecialMeals();
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to delete special meal.', 'error');
                        }
                    });
                }
            });
        }

        function toggleSpecialMealStatus(id) {
            $.ajax({
                url: '{{ url("special-meals") }}/' + id + '/toggle-status',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    loadSpecialMeals();
                }
            });
        }

        $(document).ready(function() {
            loadSpecialMeals();
        });
        @endif
    </script>
@endpush
