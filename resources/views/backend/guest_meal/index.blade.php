@extends('backend.layouts.app')
@section('title', $isAdmin ? 'Guest Meal Management' : 'My Guest Meals')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  
  {{-- Header & Breadcrumbs --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1 text-primary">
        <i class="ti ti-users me-2"></i>{{ $isAdmin ? 'Guest Meal Management' : 'My Guest Meals' }}
      </h4>
      <p class="text-muted mb-0 small">
        {{ $isAdmin ? 'Manage resident guest meals, calculate costs and adjust meal deposits' : 'View and record your guest meal history and deposit deductions' }}
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('guest-meals.create') }}" class="btn btn-primary shadow-sm fw-bold">
        <i class="ti ti-plus me-1"></i> Add Guest Meal
      </a>
    </div>
  </div>

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
      <i class="ti ti-check me-2 fs-5 align-middle"></i>
      <strong>{{ session('success') }}</strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Admin Contact Notice for Guest Meal --}}
  @php
    $adminPhone = !empty($webSetting['phone']) ? $webSetting['phone'] : '+880 1713-558-866';
    $cleanAdminPhone = preg_replace('/[^0-9+]/', '', $adminPhone);
    $waPhone = preg_replace('/[^0-9]/', '', $adminPhone);
  @endphp
  <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
    <div class="card-body py-3 px-4">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
            <i class="ti ti-phone-call fs-3 text-white"></i>
          </div>
          <div>
            <h6 class="mb-1 fw-bold text-dark d-flex align-items-center flex-wrap gap-2">
              <span class="badge bg-warning text-dark px-2 py-0.5 fw-bold">জরুরি নির্দেশিকা</span>
              <span>গেস্ট মিল (Guest Meal) দেয়ার আগে অ্যাডমিনের সাথে যোগাযোগ করুন</span>
            </h6>
            <p class="mb-0 text-muted small">
              গেস্ট মিল বুকিং বা যুক্ত করার পূর্বে নিশ্চিতকরণের জন্য সরাসরি হোস্টেল অ্যাডমিনের সাথে কথা বলুন।
            </p>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
          <a href="tel:{{ $cleanAdminPhone }}" class="btn btn-warning btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-1 text-dark">
            <i class="ti ti-phone fs-5"></i>
            <span>{{ $adminPhone }}</span>
          </a>
          @if($waPhone)
            <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="btn btn-success btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-1">
              <i class="ti ti-brand-whatsapp fs-5"></i>
              <span>WhatsApp</span>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Summary Metric Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 4px solid #2563eb !important;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-secondary fw-semibold small text-uppercase">{{ $isAdmin ? 'Total Guest Meals' : 'My Guest Meals' }}</span>
              <h3 class="mb-0 fw-bold text-primary mt-1">{{ (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : number_format($totalGuestMealsCount, 1) }}</h3>
              <small class="text-muted">{{ Carbon\Carbon::parse($selectedMonth)->format('F, Y') }}</small>
            </div>
            <div class="avatar avatar-lg bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm">
              <i class="ti ti-soup fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-left: 4px solid #dc2626 !important;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-secondary fw-semibold small text-uppercase">Total Amount</span>
              <h3 class="mb-0 fw-bold text-danger mt-1">৳ {{ number_format($totalGuestMealsAmount, 2) }}</h3>
              <small class="text-muted">Deducted from Deposit</small>
            </div>
            <div class="avatar avatar-lg bg-danger text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm">
              <i class="ti ti-cash fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #16a34a !important;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            @if($isAdmin)
              <div>
                <span class="text-secondary fw-semibold small text-uppercase">Residents Involved</span>
                <h3 class="mb-0 fw-bold text-success mt-1">{{ $totalResidentsInvolved }}</h3>
                <small class="text-muted">Residents with guest meals</small>
              </div>
              <div class="avatar avatar-lg bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm">
                <i class="ti ti-user-check fs-2"></i>
              </div>
            @else
              @php
                $myDepositMonth = \App\Models\Backend\Deposit::where('user_id', $authUser->id)
                  ->whereYear('date', Carbon\Carbon::parse($selectedMonth)->year)
                  ->whereMonth('date', Carbon\Carbon::parse($selectedMonth)->month)
                  ->sum('amount');
              @endphp
              <div>
                <span class="text-secondary fw-semibold small text-uppercase">Meal Deposit</span>
                <h3 class="mb-0 fw-bold text-success mt-1">৳ {{ number_format($myDepositMonth, 2) }}</h3>
                <small class="text-muted">{{ Carbon\Carbon::parse($selectedMonth)->format('F, Y') }}</small>
              </div>
              <div class="avatar avatar-lg bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm">
                <i class="ti ti-wallet fs-2"></i>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter Card --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('guest-meals.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4 col-sm-6">
          <div class="input-group">
            <span class="input-group-text bg-light text-secondary"><i class="ti ti-calendar"></i></span>
            <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control fw-bold" onchange="this.form.submit()">
          </div>
        </div>

        <div class="col-md-5 col-sm-6">
          <div class="input-group">
            <span class="input-group-text bg-light text-secondary"><i class="ti ti-search"></i></span>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="{{ $isAdmin ? 'Search by resident name, phone, or guest...' : 'Search by guest name or notes...' }}">
          </div>
        </div>

        <div class="col-md-3 col-sm-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill fw-bold">
            <i class="ti ti-filter me-1"></i> Filter
          </button>
          @if($search || $selectedMonth !== now()->format('Y-m'))
            <a href="{{ route('guest-meals.index') }}" class="btn btn-outline-secondary" title="Reset">
              <i class="ti ti-refresh"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Guest Meal List Table --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold text-dark">
        <i class="ti ti-list-details me-2 text-primary"></i>{{ $isAdmin ? 'Guest Meal Records' : 'My Guest Meal Records' }}
      </h5>
      <span class="badge bg-label-primary fs-7 fw-bold">{{ $guestMeals->total() }} Records</span>
    </div>

    {{-- Desktop Table View --}}
    <div class="d-none d-md-block table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
          <tr class="small text-white fw-bold text-uppercase">
            <th class="text-center text-white" style="width: 50px;">#</th>
            <th class="text-white">Date</th>
            @if($isAdmin)
              <th class="text-white">Resident</th>
            @endif
            <th class="text-white">Guest Name / Relation</th>
            <th class="text-center text-white">Meal Count</th>
            <th class="text-end text-warning">Amount (BDT)</th>
            <th class="text-white">Notes</th>
            @if($isAdmin)
              <th class="text-white">Entry By</th>
            @endif
            <th class="text-center text-white" style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          @forelse($guestMeals as $index => $meal)
            <tr>
              <td class="text-center text-dark fw-bold">
                {{ $guestMeals->firstItem() + $index }}
              </td>
              <td>
                <span class="fw-bold text-dark">
                  <i class="ti ti-calendar me-1 text-primary"></i>
                  {{ Carbon\Carbon::parse($meal->date)->format('d M, Y') }}
                </span>
              </td>
              @if($isAdmin)
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-bold text-primary fs-6">{{ $meal->user->name ?? 'N/A' }}</span>
                    <small class="text-secondary fw-semibold">
                      <i class="ti ti-phone me-1"></i>{{ $meal->user->phone ?? '-' }}
                    </small>
                  </div>
                </td>
              @endif
              <td>
                <span class="badge px-3 py-1.5 fw-bold fs-7" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                  <i class="ti ti-user me-1"></i>{{ $meal->guest_name }}
                </span>
              </td>
              <td class="text-center">
                <span class="fw-bolder text-dark fs-6">
                  {{ (float)$meal->meal_count == (int)$meal->meal_count ? (int)$meal->meal_count : number_format($meal->meal_count, 1) }}
                </span>
              </td>
              <td class="text-end">
                <span class="fw-bolder text-danger fs-6">
                  ৳ {{ number_format($meal->amount, 2) }}
                </span>
              </td>
              <td>
                <span class="text-dark fw-medium small">
                  {{ $meal->note ?: '-' }}
                </span>
              </td>
              @if($isAdmin)
                <td>
                  <small class="text-secondary fw-semibold">
                    {{ $meal->creator->name ?? 'System' }}
                  </small>
                </td>
              @endif
              <td class="text-center">
                <div class="d-inline-flex gap-1">
                  <a href="{{ route('guest-meals.edit', $meal->id) }}" class="btn btn-sm btn-icon btn-label-primary" title="Edit">
                    <i class="ti ti-edit"></i>
                  </a>
                  <form action="{{ route('guest-meals.destroy', $meal->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this guest meal record?');" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="Delete">
                      <i class="ti ti-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ $isAdmin ? 9 : 7 }}" class="text-center py-5 text-muted">
                <i class="ti ti-receipt-off fs-1 text-secondary mb-2 d-block"></i>
                <h6 class="fw-bold text-secondary mb-1">No guest meal records found</h6>
                <p class="small text-muted mb-0">No records available for the selected month or search filter.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card View (Distinct Div Blocks with High-Contrast Colors) --}}
    <div class="d-md-none p-2 p-sm-3" style="background: #f1f5f9;">
      @forelse($guestMeals as $index => $meal)
      <div class="card shadow-sm mb-3.5 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
        <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $guestMeals->firstItem() + $index }}</span>
            <span class="fw-bold text-white fs-7">
              <i class="ti ti-calendar me-1 text-info"></i>
              {{ Carbon\Carbon::parse($meal->date)->format('d M, Y') }}
            </span>
          </div>
          <span class="badge px-2.5 py-1 fw-bold fs-8" style="background: #38bdf8; color: #0f172a;">
            <i class="ti ti-user me-1"></i>{{ $meal->guest_name }}
          </span>
        </div>

        <div class="card-body p-3 bg-white">
          @if($isAdmin)
          <div class="d-flex justify-content-between align-items-center p-2 rounded-2 mb-2.5" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <small class="text-secondary fw-bold">Resident:</small>
            <span class="fw-bold text-primary">{{ $meal->user->name ?? 'N/A' }} ({{ $meal->user->phone ?? '-' }})</span>
          </div>
          @endif

          <div class="row g-2 text-start">
            <div class="col-6">
              <div class="p-2.5 rounded-3 h-100 text-center" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                <span class="d-block fw-bold" style="color: #1e40af; font-size: 11px;">মিলের সংখ্যা</span>
                <span class="fw-bolder text-dark fs-6">{{ (float)$meal->meal_count == (int)$meal->meal_count ? (int)$meal->meal_count : number_format($meal->meal_count, 1) }} টি</span>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2.5 rounded-3 h-100 text-center" style="background: #fef2f2; border: 1.5px solid #fecaca;">
                <span class="d-block fw-bold" style="color: #991b1b; font-size: 11px;">খরচ (Amount)</span>
                <span class="fw-bolder fs-6" style="color: #dc2626;">৳ {{ number_format($meal->amount, 2) }}</span>
              </div>
            </div>
          </div>

          @if($meal->note)
          <div class="mt-2.5 p-2 rounded-2" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <strong class="text-secondary small">নোট:</strong> <span class="text-dark fw-medium small">{{ $meal->note }}</span>
          </div>
          @endif

          <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <small class="text-secondary fw-semibold">
              @if($isAdmin) By: {{ $meal->creator->name ?? 'System' }} @endif
            </small>
            <div class="d-inline-flex gap-1.5">
              <a href="{{ route('guest-meals.edit', $meal->id) }}" class="btn btn-sm btn-outline-primary px-3 fw-bold" title="Edit">
                <i class="ti ti-edit me-1"></i>Edit
              </a>
              <form action="{{ route('guest-meals.destroy', $meal->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this guest meal record?');" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger px-3 fw-bold" title="Delete">
                  <i class="ti ti-trash me-1"></i>Delete
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="text-center py-5 text-muted bg-white rounded-3">
        <i class="ti ti-receipt-off fs-1 text-secondary mb-2 d-block"></i>
        <h6 class="fw-bold text-secondary mb-1">No guest meal records found</h6>
        <p class="small text-muted mb-0">No records available for the selected month or search filter.</p>
      </div>
      @endforelse
    </div>

    @if($guestMeals->hasPages())
      <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-end">
          {{ $guestMeals->links() }}
        </div>
      </div>
    @endif
  </div>

</div>
@endsection
