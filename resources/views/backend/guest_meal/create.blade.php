@extends('backend.layouts.app')
@section('title', 'Add Guest Meal')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Breadcrumbs & Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1 text-primary">
        <i class="ti ti-plus me-2"></i>Add Guest Meal
      </h4>
      <p class="text-muted mb-0 small">Record guest meal entry and adjust costs against resident's meal deposit</p>
    </div>
    <a href="{{ route('guest-meals.index') }}" class="btn btn-outline-secondary fw-semibold">
      <i class="ti ti-arrow-left me-1"></i> Back to List
    </a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
      <div class="fw-bold mb-1"><i class="ti ti-alert-triangle me-1"></i> Please correct the errors below:</div>
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
          <h5 class="card-title mb-0 fw-bold text-dark">
            <i class="ti ti-edit me-2 text-primary"></i>Guest Meal Information Form
          </h5>
        </div>

        <div class="card-body p-4">
          <form action="{{ route('guest-meals.store') }}" method="POST" id="guestMealForm">
            @csrf

            {{-- 1. Resident Selection / Profile Info --}}
            @if($isAdmin)
              <div class="mb-3">
                <label class="form-label fw-bold text-dark">
                  <i class="ti ti-user-check me-1 text-primary"></i>Select Active Resident <span class="text-danger">*</span>
                </label>
                <select name="user_id" id="residentSelect" class="form-select form-select-lg fw-semibold select2" required>
                  <option value="">-- Select Resident --</option>
                  @foreach($activeResidents as $res)
                    <option value="{{ $res['id'] }}" 
                            data-phone="{{ $res['phone'] }}" 
                            data-room="{{ $res['room_seat'] }}" 
                            data-deposit="{{ $res['deposit_total'] }}"
                            {{ old('user_id') == $res['id'] ? 'selected' : '' }}>
                      {{ $res['name'] }} ({{ $res['phone'] }}) - [Room/Seat: {{ $res['room_seat'] }}]
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Selected Resident Info Box --}}
              <div id="residentInfoBox" class="alert alert-primary d-none py-2 px-3 mb-3 border-0 rounded-3 shadow-none">
                <div class="row g-2 small">
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Room/Seat:</span>
                    <strong id="infoRoom" class="text-dark">-</strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Phone:</span>
                    <strong id="infoPhone" class="text-dark">-</strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Current Month Deposit:</span>
                    <strong id="infoDeposit" class="text-success">৳ 0.00</strong>
                  </div>
                </div>
              </div>
            @else
              <input type="hidden" name="user_id" value="{{ $authUser->id }}">
              <div class="card bg-label-primary border-0 rounded-3 p-3 mb-3 shadow-none">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center">
                      <i class="ti ti-user fs-4"></i>
                    </div>
                    <div>
                      <h6 class="mb-0 fw-bold text-dark">{{ $residentDetail['name'] ?? $authUser->name }}</h6>
                      <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $residentDetail['phone'] ?? $authUser->phone }}</small>
                    </div>
                  </div>
                  <div class="text-sm-end">
                    <span class="badge bg-white text-primary fw-bold px-2 py-1 shadow-xs border">
                      <i class="ti ti-bed me-1"></i>Room/Seat: {{ $residentDetail['room_seat'] ?? 'N/A' }}
                    </span>
                    <div class="mt-1 small text-muted">
                      Current Month Deposit: <strong class="text-success">৳ {{ number_format($residentDetail['deposit_total'] ?? 0, 2) }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            <div class="row g-3">
              {{-- 2. Date --}}
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-dark">
                  <i class="ti ti-calendar me-1 text-primary"></i>Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}" required>
              </div>

              {{-- 3. Guest Name / Relationship --}}
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-dark">
                  <i class="ti ti-user me-1 text-primary"></i>Guest Name / Relation <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input type="text" name="guest_name" id="guestNameInput" class="form-control" list="relationOptions" value="{{ old('guest_name') }}" placeholder="e.g. Father, Mother, Brother, Sister..." required>
                  <datalist id="relationOptions">
                    <option value="Father">
                    <option value="Mother">
                    <option value="Brother">
                    <option value="Sister">
                    <option value="Guardian">
                    <option value="Relative / Friend">
                    <option value="Guest">
                  </datalist>
                </div>
              </div>
            </div>

            <div class="row g-3">
              {{-- 4. Meal Count --}}
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-dark">
                  <i class="ti ti-soup me-1 text-primary"></i>Meal Count <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input type="number" step="0.5" min="0.5" name="meal_count" id="mealCountInput" class="form-control fw-bold" value="{{ old('meal_count', '1') }}" required>
                  <span class="input-group-text">Meals</span>
                </div>
                <div class="mt-1 d-flex gap-1">
                  <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 set-meal-btn" data-val="1">1 Meal</button>
                  <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 set-meal-btn" data-val="2">2 Meals</button>
                  <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 set-meal-btn" data-val="3">3 Meals</button>
                </div>
              </div>

              {{-- 5. Amount (BDT) --}}
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-dark">
                  <i class="ti ti-cash me-1 text-primary"></i>Amount (BDT) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light fw-bold text-danger">৳</span>
                  <input type="number" step="0.01" min="0" name="amount" id="amountInput" class="form-control fw-bold text-danger fs-5" value="{{ old('amount', $defaultRate) }}" required>
                </div>
                <small class="text-muted d-block mt-1">
                  Meal Rate: ৳{{ $defaultRate }} | 
                  <a href="javascript:void(0)" id="recalcBtn" class="text-primary fw-semibold text-decoration-underline">Auto Calculate</a>
                </small>
              </div>
            </div>

            {{-- 6. Note / Remarks --}}
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">
                <i class="ti ti-notes me-1 text-primary"></i>Notes / Remarks
              </label>
              <textarea name="note" class="form-control" rows="2" placeholder="Enter any additional remarks or notes...">{{ old('note') }}</textarea>
            </div>

            {{-- Submit Buttons --}}
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
              <a href="{{ route('guest-meals.index') }}" class="btn btn-light px-4">Cancel</a>
              <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="ti ti-check me-1"></i> Save Guest Meal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const residentSelect = document.getElementById('residentSelect');
    const residentInfoBox = document.getElementById('residentInfoBox');
    const infoRoom = document.getElementById('infoRoom');
    const infoPhone = document.getElementById('infoPhone');
    const infoDeposit = document.getElementById('infoDeposit');

    const mealCountInput = document.getElementById('mealCountInput');
    const amountInput = document.getElementById('amountInput');
    const defaultRate = {{ (float)$defaultRate }};

    function updateResidentInfo() {
      const selected = residentSelect.options[residentSelect.selectedIndex];
      if (selected && selected.value) {
        infoRoom.textContent = selected.getAttribute('data-room') || 'N/A';
        infoPhone.textContent = selected.getAttribute('data-phone') || 'N/A';
        const dep = parseFloat(selected.getAttribute('data-deposit') || 0);
        infoDeposit.textContent = '৳ ' + dep.toFixed(2);
        residentInfoBox.classList.remove('d-none');
      } else {
        residentInfoBox.classList.add('d-none');
      }
    }

    function calculateAmount() {
      const count = parseFloat(mealCountInput.value) || 0;
      amountInput.value = (count * defaultRate).toFixed(2);
    }

    if (residentSelect) {
      residentSelect.addEventListener('change', updateResidentInfo);
      updateResidentInfo();
    }

    document.querySelectorAll('.set-meal-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        mealCountInput.value = this.getAttribute('data-val');
        calculateAmount();
      });
    });

    document.getElementById('recalcBtn').addEventListener('click', function(e) {
      e.preventDefault();
      calculateAmount();
    });
  });
</script>
@endpush
