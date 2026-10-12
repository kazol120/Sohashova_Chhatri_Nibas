@extends('backend.layouts.app')
@section('title', 'My Meal History')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Month Filter Header -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <form method="GET" action="{{ route('dashboard.my-meals') }}" class="row g-2 align-items-center">
          <div class="col-auto">
            <label class="col-form-label fw-bold text-dark"><i class="ti ti-calendar me-1 text-primary"></i> Select Month:</label>
          </div>
          <div class="col-auto">
            <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control fw-bold" onchange="this.form.submit()">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary fw-bold"><i class="ti ti-filter me-1"></i> Filter</button>
          </div>
        </form>

        <div>
          <a href="{{ route('meal-history.resident-detail', $user->id) }}?month={{ $selectedMonth }}" 
             target="_blank" 
             class="btn btn-dark fw-bold shadow-sm">
            <i class="ti ti-printer me-1"></i> প্রিন্ট / PDF রিপোর্ট সেভ করুন
          </a>
        </div>
      </div>
    </div>
  </div>

  @if(isset($mealSummary) && ($mealSummary->deposit_total <= 0 || $mealSummary->balance <= 0))
    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm border-danger rounded-3" role="alert">
      <div class="d-flex align-items-center">
        <span class="alert-icon text-danger me-3 fs-3">
          <i class="ti ti-alert-triangle"></i>
        </span>
        <div>
          <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">মেল ডিপোজিট বকেয়া/জিরো অ্যালার্ট (Meal Deposit Alert)</h6>
          <div class="fw-medium text-dark fs-7">
            ⚠️ আপনার মেল ডিপোজিট (Meal Deposit) ব্যালেন্স ৳ {{ number_format($mealSummary->balance, 2) }} টাকা! সার্ভিস সক্রিয় রাখতে অনুগ্রহ করে মেল ডিপোজিট জমা দিন এবং মেল চালু করতে এডমিন এর সাথে যোগাযোগ করুন।
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Summary Cards -->
  <div class="row g-4 mb-4">
    <!-- Total Meals -->
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Meals</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-dark fw-bold">
                  {{ (float)$mealSummary->total_meal == (int)$mealSummary->total_meal ? (int)$mealSummary->total_meal : number_format($mealSummary->total_meal, 1) }}
                </h3>
              </div>
              <small class="text-muted">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</small>
              @if(($totalHalfCount ?? 0) > 0)
                <small class="text-muted d-block font-normal">({{ $totalFullCount ?? 0 }} Full, {{ $totalHalfCount ?? 0 }} Half)</small>
              @endif
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti ti-salad fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Cost -->
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Meal Cost</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-danger fw-bold">৳ {{ number_format($mealSummary->meal_cost, 2) }}</h3>
              </div>
              <small class="text-muted">Total meal charges</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-shopping-cart fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Deposit -->
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Meal Deposit</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-success fw-bold">৳ {{ number_format($mealSummary->deposit_total, 2) }}</h3>
              </div>
              <small class="text-muted">Deposited amount</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-cash fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Balance -->
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Outstanding Balance</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 {{ $mealSummary->balance < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                  ৳ {{ number_format($mealSummary->balance, 2) }}
                </h3>
              </div>
              <small class="text-muted">{{ $mealSummary->balance < 0 ? 'Due Amount' : 'Surplus Advance' }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-{{ $mealSummary->balance < 0 ? 'danger' : 'success' }}">
                <i class="ti ti-wallet fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Monthly Meal History Table (1 to 30/31 Days) -->
  <div class="card border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0 fw-bold text-dark">
        <i class="ti ti-history me-2 text-primary"></i>মাসের প্রতিদিনের মিলের বিবরণী — {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
      </h5>
      <div class="d-flex gap-2">
        <span class="badge bg-label-primary px-3 py-2 fs-7 fw-semibold">
          ফুল মিল রেট: ৳ {{ number_format($fullRate ?? 30, 2) }}
        </span>
        <span class="badge bg-label-warning px-3 py-2 fs-7 fw-semibold">
          হাফ মিল রেট: ৳ {{ number_format($halfRate ?? 15, 2) }}
        </span>
      </div>
    </div>
    {{-- Desktop Table View --}}
    <div class="d-none d-md-block table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
          <tr class="small text-white fw-bold text-uppercase">
            <th style="width: 60px;" class="text-center text-white">#</th>
            <th class="text-white">তারিখ (Date)</th>
            <th class="text-white">বার (Day)</th>
            <th class="text-center text-white">মিল স্ট্যাটাস</th>
            <th class="text-center text-white">মিলের সংখ্যা</th>
            <th class="text-end pe-4 text-warning">দৈনিক খরচ</th>
            <th class="text-white">নোট / স্পেশাল মিলের বিবরণ</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          @php
            $todayDate = now()->format('Y-m-d');
          @endphp
          @forelse($days as $index => $d)
            @php
              $isFuture = $d->date > $todayDate;
            @endphp
            <tr class="{{ $d->is_off ? 'text-muted' : '' }}" style="{{ $d->date == $todayDate ? 'background-color: #f0fdf4;' : ($d->is_off ? 'background-color: #f8fafc;' : '') }}">
              <td class="text-center font-monospace text-dark fw-bold">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
              <td class="fw-bold text-dark">
                {{ $d->date_formatted }}
                @if($d->date == $todayDate)
                  <span class="badge bg-primary text-white ms-1" style="font-size: 0.65rem;">আজ (Today)</span>
                @endif
              </td>
              <td class="text-secondary fw-semibold">{{ $d->day_name }}</td>
              <td class="text-center">
                @if($d->is_off)
                  @php
                    $dNoteLower = strtolower($d->note ?? '');
                  @endphp
                  @if(str_contains($dNoteLower, 'checked in') || str_contains($dNoteLower, 'resumes tomorrow') || (str_contains($dNoteLower, 'check') && str_contains($dNoteLower, 'in')) || str_contains($dNoteLower, 'চেক-ইন'))
                    <span class="badge bg-success px-2.5 py-1 fs-7">🟢 চেক-ইন (কাল চালু)</span>
                  @elseif(str_contains($dNoteLower, 'leave') || str_contains($dNoteLower, 'checked out') || str_contains($dNoteLower, 'check') || str_contains($dNoteLower, 'চেক-আউট'))
                    <span class="badge bg-danger px-2.5 py-1 fs-7">🚫 চেক-আউট (মিল বন্ধ)</span>
                  @elseif(str_contains($dNoteLower, 'zero deposit') || str_contains($dNoteLower, 'নো ডিপোজিট'))
                    <span class="badge bg-danger px-2.5 py-1 fs-7">🚫 নো ডিপোজিট (OFF)</span>
                  @else
                    <span class="badge bg-danger px-3 py-1 fs-7">🚫 অফ (OFF)</span>
                  @endif
                @elseif($d->meal_type === 'half_day')
                  <span class="badge px-3 py-1 fs-7 fw-bold" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047;">☀️ হাফ (দিন)</span>
                @elseif($d->meal_type === 'half_night')
                  <span class="badge px-3 py-1 fs-7 fw-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">🌙 হাফ (রাত)</span>
                @elseif($d->meal_type === 'full' || $d->is_meal_on)
                  <span class="badge px-3 py-1 fs-7 fw-bold" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;">✅ চালু (ON)</span>
                @else
                  <span class="badge px-3 py-1 fs-7 fw-bold" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">🚫 অফ (OFF)</span>
                @endif
              </td>
              <td class="text-center fw-bold text-dark">
                @if($d->meal_count == 0.5)
                  <span class="badge px-2.5 py-1 fw-bold" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">0.5 (হাফ)</span>
                @elseif($d->meal_count == 1)
                  <span class="fw-bolder fs-6 text-dark">1</span>
                @else
                  <span class="text-secondary">0</span>
                @endif
              </td>
              <td class="text-end pe-4 fw-bold {{ $d->day_cost > 0 ? 'text-danger' : 'text-secondary' }}">
                ৳ {{ number_format($d->day_cost, 2) }}
              </td>
              <td>
                @if($d->note)
                  <span class="badge bg-white text-dark border fw-bold shadow-xs">{{ $d->note }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">এই মাসের কোনো তথ্য পাওয়া যায়নি।</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1;" class="fw-bold">
            <td colspan="4" class="text-end text-dark fs-7">নিয়মিত মিলের মোট (Regular Meal Total):</td>
            <td class="text-center text-primary fs-6">
              @php
                $regTotal = ($totalFullCount ?? 0) + (($totalHalfCount ?? 0) * 0.5);
              @endphp
              {{ (float)$regTotal == (int)$regTotal ? (int)$regTotal : number_format($regTotal, 1) }} টি
              @if(($totalHalfCount ?? 0) > 0)
                <div class="small text-secondary fw-normal">({{ $totalFullCount }} Full, {{ $totalHalfCount }} Half)</div>
              @endif
            </td>
            <td class="text-end pe-4 text-danger fs-6 fw-bolder">৳ {{ number_format($totalCost, 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- Mobile Daily Meal Card View (Distinct Div Blocks with High-Contrast Colors) --}}
    <div class="d-md-none p-2 p-sm-3" style="background: #f1f5f9;">
      @forelse($days as $index => $d)
      <div class="card shadow-sm mb-3 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
        
        {{-- Card Header Div --}}
        <div class="py-2.5 px-3 d-flex justify-content-between align-items-center {{ $d->date == $todayDate ? 'bg-primary text-white' : '' }}" style="{{ $d->date != $todayDate ? 'background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;' : '' }}">
          <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $d->date == $todayDate ? 'bg-white text-primary' : 'bg-primary text-white' }} rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $index + 1 }}</span>
            <span class="fw-bold fs-6 text-white">{{ $d->date_formatted }} ({{ $d->day_name }})</span>
            @if($d->date == $todayDate)
              <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill fw-bold" style="font-size: 0.65rem;">আজ</span>
            @endif
          </div>
          <div>
            @if($d->is_off)
              @php
                $dNoteLower = strtolower($d->note ?? '');
              @endphp
              @if(str_contains($dNoteLower, 'checked in') || str_contains($dNoteLower, 'resumes tomorrow') || (str_contains($dNoteLower, 'check') && str_contains($dNoteLower, 'in')) || str_contains($dNoteLower, 'চেক-ইন'))
                <span class="badge bg-success px-2 py-1 fs-8">🟢 চেক-ইন</span>
              @elseif(str_contains($dNoteLower, 'leave') || str_contains($dNoteLower, 'checked out') || str_contains($dNoteLower, 'check') || str_contains($dNoteLower, 'চেক-আউট'))
                <span class="badge bg-danger px-2 py-1 fs-8">🚫 চেক-আউট</span>
              @else
                <span class="badge bg-danger px-2 py-1 fs-8">🚫 অফ</span>
              @endif
            @elseif($d->meal_type === 'half_day')
              <span class="badge px-2 py-1 fs-8 fw-bold" style="background: #fef9c3; color: #854d0e;">☀️ হাফ(দিন)</span>
            @elseif($d->meal_type === 'half_night')
              <span class="badge px-2 py-1 fs-8 fw-bold" style="background: #e0f2fe; color: #0369a1;">🌙 হাফ(রাত)</span>
            @elseif($d->meal_type === 'full' || $d->is_meal_on)
              <span class="badge px-2 py-1 fs-8 fw-bold" style="background: #22c55e; color: #ffffff;">✅ চালু</span>
            @else
              <span class="badge px-2 py-1 fs-8 fw-bold" style="background: #ef4444; color: #ffffff;">🚫 অফ</span>
            @endif
          </div>
        </div>

        {{-- Card Body Div --}}
        <div class="card-body p-3 bg-white">
          <div class="row g-2">
            
            {{-- Div 1: Meal Count --}}
            <div class="col-6">
              <div class="p-2.5 rounded-3 h-100" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                <span class="d-block fw-bold" style="color: #1e40af; font-size: 11px;">মিলের সংখ্যা</span>
                <div class="fw-bolder mt-1 text-dark fs-6">
                  @if($d->meal_count == 0.5)
                    0.5 (হাফ মিল)
                  @elseif($d->meal_count == 1)
                    1 টি মিল
                  @else
                    ০ টি
                  @endif
                </div>
              </div>
            </div>

            {{-- Div 2: Cost --}}
            <div class="col-6">
              <div class="p-2.5 rounded-3 h-100" style="background: #fef2f2; border: 1.5px solid #fecaca;">
                <span class="d-block fw-bold" style="color: #991b1b; font-size: 11px;">দৈনিক খরচ</span>
                <div class="fw-bolder mt-1 fs-6" style="color: #dc2626;">
                  ৳ {{ number_format($d->day_cost, 2) }}
                </div>
              </div>
            </div>

          </div>

          @if($d->note)
          <div class="mt-2.5 p-2 rounded-2" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <small class="text-secondary fw-bold d-block" style="font-size:11px;">নোট / স্পেশাল মিল:</small>
            <span class="text-dark fw-semibold fs-7">{{ $d->note }}</span>
          </div>
          @endif
        </div>
      </div>
      @empty
      <div class="text-center py-5 text-muted bg-white rounded-3">এই মাসের কোনো তথ্য পাওয়া যায়নি।</div>
      @endforelse

      @if(count($days) > 0)
      <div class="card rounded-4 p-3.5 mt-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: 2px solid #0284c7 !important;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-bold" style="color: #0369a1; font-size: 13px;">নিয়মিত মিল মোট:</span>
          <span class="fw-bolder fs-5" style="color: #075985;">{{ (float)$regTotal == (int)$regTotal ? (int)$regTotal : number_format($regTotal, 1) }} টি</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold" style="color: #0369a1; font-size: 13px;">নিয়মিত মোট খরচ:</span>
          <span class="fw-bolder fs-4" style="color: #dc2626;">৳ {{ number_format($totalCost, 2) }}</span>
        </div>
      </div>
      @endif
    </div>
  </div>

  @if(isset($guestMeals) && $guestMeals->count() > 0)
    <!-- Guest Meals Breakdown Card -->
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header border-bottom py-3 bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-dark">
          <i class="ti ti-users me-2 text-primary"></i>মেহমানের মিলের বিবরণী (Guest Meal Charges)
        </h5>
        <span class="badge px-3 py-1.5 fs-7 fw-bold" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
          গেস্ট মিল মোট কর্তন: ৳ {{ number_format($totalGuestMealsAmount, 2) }}
        </span>
      </div>

      {{-- Desktop Table View --}}
      <div class="d-none d-md-block table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
            <tr class="small text-white fw-bold text-uppercase">
              <th style="width: 60px;" class="text-center text-white">#</th>
              <th class="text-white">তারিখ (Date)</th>
              <th class="text-white">মেহমানের পরিচয় / সম্পর্ক (Guest)</th>
              <th class="text-center text-white">মিলের সংখ্যা</th>
              <th class="text-end pe-4 text-warning">কর্তনকৃত টাকা (Amount)</th>
              <th class="text-white">মন্তব্য / নোট</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @foreach($guestMeals as $gIdx => $gItem)
              <tr>
                <td class="text-center font-monospace text-dark fw-bold">{{ $gIdx + 1 }}</td>
                <td class="fw-bold text-dark">
                  <i class="ti ti-calendar me-1 text-primary"></i>
                  {{ \Carbon\Carbon::parse($gItem->date)->format('d M Y') }}
                </td>
                <td>
                  <span class="badge px-3 py-1.5 fw-bold fs-7" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">{{ $gItem->guest_name }}</span>
                </td>
                <td class="text-center fw-bolder text-dark">
                  {{ (float)$gItem->meal_count == (int)$gItem->meal_count ? (int)$gItem->meal_count : (float)$gItem->meal_count }} টি
                </td>
                <td class="text-end pe-4 fw-bolder text-danger">
                  ৳ {{ number_format($gItem->amount, 2) }}
                </td>
                <td>
                  <span class="text-dark fw-medium small">{{ $gItem->note ?: '—' }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1;" class="fw-bold">
              <td colspan="3" class="text-end text-dark">গেস্ট মিল মোট:</td>
              <td class="text-center text-primary fs-6">{{ (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : (float)$totalGuestMealsCount }} টি</td>
              <td class="text-end pe-4 text-danger fs-6 fw-bolder">৳ {{ number_format($totalGuestMealsAmount, 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Mobile Card View (Distinct Div Blocks with High-Contrast Colors) --}}
      <div class="d-md-none p-2 p-sm-3" style="background: #f1f5f9;">
        @foreach($guestMeals as $gIdx => $gItem)
        <div class="card shadow-sm mb-3 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
          <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $gIdx + 1 }}</span>
              <span class="fw-bold text-white fs-7">
                <i class="ti ti-calendar me-1 text-info"></i>
                {{ \Carbon\Carbon::parse($gItem->date)->format('d M Y') }}
              </span>
            </div>
            <span class="badge px-2.5 py-1 fw-bold fs-8" style="background: #38bdf8; color: #0f172a;">{{ $gItem->guest_name }}</span>
          </div>
          
          <div class="card-body p-3 bg-white">
            <div class="row g-2">
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                  <span class="d-block fw-bold" style="color: #1e40af; font-size: 11px;">মিলের সংখ্যা</span>
                  <div class="fw-bolder mt-1 text-dark fs-6">{{ (float)$gItem->meal_count == (int)$gItem->meal_count ? (int)$gItem->meal_count : (float)$gItem->meal_count }} টি</div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #fef2f2; border: 1.5px solid #fecaca;">
                  <span class="d-block fw-bold" style="color: #991b1b; font-size: 11px;">কর্তনকৃত টাকা</span>
                  <div class="fw-bolder mt-1 fs-6" style="color: #dc2626;">৳ {{ number_format($gItem->amount, 2) }}</div>
                </div>
              </div>
            </div>

            @if($gItem->note)
            <div class="mt-2.5 p-2 rounded-2" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
              <small class="text-secondary fw-bold d-block" style="font-size:11px;">মন্তব্য / নোট:</small>
              <span class="text-dark fw-semibold fs-7">{{ $gItem->note }}</span>
            </div>
            @endif
          </div>
        </div>
        @endforeach

        <div class="card rounded-4 p-3.5 mt-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #ef4444 !important;">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold" style="color: #991b1b; font-size: 13px;">গেস্ট মিল মোট:</span>
            <span class="fw-bolder fs-5" style="color: #7f1d1d;">{{ (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : (float)$totalGuestMealsCount }} টি</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold" style="color: #991b1b; font-size: 13px;">গেস্ট মিল মোট কর্তন:</span>
            <span class="fw-bolder fs-4" style="color: #991b1b;">৳ {{ number_format($totalGuestMealsAmount, 2) }}</span>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
