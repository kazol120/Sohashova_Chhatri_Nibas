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
        <thead class="table-light">
          <tr class="text-muted small fw-bold text-uppercase">
            <th style="width: 60px;" class="text-center">#</th>
            <th>তারিখ (Date)</th>
            <th>বার (Day)</th>
            <th class="text-center">মিল স্ট্যাটাস</th>
            <th class="text-center">মিলের সংখ্যা</th>
            <th class="text-end pe-4">দৈনিক খরচ</th>
            <th>নোট / স্পেশাল মিলের বিবরণ</th>
          </tr>
        </thead>
        <tbody>
          @php
            $todayDate = now()->format('Y-m-d');
          @endphp
          @forelse($days as $index => $d)
            @php
              $isFuture = $d->date > $todayDate;
            @endphp
            <tr class="{{ $d->is_off ? 'table-light text-muted' : '' }} {{ $d->date == $todayDate ? 'table-info fw-semibold' : '' }}">
              <td class="text-center font-monospace text-muted">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
              <td class="fw-semibold text-dark">
                {{ $d->date_formatted }}
                @if($d->date == $todayDate)
                  <span class="badge bg-primary text-white ms-1" style="font-size: 0.65rem;">আজ (Today)</span>
                @endif
              </td>
              <td>{{ $d->day_name }}</td>
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
                  <span class="badge bg-warning text-dark px-3 py-1 fs-7">☀️ হাফ (দিন)</span>
                @elseif($d->meal_type === 'half_night')
                  <span class="badge bg-info text-white px-3 py-1 fs-7">🌙 হাফ (রাত)</span>
                @elseif($d->meal_type === 'full' || $d->is_meal_on)
                  <span class="badge bg-success px-3 py-1 fs-7">✅ চালু (ON)</span>
                @else
                  <span class="badge bg-danger px-3 py-1 fs-7">🚫 অফ (OFF)</span>
                @endif
              </td>
              <td class="text-center fw-bold text-dark">
                @if($d->meal_count == 0.5)
                  <span class="badge bg-label-warning text-dark">0.5 (হাফ)</span>
                @elseif($d->meal_count == 1)
                  <span>1</span>
                @else
                  <span class="text-muted">0</span>
                @endif
              </td>
              <td class="text-end pe-4 fw-bold {{ $d->day_cost > 0 ? 'text-danger' : 'text-muted' }}">
                ৳ {{ number_format($d->day_cost, 2) }}
              </td>
              <td>
                @if($d->note)
                  @php
                    $badgeClass = 'bg-label-secondary text-secondary';
                    if (str_contains($d->note, 'চেক-ইন')) {
                        $badgeClass = 'bg-label-success text-success';
                    } elseif (str_contains($d->note, 'চেক-আউট') || str_contains($d->note, 'নো ডিপোজিট')) {
                        $badgeClass = 'bg-label-danger text-danger';
                    } elseif (str_contains($d->note, 'Special') || str_contains($d->note, '⭐')) {
                        $badgeClass = 'bg-label-warning text-dark';
                    } elseif (str_contains($d->note, 'Full')) {
                        $badgeClass = 'bg-label-success text-success';
                    }
                  @endphp
                  <span class="badge {{ $badgeClass }} fw-bold">{{ $d->note }}</span>
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
        <tfoot class="table-light">
          <tr class="fw-bold">
            <td colspan="4" class="text-end">নিয়মিত মিলের মোট (Regular Meal Total):</td>
            <td class="text-center text-primary fs-6">
              @php
                $regTotal = ($totalFullCount ?? 0) + (($totalHalfCount ?? 0) * 0.5);
              @endphp
              {{ (float)$regTotal == (int)$regTotal ? (int)$regTotal : number_format($regTotal, 1) }} টি
              @if(($totalHalfCount ?? 0) > 0)
                <div class="small text-muted fw-normal">({{ $totalFullCount }} Full, {{ $totalHalfCount }} Half)</div>
              @endif
            </td>
            <td class="text-end pe-4 text-danger fs-6">৳ {{ number_format($totalCost, 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- Mobile Daily Meal Card View (No Horizontal Scroll) --}}
    <div class="d-md-none p-2 p-sm-3">
      @forelse($days as $index => $d)
      <div class="card border shadow-sm mb-2.5 rounded-3 overflow-hidden {{ $d->date == $todayDate ? 'border-primary' : '' }}">
        <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center {{ $d->date == $todayDate ? 'bg-primary text-white' : 'bg-light' }}">
          <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $d->date == $todayDate ? 'bg-white text-primary' : 'bg-label-secondary' }} rounded-circle" style="width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:10px;">{{ $index + 1 }}</span>
            <span class="fw-bold fs-7 {{ $d->date == $todayDate ? 'text-white' : 'text-dark' }}">{{ $d->date_formatted }} ({{ $d->day_name }})</span>
            @if($d->date == $todayDate)
              <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill" style="font-size: 0.65rem;">আজ</span>
            @endif
          </div>
          <div>
            @if($d->is_off)
              @php
                $dNoteLower = strtolower($d->note ?? '');
              @endphp
              @if(str_contains($dNoteLower, 'checked in') || str_contains($dNoteLower, 'resumes tomorrow') || (str_contains($dNoteLower, 'check') && str_contains($dNoteLower, 'in')) || str_contains($dNoteLower, 'চেক-ইন'))
                <span class="badge bg-success px-2 py-0.5 fs-8">🟢 চেক-ইন</span>
              @elseif(str_contains($dNoteLower, 'leave') || str_contains($dNoteLower, 'checked out') || str_contains($dNoteLower, 'check') || str_contains($dNoteLower, 'চেক-আউট'))
                <span class="badge bg-danger px-2 py-0.5 fs-8">🚫 চেক-আউট</span>
              @else
                <span class="badge bg-danger px-2 py-0.5 fs-8">🚫 অফ</span>
              @endif
            @elseif($d->meal_type === 'half_day')
              <span class="badge bg-warning text-dark px-2 py-0.5 fs-8">☀️ হাফ(দিন)</span>
            @elseif($d->meal_type === 'half_night')
              <span class="badge bg-info text-white px-2 py-0.5 fs-8">🌙 হাফ(রাত)</span>
            @elseif($d->meal_type === 'full' || $d->is_meal_on)
              <span class="badge bg-success px-2 py-0.5 fs-8">✅ চালু</span>
            @else
              <span class="badge bg-danger px-2 py-0.5 fs-8">🚫 অফ</span>
            @endif
          </div>
        </div>

        <div class="card-body p-2.5">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted d-block">মিলের সংখ্যা:</small>
              <span class="fw-bold text-dark fs-7">
                @if($d->meal_count == 0.5)
                  0.5 (হাফ মিল)
                @elseif($d->meal_count == 1)
                  1 টি
                @else
                  0
                @endif
              </span>
            </div>
            <div class="text-end">
              <small class="text-muted d-block">দৈনিক খরচ:</small>
              <span class="fw-bold {{ $d->day_cost > 0 ? 'text-danger' : 'text-muted' }} fs-7">
                ৳ {{ number_format($d->day_cost, 2) }}
              </span>
            </div>
          </div>

          @if($d->note)
          <div class="mt-2 pt-2 border-top">
            <small class="text-muted d-block">নোট:</small>
            <span class="badge bg-label-secondary text-dark fw-normal text-wrap text-start mt-0.5">{{ $d->note }}</span>
          </div>
          @endif
        </div>
      </div>
      @empty
      <div class="text-center py-4 text-muted">এই মাসের কোনো তথ্য পাওয়া যায়নি।</div>
      @endforelse

      @if(count($days) > 0)
      <div class="card bg-label-primary border border-primary rounded-3 p-3 mt-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-dark small fw-bold">নিয়মিত মিল মোট:</span>
          <span class="fw-bold text-primary">{{ (float)$regTotal == (int)$regTotal ? (int)$regTotal : number_format($regTotal, 1) }} টি</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-dark small fw-bold">নিয়মিত মোট খরচ:</span>
          <span class="fw-bolder text-danger fs-6">৳ {{ number_format($totalCost, 2) }}</span>
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
        <span class="badge bg-label-danger px-3 py-1 fs-7 fw-bold">
          গেস্ট মিল মোট কর্তন: ৳ {{ number_format($totalGuestMealsAmount, 2) }}
        </span>
      </div>

      {{-- Desktop Table View --}}
      <div class="d-none d-md-block table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr class="text-muted small fw-bold text-uppercase">
              <th style="width: 60px;" class="text-center">#</th>
              <th>তারিখ (Date)</th>
              <th>মেহমানের পরিচয় / সম্পর্ক (Guest)</th>
              <th class="text-center">মিলের সংখ্যা</th>
              <th class="text-end pe-4">কর্তনকৃত টাকা (Amount)</th>
              <th>মন্তব্য / নোট</th>
            </tr>
          </thead>
          <tbody>
            @foreach($guestMeals as $gIdx => $gItem)
              <tr>
                <td class="text-center font-monospace text-muted">{{ $gIdx + 1 }}</td>
                <td class="fw-semibold text-dark">
                  <i class="ti ti-calendar me-1 text-primary"></i>
                  {{ \Carbon\Carbon::parse($gItem->date)->format('d M Y') }}
                </td>
                <td>
                  <span class="badge bg-label-info fw-bold fs-7">{{ $gItem->guest_name }}</span>
                </td>
                <td class="text-center fw-bold text-dark">
                  {{ (float)$gItem->meal_count == (int)$gItem->meal_count ? (int)$gItem->meal_count : (float)$gItem->meal_count }} টি
                </td>
                <td class="text-end pe-4 fw-bold text-danger">
                  ৳ {{ number_format($gItem->amount, 2) }}
                </td>
                <td>
                  <small class="text-muted">{{ $gItem->note ?: '—' }}</small>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr class="fw-bold">
              <td colspan="3" class="text-end">গেস্ট মিল মোট:</td>
              <td class="text-center text-primary fs-6">{{ (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : (float)$totalGuestMealsCount }} টি</td>
              <td class="text-end pe-4 text-danger fs-6">৳ {{ number_format($totalGuestMealsAmount, 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Mobile Card View (No Horizontal Scroll) --}}
      <div class="d-md-none p-2 p-sm-3">
        @foreach($guestMeals as $gIdx => $gItem)
        <div class="card border shadow-sm mb-2.5 rounded-3 overflow-hidden">
          <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-label-secondary rounded-circle" style="width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:10px;">{{ $gIdx + 1 }}</span>
              <span class="fw-bold text-dark fs-7">
                <i class="ti ti-calendar me-1 text-primary"></i>
                {{ \Carbon\Carbon::parse($gItem->date)->format('d M Y') }}
              </span>
            </div>
            <span class="badge bg-label-info fw-bold fs-8">{{ $gItem->guest_name }}</span>
          </div>
          <div class="card-body p-2.5">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted d-block">মিলের সংখ্যা:</small>
                <span class="fw-bold text-dark fs-7">{{ (float)$gItem->meal_count == (int)$gItem->meal_count ? (int)$gItem->meal_count : (float)$gItem->meal_count }} টি</span>
              </div>
              <div class="text-end">
                <small class="text-muted d-block">কর্তনকৃত টাকা:</small>
                <span class="fw-bold text-danger fs-7">৳ {{ number_format($gItem->amount, 2) }}</span>
              </div>
            </div>
            @if($gItem->note)
            <div class="mt-2 pt-2 border-top small text-muted">
              <strong>নোট:</strong> {{ $gItem->note }}
            </div>
            @endif
          </div>
        </div>
        @endforeach

        <div class="card bg-label-danger border border-danger rounded-3 p-3 mt-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-dark small fw-bold">গেস্ট মিল মোট:</span>
            <span class="fw-bold text-primary">{{ (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : (float)$totalGuestMealsCount }} টি</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-dark small fw-bold">গেস্ট মিল মোট কর্তন:</span>
            <span class="fw-bolder text-danger fs-6">৳ {{ number_format($totalGuestMealsAmount, 2) }}</span>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
